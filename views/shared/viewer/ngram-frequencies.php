<?php
echo head(array('title' => 'Corpus Viewer'));
?>

<?php echo link_to($corpus, 'show', '◄ Back to corpus');?>

<h2>Ngrams in corpus "<?php echo $corpus->name; ?>"</h2>

<form method="post">
    Display this many <?php echo $this->formText('limit', $this->limit, array('size' => 4)) ?>
    <?php echo $this->formRadio('n', $this->n, array(), array(1 => 'Unigrams', 2 => 'Bigrams', 3 => 'Trigrams'), ''); ?>
    <?php echo $this->formSubmit('submit', 'Go'); ?>
</form>

<?php if ($this->ngrams): ?>
<?php
if (2 == $this->n) {
    $nType = 'bigrams';
} elseif (3 == $this->n) {
    $nType = 'trigrams';
} else {
    $nType = 'unigrams';
}
?>
<p>This corpus has <strong><?php echo number_format($this->totalNgramCount) ?></strong> total <?php echo $nType; ?> with <strong><?php echo number_format($this->totalUniqueNgramCount) ?></strong> unique <?php echo $nType; ?>.</p>
<table>
    <thead>
    <tr>
        <th>Ngram</th>
        <th style="text-align:right;">Total Count</th>
        <th style="text-align:right;">Frequency %</th>
    </tr>
    </thead>
    <tbody style="font-family: monospace;">
    <?php foreach ($this->ngrams as $ngram => $count): ?>
    <tr>
        <td><?php echo strtolower($ngram); ?></td>
        <td style="text-align:right;"><?php echo number_format($count); ?></td>
        <td style="text-align:right;"><?php echo number_format(($count / $this->totalNgramCount) * 100, 6); ?>%</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php echo foot(); ?>
