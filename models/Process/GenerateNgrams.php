<?php
class Process_GenerateNgrams extends Omeka_Job_Process_AbstractProcess
{
    /**
     * @var array
     */
    protected $_sequenceNgramCounts;

    /**
     * @var array
     */
    protected $_totalSequenceNgramCounts;

    /**
     * @var array
     */
    protected $_ngramCounts;

    /**
     * @var int
     */
    protected $_totalNgramCount;

    public function run($args)
    {
        // Raise the memory limit.
        ini_set('memory_limit', '1G');

        $db = get_db();
        $corpus = $db->getTable('NgramCorpus')->find($args['corpus_id']);
        $textElementId = get_option('ngram_text_element_id');
        $n = $args['n'];

        /**
         * First derive and store the discrete ngrams and item ngrams.
         */

        $selectItemSql = sprintf('
        SELECT ng.id
        FROM %s ing
        JOIN %s ng
        ON ing.ngram_id = ng.id
        WHERE ing.item_id = ? 
        AND ng.n = %s',
        $db->NgramItemNgram,
        $db->NgramNgram,
        $db->quote($n, Zend_Db::INT_TYPE));

        $selectTextSql = sprintf('
        SELECT et.text
        FROM %s i
        JOIN %s et
        ON i.id = et.record_id
        WHERE et.record_id = ?
        AND et.element_id = %s',
        $db->Item,
        $db->ElementText,
        $db->quote($textElementId, Zend_Db::INT_TYPE));

        $ngramsSql = sprintf('
        INSERT INTO %s (ngram, n) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
        $db->NgramNgram);

        $itemNgramsSql = sprintf('
        INSERT INTO %s (ngram_id, item_id) VALUES (?, ?)',
        $db->NgramItemNgram);

        $db->beginTransaction();
        try {
            // Iterate sequenced items.
            foreach ($corpus->ItemsCorpus as $key => $value) {

                // Account for different storage formats for sequenced and
                // unsequenced corpora.
                $itemId = $corpus->isSequenced() ? $key : $value;
                $sequenceMember = $corpus->isSequenced() ? $value : null;

                // Do not re-generate item ngrams for the current n.
                $ngramIds = $db->query($selectItemSql, $itemId)->fetchAll(Zend_Db::FETCH_COLUMN, 0);
                if ($ngramIds) {
                    foreach ($ngramIds as $ngramId) {
                        $this->_incrementCount($ngramId, $sequenceMember);
                    }
                    continue;
                }

                // Get the item text.
                $stmt = $db->query($selectTextSql, $itemId);
                $text = new Ngram_Text($stmt->fetchColumn(0));

                // Iterate item ngrams.
                foreach ($text->getNgrams($n) as $ngram) {
                    $db->query($ngramsSql, array($ngram, $n));
                    $ngramId = $db->lastInsertId();
                    $db->query($itemNgramsSql, array($ngramId, $itemId));
                    $this->_incrementCount($ngramId, $sequenceMember);
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        /**
         * Now, calculate and store the corpus ngrams and total ngram count.
         */

        $corpusNgramsSql = sprintf('
        INSERT INTO %s (
            corpus_id, ngram_id, sequence_member, match_count, relative_frequency
        ) VALUES (
            %s, ?, ?, ?, ?
        )',
        $db->NgramCorpusNgram,
        $corpus->id);

        $corpusTotalCountsSql = sprintf('
        INSERT INTO %s (corpus_id, n, sequence_member, count) VALUES (%s, %s, ?, ?)',
        $db->NgramCorpusTotalCount,
        $corpus->id,
        $db->quote($n, Zend_Db::INT_TYPE));

        $corpusCountsSql = sprintf('
        INSERT INTO %s (corpus_id, ngram_id, count) VALUES (%s, ?, ?)',
        $db->NgramCorpusCount,
        $corpus->id);

        $db->beginTransaction();
        try {
            if ($corpus->isSequenced()) {
                foreach ($this->_sequenceNgramCounts as $sequenceMember => $ngrams) {
                    foreach ($ngrams as $ngramId => $count) {
                        $db->query($corpusNgramsSql, array(
                            $ngramId,
                            $sequenceMember,
                            $count,
                            $count / $this->_totalSequenceNgramCounts[$sequenceMember],
                        ));
                    }
                }
                foreach ($this->_ngramCounts as $ngramId => $count) {
                    $db->query($corpusCountsSql, array($ngramId, $count));
                }
                foreach ($this->_totalSequenceNgramCounts as $sequenceMember => $count) {
                    $db->query($corpusTotalCountsSql, array($sequenceMember, $count));
                }
            } else {
                foreach ($this->_ngramCounts as $ngramId => $count) {
                    $db->query($corpusNgramsSql, array(
                        $ngramId,
                        null,
                        $count,
                        $count / $this->_totalNgramCount,
                    ));
                    $db->query($corpusCountsSql, array($ngramId, $count));
                }
                $db->query($corpusTotalCountsSql, array(null, $this->_totalNgramCount));
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        _log(sprintf(
            'Peak usage for corpus #%s (n=%s): %s',
            $corpus->id, $n, memory_get_peak_usage()
        ));
    }

    /**
     * Increment ngram counts.
     *
     * A null sequence member indicates that this corpus is unsequenced;
     * otherwise it is sequenced.
     *
     * @param int $ngramId
     * @param null|string $sequenceMember
     */
    protected function _incrementCount($ngramId, $sequenceMember = null)
    {
        if (!isset($this->_ngramCounts[$ngramId])) {
            $this->_ngramCounts[$ngramId] = 0;
        }
        $this->_ngramCounts[$ngramId]++;

        if (!isset($this->_totalNgramCount)) {
            $this->_totalNgramCount = 0;
        }
        $this->_totalNgramCount++;

        if (null !== $sequenceMember) {
            // This is a sequenced corpus.
            if (!isset($this->_sequenceNgramCounts[$sequenceMember])) {
                $this->_sequenceNgramCounts[$sequenceMember] = array();
            }
            if (!isset($this->_sequenceNgramCounts[$sequenceMember][$ngramId])) {
                $this->_sequenceNgramCounts[$sequenceMember][$ngramId] = 0;
            }
            $this->_sequenceNgramCounts[$sequenceMember][$ngramId]++;

            if (!isset($this->_totalSequenceNgramCounts[$sequenceMember])) {
                $this->_totalSequenceNgramCounts[$sequenceMember] = 0;
            }
            $this->_totalSequenceNgramCounts[$sequenceMember]++;
        }
    }
}
