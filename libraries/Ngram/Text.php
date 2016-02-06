<?php
class Ngram_Text
{
    /**
     * @var array
     */
    protected $words = array();
    /**
     * Constructor
     *
     * @param string $text
     * @param string $locale
     */
    public function __construct($text, $locale = null)
    {
        if (!$locale) {
            $locale = ini_get('intl.default_locale');
        }
        $iterator = \IntlBreakIterator::createWordInstance($locale);
        $iterator->setText($text);
        foreach($iterator->getPartsIterator() as $part) {
            if (\IntlBreakIterator::WORD_NONE !== $iterator->getRuleStatus()) {
                $this->words[] = $part;
            }
        }
    }
    /**
     * Get all words in the text.
     *
     * @return array
     */
    public function getWords()
    {
        return $this->words;
    }
    /**
     * Generate a n-gram sequence.
     *
     * @param int $n n-gram sequence number
     * @return array
     */
    public function getNgrams($n)
    {
        $ngrams = array();
        foreach ($this->words as $key => $token) {
            if (isset($this->words[$key - ($n - 1)])) {
                $ngram = array();
                for ($i = $n - 1; $i >= 0; $i--) {
                    $ngram[] = $this->words[$key - $i];
                }
                $ngrams[] = implode(' ', $ngram);
            }
        }
        return $ngrams;
    }
}
