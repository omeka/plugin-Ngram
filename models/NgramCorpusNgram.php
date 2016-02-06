<?php
class NgramCorpusNgram extends Omeka_Record_AbstractRecord
{
  public $id;
  public $corpus_id;
  public $ngram_id;
  public $sequence_member;
  public $match_count;
  public $item_count;
  public $relative_frequency;
}
