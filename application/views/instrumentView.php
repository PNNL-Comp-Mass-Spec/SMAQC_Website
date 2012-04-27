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
    <td><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", $startdate, $enddate))) ?>" class="customdate button">Update</a></td>
	</tr>
</table>

<table border=1>
	<tr>
		<th>Metric</th>
		<th>Average Over Range</th>
		<th>Most Recent Value</th>
	</tr>
<?php foreach($metriclist as $metricname): ?>
	<tr>
		<td>
			<a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $title, $metricname, $startdate, $enddate))) ?>"><?=$metricname?></a>
		</td>
		<td align="right">
			<?=$averagedmetrics->row()->$metricname?>
		</td>
		<td align="right">
			<?=$latestmetrics->row()->$metricname?>
		</td>
	</tr>
<?php endforeach; ?>

</table>
</div>
