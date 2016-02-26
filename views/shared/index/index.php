<?php
if ($this->dataJson) {
    queue_css_file('c3/0.4.10/c3.min');
    queue_js_file('d3/3.5.16/d3.min');
    queue_js_file('c3/0.4.10/c3.min');
    queue_js_file('ngram-graph');
}
echo head(array('title' => 'Ngram Viewer'));
?>
<?php if ($this->corporaOptions): ?>
<form method="post">
    Graph these comma-separated phrases: <?php echo $this->formText('queries', $this->queries, array('size' => 40, 'style' => 'margin-bottom:4px')); ?><br>
    between <?php echo $this->formText('start', $this->start, array('size' => 8, 'style' => 'margin-bottom:4px')); ?>
    and <?php echo $this->formText('end', $this->end, array('size' => 8, 'style' => 'margin-bottom:4px')); ?>
    from the corpus <?php echo $this->formSelect('corpus_id', $this->corpusId, null, $this->corporaOptions); ?>
    <?php echo $this->formSubmit('submit', 'Search'); ?>
</form>
<?php if ($this->queries && !$this->dataJson): ?>
<p>No ngrams to plot.</p>
<?php endif; ?>
<div id="chart"
    data-graph-config="<?php echo $this->escape(json_encode($this->graphConfig)); ?>"
    data-data-keys-value="<?php echo $this->escape(json_encode($this->dataKeysValue)); ?>"
    data-data-json="<?php echo $this->escape(json_encode($this->dataJson)); ?>"></div>
<?php if ($this->queryStats): ?>
<h3>Corpus Statistics</h3>
<table>
    <thead>
        <tr>
            <th>Ngram</th>
            <th>n</th>
            <th>Total Count</th>
            <th>Relative Frequency %</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->queryStats as $query => $stat): ?>
        <tr>
            <td><?php echo $query; ?></td>
            <td><?php echo $stat['n'] ? $stat['n'] : 'n/a'; ?></td>
            <td><?php echo $stat['count']; ?></td>
            <td><?php echo $stat['relative_frequency'] ? number_format($stat['relative_frequency'] * 100, 6) . '%' : 'n/a'; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php else: ?>
<p>No corpora to search.</p>
<?php endif; ?>
<?php echo foot(); ?>
