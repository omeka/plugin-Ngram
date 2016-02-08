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
            <p class="error"><strong>WARNING</strong>: Changing the text element after it's been set may lock existing corpora. You will not be able to edit, validate items, or generate ngrams for corpora set to a different text element. Existing corpus ngrams are not affected.</p>
        </div>
    </div>
</section>
