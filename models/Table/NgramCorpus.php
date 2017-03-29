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

    protected function _getColumnPairs()
    {
        return array('id', 'name');
    }

    public function getSelect()
    {
        $select = parent::getSelect();
        $permissions = new Omeka_Db_Select_PublicPermissions('Ngram_Corpora');
        $permissions->apply($select, 'ngram_corpus', null);
        return $select;
    }

    /**
     * Query a corpus ngram.
     *
     * @param int $corpusId The corpus ID
     * @param string $ngram The ngram to query
     * @param null|int $start The range start
     * @param null|int $end The range end
     * @return array
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
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $db->fetchAll($select);
    }

    /**
     * Get the total count for one ngram in a corpus, with or without a sequence.
     *
     * @param int $corpusId The corpus ID
     * @param string $ngram The ngram to query
     * @param null|int $start The range start
     * @param null|int $end The range end
     * @return array
     */
    public function getNgramCount($corpusId, $ngram, $start = null, $end = null)
    {
        $db = $this->getDb();
        $select = $db->select()
            ->from(array('cn' => $db->NgramCorpusNgram), array('count' => 'SUM(cn.match_count)'))
            ->join(array('n' => $db->NgramNgram), 'cn.ngram_id = n.id', array('n.n'))
            ->where('cn.corpus_id = ?', (int) $corpusId)
            ->where('n.ngram =  ?', $ngram);
        if (is_numeric($start)) {
            $select->where('cn.sequence_member >= ?', (int) $start);
        }
        if (is_numeric($end)) {
            $select->where('cn.sequence_member <= ?', (int) $end);
        }
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $db->fetchRow($select);
    }

    /**
     * Get the total count for all ngrams in a corpus, with or without a sequence.
     *
     * @param int $corpusId The corpus ID
     * @param int $n The n to count
     * @param null|int $start The range start
     * @param null|int $end The range end
     * @return int
     */
    public function getTotalNgramCount($corpusId, $n, $start = null, $end = null)
    {
        $db = $this->getDb();
        $select = $db->select()
            ->from($db->NgramCorpusTotalCount, 'SUM(count)')
            ->where('corpus_id = ?', (int) $corpusId)
            ->where('n =  ?', $n);
        if (is_numeric($start)) {
            $select->where('sequence_member >= ?', (int) $start);
        }
        if (is_numeric($end)) {
            $select->where('sequence_member <= ?', (int) $end);
        }
        return $db->fetchOne($select);
    }

    /**
     * Get the total count for all unique ngrams in a corpus, no sequence.
     *
     * @param int $corpusId The corpus ID
     * @param int $n The n to count
     * @param null|int $start The range start
     * @param null|int $end The range end
     * @return int
     */
    public function getTotalUniqueNgramCount($corpusId, $n, $start = null, $end = null)
    {
        $db = $this->getDb();
        $select = $db->select()
            ->from($db->NgramCorpusTotalUniqueCount, 'SUM(count)')
            ->where('corpus_id = ?', (int) $corpusId)
            ->where('n =  ?', $n);
        if (is_numeric($start)) {
            $select->where('sequence_member >= ?', (int) $start);
        }
        if (is_numeric($end)) {
            $select->where('sequence_member <= ?', (int) $end);
        }
        return $db->fetchOne($select);
    }

    /**
     * Get the total counts for one-or-more ngrams in a corpus, no sequence.
     *
     * @param int $corpusId The corpus ID
     * @param int $n The n to count
     * @param null|int $limit The number of ngrams
     * @return array
     */
    public function getNgramsAndCounts($corpusId, $n, $limit = null)
    {
        $db = $this->getDb();
        $select = $db->select()
            ->from(array('cc' => $db->NgramCorpusCount), array('n.ngram', 'cc.count'))
            ->join(array('n' => $db->NgramNgram), 'cc.ngram_id = n.id', array())
            ->where('cc.corpus_id = ?', $corpusId)
            ->where('n.n = ?', $n)
            ->order('cc.count DESC');
        if (is_numeric($limit)) {
            $select->limit($limit);
        }
        return $db->fetchPairs($select);
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
     * Is a ngram generation process available?
     *
     * A ngram generation process is available only when no other process *in
     * any corpus* is currently running.
     *
     * @return bool
     */
    public function processIsAvailable()
    {
        $db = $this->getDb();
        $sql = sprintf('
        SELECT 1
        FROM %s nc
        LEFT JOIN %s p1 ON nc.n1_process_id = p1.id
        LEFT JOIN %s p2 ON nc.n2_process_id = p2.id
        LEFT JOIN %s p3 ON nc.n3_process_id = p3.id
        WHERE p1.status != ?
        OR p2.status != ?
        OR p3.status != ?',
        $db->NgramCorpus,
        $db->Process,
        $db->Process,
        $db->Process);
        return !$db->fetchOne($sql, array(
            Process::STATUS_COMPLETED,
            Process::STATUS_COMPLETED,
            Process::STATUS_COMPLETED,
        ));
    }

    /**
     * Reset problem ngram generation processes.
     *
     * This resets hanging, error, and stopped processes, allowing users to
     * re-generate ngrams if something goes wrong.
     *
     * Becuase of our use of database transactions, we can assume that hanging
     * or error processes result in zero affected rows in the corpus_ngram
     * table, so there's no need to delete anything when resolving processes.
     */
    public function resetProcesses()
    {
        $corpora = $this->getDb()->getTable('NgramCorpus')->findAll();
        foreach ($corpora as $corpus) {
            $processVars = array(
                'N1Process' => 'n1_process_id',
                'N2Process' => 'n2_process_id',
                'N3Process' => 'n3_process_id',
            );
            foreach ($processVars as $nProcess => $nProcessId) {
                $process = $corpus->$nProcess;
                if (Process::STATUS_ERROR === $process->status
                    || Process::STATUS_STOPPED === $process->status
                ) {
                    $corpus->$nProcessId = null;
                } elseif (Process::STATUS_STARTING === $process->status
                    || Process::STATUS_IN_PROGRESS === $process->status
                ) {
                    $corpus->$nProcessId = null;
                    Omeka_Job_Process_Dispatcher::stopProcess($process);
                }
            }
            $corpus->save(false);
        }
    }

    /**
     * Delete corpus ngrams.
     *
     * @param int $corpusId
     */
    public function deleteCorpusNgrams($corpusId)
    {
        $db = $this->getDb();
        $sql = sprintf('DELETE FROM %s WHERE corpus_id = ?', $db->NgramCorpusNgram);
        $db->query($sql, $corpusId);
    }

    /**
     * Delete corpus total counts.
     *
     * @param int $corpusId
     */
    public function deleteCorpusTotalCounts($corpusId)
    {
        $db = $this->getDb();
        $sql = sprintf('DELETE FROM %s WHERE corpus_id = ?', $db->NgramCorpusTotalCount);
        $db->query($sql, $corpusId);
    }

    /**
     * Delete corpus total unique counts.
     *
     * @param int $corpusId
     */
    public function deleteCorpusTotalUniqueCounts($corpusId)
    {
        $db = $this->getDb();
        $sql = sprintf('DELETE FROM %s WHERE corpus_id = ?', $db->NgramCorpusTotalUniqueCount);
        $db->query($sql, $corpusId);
    }

    /**
     * Delete corpus counts.
     *
     * @param int $corpusId
     */
    public function deleteCorpusCounts($corpusId)
    {
        $db = $this->getDb();
        $sql = sprintf('DELETE FROM %s WHERE corpus_id = ?', $db->NgramCorpusCount);
        $db->query($sql, $corpusId);
    }
}
