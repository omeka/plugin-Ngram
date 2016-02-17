<?php
queue_css_url('https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.css');
queue_js_url('https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.14/d3.min.js');
queue_js_url('https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.js');
echo head(array('title' => 'Ngram Viewer'));
?>
<?php if (isset($this->json)): ?>
<script>
jQuery(document).ready(function() {
    var chart = c3.generate({
        bindto: '#chart',
        data: {
            xFormat: <?php echo json_encode($this->graphConfig['dataXFormat']); ?>,
            json: <?php echo json_encode($this->json); ?>,
            keys: {
                x: 'x',
                value: <?php echo json_encode($this->dataKeysValue); ?>
            }
        },
        axis: {
            x: {
                type: <?php echo json_encode($this->graphConfig['axisXType']); ?>,
                tick: {
                    count: <?php echo json_encode($this->graphConfig['axisXTickCount']); ?>,
                    format: <?php echo json_encode($this->graphConfig['axisXTickFormat']); ?>
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
<?php endif; ?>
<h1>Ngram Viewer</h1>
<form method="post">
    Graph these comma-separated phrases: <?php echo $this->formText('queries', $this->queries, array('size' => 40, 'style' => 'margin-bottom:4px')); ?><br>
    between <?php echo $this->formText('start', $this->start, array('size' => 8, 'style' => 'margin-bottom:4px')); ?>
    and <?php echo $this->formText('end', $this->end, array('size' => 8, 'style' => 'margin-bottom:4px')); ?><br>
    from the corpus <?php echo $this->formSelect('corpus_id', $this->corpusId, null, $this->corporaOptions); ?>
    <?php echo $this->formSubmit('submit', 'Search'); ?>
</form>
<br>
<div id="chart"></div>
<?php echo foot(); ?>
