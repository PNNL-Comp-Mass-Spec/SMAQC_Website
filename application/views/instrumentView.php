<?php
/**
 * instrumentView.php
 *
 * File containing the code for the instrumentView.
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @version 1.0
 * @copyright TODO
 * @license TODO
 * @package SMAQC
 * @subpackage views
 */
 	
	function format_metric($metric)
	{
		if ($metric == 0)
			return "0";

		if (abs($metric) < 0.01)
			return number_format($metric, 4);

		if (abs($metric) < 0.1)
			return number_format($metric, 3);

		if (abs($metric) < 1)
			return number_format($metric, 2);

		if (abs($metric) < 10)
			return number_format($metric, 1);

		return number_format($metric, 0);
		
	}
	
?>
<div id="main-page">
<p><?=$definition?></p>

<table id="datepickertable">
	<tr>
		<td align="right"><label for="from">From</label></td>
		<td><input type="text" id="from" name="from" value="<?=$startdate?>" /></td>
	</tr>
	<tr>
		<td align="right"><label for="to">To</label></td>
		<td><input type="text" id="to" name="to" value="<?=$enddate?>" /></td>
    <td><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", $startdate, $enddate, $windowsize, $datasetfilter))) ?>" class="customdate button">Update</a></td>
	</tr>
</table>

<table border=1>
	<tr>
		<th>Metric</th>
		<th>Average Over Range</th>
		<th>Most Recent Value</th>
		<th>Description</th>
		<th>Category</th>
		<th>Source</th>
	</tr>
<?php foreach($metriclist as $metricname): ?>
	<tr>
		<td>
			<a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $title, $metricname, $startdate, $enddate, $windowsize, $datasetfilter))) ?>"><?=$metricname?></a>
		</td>
		<td align="right">
			<?=format_metric($averagedmetrics->row()->$metricname)?>
		</td>
		<td align="right">
			<?=format_metric($latestmetrics->row()->$metricname)?>
		</td>
		<td align="left">
			<?=$metricDescriptions[$metricname]?>
		</td>
		<td align="left">
			<?=$metricCategories[$metricname]?>
		</td>
		<td align="left">
			<?=$metricSources[$metricname]?>
		</td>
	</tr>
<?php endforeach; ?>

</table>
</div>
