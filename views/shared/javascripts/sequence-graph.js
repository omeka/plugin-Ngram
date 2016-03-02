jQuery(document).ready(function() {
    var chartDiv = jQuery('#sequence-graph');
    var graphConfig = chartDiv.data('graph-config');
    var dataKeysValue = chartDiv.data('data-keys-value');
    var dataJson = chartDiv.data('data-json');

    c3.generate({
        bindto: '#sequence-graph',
        data: {
            xFormat: graphConfig.dataXFormat,
            json: dataJson,
            keys: {
                x: 'x',
                value: dataKeysValue
            }
        },
        axis: {
            x: {
                type: graphConfig.axisXType,
                tick: {
                    count: graphConfig.axisXTickCount,
                    format: graphConfig.axisXTickFormat
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
    });
});
