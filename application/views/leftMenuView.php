<?php
/**
 * leftMenuView.php
 *
 * File containing the code for the leftMenuView loaded on each page.
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @version 1.0
 * @copyright TODO
 * @license TODO
 * @package SMAQC
 * @subpackage views
 */

// set the $metric value to all for use in building the correct links
if(empty($metric))
{
    $metric = "all";
}
?>
<div id="left-menu">
 <ul>
  <li><button class="button" onClick="location.href='<?= site_url() ?>'">Home</button></li>
  <li><button id="left-menu-instruments" class="dropdownbutton">Instruments</button>
    <ul>
  <?php foreach($instrumentlist as $row): ?>
      <li><button id="left-menu-instrument-<?=$row?>" class="dropdownbutton"><?=$row?></button>
        <ul class="menulinks">
          <li><a href="<?= site_url(join('/', array("smaqc", "instrument", $row, $metric, date("m-d-Y", strtotime("-24 hours")), date("m-d-Y", time())))) ?>">24 Hours</a></li>
          <li><a href="<?= site_url(join('/', array("smaqc", "instrument", $row, $metric, date("m-d-Y", strtotime("-3 weeks")), date("m-d-Y", time())))) ?>">3 Weeks</a></li>
          <li><a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $row, $metric, $startdate, $enddate, $windowsize))) ?>">Current</a></li>
        </ul>
      </li>
  <?php endforeach; ?>
    </ul>
  </li>
  <?php if(!empty($instrument)): ?>
  <li><button class="dropdownbutton">Change Metric</button>
    <ul>
      <li><button class="dropdownbutton">All</button>
        <ul class="menulinks">
          <li><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", date("m-d-Y", strtotime("-24 hours")), date("m-d-Y", time())))) ?>">24 Hours</a></li>
          <li><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", date("m-d-Y", strtotime("-3 weeks")), date("m-d-Y", time())))) ?>">3 Weeks</a></li>
          <li><a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, "all", $startdate, $enddate, $windowsize))) ?>">Current</a></li>
        </ul>
      </li>
  <?php foreach($metriclist as $row): ?>
      <li><button class="dropdownbutton"><?=$row?></button>
        <ul class="menulinks">
          <li><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, $row, date("m-d-Y", strtotime("-24 hours")), date("m-d-Y", time())))) ?>">24 Hours</a></li>
          <li><a href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, $row, date("m-d-Y", strtotime("-3 weeks")), date("m-d-Y", time())))) ?>">3 Weeks</a></li>
          <li><a class="customdate" href="<?= site_url(join('/', array("smaqc", "instrument", $instrument, $row, $startdate, $enddate, $windowsize))) ?>">Current</a></li>
        </ul>
      </li>
  <?php endforeach; ?>
  <?php endif; ?>
    </ul>
  </li>
 </ul>
</div>

<script type="text/javascript">
    // style all the dropdown buttons
    $(".dropdownbutton").button( {
        icons: { primary:'ui-icon-triangle-1-e' }
    });
    
    // set the onclick function for dropdown buttons
    $(".dropdownbutton").click(function() {        
        // show or hide all subitems
        $(this).next().toggle();
        
        // change the arrow direction on the button
        if($(this).hasClass('selected'))
        {
            $(this).button("option", "icons", { primary:'ui-icon-triangle-1-e' });
        }
        else
        {
            $(this).button("option", "icons", { primary:'ui-icon-triangle-1-s' });
        }
        
        $(this).toggleClass('selected');
    });
    
    // initially, hide all of the items
    $(".dropdownbutton").next().hide();
    
    // open the menu to the current instrument
    <?php if(!empty($instrument)): ?>
        $("#left-menu-instruments").trigger('click');
        $("#left-menu-instrument-<?=$instrument?>").trigger('click');
    <?php endif; ?>
</script>
