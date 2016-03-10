<?php
if ($this->dataJson) {
    queue_css_file('c3/0.4.10/c3.min');
    queue_js_file('d3/3.5.16/d3.min');
    queue_js_file('c3/0.4.10/c3.min');
    queue_js_file('word-frequencies-graph');
}
echo head(array('title' => 'Corpus Viewer'));
?>

<h2>Ngrams in corpus "<?php echo $corpus->name; ?>"</h2>

<form method="post">
    Display this many <?php echo $this->formText('limit', $this->limit, array('size' => 4)) ?>
    <?php echo $this->formRadio('n', $this->n, array(), array(1 => 'Unigrams', 2 => 'Bigrams'), ''); ?>
    <?php echo $this->formSubmit('submit', 'Go'); ?>
</form>

<?php if ($this->ngrams): ?>
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
