<?php
/**
 * (e32) ibEconomy
 * 1.3.1->1.3.2 DB Updates
 */

$SQL[] = "ALTER TABLE forums MODIFY eco_tpc_pts decimal(10,2) NOT NULL default '0';";
$SQL[] = "ALTER TABLE forums MODIFY eco_rply_pts decimal(10,2) NOT NULL default '0';";
$SQL[] = "ALTER TABLE forums MODIFY eco_get_rply_pts decimal(10,2) NOT NULL default '0';";

?>