<?php
if ($this->dataJson) {
    queue_css_file('c3/0.4.11/c3.min');
    queue_js_file('d3/3.5.17/d3.min');
    queue_js_file('c3/0.4.11/c3.min');
    queue_js_file('sequence-graph');
}
echo head(array('title' => 'Corpus Viewer'));
?>

<?php echo link_to($corpus, 'show', '◄ Back to corpus');?>

<h2>Search corpus "<?php echo $corpus->name; ?>"</h2>

<form method="post">
    Graph these comma-separated phrases: <?php echo $this->formText('queries', $this->queries, array('size' => 60, 'style' => 'margin-bottom:4px')); ?><br>
    <?php
    if ($corpus->isSequenced()):
    switch ($corpus->sequence_type) {
        case 'year':
            $placeholder = 'yyyy';
            break;
        case 'month':
            $placeholder = 'yyyymm';
            break;
        case 'day':
            $placeholder = 'yyyymmdd';
            break;
        case 'numeric':
            $placeholder = 'n-n';
            break;
        default:
            $placeholder = '';
    }
    ?>
    between <?php echo $this->formText('start', $this->start, array('size' => 8, 'style' => 'margin-bottom:4px', 'placeholder' => $placeholder)); ?>
    and <?php echo $this->formText('end', $this->end, array('size' => 8, 'style' => 'margin-bottom:4px', 'placeholder' => $placeholder)); ?>
    <?php endif; ?>
    <?php echo $this->formSubmit('submit', 'Go'); ?>
</form>

<?php if ($this->queryStats):?>

<?php if ($corpus->isSequenced()): ?>
<h3>Sequence Graph</h3>
<?php if ($this->dataJson): ?>
<div id="sequence-graph"
    data-graph-config="<?php echo $this->escape(json_encode($this->graphConfig)); ?>"
    data-data-keys-value="<?php echo $this->escape(json_encode($this->dataKeysValue)); ?>"
    data-data-json="<?php echo $this->escape(json_encode($this->dataJson)); ?>">Loading...</div>
<?php else: ?>
<p>No results to graph.</p>
<?php endif; ?>
<?php endif; ?>

<h3>Ngram Counts</h3>
<table>
    <thead>
        <tr>
            <th>Ngram</th>
            <th style="text-align:right;">n</th>
            <th style="text-align:right;">Count</th>
            <th style="text-align:right;">Frequency %</th>
        </tr>
    </thead>
    <tbody style="font-family: monospace;">
        <?php foreach ($this->queryStats as $query => $stat): ?>
        <tr>
            <td><?php echo strtolower($query); ?></td>
            <td style="text-align:right;"><?php echo $stat['n'] ? $stat['n'] : 'n/a'; ?></td>
            <td style="text-align:right;"><?php echo number_format($stat['count']); ?></td>
            <td style="text-align:right;"><?php echo $stat['relative_frequency'] ? number_format($stat['relative_frequency'] * 100, 6) . '%' : 'n/a'; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Total Ngram Counts</h3>
<table>
    <thead>
        <tr>
            <th style="text-align:right;">n</th>
            <th style="text-align:right;">Total Count</th>
            <th style="text-align:right;">Total Unique Count</th>
        </tr>
    </thead>
    <tbody style="font-family: monospace;">
        <?php foreach ($this->corpusStats as $n => $stat): ?>
        <tr>
            <td style="text-align:right;"><?php echo $n; ?></td>
            <td style="text-align:right;"><?php echo number_format($stat['total_count']); ?></td>
            <td style="text-align:right;"><?php echo number_format($stat['total_unique_count']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<?php echo foot(); ?>
