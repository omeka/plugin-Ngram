<?php
class Ngram_CorpusValidator_Year extends Ngram_CorpusValidator_AbstractCorpusValidator
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
            return $text;
        } else {
            return $this->getDateSequenceMember($text, 'Y');
        }
    }
}
