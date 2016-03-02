<?php
class Ngram_ViewerController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('NgramCorpus');
    }

    public function searchAction()
    {
        $request = $this->getRequest();
        $table = $this->_helper->db;
        $corpus = $table->findById();

        if ($request->isPost()) {
            $queries = array_filter(array_map('trim', explode(',', $request->get('queries'))));

            // Get the ngram statistics for the entire corpus.
            $queryStats = array();
            foreach ($queries as $query) {
                $ngramCount = $table->getNgramCount(
                    $corpus->id, $query, $request->get('start'), $request->get('end')
                );
                if ($ngramCount['n']) {
                    $totalNgramCount = $table->getTotalNgramCount(
                        $corpus->id, $ngramCount['n'], $request->get('start'), $request->get('end')
                    );
                } else {
                    $totalNgramCount = 0;
                }
                $queryStats[$query] = array(
                    'n' => $ngramCount['n'],
                    'count' => (int) $ngramCount['count'],
                    'relative_frequency' => $totalNgramCount ? $ngramCount['count'] / $totalNgramCount : null
                );
            }
            uasort($queryStats, function ($a, $b) {
                if ($a['count'] == $b['count']) {
                    return 0;
                }
                return ($a['count'] < $b['count']) ? 1 : -1;
            });

            if ($queryStats) {

                if ($corpus->isSequenced()) {
                    // Query each string and combine the results.
                    $data = array();
                    foreach ($queries as $query) {
                        $results = $table->query($corpus->id, $query,
                            $request->get('start'), $request->get('end'));
                        foreach ($results as $result) {
                            $data[$result['sequence_member']][$query] = $result['relative_frequency'];
                        }
                    }

                    // Sort to get accurate range start and end.
                    ksort($data);

                    // Fill gaps in the the sequence.
                    $seqMems = array_keys($data);
                    $seqFiller = $table->getSequenceFiller($corpus->sequence_type);
                    $filledSeq = $seqFiller->getFilledSequence(reset($seqMems), end($seqMems));
                    $data = $data + array_flip($filledSeq);

                    // Sort to ensure filled sequence is ordered properly.
                    ksort($data);

                    // Build JSON for C3 graph.
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

                    $this->view->dataJson = $json;
                    $this->view->dataKeysValue = $queries;
                    $this->view->graphConfig = $table->getSequenceTypeGraphConfig($corpus->sequence_type);
                }
                $this->view->queryStats = $queryStats;
            }
        }

        $this->view->corpus = $corpus;
        $this->view->queries = $request->get('queries');
        $this->view->start = $request->get('start');
        $this->view->end = $request->get('end');
    }
}
