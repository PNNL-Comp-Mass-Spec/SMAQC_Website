<?php
/**
 * headViewQCArt.php
 *
 * File containing the opening code for view qcartView
 *
 */
?>
<html>
<head>
<title>SMAQC</title>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/layout.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/present.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/smoothness/jquery-ui-1.8.17.custom.css") ?>" />
<script type="text/javascript" src="<?= base_url("assets/js/jquery-1.7.1.min.js") ?>"></script>
<script type="text/javascript" src="<?= base_url("assets/js/jquery-ui-1.8.17.custom.min.js") ?>"></script>
<script type="text/javascript" src="<?= base_url("assets/js/calendar_pop.js") ?>"></script>

<?php if( $includegraph ) :?>
    <!--[if IE]>
    <script language="javascript" type="text/javascript" src="<?= base_url("assets/graph/excanvas.js") ?>"></script>
    <![endif]-->
    <script type="text/javascript" src="<?= base_url("assets/graph/jquery.jqplot.min.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.dateAxisRenderer.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.canvasTextRenderer.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.canvasAxisTickRenderer.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.canvasAxisLabelRenderer.min.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.highlighter.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.trendline.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.cursor.js") ?>"></script>
    <link rel="stylesheet" href="<?= base_url("assets/graph/jquery.jqplot.min.css") ?>" type="text/css" media="all">
    <script type="text/javascript">
        <?php
            // we need to set these dates, as well as other values in the Settings var
            // metricplot.js will use them

            // we need to get the date in milliseconds for jqplot
            /*
            $tempStartDate = explode('-', $startdate);
            $tempStartDate = $tempStartDate[2] . '-' . $tempStartDate[0] . '-' . $tempStartDate[1];
            $jqStartdate = new DateTime($tempStartDate);

            $tempEndDate = explode('-', $enddate);
            $tempEndDate = $tempEndDate[2] . '-' . $tempEndDate[0] . '-' . $tempEndDate[1] . ' 23:59:59';
            $jqEnddate = new DateTime($tempEndDate);
            */
        ?>
        var Settings = {
            title: '<?=$title?>',
            plotdata: <?=$plotdata?>,
            plotdata_average: <?=$plotdata_average?>,
            stddevupper: <?=$stddevupper?>,
            stddevlower: <?=$stddevlower?>,
            plotDataBad: <?=$plotDataBad?>,
            plotDataPoor: <?=$plotDataPoor?>,
            metric_units: <?=$metric_units?>
        };

        var filterText = '<?=$datasetfilter?>';

        $(window).resize(function() {
            plot.replot( {resetAxes: true } );
        });
    </script>
    <script type="text/javascript" src="<?= base_url("assets/js/metricplot.js") ?>"></script>

<?php endif; ?>

<script type="text/javascript">
  $(function() {

    $(".button").button();

    $(".categorytitle").click(function() {
        $(this).siblings('table').toggle();
    });

    $(".categorylink").click(function() {
        $(this.hash).children().show();
    });

    $("#updatesettings").click(function() {
        var newurl = '<?=site_url()?>/smaqc/qcart';

        newurl = newurl + "/inst/" + $("#instrumentlist").val();

        if($("#from").length)
        {
            newurl = newurl + "/from/" + $("#from").val();
        }

        if($("#to").length)
        {
            newurl = newurl + "/to/" + $("#to").val();
        }

        if($("#filterDS").length)
        {
            var txt = $("#filterDS").val();
            if(txt != "")
                newurl = newurl + "/filterDS/" + $("#filterDS").val();
        }

        if($("#ignoreDS").length)
        {
            var txt = $("#ignoreDS").val();
            if(txt != "")
                newurl = newurl + "/ignoreDS/" + $("#ignoreDS").val();
        }

        document.location.href = newurl;
    });
  });
</script>
</head>

<body>
