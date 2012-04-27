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
    <td><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, $metric, $startdate, $enddate, $windowsize))) ?>" class="customdate button">Update</a></td>
  </tr>
  <tr>
    <td colspan=2 align="right"><label for="windowsize">StdDev Window Size (days)</label></td>
    <td><input type="text" id="windowsize" name="windowsize" value="<?=$windowsize?>" /></td>
  </tr>
</table>

<div style="text-align: right"><a class="customdate button" href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", $startdate, $enddate))) ?>">View All Metrics</a></div>
<div id="chartdiv" style="height:480px;width:100%;"></div>
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
      <td><?=$row->$metric?></td>
      <td><a href="http://dms2.pnl.gov/dataset/show/<?=$row->Dataset?>"><?=$row->Dataset?></a></td>
    </tr>
<?php endforeach; ?>
  </table>
</div>
