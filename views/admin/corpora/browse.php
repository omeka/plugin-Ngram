<?php
echo head(array('title' => 'Browse Corpora', 'bodyclass' => 'browse'));
echo flash();
?>

<?php if ($total_results): ?>

<div class="table-actions">
    <a href="<?php echo url(array('action' => 'add')); ?>" class="small green button">Add a Corpus</a>
</div>

<table>
<thead>
    <tr>
        <th>Name</th>
        <th>Text Element</th>
        <th>Sequence Element</th>
        <th>Sequence Type</th>
        <th>Sequence Range</th>
    </tr>
</thead>
<tbody>
<?php foreach (loop('ngram_corpus') as $corpus): ?>
<?php
$textElement = $corpus->TextElement;
$textElementName = $textElement->name;
$textElementSetName = $textElement->getElementSet()->name;

$sequenceElement = $corpus->SequenceElement;
if ($sequenceElement) {
    $sequenceElementName = $sequenceElement->name;
    $sequenceElementSetName = $sequenceElement->getElementSet()->name;
}
?>
    <tr>
        <td><?php echo link_to($corpus, 'show', $corpus->name);?></td>
        <td><?php echo sprintf('%s<br>(%s)', $textElementName, $textElementSetName); ?></td>
        <td>
            <?php if ($corpus->sequence_element_id): ?>
            <?php echo sprintf('%s<br>(%s)', $sequenceElementName, $sequenceElementSetName); ?>
            <?php else: ?>
            [no element]
            <?php endif; ?>
        </td>
        <td>
            <?php if ($corpus->sequence_type): ?>
            <?php echo $corpus->getSequenceTypeLabel(); ?>
            <?php else: ?>
            [no type]
            <?php endif; ?>
        </td>
        <td>
            <?php if ($corpus->sequence_range): ?>
            <?php echo $corpus->sequence_range; ?>
            <?php else: ?>
            [no range]
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<?php else: ?>

<h2>You have no corpora.</h2>
<p>Get started by adding your first corpus.</p>
<a href="<?php echo url(array('action' => 'add')); ?>" class="add big green button">Add a Corpus</a>
<?php endif; ?>

<?php echo foot(); ?>
