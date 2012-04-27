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
		<th>24 Hours</th>
		<th>3 Weeks</th>
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
			<a href="<?= site_url(join('/', array("smaqc", "instrument", $row, "all", date("m-d-Y", strtotime("-24 hours")), date("m-d-Y", time())))) ?>">24 Hours</a>
		</td>
		<td>
			<a href="<?= site_url(join('/', array("smaqc", "instrument", $row, "all", date("m-d-Y", strtotime("-3 weeks")), date("m-d-Y", time())))) ?>">3 Weeks</a>
		</td>
		<td>
			<a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $row, "all", $startdate, $enddate, $windowsize))) ?>">Current</a>
		</td>
	</tr>
<?php endforeach; ?>

</table>
</div>
