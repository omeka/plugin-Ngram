<?php
abstract class Ngram_CorpusValidator_AbstractCorpusValidator
    implements Ngram_CorpusValidator_CorpusValidatorInterface
{
    protected $_range;

    protected $_validItems = array();

    protected $_invalidItems = array();

    protected $_outOfRangeItems = array();

    public function setRange($from, $to)
    {
        $this->_range = array('from' => $from, 'to' => $to);
    }

    public function getValidItems()
    {
        return $this->_validItems;
    }

    public function getInvalidItems()
    {
        return $this->_invalidItems;
    }

    public function getOutOfRangeItems()
    {
        return $this->_outOfRangeItems;
    }

    protected function isWithinRangeNumeric($member)
    {
        return $this->_range
            ? ($member >= $this->_range['from'] && $member <= $this->_range['to'])
            : true;
    }

    /**
     * Get the sequence member for a date.
     *
     * @param string $text
     * @param string $format
     * @return string|false
     */
    protected function getDateSequenceMember($text, $format)
    {
        $timestamp = strtotime($text);
        if ($timestamp) {
            $member = date($format, $timestamp);
            // Do not accept dates that resolve to a negative. The sequence
            // filler and third-party graphing libraries have trouble with
            // negative dates.
            return ('-' === $member[0]) ? false : $member;
        } else {
            return false;
        }
    }
}
