<?php
echo head(array('title' => 'Validate Corpus Items'));
?>

<h2><?php echo $corpus->name; ?> (<?php echo count($corpus->ItemsPool); ?> items)</h2>

<p>This corpus has no sequence. Once you've accepted the items you will be able to
generate ngrams.</p>
<form method="post">
    <?php echo $this->formSubmit('accept_items', 'Accept Items'); ?>
    <span class="alert"><strong>CAUTION</strong>: you will not be able to configure
    the item pool or re-validate after you accept.</span>
</form>

<?php echo foot(); ?>
