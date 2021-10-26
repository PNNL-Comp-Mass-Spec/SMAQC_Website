<?php
/**
 * headView.php
 *
 * File containing the opening code for all views in SMAQC.
 *
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @author Aaron Cain
 * @version 1.0
 * @copyright TODO
 * @license TODO
 * @package SMAQC
 * @subpackage views
 */
?>
<html>
<head>
<title>SMAQC</title>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="<?= base_url("css/layout.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url("css/present.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url("css/smoothness/jquery-ui-1.8.17.custom.css") ?>" />
<script type="text/javascript" src="<?= base_url("js/jquery-1.7.1.min.js") ?>"></script>
<script type="text/javascript" src="<?= base_url("js/jquery-ui-1.8.17.custom.min.js") ?>"></script>
<script type="text/javascript" src="<?= base_url("js/calendar_pop.js") ?>"></script>

<?php if( $includegraph ) :?>
    <!--[if IE]>
    <script language="javascript" type="text/javascript" src="<?= base_url("graph/excanvas.js") ?>"></script>
    <![endif]-->
    <script type="text/javascript" src="<?= base_url("graph/jquery.jqplot.min.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.dateAxisRenderer.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.canvasTextRenderer.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.canvasAxisTickRenderer.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.canvasAxisLabelRenderer.min.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.highlighter.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.trendline.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("graph/plugins/jqplot.cursor.js") ?>"></script>
    <link rel="stylesheet" href="<?= base_url("graph/jquery.jqplot.min.css") ?>" type="text/css" media="all">
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
    <script type="text/javascript" src="<?= base_url("js/metricplot.js") ?>"></script>

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
        var newurl = '<?=site_url()?>/smaqc';

        if($("#metriclist").length)
        {
            newurl = newurl + "/metric/" + $("#metriclist").val();
            newurl = newurl + "/inst/" + $("#instrumentlist").val();
        }
        else if($("#instrumentlist").length)
        {
            newurl = newurl + "/instrument/" + $("#instrumentlist").val();
        }

        if($("#windowsize").length)
        {
            newurl = newurl + "/window/" + $("#windowsize").val();
        }

        if($("#units").length)
        {
            newurl = newurl + "/unit/" + $("#units").val();
        }

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
