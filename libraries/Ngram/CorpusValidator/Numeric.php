<?php
class Ngram_CorpusValidator_Numeric extends Ngram_CorpusValidator_AbstractCorpusValidator
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
        if (is_numeric($text)) {
            return $text;
        } else {
            return false;
        }
    }
}
