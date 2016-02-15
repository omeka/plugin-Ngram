<?php
class Ngram_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();
        $corpusId = $request->get('corpus_id');
        $queries = array_map('trim', explode(',', $request->get('queries')));

        $table = $this->_helper->db;
        $ngramCorpusTable = $table->getTable('NgramCorpus');
        $corpus = $ngramCorpusTable->find($corpusId);

        // Query each string and combine the results.
        $data = array();
        foreach ($queries as $query) {
            $results = $ngramCorpusTable->query($corpusId, $query);
            foreach ($results as $result) {
                $data[$result[0]][$query] = $result[1];
            }
        }

        // Sort and define the sequence range.
        ksort($data);
        $seqMems = array_keys($data);
        $seqRangeStart = reset($seqMems);
        $seqRangeEnd = end($seqMems);

        // Fill in range gaps and set C3 variables, according to sequence type.
        $xFormat = null;
        $xTickFormat = null;
        $seqRange = array();
        switch ($corpus->sequence_type) {
            case 'year':
                $xFormat = '%Y';
                $xTickFormat = '%Y';
                $period = new DatePeriod(
                    DateTime::createFromFormat('Y', $seqRangeStart),
                    new DateInterval('P1Y'),
                    DateTime::createFromFormat('Y', $seqRangeEnd)
                );
                foreach ($period as $date) {
                    $seqRange[] = $date->format('Y');
                }
                break;
            case 'month':
                $xFormat = '%Y%m';
                $xTickFormat = '%Y-%m';
                $period = new DatePeriod(
                    DateTime::createFromFormat('Ym', $seqRangeStart),
                    new DateInterval('P1M'),
                    DateTime::createFromFormat('Ym', $seqRangeEnd)
                );
                foreach ($period as $date) {
                    $seqRange[] = $date->format('Ym');
                }
                break;
            case 'day':
                $xFormat = '%Y%m%d';
                $xTickFormat = '%Y-%m-%d';
                $period = new DatePeriod(
                    DateTime::createFromFormat('Ymd', $seqRangeStart),
                    new DateInterval('P1D'),
                    DateTime::createFromFormat('Ymd', $seqRangeEnd)
                );
                foreach ($period as $date) {
                    $seqRange[] = $date->format('Ymd');
                }
                break;
            case 'numeric':
                // @todo
                break;
            default:
        }
        $data = $data + array_flip($seqRange);

        // Build JSON for C3.
        $json = array();
        foreach ($data as $seqMem => $relFreqs) {
            $jsonPart = array(
                'x' => (string) $seqMem,
            );
            foreach ($queries as $query) {
                if (isset($relFreqs[$query])) {
                    $jsonPart[$query] = $relFreqs[$query];
                } else {
                    $jsonPart[$query] = 0;
                }
            }
            $json[] = $jsonPart;
        }

        $this->view->json = $json;
        $this->view->keysValue = $queries;
        $this->view->xFormat = $xFormat;
        $this->view->xTickFormat = $xTickFormat;
        $this->view->queries = $request->get('queries');
    }
}
