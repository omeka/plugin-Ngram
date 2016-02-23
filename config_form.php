<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="text_element_id">Text Element</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select the element that contains the text from
            which to derive ngrams. Items without this element are ignored.</p>
            <?php echo $view->formSelect(
                'text_element_id',
                get_option('ngram_text_element_id'),
                array('id' => 'text_element_id'),
                $elementOptions
            ) ?>
            <p class="alert"><strong>CAUTION</strong>: Changing the text element
            after it's been set will prevent you from validating items and generating
            ngrams for corpora set to a different text element. Existing corpus
            ngrams are not affected.</p>
        </div>
    </div>
    <div class="field">
        <div class="two column alpha">
            <label for="reset_processes">Reset Processes</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Check this box to reset ngram generation
            processes that are hanging or showing errors.</p>
            <?php echo $view->formCheckbox(
                'reset_processes',
                null,
                array('id' => 'reset_processes')
            ) ?>
        </div>
    </div>
</section>
