<?php
/**
 * (e32) ibEconomy
 * 1.4.3->1.4.4 DB Updates
 */

$SQL[] = "ALTER TABLE eco_lotteries ADD l_winning_nums varchar(64) NOT NULL default '';";
$SQL[] = "ALTER TABLE eco_lotteries ADD l_winners varchar(128) NOT NULL default '';";
	   
?>