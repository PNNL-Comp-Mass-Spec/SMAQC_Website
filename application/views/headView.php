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
	<script type="text/javascript" src="<?= base_url("assets/graph/plugins/jqplot.highlighter.js") ?>"></script>
	<link rel="stylesheet" href="<?= base_url("assets/graph/jquery.jqplot.min.css") ?>" type="text/css" media="all">
	<script type="text/javascript">
		$(document).ready(function() {
			$.jqplot.config.enablePlugins = true;
			$.jqplot('chartdiv', [<?=$plotdata?>], {
					title:'<?=$title?>',
					axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer}}
			});
		});
	</script>
<?php endif; ?>

<script type="text/javascript">
  $(function() {
    $(".button").button();
  });
</script>
</head>

<body>
