<?php
queue_css_url('https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.css');
queue_js_url('https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.14/d3.min.js');
queue_js_url('https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.js');
echo head(array('title' => 'Ngram Viewer'));
?>
<script>
jQuery(document).ready(function() {
    var chart = c3.generate({
        bindto: '#chart',
        data: {
            x: 'x',
            xFormat: <?php echo $this->xFormat; ?>,
            rows: <?php echo $this->data; ?>
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    count: 8,
                    format: <?php echo $this->xTickFormat; ?>
                },
                padding: {left: 6},
            },
            y: {
                label: {
                    text: 'relative frequency %',
                    position: 'outer-middle'
                },
                tick: {
                    format: d3.format('.6%')
                },
                padding: {bottom: 6}
            }
        },
        tooltip: {
            format: {
                value: d3.format('.10%')
            }
        },
        zoom: {
            enabled: true
        }
    })
});
</script>
<h1>Ngram Viewer</h1>
<form>
    <div>
    Search this phrase <?php echo $this->formText('query', null, array('style' => 'margin:0px')); ?>
    from the corpus <?php echo $this->formSelect('corpus_id', null, null, array('1' => 'My Corpus (year)', '2' => 'My Other Corpus (month)')); ?>
    <input type="submit" value="Search">
    </div>
</form>
<br>
<div id="chart"></div>
<?php echo foot(); ?>
