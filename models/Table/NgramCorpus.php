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
            'filler' => 'Ngram_SequenceFiller_Year',
            'graphConfig' => array(
                'dataXFormat' => '%Y',
                'axisXType' => 'timeseries',
                'axisXTickCount' => 8,
                'axisXTickFormat' => '%Y',
            ),
        ),
        'month' => array(
            'label' => 'Date by month',
            'validator' => 'Ngram_CorpusValidator_Month',
            'filler' => 'Ngram_SequenceFiller_Month',
            'graphConfig' => array(
                'dataXFormat' => '%Y%m',
                'axisXType' => 'timeseries',
                'axisXTickCount' => 8,
                'axisXTickFormat' => '%Y-%m',
            ),
        ),
        'day' => array(
            'label' => 'Date by day',
            'validator' => 'Ngram_CorpusValidator_Day',
            'filler' => 'Ngram_SequenceFiller_Day',
            'graphConfig' => array(
                'dataXFormat' => '%Y%m%d',
                'axisXType' => 'timeseries',
                'axisXTickCount' => 8,
                'axisXTickFormat' => '%Y-%m-%d',
            ),
        ),
        'numeric' => array(
            'label' => 'Numerical',
            'validator' => 'Ngram_CorpusValidator_Numeric',
            'filler' => 'Ngram_SequenceFiller_Numeric',
            'graphConfig' => array(
                'dataXFormat' => null,
                'axisXType' => 'category',
                'axisXTickCount' => null,
                'axisXTickFormat' => null,
            ),
        ),
    );

    public function query($corpusId, $query)
    {
        $db = $this->getDb();
        $sql = sprintf('
        SELECT cn.sequence_member, cn.relative_frequency
        FROM %s cn
        JOIN %s n ON cn.ngram_id = n.id
        WHERE cn.corpus_id = ?
        AND n.ngram =  ?
        ORDER BY cn.sequence_member',
        $db->NgramCorpusNgram,
        $db->NgramNgram);
        $db->setFetchMode(Zend_Db::FETCH_NUM);
        return $db->fetchAll($sql, array($corpusId, $query));
    }

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
     * Get the graph configuration for a sequence type.
     *
     * @param string $sequenceType
     * @return array
     */
    public function getSequenceTypeGraphConfig($sequenceType)
    {
        return $this->_sequenceTypes[$sequenceType]['graphConfig'];
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
     * Get a sequence filler by sequence type.
     *
     * @param string $sequenceType
     * @return Ngram_SequenceFiller_SequenceFillerInterface
     */
    public function getSequenceFiller($sequenceType)
    {
        $class = $this->_sequenceTypes[$sequenceType]['filler'];
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
     * Get elements array used as select options.
     *
     * @return array
     */
    public function getElementsForSelect()
    {
        $db = $this->getDb();
        $sql = sprintf('
        SELECT es.name element_set_name, e.id element_id, e.name element_name
        FROM %s es
        JOIN %s e ON es.id = e.element_set_id
        LEFT JOIN %s ite ON e.id = ite.element_id
        WHERE es.record_type IS NULL OR es.record_type = "Item"
        ORDER BY es.name, e.name',
        $db->ElementSet,
        $db->Element,
        $db->ItemTypesElements);

        $options = array('' => 'Select Below');
        foreach ($db->fetchAll($sql) as $element) {
            $optGroup = __($element['element_set_name']);
            $value = __($element['element_name']);
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }

    /**
     * Get corpora array used as select options.
     *
     * @return array
     */
    public function getCorporaForSelect()
    {
        $db = $this->getDb();
        $sql = sprintf('SELECT id, name FROM %s', $db->NgramCorpus);
        return $db->fetchPairs($sql);
    }
}
