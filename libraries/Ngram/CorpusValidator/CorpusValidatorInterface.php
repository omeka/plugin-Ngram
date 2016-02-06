<?php
interface Ngram_CorpusValidator_CorpusValidatorInterface
{
    /**
     * Add an item's sequence text.
     *
     * Implementations should validate the text and make valid items and invalid
     * items available through the respective accessor methods.
     *
     * @param int $id The item ID
     * @param string $text The item sequence text
     */
    public function addItem($id, $text);

    /**
     * Set the sequence range.
     *
     * @param mixed $from
     * @param mixed $to
     */
    public function setRange($from, $to);

    /**
     * Get valid items.
     *
     * Implementations should return an array of valid (and transformed if
     * applicable) sequence members keyed by item ID.
     *
     * @return array
     */
    public function getValidItems();

    /**
     * Get invalid items.
     *
     * Implementations should return an array of item IDs that have invalid
     * sequence text.
     *
     * @return array
     */
    public function getInvalidItems();

    /**
     * Get out-of-range items.
     *
     * Implementations should return an array of out-of-range (but otherwise
     * valid and transformed if applicable) sequence members keyed by item ID.
     *
     * @return array
     */
    public function getOutOfRangeItems();
}
