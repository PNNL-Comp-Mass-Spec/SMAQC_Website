<?php
/**
 * instrumentView.php
 *
 * File containing the code for the metricView.
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

<div id="chartdiv" style="height:480px;width:100%;"></div>

<table id="metricplotcontrols">
  <tr>
  	<td width="85%">
	  <table id="datepickertable">
	  <tr>
	    <td align="right"><label for="from">From</label></td>
	    <td><input type="text" id="from" name="from" value="<?=$startdate?>" /></td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td align="right"><label for="to">To</label></td>
	    <td><input type="text" id="to" name="to" value="<?=$enddate?>" /></td>
	    <td><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, $metric, $startdate, $enddate, $windowsize, $datasetfilter))) ?>" class="customdate button">Update</a></td>
	  </tr>
	  <tr>
	    <td colspan=2 align="right"><label for="datasetfilter">Dataset filter</label></td>
	    <td><input type="text" id="datasetfilter" name="datasetfilter" value="<?=$datasetfilter?>" /></td>
	  </tr>
	  <tr>
	    <td colspan=2 align="right"><label for="windowsize">StdDev Window Size (days)</label></td>
	    <td><input type="text" id="windowsize" name="windowsize" value="<?=$windowsize?>" /></td>
	  </tr>
	  </table>
	</td>
	<td>
		<div style="text-align: right"><a class="customdate button" href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", $startdate, $enddate, $windowsize, $datasetfilter))) ?>">View All Metrics</a></div>
	</td>
  </tr>
</table>

  <table border=1 >
    <tr>
      <th>Dataset ID</th>
      <th>Start Time</th>
      <th>Value</th>
      <th>Dataset</th>
    </tr>
<?php foreach($metrics->result() as $row): ?>
    <tr>
      <td><?=$row->Dataset_ID?></td>
      <td><?=preg_replace('/:[0-9][0-9][0-9]/', '', $row->Acq_Time_Start)?></td>
      <td align="center"><?=format_metric($row->$metric)?></td>
      <td><a href="http://dms2.pnl.gov/dataset/show/<?=$row->Dataset?>" target="_Dataset"><?=$row->Dataset?></a></td>
    </tr>
<?php endforeach; ?>
  </table>
</div>
