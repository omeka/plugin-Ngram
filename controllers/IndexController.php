<?php
class Ngram_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();
        $table = $this->_helper->db;
        $ngramCorpusTable = $table->getTable('NgramCorpus');

        if ($request->isPost()) {
            $corpusId = $request->get('corpus_id');
            $queries = array_map('trim', explode(',', $request->get('queries')));
            $corpus = $ngramCorpusTable->find($corpusId);

            // Query each string and combine the results.
            $data = array();
            foreach ($queries as $query) {
                $results = $ngramCorpusTable->query($corpusId, $query,
                    $request->get('start'), $request->get('end'));
                foreach ($results as $result) {
                    $data[$result[0]][$query] = $result[1];
                }
            }

            if ($data) {
                // Sort to get accurate range start and end.
                ksort($data);

                // Fill gaps in the the sequence.
                $seqMems = array_keys($data);
                $seqFiller = $ngramCorpusTable->getSequenceFiller($corpus->sequence_type);
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

                $this->view->json = $json;
                $this->view->dataKeysValue = $queries;
                $this->view->graphConfig = $ngramCorpusTable->getSequenceTypeGraphConfig($corpus->sequence_type);
            }
        }

        $this->view->queries = $request->get('queries');
        $this->view->start = $request->get('start');
        $this->view->end = $request->get('end');
        $this->view->corpusId = $request->get('corpus_id');
        $this->view->corporaOptions = $ngramCorpusTable->findPairsForSelectForm(array('public' => 1));
    }
}
