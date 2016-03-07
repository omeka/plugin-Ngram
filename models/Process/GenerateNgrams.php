<?php
class Process_GenerateNgrams extends Omeka_Job_Process_AbstractProcess
{
    /**
     * @var array
     */
    protected $_ngramCount;

    /**
     * @var int|array
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
                        $this->_incrementNgramCount($ngramId, $sequenceMember);
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
                    $this->_incrementNgramCount($ngramId, $sequenceMember);
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        /**
         * Now, calculate and store the corpus ngrams.
         */

        $corpusNgramsSql = sprintf('
        INSERT INTO %s (
            corpus_id, ngram_id, sequence_member, match_count, relative_frequency
        ) VALUES (
            %s, ?, ?, ?, ?
        )',
        $db->NgramCorpusNgram,
        $corpus->id);

        $db->beginTransaction();
        try {
            if ($corpus->isSequenced()) {
                foreach ($this->_ngramCount as $sequenceMember => $ngrams) {
                    foreach ($ngrams as $ngramId => $matchCount) {
                        $db->query($corpusNgramsSql, array(
                            $ngramId,
                            $sequenceMember,
                            $matchCount,
                            $matchCount / $this->_totalNgramCount[$sequenceMember],
                        ));
                    }
                }
            } else {
                foreach ($this->_ngramCount as $ngramId => $matchCount) {
                    $db->query($corpusNgramsSql, array(
                        $ngramId,
                        null,
                        $matchCount,
                        $matchCount / $this->_totalNgramCount,
                    ));
                }
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
     * Increment individual ngram and total ngram counts.
     *
     * A null sequence member indicates that this corpus is unsequenced;
     * otherwise it is sequenced.
     *
     * @param int $ngramId
     * @param null|string $sequenceMember
     */
    protected function _incrementNgramCount($ngramId, $sequenceMember = null)
    {
        if (null === $sequenceMember) {
            // This is an unsequenced corpus.
            if (!isset($this->_ngramCount[$ngramId])) {
                $this->_ngramCount[$ngramId] = 0;
            }
            $this->_ngramCount[$ngramId]++;

            if (!isset($this->_totalNgramCount)) {
                $this->_totalNgramCount = 0;
            }
            $this->_totalNgramCount++;
        } else {
            // This is a sequenced corpus.
            if (!isset($this->_ngramCount[$sequenceMember])) {
                $this->_ngramCount[$sequenceMember] = array();
            }
            if (!isset($this->_ngramCount[$sequenceMember][$ngramId])) {
                $this->_ngramCount[$sequenceMember][$ngramId] = 0;
            }
            $this->_ngramCount[$sequenceMember][$ngramId]++;

            if (!isset($this->_totalNgramCount[$sequenceMember])) {
                $this->_totalNgramCount[$sequenceMember] = 0;
            }
            $this->_totalNgramCount[$sequenceMember]++;
        }
    }
}
