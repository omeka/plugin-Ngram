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
    <?php if ($corpus->hasValidTextElement()): ?>
    <div id="save" class="panel">
        <h4>Edit Corpus</h4>
        <?php if ($corpus->canEdit()): ?>
            <a href="<?php echo $corpus->getRecordUrl('edit'); ?>" class="big green button">Edit Corpus</a>
            <?php if ($corpus->canValidateItems()): ?>
                <a href="<?php echo $corpus->getRecordUrl('validate');; ?>" class="big green button">Validate Items</a>
            <?php endif; ?>
        <?php else: ?>
            <p>The corpus items have been validated. No further edits are allowed.</p>
        <?php endif; ?>
    </div>
    <div id="save" class="panel">
        <h4>Generate Ngrams</h4>
        <?php if ($corpus->canEdit()): ?>
            <p class="error">Cannot generate ngrams until items have been validated.</p>
        <?php else: ?>
            <?php if ($corpus->canGenerateN1grams()): ?>
                <form method="post" action="<?php echo url('ngram/corpora/generate-ngrams/' . $corpus->id); ?>">
                    <?php echo $this->formHidden('n', 1); ?>
                    <?php echo $this->formSubmit('generate_ngrams', 'Generate Unigrams', array('class' => 'big green button')) ?>
                </form>
            <?php elseif ($corpus->N1Process && Process::STATUS_IN_PROGRESS === $corpus->N1Process->status): ?>
                <p>Unigram generation in progress...</p>
            <?php elseif ($corpus->N1Process && Process::STATUS_COMPLETED === $corpus->N1Process->status): ?>
                <p>Unigram generation completed.</p>
            <?php else: ?>
                <p class="error">Error generating unigrams.</p>
            <?php endif; ?>
            <?php if ($corpus->canGenerateN2grams()): ?>
                <form method="post" action="<?php echo url('ngram/corpora/generate-ngrams/' . $corpus->id); ?>">
                    <?php echo $this->formHidden('n', 2); ?>
                    <?php echo $this->formSubmit('generate_ngrams', 'Generate Bigrams', array('class' => 'big green button')) ?>
                </form>
            <?php elseif ($corpus->N2Process && Process::STATUS_IN_PROGRESS === $corpus->N2Process->status): ?>
                <p>Bigram generation in progress...</p>
            <?php elseif ($corpus->N2Process && Process::STATUS_COMPLETED === $corpus->N2Process->status): ?>
                <p>Bigram generation completed.</p>
            <?php elseif ($corpus->N2Process): ?>
                <p class="error">Error generating bigrams.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="panel">
        <h4>Corpus Locked</h4>
        <p class="error">This corpus is locked. The corpus text element does not match the one currently set in plugin configuration.</p>
    </div>
    <?php endif; ?>
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
