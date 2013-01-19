<?php
/**
 * (e32) ibEconomy
 * 1.4.5->1.4.6 DB Updates
 */

$SQL[] = "ALTER TABLE eco_shop_items ADD si_max_daily_buys smallint(3) NOT NULL default '0';";
	   
?>