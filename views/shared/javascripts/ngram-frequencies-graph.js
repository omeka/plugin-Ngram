jQuery(document).ready(function() {
    var chartDiv = jQuery('#word-frequencies-graph');

    c3.generate({
        bindto: '#word-frequencies-graph'
    });
});
