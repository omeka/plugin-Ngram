<?php
class NgramCorpus extends Omeka_Record_AbstractRecord
{
    public $id;
    public $name;
    public $query;
    public $text_element_id;
    public $sequence_element_id;
    public $sequence_type;
    public $sequence_range;
    public $items_pool;
    public $items_corpus;

    protected $_related = array(
        'TextElement' => 'getTextElement',
        'SequenceElement' => 'getSequenceElement',
        'ItemsPool' => 'getItemsPool',
        'ItemsCorpus' => 'getItemsCorpus',
        'Query' => 'getQuery',
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
     * Can a user edit this corpus?
     *
     * A user cannot edit a corpus if the items have been validated.
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->hasValidTextElement() && !$this->ItemsCorpus;
    }

    /**
     * Can a user validate items?
     *
     * @return bool
     */
    public function canValidateItems()
    {
        return $this->hasValidTextElement() && $this->ItemsPool && !$this->ItemsCorpus;
    }

    /**
     * Can a user generate ngrams?
     *
     * @return bool
     */
    public function canGenerateNgrams()
    {
        return $this->hasValidTextElement() && $this->ItemsCorpus;
    }

    public function getRecordUrl($action = 'show')
    {
        return url(array('action' => $action, 'id' => $this->id), 'ngramId');
    }

    protected function _validate() {
        if ('' === trim($this->name)) {
            $this->addError('name', 'A name is required');
        }
        if (!$this->getTable('Element')->exists($this->sequence_element_id)) {
            $this->addError('Sequence Element', 'Invalid sequence element');
        }
        if (!$this->getTable()->sequenceTypeExists($this->sequence_type)) {
            $this->addError('Sequence Type', 'Invalid sequence type');
        }
        if ($this->sequence_range && !preg_match('/^[^\s-]+-[^\s-]+$/', $this->sequence_range)) {
            $this->addError('Sequence Range', 'Invalid sequence range');
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
            // Items must be described by the corpus sequence element.
            $query['advanced'][] = array(
                'element_id' => $this->sequence_element_id,
                'type' => 'is not empty',
            );
            // Items must be described by the corpus text element.
            $query['advanced'][] = array(
                'element_id' => get_option('ngram_text_element_id'),
                'type' => 'is not empty',
            );
            $items = $this->getTable('Item')->findBy($query);
            $itemIds = array();
            foreach ($items as $item) {
                $itemIds[] = $item->id;
            }
            $this->items_pool = json_encode($itemIds);
        }
    }
}
