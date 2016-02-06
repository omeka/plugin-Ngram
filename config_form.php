<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="text_element_id">Text Element</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formSelect(
                'text_element_id',
                get_option('ngram_text_element_id'),
                array('id' => 'text_element_id'),
                $elementOptions
            ) ?>
            <p class="explanation">Select an element that contains the text from which to derive ngrams.</p>
            <p class="error"><strong>WARNING</strong>: Changing the text element after it's been set will delete all corpora, item ngrams, and corpora ngrams. Be sure to back up existing corpora ngrams before changing the text element.</p>
        </div>
    </div>
</section>
