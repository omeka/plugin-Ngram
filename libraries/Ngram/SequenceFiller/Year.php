<?php
class Ngram_SequenceFiller_Year
    implements Ngram_SequenceFiller_SequenceFillerInterface
{
    public function getFilledSequence($start, $end)
    {
        $period = new DatePeriod(
            DateTime::createFromFormat('Y', $start),
            new DateInterval('P1Y'),
            DateTime::createFromFormat('Y', $end)
        );
        $filledSequence = array();
        foreach ($period as $date) {
            $filledSequence[] = $date->format('Y');
        }
        return $filledSequence;
    }
}
