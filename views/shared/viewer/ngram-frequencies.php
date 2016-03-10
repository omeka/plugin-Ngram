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
    Display this many <?php echo $this->formText('limit', $this->limit, array('size' => 3)) ?>
    <?php echo $this->formRadio('n', $this->n, array(), array(1 => 'Unigrams', 2 => 'Bigrams'), ''); ?>
    <?php echo $this->formSubmit('submit', 'Go'); ?>
</form>

<?php if ($this->ngrams): ?>
<table>
    <thead>
    <tr>
        <th></th>
        <th>Ngram</th>
        <th>Count</th>
        <th>%</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php foreach ($this->ngrams as $ngram => $count): ?>
    <tr>
        <td><?php echo $i++; ?></td>
        <td><?php echo $ngram; ?></td>
        <td><?php echo $count; ?></td>
        <td><?php echo number_format(($count / $this->totalNgramCount) * 100, 6); ?>%</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php echo foot(); ?>
