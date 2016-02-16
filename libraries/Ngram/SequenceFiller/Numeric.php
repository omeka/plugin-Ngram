<?php
class Ngram_SequenceFiller_Numeric
    implements Ngram_SequenceFiller_SequenceFillerInterface
{
    public function getFilledSequence($start, $end)
    {
        return range($start, $end);
    }
}
