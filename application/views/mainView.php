<?php
/**
 * mainView.php
 *
 * File containing the code for the default view for SMAQC.
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
<div id="main-page">
<table border=1 >
	<tr>
		<th>Instrument</th>
		<th>1 Week</th>
		<th>1 Month</th>
		<th>
			Custom Range
			<table id="datepickertable">
				<tr>
					<td align="right"><label for="from">From</label></td>
					<td><input type="text" id="from" name="from" value="<?=$startdate?>" /></td>
				</tr>
				<tr>
					<td align="right"><label for="to">To</label></td>
					<td><input type="text" id="to" name="to" value="<?=$enddate?>" /></td>
				</tr>
			</table>
		</th>
		
	</tr>
<?php foreach($instrumentlist as $row): ?>
	<tr>
		<td>
			<a href="<?= site_url(join('/', array("smaqc", "instrument", $row))) ?>"><?=$row?></a>
		</td>
		<td>
			<a href="<?= site_url(join('/', array("smaqc", "instrument", $row, "all", date("m-d-Y", strtotime("-1 week")), date("m-d-Y", time())))) ?>">1 Week</a>
		</td>
		<td>
			<a href="<?= site_url(join('/', array("smaqc", "instrument", $row, "all", date("m-d-Y", strtotime("-1 month")), date("m-d-Y", time())))) ?>">1 Month</a>
		</td>
		<td>
			<a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $row, "all", $startdate, $enddate, $windowsize, $datasetfilter))) ?>">Current</a>
		</td>
	</tr>
<?php endforeach; ?>

</table>
</div>
