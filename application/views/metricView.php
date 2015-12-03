<?php
/**
 * metricView.php
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
		if(is_null($metric))
			return "";
			
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


	function link_to_instrument_dash($instrument, $windowsize = FALSE, $unit = FALSE, $filterDS = FALSE, $ignoreDS = FALSE)
	{

        $URI_elements = array('smaqc', 'instrument', $instrument);

        if($windowsize != FALSE)
        {
        	$URI_elements[] = "window";
        	$URI_elements[] = $windowsize;
        }

        if($unit != FALSE)
        {
        	$URI_elements[] = "unit";
        	$URI_elements[] = $unit;
        }

        if($filterDS != FALSE)
        {
        	$URI_elements[] = "filterDS";
        	$URI_elements[] = $filterDS;
        }

        if($ignoreDS != FALSE)
        {
        	$URI_elements[] = "ignoreDS";
        	$URI_elements[] = $ignoreDS;
        }

        return site_url(join("/", $URI_elements));
	}
	
	function link_to_metric_dash($metricname, $instrument, $windowsize = FALSE, $unit = FALSE, $filterDS = FALSE, $ignoreDS = FALSE)
	{
		// Required URL parameters:
        // metric: the name of the metric
        // instrument: the name of the instrument

        // Optional URL parameters:
        // filterDS: used to select datasets based on a SQL 'LIKE' match
        // ignoreDS: used to exclude datasets based on a SQL 'LIKE' match

        $URI_elements = array('smaqc', 'metric', $metricname, 'inst', $instrument);

        if($windowsize != FALSE)
        {
        	$URI_elements[] = "window";
        	$URI_elements[] = $windowsize;
        }

        if($unit != FALSE)
        {
        	$URI_elements[] = "unit";
        	$URI_elements[] = $unit;
        }

        if($filterDS != FALSE)
        {
        	$URI_elements[] = "filterDS";
        	$URI_elements[] = $filterDS;
        }

        if($ignoreDS != FALSE)
        {
        	$URI_elements[] = "ignoreDS";
        	$URI_elements[] = $ignoreDS;
        }

        return site_url(join("/", $URI_elements));
	}
	
?>
<div id="left-menu">
  <ul class="menuitems">
    <li><button class="button" onClick="location.href='<?= link_to_instrument_dash($instrument, $windowsize) ?>'">Home</button></li>
	<li>
		<strong>Settings</strong><br />
		
		Instrument:
		<select id="instrumentlist">
	    	<?php foreach($instrumentlist as $row): ?>
	          	<?php if($instrument == $row) { ?>
					<option value="<?=$row?>" selected="selected"><?=$row?></option>
				<?php } else { ?>
					<option value="<?=$row?>"><?=$row?></option>
				<?php } ?>
	      	<?php endforeach; ?>
	    </select>
	    
	    Metric:
		<select id="metriclist">
	    	<?php foreach($metriclist as $row): 
		    	$shortDescription = $metricShortDescription[$row];
		    	if (strlen(trim($shortDescription)) == 0)
		    		$shortDescription = '';
				else
			    	$shortDescription = ' (' . $shortDescription . ')';

	    	?>
	          	<?php if($metric == $row) { ?>
					<option value="<?=$row?>" selected="selected"><?=$row . $shortDescription?></option>
				<?php } else { ?>
					<option value="<?=$row?>"><?=$row . $shortDescription?></option>
				<?php } ?>
	      	<?php endforeach; ?>
	    </select>
	    
		Window Size:
		<input id="windowsize" type="number" name="windowsize" min="1" value="<?=$windowsize?>">
		
		Units for Window:
		<select id="units">
			<?php if($unit == "days") { ?>
				<option value="days" selected="selected">days</option>
				<option value="datasets">datasets</option>
			<?php } else { ?>
				<option value="days">days</option>
				<option value="datasets" selected="selected">datasets</option>
			<?php } ?>
		</select>
		
		<label for="from">From</label>
		<input type="text" id="from" name="from" value="<?=$startdate?>" />
		
		<label for="to">To</label>
		<input type="text" id="to" name="to" value="<?=$enddate?>" />
		
		<label for="filterDS">Dataset Filter</label>
		<input type="text" id="filterDS" name="filterDS" value="<?=$filterDS?>" />
		
		<!--
		<label for="ignoreDS">Excluded Datasets</label>
		<input type="text" id="ignoreDS" name="ignoreDS" value="<?=$ignoreDS?>" disabled="disabled" />
		-->
		
		<button id="updatesettings" class="button">Update</button>
	</li>
  </ul>
</div>

<div id="main-page">
<p><?=$definition?></p>

<div id="chartdiv" style="height:480px; width:100%; margin-bottom:40px;"></div>
  <table border=1 >
    <tr>
      <th>Dataset ID</th>
      <th>Start Time</th>
      <th>Value</th>
      <th>Rating</th>
      <th>Dataset</th>
    </tr>
<?php foreach($metrics->result() as $row): ?>
    <tr>
      <td align="center"><?=$row->Dataset_ID?></td>
      <td><?=preg_replace('/:[0-9][0-9][0-9]/', '', $row->Acq_Time_Start)?></td>
      <td align="center"><?=format_metric($row->$metric)?></td>
      <td><?=$row->Dataset_Rating?></td>
      <td><a href="http://dms2.pnl.gov/dataset/show/<?=$row->Dataset?>" target="_Dataset"><?=$row->Dataset?></a></td>
    </tr>
<?php endforeach; ?>
  </table>
</div>
