<?php
$textElement = $corpus->TextElement;
$textElementName = $textElement->name;
$textElementSetName = $textElement->getElementSet()->name;

$sequenceElement = $corpus->SequenceElement;
$sequenceElementName = $sequenceElement->name;
$sequenceElementSetName = $sequenceElement->getElementSet()->name;

echo head(array('title' => $corpus->name, 'bodyclass'=>'show'));
echo flash();
?>

<section class="seven columns alpha">
    <h2>Sequence Element</h2>
    <p><?php echo sprintf('%s (%s)', $sequenceElementName, $sequenceElementSetName); ?></p>
    <h2>Sequence Type</h2>
    <p><?php echo $corpus->getSequenceTypeLabel(); ?></p>
    <h2>Sequence Range</h2>
    <?php if ($corpus->sequence_range): ?>
    <p><?php echo $corpus->sequence_range; ?></p>
    <?php else: ?>
    <p><em>No range</em></p>
    <?php endif; ?>
    <h2>Item Pool Query</h2>
    <?php if ($corpus->query): ?>
    <p><textarea rows="3"><?php echo $corpus->query; ?></textarea></p>
    <?php else: ?>
    <p><em>No query</em></p>
    <?php endif; ?>
</section>

<section class="three columns omega">
    <div id="save" class="panel">
        <?php if ($corpus->hasValidTextElement()): ?>
            <?php if ($corpus->canEdit()): ?>
                <a href="<?php echo $corpus->getRecordUrl('edit'); ?>" class="big green button">Edit Corpus</a>
                <?php if ($corpus->canValidateItems()): ?>
                    <a href="<?php echo $corpus->getRecordUrl('validate');; ?>" class="big green button">Validate Items</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="error">This corpus is locked. The items are validated and accepted. No further edits are allowed.</p>
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
        <?php else: ?>
            <p class="error">This corpus is locked. The corpus text element does not match the one currently set in plugin configuration.</p>
        <?php endif; ?>
    </div>
    <div class="panel">
        <h4>Text Element</h4>
        <p><?php echo sprintf('%s (%s)', $textElementName, $textElementSetName); ?></p>
    </div>
    <div class="panel">
        <h4>Item Counts</h4>
        <ul>
            <li>Pool: <?php echo count($corpus->ItemsPool); ?></li>
            <li>Corpus: <?php echo count($corpus->ItemsCorpus); ?></li>
        </ul>
    </div>
</section>

<?php echo foot(); ?>
