<?php
$textElementName = $textElement->name;
$textElementSetName = $textElement->getElementSet()->name;
?>
<section class="seven columns alpha">
    <div class="field">
        <div class="two columns alpha">
            <label for="name" class="required">Name</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formText('name', $corpus->name); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="name">Public</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Make this corpus available to the public?</p>
            <?php echo $this->formCheckbox('public', (bool) $corpus->public, null, array('checked' => true)); ?>
        </div>
    </div>
    <fieldset>
    <legend>Item Pool</legend>
    <?php if (!$corpus->id || $corpus->canValidateItems()): ?>
    <p>The item pool is the set of items from which you select a corpus. You may
    continue to configure the item pool until you validate and accept corpus items.</p>
    <div class="field">
        <div class="two columns alpha">
            <label for="sequence_element_id" class="required">Sequence Element</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select the element (such as a date or numeric field)
            from which to derive a sequence. Items without this element are ignored.</p>
            <?php echo $this->formSelect(
                'sequence_element_id',
                $corpus->sequence_element_id,
                array('id' => 'sequence_element_id'),
                $sequenceElementOptions
            ); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="sequence_type" class="required">Sequence Type</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select the type of sequence to validate and represent
            on the x-axis of the ngram viewer.</p>
            <?php echo $this->formSelect(
                'sequence_type',
                $corpus->sequence_type,
                array('id' => 'sequence_type'),
                $sequenceTypeOptions
            ); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="sequence_range">Sequence Range</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Enter the range that the sequence must come between,
            in the format <code>START-END</code>. The start and end must conform
            to the standardized format of the selected sequence type.</p>
            <?php echo $this->formText('sequence_range', $corpus->sequence_range); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="query">Item Pool Query</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Filter the item pool using a URL query string
            that comes as a result of submitting an advanced item search.</p>
            <?php echo $this->formText('query', $corpus->query); ?>
        </div>
    </div>
    <?php else: ?>
    <p class="alert">The item pool cannot be configured.</p>
    <?php endif; ?>
    </fieldset>
</section>
