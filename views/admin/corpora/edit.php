<?php
echo head(array('title' => 'Edit Corpus', 'bodyclass' => 'edit'));
echo flash();
?>
<form method="post">
<?php include 'form.php'; ?>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" name="submit" id="submit" value="Save Changes" class="submit big green button">
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
</form>

<?php echo foot(); ?>
