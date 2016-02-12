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
        $this->view->xFormat = '%Y';
        $this->view->xTickFormat = '%Y';
        $this->view->queries = $request->get('queries');
    }
}
