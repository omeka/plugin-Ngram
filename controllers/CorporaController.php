<?php
class Ngram_CorporaController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        if ('no-text-element' !== $this->getRequest()->getActionName()
            && !get_option('ngram_text_element_id')
        ) {
            // A text element must be set to use this module.
            $this->_helper->redirector('no-text-element');
        }
        $this->_helper->db->setDefaultModelName('NgramCorpus');
    }

    public function noTextElementAction()
    {}

    public function addAction()
    {
        parent::addAction();

        $table = $this->_helper->db;
        $this->view->corpus = $this->view->ngram_corpu; // correct poor inflection
        $this->view->textElement = $table->getTable('Element')->find(get_option('ngram_text_element_id'));
        $this->view->sequenceTypeOptions = $table->getTable()->getSequenceTypesForSelect();
        $this->view->sequenceElementOptions = $table->getTable()->getElementsForSelect();
    }

    public function editAction()
    {
        parent::editAction();

        $table = $this->_helper->db;
        $this->view->corpus = $this->view->ngram_corpu; // correct poor inflection
        $this->view->textElement = $this->view->corpus->TextElement;
        $this->view->sequenceTypeOptions = $table->getTable()->getSequenceTypesForSelect();
        $this->view->sequenceElementOptions = $table->getTable()->getElementsForSelect();
    }

    public function showAction()
    {
        parent::showAction();

        $this->view->corpus = $this->view->ngram_corpu; // correct poor inflection
    }

    public function generateNgramsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $n = $request->getPost('n');
            $corpus = $this->_helper->db->findById();
            $process = Omeka_Job_Process_Dispatcher::startProcess(
                'Process_GenerateNgrams', null, array('corpus_id' => $corpus->id, 'n' => $n)
            );
            $processProperty = "n{$n}_process_id";
            $corpus->$processProperty = $process->id;
            $corpus->save(true);
            $this->_helper->flashMessenger(
            'The corpus ngrams are now being generated. This may take some time. '
            . 'Feel free to navigate away from this page and close your browser. '
            . 'Refresh this page to see if the process is complete.', 'success');
            $this->_helper->redirector('show', null, null, array('id' => $corpus->id));
        }
        $this->_helper->redirector('browse');
    }

    public function validateNonsequenceAction()
    {
        $table = $this->_helper->db;
        $db = $table->getDb();
        $corpus = $table->findById();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $corpus->items_corpus = $corpus->items_pool;
            $corpus->save(false);
            $this->_helper->flashMessenger('The items were successfully accepted. You may now generate unigrams and bigrams.', 'success');
            $this->_helper->redirector('show', null, null, array('id' => $corpus->id));
        }

        $this->view->corpus = $corpus;
    }

    public function validateSequenceAction()
    {
        $table = $this->_helper->db;
        $db = $table->getDb();
        $corpus = $table->findById();

        // Query the database directly to get sequence element text. This
        // reduces the overhead that would otherwise be required to cache all
        // element texts.
        $sql = sprintf(
        'SELECT i.id, et.text
        FROM %s i JOIN %s et
        ON i.id = et.record_id
        WHERE i.id IN (%s)
        AND et.element_id = %s
        GROUP BY i.id',
        $db->Item,
        $db->ElementText,
        $db->quote(json_decode($corpus->items_pool, true)),
        $db->quote($corpus->sequence_element_id));
        $sequenceTexts = $db->fetchPairs($sql);

        // Set the range and validate the sequence text.
        $validator = $table->getCorpusValidator($corpus->sequence_type);
        if ($corpus->sequence_range) {
            $range = explode('-', $corpus->sequence_range);
            $validator->setRange($range[0], $range[1]);
        }
        foreach ($sequenceTexts as $id => $text) {
            $validator->addItem($id, $text);
        }

        $validItems = $validator->getValidItems();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $corpus->items_corpus = json_encode($validItems);
            $corpus->save(false);
            $this->_helper->flashMessenger('The valid items were successfully accepted. You may now generate unigrams and bigrams.', 'success');
            $this->_helper->redirector('show', null, null, array('id' => $corpus->id));
        }

        // Prepare valid items.
        natcasesort($validItems);
        foreach ($validItems as $id => $sequenceMember) {
            $validItems[$id] = array(
                'member' => $sequenceMember,
                'text' => $sequenceTexts[$id],
            );
        }

        // Prepare out of range items.
        $outOfRangeItems = $validator->getOutOfRangeItems();
        natcasesort($outOfRangeItems);
        foreach ($outOfRangeItems as $id => $sequenceMember) {
            $outOfRangeItems[$id] = array(
                'member' => $sequenceMember,
                'text' => $sequenceTexts[$id],
            );
        }

        // Prepare invalid items.
        $invalidItems = array();
        foreach ($validator->getInvalidItems() as $id) {
            $invalidItems[$id] = $sequenceTexts[$id];
        }
        natcasesort($invalidItems);

        $this->view->corpus = $corpus;
        $this->view->validItems = $validItems;
        $this->view->invalidItems = $invalidItems;
        $this->view->outOfRangeItems = $outOfRangeItems;
    }

    protected function _redirectAfterAdd($corpus)
    {
        $this->_helper->redirector('show', null, null, array('id' => $corpus->id));
    }

    protected function _redirectAfterEdit($corpus)
    {
        $this->_helper->redirector('show', null, null, array('id' => $corpus->id));
    }

    protected function _getAddSuccessMessage($record)
    {
        return sprintf('The corpus "%s" was sucessfully added.', $record->name);
    }

    protected function _getEditSuccessMessage($record)
    {
        return sprintf('The corpus "%s" was sucessfully edited.', $record->name);;
    }

    protected function _getDeleteConfirmMessage($record)
    {
        return sprintf('This will delete the corpus "%s" and all its associated ngrams.', $record->name);
    }
}
