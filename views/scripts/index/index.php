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
            xFormat: <?php echo json_encode($this->xFormat); ?>,
            json: <?php echo json_encode($this->json); ?>,
            keys: {
                x: 'x',
                value: <?php echo json_encode($this->keysValue); ?>
            }
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    count: 8,
                    format: <?php echo json_encode($this->xTickFormat); ?>
                },
                padding: {left: 0},
            },
            y: {
                label: {
                    text: 'relative frequency %',
                    position: 'outer-middle'
                },
                tick: {
                    format: d3.format('.6%')
                },
                padding: {bottom: 0}
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
    Graph these comma-separated phrases: <?php echo $this->formText('queries', $this->queries, array('style' => 'margin-bottom:4px')); ?><br>
    from the corpus <?php echo $this->formSelect('corpus_id', null, null, array('1' => 'My Corpus (year)', '2' => 'My Other Corpus (month)')); ?>
    <input type="submit" value="Search">
    </div>
</form>
<br>
<div id="chart"></div>
<?php echo foot(); ?>
