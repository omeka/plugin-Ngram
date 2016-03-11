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

<section class="seven columns alpha">
    <h2>Public</h2>
    <?php if ($corpus->public): ?>
    <p>Yes</p>
    <?php else: ?>
    <p>No</p>
    <?php endif; ?>

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
</section>

<section class="three columns omega">
    <div class="panel">
        <h4>Manage Corpus</h4>
        <a href="<?php echo $corpus->getRecordUrl('edit'); ?>" class="big green button">Edit</a>
        <?php if ($corpus->canDelete()): ?>
            <a href="<?php echo $corpus->getRecordUrl('delete-confirm'); ?>" class="delete-confirm big red button">Delete</a>
        <?php else: ?>
            <a class="big red button" disabled>Delete</a>
        <?php endif; ?>
        <?php if ($corpus->hasValidTextElement()): ?>
        <?php if ($corpus->canValidateItems()): ?>
            <?php if ($corpus->isSequenced()): ?>
            <a href="<?php echo $corpus->getRecordUrl('validate-sequence'); ?>" class="big green button">Validate Items</a>
            <?php else: ?>
            <a href="<?php echo $corpus->getRecordUrl('validate-nonsequence'); ?>" class="big green button">Validate Items</a>
            <?php endif; ?>
        <?php else: ?>
            <a class="big green button" disabled>Items Validated</a>
        <?php endif; ?>
        <?php if ($corpus->canGenerateN1grams()): ?>
            <form method="post" action="<?php echo $corpus->getRecordUrl('generate-ngrams'); ?>">
                <?php echo $this->formHidden('n', 1); ?>
                <?php echo $this->formSubmit('generate_ngrams', 'Generate Unigrams', array('class' => 'big green button', 'style' => 'width:100%')) ?>
            </form>
        <?php elseif ($corpus->N1Process && Process::STATUS_STARTING === $corpus->N1Process->status): ?>
            <a class="big green button" disabled>Unigrams Starting …</a>
        <?php elseif ($corpus->N1Process && Process::STATUS_IN_PROGRESS === $corpus->N1Process->status): ?>
            <a class="big green button" disabled>Unigrams In Progress …</a>
        <?php elseif ($corpus->N1Process && Process::STATUS_COMPLETED === $corpus->N1Process->status): ?>
            <a class="big green button" disabled>Unigrams Generated</a>
        <?php elseif ($corpus->N1Process && Process::STATUS_ERROR === $corpus->N1Process->status): ?>
            <a class="big red button" disabled>Unigrams Error</a>
        <?php else: ?>
            <a class="big green button" disabled>Generate Unigrams</a>
        <?php endif; ?>
        <?php if ($corpus->canGenerateN2grams()): ?>
            <form method="post" action="<?php echo $corpus->getRecordUrl('generate-ngrams'); ?>">
                <?php echo $this->formHidden('n', 2); ?>
                <?php echo $this->formSubmit('generate_ngrams', 'Generate Bigrams', array('class' => 'big green button', 'style' => 'width:100%')) ?>
            </form>
        <?php elseif ($corpus->N2Process && Process::STATUS_STARTING === $corpus->N2Process->status): ?>
            <a class="big green button" disabled>Bigrams Starting …</a>
        <?php elseif ($corpus->N2Process && Process::STATUS_IN_PROGRESS === $corpus->N2Process->status): ?>
            <a class="big green button" disabled>Bigrams In Progress …</a>
        <?php elseif ($corpus->N2Process && Process::STATUS_COMPLETED === $corpus->N2Process->status): ?>
            <a class="big green button" disabled>Bigrams Generated</a>
        <?php elseif ($corpus->N2Process && Process::STATUS_ERROR === $corpus->N2Process->status): ?>
            <a class="big red button" disabled>Bigrams Error</a>
        <?php else: ?>
            <a class="big green button" disabled>Generate Bigrams</a>
        <?php endif; ?>
        <?php if ($corpus->canGenerateN3grams()): ?>
            <form method="post" action="<?php echo $corpus->getRecordUrl('generate-ngrams'); ?>">
                <?php echo $this->formHidden('n', 3); ?>
                <?php echo $this->formSubmit('generate_ngrams', 'Generate Trigrams', array('class' => 'big green button', 'style' => 'width:100%')) ?>
            </form>
        <?php elseif ($corpus->N3Process && Process::STATUS_STARTING === $corpus->N3Process->status): ?>
            <a class="big green button" disabled>Trigrams Starting …</a>
        <?php elseif ($corpus->N3Process && Process::STATUS_IN_PROGRESS === $corpus->N3Process->status): ?>
            <a class="big green button" disabled>Trigrams In Progress …</a>
        <?php elseif ($corpus->N3Process && Process::STATUS_COMPLETED === $corpus->N3Process->status): ?>
            <a class="big green button" disabled>Trigrams Generated</a>
        <?php elseif ($corpus->N3Process && Process::STATUS_ERROR === $corpus->N3Process->status): ?>
            <a class="big red button" disabled>Trigrams Error</a>
        <?php else: ?>
            <a class="big green button" disabled>Generate Trigrams</a>
        <?php endif; ?>
        <?php else: ?>
        <p class="alert">The corpus text element does not match the one currently
        set in plugin configuration. Some features have been restricted.</p>
        <?php endif; ?>
    </div>
    <div class="panel">
        <h4>View Corpus</h4>
        <a class="big blue button" href="<?php echo url(array('controller' => 'viewer', 'action' => 'ngram-search', 'id' => $corpus->id)); ?>">Ngram Search</a>
        <a class="big blue button" href="<?php echo url(array('controller' => 'viewer', 'action' => 'ngram-frequencies', 'id' => $corpus->id)); ?>">Ngram Frequencies</a>
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
