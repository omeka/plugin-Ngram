<?php
echo head(array('title' => 'Validate Corpus Items'));
echo js_tag('tabs');
$validCount = count($validItems);
$invalidCount = count($invalidItems);
$outOfRangeCount = count($outOfRangeItems);
?>
<script type="text/javascript" charset="utf-8">
jQuery(window).load(function () {
    Omeka.Tabs.initialize();
});
</script>

<ul id="section-nav" class="navigation tabs">
    <li><a href="#valid-items">Valid Items</a></li>
    <li><a href="#invalid-items">Invalid Items</a></li>
    <li><a href="#out-of-range-items">Out of Range Items</a></li>
</ul>

<h2><?php echo $corpus->name; ?> (<?php echo count($corpus->ItemsPool); ?> total items)</h2>

<ul>
    <li>Sequence Type: <?php echo $corpus->getSequenceTypeLabel(); ?></li>
    <li>Sequence Range: <?php echo $corpus->sequence_range; ?></li>
</ul>

<div id="valid-items">
<h3>Valid Items (<?php echo $validCount; ?>)</h3>
<?php if ($validCount): ?>
<form method="post" action="<?php echo html_escape(url('ngram/corpora/validate/' . $corpus->id)); ?>">
    <?php echo $this->formSubmit('accept_items', 'Accept Valid Items'); ?>
</form>
<table>
<thead>
    <tr>
        <th>Item</th>
        <th>Sequence Text</th>
        <th>Sequence Member</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($validItems as $id => $item): ?>
    <tr>
        <td><a href="<?php echo url(array('controller' => 'items', 'action' => 'edit', 'id' => $id), 'id'); ?>"><?php echo $id; ?></a></td>
        <td><?php echo $item['text']; ?></td>
        <td><kbd><?php echo $item['member']; ?></kbd></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>There are no valid items.</p>
<?php endif; ?>
</div>

<div id="invalid-items">
<h3>Invalid Items (<?php echo $invalidCount; ?>)</h3>
<?php if ($invalidCount): ?>
<table>
<thead>
    <tr>
        <th>Item</th>
        <th>Sequence Text</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($invalidItems as $id => $sequenceText): ?>
    <tr>
        <td><a href="<?php echo url(array('controller' => 'items', 'action' => 'edit', 'id' => $id), 'id'); ?>"><?php echo $id; ?></a></td>
        <td><?php echo $sequenceText; ?></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>There are no invalid items.</p>
<?php endif; ?>
</div>

<div id="out-of-range-items">
<h3>Out of Range Items (<?php echo $outOfRangeCount; ?>)</h3>
<?php if ($outOfRangeCount): ?>
<table>
<thead>
    <tr>
        <th>Item</th>
        <th>Sequence Text</th>
        <th>Sequence Member</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($outOfRangeItems as $id => $item): ?>
    <tr>
        <td><a href="<?php echo url(array('controller' => 'items', 'action' => 'edit', 'id' => $id), 'id'); ?>"><?php echo $id; ?></a></td>
        <td><?php echo $item['text']; ?></td>
        <td><kbd><?php echo $item['member']; ?></kbd></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>There are no out of range items.</p>
<?php endif; ?>
</div>

<?php echo foot(); ?>
