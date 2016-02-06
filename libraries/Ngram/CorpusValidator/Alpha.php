<?php
class Ngram_CorpusValidator_Alpha extends Ngram_CorpusValidator_AbstractCorpusValidator
{
    const MAX_LENGTH = 20;

    public function addItem($id, $text)
    {
        $text = trim($text);
        if (self::MAX_LENGTH > strlen($text)) {
            $this->_validItems[$id] = $text;
        } else {
            $this->_invalidItems[] = $id;
        }
    }
}
