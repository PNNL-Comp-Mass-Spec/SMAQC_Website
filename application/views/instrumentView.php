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

	function get_keys_by_value($arr, $value)
	{
		$result = array();

		foreach(array_keys($arr) as $key)
		{
			if($arr[$key] === $value)
			{
				$result[] = $key;
			}
		}

		return $result;
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
    <li><button class="button" onClick="location.href='<?= site_url() ?>'">Home</button></li>
    <li><strong>Category Shortcuts</strong>
    	<ul>
	    <?php foreach(array_unique($metricCategories) as $category): ?>
			<li><a class="categorylink" href="#cat_<?=str_replace(' ','',$category)?>"><?=$category?></a></li>
		<?php endforeach; ?>
		</ul>
	</li>
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
		Window Size:
		<input id="windowsize" type="number" name="windowsize" min="1" value=<?=$windowsize?>>
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
		<button id="updatesettings" class="button">Update</button>
	</li>
  </ul>
</div>

<div id="top-menu">
	<h2 align=right > <?=$title?> </h2>
</div>

<div id="main-page">
	<p><?=$definition?></p>
	<?php foreach(array_unique($metricCategories) as $category): ?>
	<div id="cat_<?=str_replace(' ','',$category)?>" class="category">
		<h2 class="categorytitle"><?=$category?></h2>
		<hr />
		<table style="border:0; width:100%;" id="tbl_<?=str_replace(' ','',$category)?>">
			<tr>
				<th style="width:50%;"></th>
				<th style="width:40%;"></th>
				<th>Status</th>
			</tr>
			<?php foreach(get_keys_by_value($metricCategories, $category) as $metricname): ?>
			<tr>
				<td>
					<h3><a href="<?=link_to_metric_dash($metricname, $instrument, $windowsize, $unit)?>"><?=$metricname?></a></h3>
					<p>
						Description: <?=$metricDescriptions[$metricname]?><br />
						Source: <?=$metricSources[$metricname]?>
					</p>
				</td>
				<td>
					
					average: <?=format_metric($averagedmetrics->row()->$metricname)?> -- 
					stdev: <?=format_metric($stddevmetrics[$metricname])?><br />
					bounds: (<?=format_metric($averagedmetrics->row()->$metricname - 2 * $stddevmetrics[$metricname] )?>, 
					         <?=format_metric($averagedmetrics->row()->$metricname + 2 * $stddevmetrics[$metricname] )?>) -- 
					latest:  <?=format_metric($latestmetrics->row()->$metricname)?>
				</td>
				<td align="center"><img src="/smaqc/assets/status_placeholder.png" alt="placeholder" /></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<?php endforeach; ?>
</div>
