<?php
class Ngram_ViewerController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('NgramCorpus');
    }

    public function ngramSearchAction()
    {
        $request = $this->getRequest();
        $table = $this->_helper->db;
        $corpus = $table->findById();

        if ($request->isPost()) {
            $queries = array_filter(array_map('trim', explode(',', $request->get('queries'))));

            // Get the ngram statistics for the entire corpus.
            $queryStats = array();
            $totalNgramCounts = array();
            foreach ($queries as $query) {
                $ngramCount = $table->getNgramCount(
                    $corpus->id, $query, $request->get('start'), $request->get('end')
                );
                if ($ngramCount['n'] && !isset($totalNgramCounts[$ngramCount['n']])) {
                    // Get the total ngram count only once per n.
                    $totalNgramCounts[$ngramCount['n']] = $table->getTotalNgramCount(
                        $corpus->id, $ngramCount['n'], $request->get('start'), $request->get('end')
                    );
                }
                $queryStats[$query] = array(
                    'n' => $ngramCount['n'],
                    'count' => (int) $ngramCount['count'],
                    'relative_frequency' => isset($totalNgramCounts[$ngramCount['n']])
                        ? $ngramCount['count'] / $totalNgramCounts[$ngramCount['n']]
                        : null
                );
            }
            uasort($queryStats, function ($a, $b) {
                if ($a['count'] == $b['count']) {
                    return 0;
                }
                return ($a['count'] < $b['count']) ? 1 : -1;
            });

            $corpusStats = array();
            foreach (array(1, 2, 3) as $n) {
                $corpusStats[$n] = array(
                    'total_count' => $table->getTotalNgramCount(
                        $corpus->id, $n, $request->get('start'), $request->get('end')
                    ),
                    'total_unique_count' => $table->getTotalUniqueNgramCount(
                        $corpus->id, $n, $request->get('start'), $request->get('end')
                    ),
                );
            }

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

                if ($data) {
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
            }

            $this->view->queryStats = $queryStats;
            $this->view->corpusStats = $corpusStats;
        }

        $this->view->corpus = $corpus;
        $this->view->queries = $request->get('queries');
        $this->view->start = $request->get('start');
        $this->view->end = $request->get('end');
    }

    public function ngramFrequenciesAction()
    {
        $request = $this->getRequest();
        $table = $this->_helper->db;
        $corpus = $table->findById();

        $n = 1;
        $limit = 100;

        if ($request->isPost()) {
            $n = $request->get('n');
            $limit = $request->get('limit');
            $this->view->ngrams = $table->getNgramsAndCounts($corpus->id, $n, $limit);
            $this->view->totalNgramCount = $table->getTotalNgramCount($corpus->id, $n);
            $this->view->totalUniqueNgramCount = $table->getTotalUniqueNgramCount($corpus->id, $n);
        }

        $this->view->corpus = $corpus;
        $this->view->n = $n;
        $this->view->limit = $limit;
    }
}
