<?php
class Ngram_SequenceFiller_Month
    implements Ngram_SequenceFiller_SequenceFillerInterface
{
    public function getFilledSequence($start, $end)
    {
        $period = new DatePeriod(
            DateTime::createFromFormat('Ym', $start),
            new DateInterval('P1M'),
            DateTime::createFromFormat('Ym', $end)
        );
        $filledSequence = array();
        foreach ($period as $date) {
            $filledSequence[] = $date->format('Ym');
        }
        return $filledSequence;
    }
}
