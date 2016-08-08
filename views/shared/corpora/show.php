<?php
$textElement = $corpus->TextElement;
$textElementName = $textElement->name;
$textElementSetName = $textElement->getElementSet()->name;

$sequenceElement = $corpus->SequenceElement;
if ($sequenceElement) {
    $sequenceElementName = $sequenceElement->name;
    $sequenceElementSetName = $sequenceElement->getElementSet()->name;
}

echo head(array('title' => $corpus->name, 'bodyclass'=>'show'));
echo flash();
?>

<h1><?php echo $corpus->name; ?></h1>

<p><a href="<?php echo url(array('controller' => 'viewer', 'action' => 'ngram-search', 'id' => $corpus->id)); ?>">Search Ngrams</a></p>
<p><a href="<?php echo url(array('controller' => 'viewer', 'action' => 'ngram-frequencies', 'id' => $corpus->id)); ?>">View Ngram Frequencies</a></p>

<h2>Item Count</h2>
<p><?php echo number_format(count($corpus->ItemsCorpus)); ?></p>

<h2>Text Element</h2>
<p><?php echo sprintf('%s (%s)', $textElementName, $textElementSetName); ?></p>

<h2>Search Query</h2>
<?php if ($corpus->query): ?>
<p><?php echo $corpus->query; ?></p>
<p><?php echo link_to_items_browse('Browse search results', $corpus->Query); ?></p>
<?php else: ?>
<p>[no query]</em></p>
<?php endif; ?>

<h2>Sequence Element</h2>
<?php if ($corpus->sequence_element_id): ?>
<p><?php echo sprintf('%s (%s)', $sequenceElementName, $sequenceElementSetName); ?></p>
<?php else: ?>
<p>[no element]</em></p>
<?php endif; ?>

<h2>Sequence Type</h2>
<?php if ($corpus->sequence_type): ?>
<p><?php echo $corpus->getSequenceTypeLabel(); ?></p>
<?php else: ?>
<p>[no type]</em></p>
<?php endif; ?>

<h2>Sequence Range</h2>
<?php if ($corpus->sequence_range): ?>
<p><?php echo $corpus->sequence_range; ?></p>
<?php else: ?>
<p>[no range]</em></p>
<?php endif; ?>

<?php echo foot(); ?>
