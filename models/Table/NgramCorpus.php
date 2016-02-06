<?php
class Table_NgramCorpus extends Omeka_Db_Table
{
    /**
     * @var array Sequence type configuration.
     */
    protected $_sequenceTypes = array(
        'year' => array(
            'label' => 'Date by year',
            'validator' => 'Ngram_CorpusValidator_Year',
        ),
        'month' => array(
            'label' => 'Date by month',
            'validator' => 'Ngram_CorpusValidator_Month',
        ),
        'day' => array(
            'label' => 'Date by day',
            'validator' => 'Ngram_CorpusValidator_Day',
        ),
        'alpha' => array(
            'label' => 'Alphabetical',
            'validator' => 'Ngram_CorpusValidator_Alpha',
        ),
        'numeric' => array(
            'label' => 'Numerical',
            'validator' => 'Ngram_CorpusValidator_Numeric',
        ),
    );

    /**
     * Does this sequence type exist?
     *
     * @param string $sequenceType
     * @return bool
     */
    public function sequenceTypeExists($sequenceType)
    {
        return (bool) isset($this->_sequenceTypes[$sequenceType]);
    }

    /**
     * Get the label for a sequence type.
     *
     * @param string $sequenceType
     * @return string
     */
    public function getSequenceTypeLabel($sequenceType)
    {
        return $this->_sequenceTypes[$sequenceType]['label'];
    }

    /**
     * Get a corpus validator by sequence type.
     *
     * @param string $sequenceType
     * @return Ngram_CorpusValidator_CorpusValidatorInterface
     */
    public function getCorpusValidator($sequenceType)
    {
        $class = $this->_sequenceTypes[$sequenceType]['validator'];
        return class_exists($class) ? new $class : null;
    }

    /**
     * Get sequence types array used as select options.
     *
     * @return array
     */
    public function getSequenceTypesForSelect()
    {
        $options = array('' => 'Select Below');
        foreach ($this->_sequenceTypes as $key => $value) {
            $options[$key] = $value['label'];
        }
        return $options;
    }

    /**
     * Find all elements and their element sets.
     *
     * @return array
     */
    public function findElements()
    {
        $db = $this->getDb();
        $select = $db->select()
            ->from(
                array('element_sets' => $db->ElementSet),
                array('element_set_name' => 'element_sets.name')
            )->join(
                array('elements' => $db->Element),
                'element_sets.id = elements.element_set_id',
                array('element_id' =>'elements.id',
                'element_name' => 'elements.name')
            )->joinLeft(
                array('item_types_elements' => $db->ItemTypesElements),
                'elements.id = item_types_elements.element_id',
                array()
            )->where('element_sets.record_type IS NULL OR element_sets.record_type = "Item"')
            ->order(array('element_sets.name', 'elements.name'));
        return $db->fetchAll($select);
    }

    /**
     * Get elements array used as select options.
     *
     * @return array
     */
    public function getElementsForSelect()
    {
        $options = array('' => 'Select Below');
        foreach ($this->findElements() as $element) {
            $optGroup = __($element['element_set_name']);
            $value = __($element['element_name']);
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }
}
