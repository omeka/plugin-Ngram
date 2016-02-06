<?php
class Ngram_CorpusValidator_Day extends Ngram_CorpusValidator_AbstractCorpusValidator
{
    public function addItem($id, $text)
    {
        $text = trim($text);
        $member = $this->getSequenceMember($text);
        if (false === $member) {
            $this->_invalidItems[] = $id;
        } elseif ($this->isWithinRangeNumeric($member)) {
            $this->_validItems[$id] = $member;
        } else {
            $this->_outOfRangeItems[$id] = $member;
        }
    }

    protected function getSequenceMember($text)
    {
        if (preg_match('/^\d{4}$/', $text)) {
            return false;
        } elseif (preg_match('/^[a-z]+ \d+$/i', $text)) {
            return false;
        } elseif (preg_match('/^\d+-\d+$/i', $text)) {
            return false;
        } else {
            $timestamp = strtotime($text);
            if ($timestamp) {
                return date('Ymd', $timestamp);
            } else {
                return false;
            }
        }

    }
}
