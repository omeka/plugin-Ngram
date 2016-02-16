<?php
class Ngram_SequenceFiller_Day
    implements Ngram_SequenceFiller_SequenceFillerInterface
{
    public function getFilledSequence($start, $end)
    {
        $period = new DatePeriod(
            DateTime::createFromFormat('Ymd', $start),
            new DateInterval('P1D'),
            DateTime::createFromFormat('Ymd', $end)
        );
        $filledSequence = array();
        foreach ($period as $date) {
            $filledSequence[] = $date->format('Ymd');
        }
        return $filledSequence;
    }
}
