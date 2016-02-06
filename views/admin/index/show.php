<?php
echo head(array('title' => $corpus->name, 'bodyclass'=>'show'));
echo flash();
$sequenceElement = $corpus->SequenceElement;
$sequenceElementName = $sequenceElement->name;
$sequenceElementSetName = $sequenceElement->getElementSet()->name;
?>

<section class="seven columns alpha">
    <h2>Sequence Element</h2>
    <p><?php echo sprintf('%s (%s)', $sequenceElementName, $sequenceElementSetName); ?></p>
    <h2>Sequence Type</h2>
    <p><?php echo $corpus->getSequenceTypeLabel(); ?></p>
    <h2>Sequence Range</h2>
    <p><?php echo $corpus->sequence_range; ?></p>
    <h2>Item Counts</h2>
    <ul>
        <li>Pool: <?php echo count($corpus->ItemsPool); ?></li>
        <li>Corpus: <?php echo count($corpus->ItemsCorpus); ?></li>
    </ul>
</section>

<section class="three columns omega">
    <div id="save" class="panel">
        <?php if ($corpus->canEdit()): ?>
        <a href="<?php echo $corpus->getRecordUrl('edit'); ?>" class="big green button">Edit</a>
        <?php else: ?>
        <p class="error">This corpus is locked: the items were validated and accepted; no further edits are allowed.</p>
        <?php endif; ?>
        <?php if ($corpus->canValidateItems()): ?>
        <a href="<?php echo $corpus->getRecordUrl('validate');; ?>" class="big green button">Validate Items</a>
        <?php endif; ?>
        <?php if ($corpus->canGenerateNgrams()): ?>
        <form method="post" action="<?php echo url('ngram/corpora/generate-ngrams/' . $corpus->id); ?>">
            <?php echo $this->formHidden('n', 1); ?>
            <?php echo $this->formSubmit('generate_ngrams', 'Generate Unigrams', array('class' => 'big green button')) ?>
        </form>
        <form method="post" action="<?php echo url('ngram/corpora/generate-ngrams/' . $corpus->id); ?>">
            <?php echo $this->formHidden('n', 2); ?>
            <?php echo $this->formSubmit('generate_ngrams', 'Generate Bigrams', array('class' => 'big green button')) ?>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php echo foot(); ?>
