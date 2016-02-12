<?php
class Ngram_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();
        $corpusId = $request->get('corpus_id');
        $query = $request->get('query');

        $table = $this->_helper->db;
        $ngramCorpusTable = $table->getTable('NgramCorpus');
        $corpus = $ngramCorpusTable->find($corpusId);

        $data = $ngramCorpusTable->query($corpusId, $query);
        array_unshift($data, array('x', $query)); // prepend headers (for c3)

        $this->view->data = json_encode($data);
        $this->view->xFormat = json_encode('%Y%m');
        $this->view->xTickFormat = json_encode('%Y-%m');
    }
}
