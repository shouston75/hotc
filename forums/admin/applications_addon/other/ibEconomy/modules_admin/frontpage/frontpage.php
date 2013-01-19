<?php

/**
 * (e32) ibEconomy
 * Admin Module: Frontpage
 * @ ACP
 * + Quick Tools + Stats
 * + Updates, etc
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_frontpage_frontpage extends ipsCommand
{
	public $html;
	public $registry;
	
	public $form_code;
	public $form_code_js;
	
	/**
	 * Main execution method
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		#load templates
		$this->html         	= $this->registry->output->loadTemplate( 'cp_skin_ibEconomy');

		#load langs
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_main' ) );
		
		#do form codes
		$this->form_code 		= $this->html->form_code    = 'module=frontpage&amp;section=frontpage';
		$this->form_code_js 	= $this->html->form_code_js = 'module=frontpage&section=frontpage';		
		
		#saved message
		if( $this->request['saved'] == 1 )
		{
			$this->registry->output->global_message = $this->lang->words['s_updated'];
		}
		
		switch( $this->request['do'] )
		{		
			//******General ECO Settings******//	
			case 'general_settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_gen_settings_edit' );
				$this->registry->class_ibEco_CP->doSettings( $this->request['do'], $this->form_code );
			break;
			//******Recache ALL!******//	
			case 'recache_all':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_recache' );
				$this->recacheAll();
			break;	
			//******Our Fab ACP Frontpage******//				
			case 'frontpage':
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_frontpage_view' );
				$this->frontPage();
			break;
		}
		
		#footer
		$this->registry->output->html .= $this->html->footer();		
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}

	/**
	 * Frontpage
	 */	
	private function frontpage()
	{
		#init
		$buttons 		= "";
		$frontpageCrap 	= array();
		$newsItemRows 	= array();
		
		#update stuff?
		if ( $this->settings['eco_update_checker'] )
		{
			#get xml class
			require_once( IPS_KERNEL_PATH . 'class_xml.php' );
			$xml = new class_xml();
			
			require_once( IPS_KERNEL_PATH . '/classFileManagement.php' );
			$checker                = new classFileManagement();
			$checker->use_sockets    = $this->settings['enable_sockets'];
			
			#This is a low timeout to prevent page from taking too long
			$checker->timeout        = 5;
			
			$content    = $checker->getFileContents( 'http://emoneycodes.com/e$mods.xml' );		
		
			$xml->xml_parse_document( $content );
				
			#our versions..
			$frontpageCrap['latestVer'] = $xml->xml_array['mods']['ipb30']['ibEconomy']['human']['VALUE'];
			$frontpageCrap['instalVer'] = $this->caches['app_cache']['ibEconomy']['app_version'];
			
			#latest ibEconomy news...
			if ( is_array( $xml->xml_array['mods']['news']['ibEconomy'] ) )
			{
				foreach ( $xml->xml_array['mods']['news']['ibEconomy'] as $k => $v )
				{
					#county
					$count++;

					#text
					$newsItemRows['ecoNewsText'] = $v['text']['VALUE'];
					$newsItemRows['ecoNewsTime'] = $this->registry->getClass('class_localization')->getDate( $v['time']['VALUE'], 'JOINED' );				
				
					#output row
					$newsItems .= $this->html->frontpageNewItem($newsItemRows);

					#no more!
					if ( $count >= 4 )
					{
						break;
					}
				}
			}

			#time for an upgrade?
			$frontpageCrap['upgrade_image'] = ( $frontpageCrap['latestVer'] > $frontpageCrap['instalVer'] || !intval($frontpageCrap['instalVer']) ) ? 'cross.png' : 'accept.png';
			$frontpageCrap['dl_link']		= "<a style='font-weight:bolder;color=red' href='http://emoneycodes.com/forums/'>(e$) Mods</a>";
			
			#warning needed?
			$frontpageCrap['warning_html'] 	= ( $frontpageCrap['latestVer'] > $frontpageCrap['instalVer'] || !intval($frontpageCrap['instalVer']) ) ?  $this->lang->words['time_for_upgrade'] : '';
		}
		
		#quick add item dropdown
		$frontpageCrap['stock_items'] 	= $this->registry->class_ibEco_CP->getShopItemFiles();

		#create group dropdown for quick tools
		$frontpageCrap['group_dd'] 		= $this->registry->ecoclass->getGroups('fp');
		
		#create group dropdown for advanced tools
		$frontpageCrap['adv_group_dd'] 	= $this->registry->ecoclass->getGroups('mass_donate');
		
		#create group dropdown for advanced tools
		$frontpageCrap['pt_fields']	   	= $this->registry->class_ibEco_CP->getPtFields();

		#create group dropdown for advanced tools
		$frontpageCrap['allStuff']	   	= $this->registry->class_ibEco_CP->getAllItems('fp');		
		
		#stats block!
		if ( is_array($this->caches['ibEco_stats'] ) )
		{
			 $stats = $this->caches['ibEco_stats'];

			 $frontpageCrap['total_points'] = $this->registry->getClass('class_localization')->formatNumber( $stats['total_points'], $this->registry->ecoclass->decimal );
			 $frontpageCrap['total_worth'] = $this->registry->getClass('class_localization')->formatNumber( $stats['total_worth'], $this->registry->ecoclass->decimal );
			 $frontpageCrap['total_welfare'] = $this->registry->getClass('class_localization')->formatNumber( $stats['total_welfare'], $this->registry->ecoclass->decimal );
			 $frontpageCrap['item_count'] = $this->registry->getClass('class_localization')->formatNumber( $stats['item_count'] );
		}

		#buttons
		$buttons = $this->registry->class_ibEco_CP->makeButtonRow( array('cache') );
		
		#output frontpage!
		$this->registry->output->html .= $this->html->frontPage($buttons, $frontpageCrap, $newsItems);
	}

	/**
	 * Recache ALL!
	 */	
	protected function recacheAll()
	{
		#recache method via our ACP class
		$this->registry->ecoclass->acm('all');

		#init redirect url
		$url = explode('_',$this->request['url']);
		
		#funky 2 word modules...
		$url[2] = ( $url[3] ) ? $url[2].'_'.$url[3] : $url[2];

		#message
		$this->registry->output->global_message = $this->lang->words['all_is_recached'];
		
		#redirect!
		$this->registry->output->silentRedirectWithMessage( "{$this->settings['base_url']}module=".$url[0]."&amp;section=".$url[1]."&amp;do=".$url[2] );
	}
}