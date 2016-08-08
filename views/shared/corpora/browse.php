<?php
echo head(array('title' => 'Browse Corpora', 'bodyclass' => 'browse'));
echo flash();
?>

<h1>Browse Corpora</h1>

<?php if ($total_results): ?>

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

<h2>There are no corpora.</h2>
<?php endif; ?>

<?php echo foot(); ?>
