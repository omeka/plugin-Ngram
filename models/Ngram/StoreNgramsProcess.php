<?php
class Ngram_StoreNgramsProcess extends Omeka_Job_Process_AbstractProcess
{
    public function run($args)
    {}

    public function save_item_ngrams($item)
    {
        $item_text = get_item_text($corpus_item); // get all item and file texts from `search_texts`
        $item_one_grams = get_item_one_grams($item_text);
        save_item_one_grams($item_one_grams);
    }

    public function save_corpus($args)
    {
        $valid_items = [];
        $invalid_items = [];
        $corpus = get_corpus($args['corpus_id']);
        $corpus_items = get_corpus_items($corpus);
        foreach ($corpus_items as $key => $corpus_item) {
            if (item_has_member_element($corpus_item)) {
                $sequence_member = get_sequence_member($corpus_item);
                if (sequence_member_matches_pattern($sequence_member)
                    && sequence_member_within_range($sequence_member)
                ) {
                    $valid_items[$corpus_item->id] = $sequence_member;
                } else {
                    $invalid_items[$corpus_item->id] = $sequence_member;
                }
            } else {
                $invalid_items[$corpus_item->id] = null;
            }
        }
        save_corpus($name, $query, $sequence_member_element,
            $sequence_member_pattern, $sequence_member_range, $valid_items,
            $invalid_items);
    }

    public function save_corpus_ngrams()
    {
        $corpus_ngrams = [];
        foreach ($corpus_items as $key => $corpus_item) {
            $item_one_grams = get_item_one_grams($corpus_item);
            foreach ($item_one_grams as $one_gram) {
                if (!isset($corpus_ngrams[$one_gram])) {
                    $corpus_ngrams[$one_gram] = [];
                }
                if (!isset($corpus_ngrams[$one_gram][$sequence_member])) {
                    $corpus_ngrams[$one_gram][$sequence_member] = [];
                }
                // This is an array containing item IDs for every instance of the ngram.
                // Note that an item may have more than one instance of the same ngram.
                $corpus_ngrams[$one_gram][$sequence_member][] = get_item_id($corpus_item);
            }
        }
    }
}
