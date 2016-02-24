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

<h2><?php echo $corpus->name; ?> (<?php echo count($corpus->ItemsPool); ?> items)</h2>

<p>Review any valid, invalid, and out of range items below. If necessary you may
edit items to correct their sequence texts and reload this page to update the lists.
Once you've accepted the valid items you will be able to generate ngrams.</p>
<form method="post">
    <?php echo $this->formSubmit('accept_items', 'Accept Valid Items'); ?>
    <span class="alert"><strong>CAUTION</strong>: you will not be able to configure
    the item pool or re-validate after you accept.</span>
</form>
<hr>
<div id="valid-items">
<h3>Valid Items (<?php echo $validCount; ?>)</h3>
<?php if ($validCount): ?>
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
        <td><a target="_blank" href="<?php echo url(array('controller' => 'items', 'action' => 'edit', 'id' => $id), 'id'); ?>"><?php echo $id; ?></a></td>
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
        <td><a target="_blank" href="<?php echo url(array('controller' => 'items', 'action' => 'edit', 'id' => $id), 'id'); ?>"><?php echo $id; ?></a></td>
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
        <td><a target="_blank" href="<?php echo url(array('controller' => 'items', 'action' => 'edit', 'id' => $id), 'id'); ?>"><?php echo $id; ?></a></td>
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
