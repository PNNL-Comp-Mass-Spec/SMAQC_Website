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
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.highlighter.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.trendline.js") ?>"></script>
    <script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.cursor.js") ?>"></script>
    <link rel="stylesheet" href="<?= base_url("assets/graph/jquery.jqplot.min.css") ?>" type="text/css" media="all">
    <script type="text/javascript">
        <?php
            // we need to set these dates, as well as other values in the Settings var
            // metricplot.js will use them

            // we need to get the date in milliseconds for jqplot
            $temp = explode('-', $startdate);
            $temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1];
            $jqStartdate = new DateTime($temp);
            
            $temp = explode('-', $enddate);
            $temp = $temp[2] . '-' . $temp[0] . '-' . $temp[1] . ' 23:59:59';
            $jqEnddate = new DateTime($temp);
        ?>
        var Settings = {
            title: '<?=$title?>',
            startdate: <?=$jqStartdate->getTimeStamp() * 1000?>,
            enddate: <?=$jqEnddate->getTimeStamp() * 1000?>,
            plotdata: <?=$plotdata?>,
            plotdata_average: <?=$plotdata_average?>,
            stddevupper: <?=$stddevupper?>,
            stddevlower: <?=$stddevlower?>
        }

        $(window).resize(function() {
            plot.replot( {resetAxes: true } );
        });

        $("#windowsize").live('change', function (event) {
          var input = $(this).val();
          $('a.customdate').each(function() {
                $(this).attr("href", function(index, old) {
                    var substr = old.split('/');
                    substr[substr.length - 1] = input;
                    return substr.join('/');
                });
            });
        });

        $("#datasetfilter").live('change', function (event) {
          var input = $(this).val();
          $('a.customdate').each(function() {
                $(this).attr("href", function(index, old) {
                    var substr = old.split('/');
                    substr[substr.length - 1] = input;
                    return substr.join('/');
                });
            });
        });
        
    </script>
    <script type="text/javascript" src="<?= base_url("assets/js/metricplot.js") ?>"></script>

<?php endif; ?>

<script type="text/javascript">
  $(function() {
    $(".button").button();
  });
</script>
</head>

<body>
