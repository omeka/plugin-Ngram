<?php
class Process_GenerateNgrams extends Omeka_Job_Process_AbstractProcess
{
    /**
     * @var array
     */
    protected $_sequencedCorpus = array();

    /**
     * @var array
     */
    protected $_unsequencedCorpus = array();

    /**
     * @var array Total ngram counts (per sequence) for a sequenced corpus
     */
    protected $_totalNgramCounts = array();

    /**
     * @var int Total ngram count for an unsequenced corpus
     */
    protected $_totalNgramCount = 0;

    public function run($args)
    {
        // Raise the memory limit.
        ini_set('memory_limit', '500M');

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

                // Do not re-generate item ngrams for the current n.
                $ngramIds = $db->query($selectItemSql, $key)->fetchAll(Zend_Db::FETCH_COLUMN, 0);
                if ($ngramIds) {
                    foreach ($ngramIds as $ngramId) {
                        $corpus->isSequenced()
                            ? $this->_addSequencedItem($value, $ngramId, $key)
                            : $this->_addUnsequencedItem($ngramId, $value);
                    }
                    continue;
                }

                // Get the item text.
                $stmt = $corpus->isSequenced()
                    ? $db->query($selectTextSql, $key)
                    : $db->query($selectTextSql, $value);
                $text = new Ngram_Text($stmt->fetchColumn(0));

                // Iterate item ngrams.
                foreach ($text->getNgrams($n) as $ngram) {
                    $db->query($ngramsSql, array($ngram, $n));
                    $ngramId = $db->lastInsertId();
                    $db->query($itemNgramsSql, array($ngramId, $key));
                    $corpus->isSequenced()
                        ? $this->_addSequencedItem($value, $ngramId, $key)
                        : $this->_addUnsequencedItem($ngramId, $value);
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
            corpus_id, ngram_id, sequence_member, match_count, item_count, relative_frequency
        ) VALUES (
            %s, ?, ?, ?, ?, ?
        )',
        $db->NgramCorpusNgram,
        $corpus->id);

        $db->beginTransaction();
        try {
            if ($corpus->isSequenced()) {
                foreach ($this->_sequencedCorpus as $sequenceMember => $ngrams) {
                    foreach ($ngrams as $ngramId => $items) {
                        $matchCount = count($items);
                        $db->query($corpusNgramsSql, array(
                            $ngramId,
                            $sequenceMember,
                            $matchCount,
                            count(array_unique($items)),
                            $matchCount / $this->_totalNgramCounts[$sequenceMember],
                        ));
                    }
                }
            } else {
                foreach ($this->_unsequencedCorpus as $ngramId => $items) {
                    $matchCount = count($items);
                    $db->query($corpusNgramsSql, array(
                        $ngramId,
                        null,
                        $matchCount,
                        count(array_unique($items)),
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
     * Add a sequenced item and increment total ngram count.
     *
     * @param string $sequenceMember
     * @param string $ngramId
     * @param string $itemId
     */
    protected function _addSequencedItem($sequenceMember, $ngramId, $itemId)
    {
        if (!isset($this->_sequencedCorpus[$sequenceMember])) {
            $this->_sequencedCorpus[$sequenceMember] = [];
        }
        if (!isset($this->_sequencedCorpus[$sequenceMember][$ngramId])) {
            $this->_sequencedCorpus[$sequenceMember][$ngramId] = [];
        }
        $this->_sequencedCorpus[$sequenceMember][$ngramId][] = $itemId;

        if (!isset($this->_totalNgramCounts[$sequenceMember])) {
            $this->_totalNgramCounts[$sequenceMember] = 0;
        }
        $this->_totalNgramCounts[$sequenceMember]++;
    }

    /**
     * Add an unsequenced item.
     *
     * @param string $ngramId
     * @param string $itemId
     */
    protected function _addUnsequencedItem($ngramId, $itemId)
    {
        if (!isset($this->_unsequencedCorpus[$ngramId])) {
            $this->_unsequencedCorpus[$ngramId] = [];
        }
        $this->_unsequencedCorpus[$ngramId][] = $itemId;

        $this->_totalNgramCount++;
    }
}
