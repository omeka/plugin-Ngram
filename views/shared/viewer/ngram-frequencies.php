<?php
if ($this->dataJson) {
    queue_css_file('c3/0.4.10/c3.min');
    queue_js_file('d3/3.5.16/d3.min');
    queue_js_file('c3/0.4.10/c3.min');
    queue_js_file('word-frequencies-graph');
}
echo head(array('title' => 'Corpus Viewer'));
?>

<h2>Ngrams in corpus "<?php echo $corpus->name; ?>"</h2>

<?php echo foot(); ?>
