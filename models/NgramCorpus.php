<?php
class NgramCorpus extends Omeka_Record_AbstractRecord
{
    public $id;
    public $name;
    public $public = 0;
    public $query;
    public $text_element_id;
    public $sequence_element_id;
    public $sequence_type;
    public $sequence_range;
    public $items_pool;
    public $items_corpus;
    public $n1_process_id;
    public $n2_process_id;
    public $n3_process_id;

    protected $_related = array(
        'TextElement' => 'getTextElement',
        'SequenceElement' => 'getSequenceElement',
        'ItemsPool' => 'getItemsPool',
        'ItemsCorpus' => 'getItemsCorpus',
        'Query' => 'getQuery',
        'N1Process' => 'getN1Process',
        'N2Process' => 'getN2Process',
        'N3Process' => 'getN3Process',
    );

    /**
     * Get the element containing item text.
     *
     * @return Element
     */
    public function getTextElement()
    {
        return $this->getTable('Element')->find($this->text_element_id);
    }

    /**
     * Get the element containing sequence text.
     *
     * @return Element
     */
    public function getSequenceElement()
    {
        return $this->getTable('Element')->find($this->sequence_element_id);
    }

    /**
     * Get the sequence type label.
     *
     * @return string
     */
    public function getSequenceTypeLabel()
    {
        return $this->getTable()->getSequenceTypeLabel($this->sequence_type);
    }

    /**
     * Get an array of item IDs in the items pool.
     *
     * The items pool is the pool of items available to the corpus, following
     * the query and the existence of a text and sequence value.
     *
     * @return array
     */
    public function getItemsPool()
    {
        return json_decode($this->items_pool, true);
    }

    /**
     * Get all item IDs and sequence members in this corpus.
     *
     * @return array
     */
    public function getItemsCorpus()
    {
        return json_decode($this->items_corpus, true);
    }

    /**
     * Get the query used to fetch the item pool.
     *
     * @return array
     */
    public function getQuery()
    {
        parse_str($this->query, $query);
        return $query;
    }

    /**
     * Get the process responsible for generating unigrams.
     *
     * @return Process
     */
    public function getN1Process()
    {
        return $this->getTable('Process')->find($this->n1_process_id);
    }

    /**
     * Get the process responsible for generating bigrams.
     *
     * @return Process
     */
    public function getN2Process()
    {
        return $this->getTable('Process')->find($this->n2_process_id);
    }

    /**
     * Get the process responsible for generating trigrams.
     *
     * @return Process
     */
    public function getN3Process()
    {
        return $this->getTable('Process')->find($this->n3_process_id);
    }

    /**
     * Is this a sequenced corpus?
     *
     * @return bool
     */
    public function isSequenced()
    {
        return (bool) $this->sequence_element_id;
    }

    /**
     * Does this corpus have a valid text element.
     *
     * A corpus text element is valid if it's the one currently set in plugin
     * configuration.
     *
     * @return bool
     */
    public function hasValidTextElement()
    {
        return $this->text_element_id === (int) get_option('ngram_text_element_id');
    }

    /**
     * Can a user delete this corpus?
     *
     * A user cannot delete a corpus if a ngram generation process is running.
     *
     * @return bool
     */
    public function canDelete()
    {
        return $this->getTable()->processIsAvailable();
    }

    /**
     * Can a user validate items?
     *
     * @return bool
     */
    public function canValidateItems()
    {
        return $this->hasValidTextElement()
            && $this->ItemsPool
            && !$this->ItemsCorpus;
    }

    /**
     * Can a user generate unigrams?
     *
     * @return bool
     */
    public function canGenerateN1grams()
    {
        return $this->getTable()->processIsAvailable()
            && $this->hasValidTextElement()
            && $this->ItemsCorpus
            && !$this->N1Process;
    }

    /**
     * Can a user generate bigrams?
     *
     * @return bool
     */
    public function canGenerateN2grams()
    {
        return $this->getTable()->processIsAvailable()
            && $this->hasValidTextElement()
            && $this->ItemsCorpus
            && !$this->N2Process;
    }

    /**
     * Can a user generate trigrams?
     *
     * @return bool
     */
    public function canGenerateN3grams()
    {
        return $this->getTable()->processIsAvailable()
            && $this->hasValidTextElement()
            && $this->ItemsCorpus
            && !$this->N3Process;
    }

    public function getRecordUrl($action = 'show')
    {
        return url(array(
            'module' => 'ngram',
            'controller' => 'corpora',
            'action' => $action,
            'id' => $this->id
        ));
    }

    protected function _validate() {
        if ('' === trim($this->name)) {
            $this->addError('name', 'A name is required');
        }
        if ($this->sequence_element_id && !$this->getTable('Element')->exists($this->sequence_element_id)) {
            $this->addError('Sequence Element', 'Invalid sequence element');
        }
        if ($this->sequence_type && !$this->getTable()->sequenceTypeExists($this->sequence_type)) {
            $this->addError('Sequence Type', 'Invalid sequence type');
        }
        if ($this->sequence_range && !preg_match('/^[^\s-]+-[^\s-]+$/', $this->sequence_range)) {
            $this->addError('Sequence Range', 'Invalid sequence range');
        }
        if ($this->sequence_element_id && !$this->sequence_type) {
            $this->addError('Sequence Type', 'Sequence must have a type');
        }
        if ($this->sequence_type && !$this->sequence_element_id) {
            $this->addError('Sequence Element', 'Sequence must have an element');
        }
    }

    protected function beforeSave($args)
    {
        if ($args['insert']) {
            // Set the text element ID on insert.
            $this->text_element_id = get_option('ngram_text_element_id');
        }

        if (!$this->items_corpus) {
            // Retrieve and set the item pool.
            $query = $this->Query;
            // Items might be described by the corpus sequence element.
            if ($this->sequence_element_id) {
                $query['advanced'][] = array(
                    'element_id' => $this->sequence_element_id,
                    'type' => 'is not empty',
                );
            }
            // Items must be described by the corpus text element.
            $query['advanced'][] = array(
                'element_id' => get_option('ngram_text_element_id'),
                'type' => 'is not empty',
            );

            // Fetch only the Item record IDs to avoid reaching the memory limit
            // for large item pools.
            $table = $this->getTable('Item');
            $select = $table->getSelectForFindBy($query)
                ->reset(Zend_Db_Select::COLUMNS)
                ->from(array(), 'items.id');
            $itemIds = $table->fetchCol($select);
            $this->items_pool = json_encode($itemIds);
        }

        if (!$this->query) {
            $this->query = null;
        }
        if (!$this->sequence_element_id) {
            $this->sequence_element_id = null;
        }
        if (!$this->sequence_type) {
            $this->sequence_type = null;
        }
        if (!$this->sequence_range) {
            $this->sequence_range = null;
        }
    }

    protected function beforeDelete()
    {
        $this->getTable()->deleteCorpusNgrams($this->id);
        $this->getTable()->deleteCorpusTotalCounts($this->id);
        $this->getTable()->deleteCorpusTotalUniqueCounts($this->id);
        $this->getTable()->deleteCorpusCounts($this->id);
    }
}
