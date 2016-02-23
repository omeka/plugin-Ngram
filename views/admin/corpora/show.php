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
        <h4>Manage Corpus</h4>
        <a href="<?php echo $corpus->getRecordUrl('edit'); ?>" class="big green button">Edit</a>
        <?php if ($corpus->canDelete()): ?>
            <a href="<?php echo $corpus->getRecordUrl('delete-confirm'); ?>" class="delete-confirm big red button">Delete</a>
        <?php else: ?>
            <button class="big red button" disabled>Delete</button>
        <?php endif; ?>
        <?php if ($corpus->hasValidTextElement()): ?>
        <?php if ($corpus->canValidateItems()): ?>
            <a href="<?php echo $corpus->getRecordUrl('validate'); ?>" class="big green button">Validate Items</a>
        <?php else: ?>
            <button class="big green button" disabled>Items Validated</button>
        <?php endif; ?>
        <?php if ($corpus->canGenerateN1grams()): ?>
            <form method="post" action="<?php echo $corpus->getRecordUrl('generate-ngrams'); ?>">
                <?php echo $this->formHidden('n', 1); ?>
                <?php echo $this->formSubmit('generate_ngrams', 'Generate Unigrams', array('class' => 'big green button')) ?>
            </form>
        <?php elseif ($corpus->N1Process && Process::STATUS_STARTING === $corpus->N1Process->status): ?>
            <button class="big green button" disabled>Unigrams Starting …</button>
        <?php elseif ($corpus->N1Process && Process::STATUS_IN_PROGRESS === $corpus->N1Process->status): ?>
            <button class="big green button" disabled>Unigrams In Progress …</button>
        <?php elseif ($corpus->N1Process && Process::STATUS_COMPLETED === $corpus->N1Process->status): ?>
            <button class="big green button" disabled>Unigrams Generated</button>
        <?php elseif ($corpus->N1Process && Process::STATUS_ERROR === $corpus->N1Process->status): ?>
            <button class="big red button" disabled>Unigrams Error</button>
        <?php else: ?>
            <button class="big green button" disabled>Generate Unigrams</button>
        <?php endif; ?>
        <?php if ($corpus->canGenerateN2grams()): ?>
            <form method="post" action="<?php echo $corpus->getRecordUrl('generate-ngrams'); ?>">
                <?php echo $this->formHidden('n', 2); ?>
                <?php echo $this->formSubmit('generate_ngrams', 'Generate Bigrams', array('class' => 'big green button')) ?>
            </form>
        <?php elseif ($corpus->N2Process && Process::STATUS_STARTING === $corpus->N2Process->status): ?>
            <button class="big green button" disabled>Bigrams Starting …</button>
        <?php elseif ($corpus->N2Process && Process::STATUS_IN_PROGRESS === $corpus->N2Process->status): ?>
            <button class="big green button" disabled>Bigrams In Progress …</button>
        <?php elseif ($corpus->N2Process && Process::STATUS_COMPLETED === $corpus->N2Process->status): ?>
             <button class="big green button" disabled>Bigrams Generated</button>
        <?php elseif ($corpus->N2Process && Process::STATUS_ERROR === $corpus->N2Process->status): ?>
            <button class="big red button" disabled>Bigrams Error</button>
        <?php else: ?>
            <button class="big green button" disabled>Generate Bigrams</button>
        <?php endif; ?>
        <?php else: ?>
        <p class="alert">The corpus text element does not match the one currently
        set in plugin configuration. Some features have been restricted.</p>
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
