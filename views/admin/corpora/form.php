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
            <?php echo $this->formCheckbox('public', null, array('checked' => (bool) $corpus->public)); ?>
        </div>
    </div>
    <fieldset>
    <legend>Item Pool</legend>
    <?php if (!$corpus->id || $corpus->canValidateItems()): ?>
    <p>The item pool is the set of items from which you select a corpus. Items without
    the configured text element are removed from the item pool. You may continue to
    configure the item pool until you validate and accept corpus items.</p>
    <div class="field">
        <div class="two columns alpha">
            <label for="query">Search Query</label>
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
    <fieldset>
    <legend>Sequence</legend>
    <?php if (!$corpus->id || $corpus->canValidateItems()): ?>
    <p>The sequence is the logical order of items in your corpus. A sequence is not
    required but is needed to generate a sequence graph. You may continue to configure
    the sequence until you validate and accept corpus items.</p>
    <div class="field">
        <div class="two columns alpha">
            <label for="sequence_element_id">Sequence Element</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select the element (such as a date or numeric field)
            from which to derive a sequence. Items without this element are removed
            from the item pool.</p>
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
            <label for="sequence_type">Sequence Type</label>
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
    <?php else: ?>
    <p class="alert">The sequence cannot be configured.</p>
    <?php endif; ?>
    </fieldset>
</section>
<script type="text/javascript">
// Set the placeholder attribute to hint a format for the sequence range.
function setRangePlaceholder(type) {
    var placeholder;
    switch (type) {
        case 'year':
            placeholder = 'yyyy-yyyy';
            break;
        case 'month':
            placeholder = 'yyyymm-yyyymm';
            break;
        case 'day':
            placeholder = 'yyyymmdd-yyyymmdd';
            break;
        case 'numeric':
            placeholder = 'n-n';
            break;
        default:
            placeholder = '';
    }
    jQuery('#sequence_range').attr('placeholder', placeholder);
}
setRangePlaceholder(jQuery('#sequence_type').val());
jQuery('#sequence_type').on('change', function(foo) {
    setRangePlaceholder(jQuery(this).val());
});
</script>
