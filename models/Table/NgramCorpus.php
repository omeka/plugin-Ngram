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

    /**
     * Query a corpus ngram.
     *
     * @param int $corpusId The corpus ID
     * @param string $ngram The ngram to query
     * @param null|int $start The range start
     * @param null|int $end The range end
     */
    public function query($corpusId, $ngram, $start = null, $end = null)
    {
        $db = $this->getDb();
        $select = $db->select()
            ->from(
                array('cn' => $db->NgramCorpusNgram),
                array('cn.sequence_member', 'cn.relative_frequency')
            )
            ->join(array('n' => $db->NgramNgram), 'cn.ngram_id = n.id', array())
            ->where('cn.corpus_id = ?', (int) $corpusId)
            ->where('n.ngram =  ?', $ngram)
            ->order('cn.sequence_member');
        if (is_numeric($start)) {
            $select->where('cn.sequence_member >= ?', (int) $start);
        }
        if (is_numeric($end)) {
            $select->where('cn.sequence_member <= ?', (int) $end);
        }
        $db->setFetchMode(Zend_Db::FETCH_NUM);
        return $db->fetchAll($select);
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

    /**
     * Is a ngram generation process available?
     *
     * Users can run a process only when no other ngram generation process
     * *in any corpus* is currently being run.
     *
     * @return bool
     */
    public function processIsAvailable()
    {
        $db = $this->getDb();
        $sql = sprintf('
        SELECT 1
        FROM omeka_ngram_corpus nc
        LEFT JOIN omeka_processes p1 ON nc.n1_process_id = p1.id
        LEFT JOIN omeka_processes p2 ON nc.n2_process_id = p2.id
        WHERE p1.status != ?
        OR p2.status != ?',
        $db->NgramCorpus,
        $db->Process,
        $db->Process);
        return !$db->fetchOne($sql, array(
            Process::STATUS_COMPLETED,
            Process::STATUS_COMPLETED
        ));
    }
}
