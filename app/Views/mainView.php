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
    <div class="statusTableContainer" >
        <table class="statustable" >
            <tr>
                <th>Instrument</th>
            </tr>
        <?php foreach($instrumentlist as $row): ?>
            <tr>
                <td style="text-align: left;"><a href="<?= site_url(join('/', array("smaqc", "instrument", $row))) ?>"><?=$row?></a></td>
            </tr>
        <?php endforeach; ?>

        </table>
    </div>
</div>
