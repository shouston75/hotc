<?php

/**
 * (e32) ibEconomy
 * Core Variables
 * @ Global
 * + Load Cache
 * + House ibEconomy Cache Details
 */

	
//-----------------------------------------
// Extension File: Registered Caches
//-----------------------------------------

$_LOAD = array();

#load emoticons/badwords/bbcode (used in blocks, announcement, etc, just leave em on EVERYWHERE!
$_LOAD['badwords']			= 1;
$_LOAD['emoticons']			= 1;
$_LOAD['bbcode']			= 1;

#load member data cache for member page
if ( $_GET['area'] == 'member' )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['profilefields']		= 1;
	$_LOAD['reputation_levels']	= 1;
}

#Banks
$CACHE['ibEco_banks'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildBankCache' 
							);

#Stocks
$CACHE['ibEco_stocks'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildStockCache' 
							);

#Credit-Cards							
$CACHE['ibEco_ccs'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildCCCache' 
							);
						
#Long-Term Investments
$CACHE['ibEco_lts'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildLTCache' 
							);
							
#Portfolios
$CACHE['ibEco_portfolios'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildPortfolioCache' 
							);
							
#Shop Categories
$CACHE['ibEco_shopcats'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildShopCatCache' 
							);	

#Shop Items
$CACHE['ibEco_shopitems'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildShopItemCache' 
							);
							
#Footer stats
$CACHE['ibEco_stats'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildStatsCache' 
							);	

#Sidebar Blocks
$CACHE['ibEco_blocks'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildSidebarBlockCache' 
							);

#Current Lotto
$CACHE['ibEco_live_lotto'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php',
								'recache_class'    => 'ibEconomyMySQL',
							    'recache_function' => 'rebuildLiveLottoCache' 
							);							