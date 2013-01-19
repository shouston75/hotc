<?php
/**
 * Invision Power Services
 * IP.Board v3.0.1
 * 123flashchat Management
 *
 * @author 		$Author: TopCMM $
 * @copyright	(c) 2001 - 2010 TopCMM, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	123flashchat
 * @link		http://www.123flashchat.com
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_123flashchat_123flashchat_settings extends ipsCommand 
{
	/**
	 * Skin file
	 *
	 * @access	public
	 * @var		object
	 */
	public $html;
	
	public $fc_row;
	
	/**
	 * Main execution method
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void
	 */
	public function doExecute( ipsRegistry $registry )
	{
        ini_set("default_socket_timeout",5);
		/* Load Skin and Lang */
		$this->html               = $this->registry->output->loadTemplate( 'cp_skin_123flashchat' );
		$this->html->form_code    = 'module=123flashchat&amp;section=settings';
		$this->html->form_code_js = 'module=123flashchat&section=settings';
		
		$this->lang->loadLanguageFile( array( 'admin_123flashchat' ) );
		switch( $this->request['do'] )
		{
			case 'save':
				$this->flashchatSave();
			break;
			
			case 'list':				
			default:
				$this->flashchatForm(array());
			break;
		}
		
		/* Output */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();			
	}
	
	/**
	 * Handles the 123flashchat settings form
	 *
	 * @access	public
	 * @return	void
	 */
	public function flashchatSave()
	{
		/* INIT */
		$fc_row['fc_server'] = intval( $this->request['fc_server'] );
		switch ($fc_row['fc_server'])
		{
	        case 0:
	            if ($this->request['fc_server_local'])
	            {
	                $fc_row['fc_server_host'] = ($this->request['fc_server_host'] != '127.0.0.1') ? $this->request['fc_server_host'] : parse_url($this->settings['upload_url'], PHP_URL_HOST);
	                $fc_row['fc_server_port'] = $this->request['fc_server_port'] ? $this->request['fc_server_port'] : 51127;
	                $fc_row['fc_http_port'] = $this->request['fc_http_port'] ? $this->request['fc_http_port'] : 35555;
	                $fc_row['fc_api_url'] = 'http://' . $fc_row['fc_server_host'] . ':' . $fc_row['fc_http_port'] . '/'; 
	                $fc_row['fc_client_loc'] = $fc_row['fc_api_url'];
	                $s_own_loc = $this->validate_server($fc_row['fc_server_host'], $fc_row['fc_server_port']);
	                $c_own_loc = $this->validate_client($fc_row['fc_client_loc']);
	                $da = $this->validate_data_api($fc_row['fc_api_url'] . 'online.js');
	                if ($s_own_loc)
	                {
	                    $error[] = 'Chat Server host or port is configured incorrectly';
	                }
	                if ($da || $c_own_loc)
	                {
	                    $error[] = 'Chat Server host or http_port is configured incorrectly';
	                }
                }
                else
                {
                    $fc_row['fc_server_host'] = parse_url($this->settings['upload_url'], PHP_URL_HOST);
                    $fc_row['fc_server_port'] = 51127;
	                $fc_row['fc_http_port'] = 35555;
	                $fc_row['fc_api_url'] = 'http://' . $fc_row['fc_server_host'] . ':' . $fc_row['fc_http_port'] . '/'; 
	                $fc_row['fc_client_loc'] = $fc_row['fc_api_url'];
                }
	            $fc_row['fc_client'] = $this->request['fc_client'];
	            break;
	        case 1:
	            $fc_row['fc_client_loc'] = $this->request['fc_client_loc'] ? ($this->request['fc_client_loc'] . (substr($this->request['fc_client_loc'],-1,1) != '/' ? '/' : '')) : '';
	            $fc_row['fc_server_host'] = parse_url($fc_row['fc_client_loc'], PHP_URL_HOST);
	            if ($content = @file_get_contents($fc_row['fc_client_loc']))
	            {
	                preg_match('/init_port=([0-9]*)/', $content, $matches);
	                $fc_row['fc_server_port'] = $matches[1];
	            }
	            $fc_row['fc_api_url'] = 'http://' . $fc_row['fc_server_host'] . '/';
	            $fc_row['fc_group'] = substr(parse_url($fc_row['fc_client_loc'], PHP_URL_PATH),1,-1);
	            $c_own_loc = $this->validate_client($fc_row['fc_client_loc']);
	            $da = $this->validate_data_api($fc_row['fc_api_url'] . 'online.js?group=' . $fc_row['fc_group']);
	            if ($c_own_loc or $da)
	            {
	                $error[] = 'Client Location is configured incorrectly';
	            }
	            $fc_row['fc_client'] = $this->request['fc_client'];
	            break;
	        case 2:
	            $fc_row['fc_room'] = $this->request['fc_room'] ? $this->request['fc_room'] : parse_url($this->settings['upload_url'], PHP_URL_HOST);
	            $fc_row['fc_client'] = 0;
	            break;
		}
		if ($fc_row['fc_status'] = $this->request['fc_status'])
		{
		    if ($fc_row['fc_server'] !=2 )
			{
				$fc_row['fc_room_list'] = $this->request['fc_room_list'];
			}
		    $fc_row['fc_user_list'] = $this->request['fc_user_list'];
		}
		$fc_row['fc_client_present'] = $this->request['fc_client_present'];
		if ($this->request['fc_client_size'])
	    {
	        $fc_row['fc_client_width'] = '100%';
	        $fc_row['fc_client_height'] = '100%';
        }
        else
		{
	        $fc_row['fc_client_width'] = $this->request['fc_client_width'] ? $this->request['fc_client_width'] : 800;
            $fc_row['fc_client_height'] = $this->request['fc_client_height'] ? $this->request['fc_client_height'] : 600;
		}
	    $fc_row['fc_client_lang'] = $this->request['fc_client_lang'];
        if (!$fc_row['fc_client'])
        {
            $fc_row['fc_client_skin'] = $this->request['fc_client_skin'];
        }
        if($this->request['fc_client_present']){
        	$fc_row['fc_client_width'] = '100%';
	        $fc_row['fc_client_height'] = '100%';
        }
		
		/* Modify 123flashchat */
        if (!sizeof($error))
    	{
            
		    foreach ( $fc_row as $conf_key => $conf_value)
		    {
			    $this->DB->update( 'core_sys_conf_settings', array('conf_value' => $conf_value), "conf_key ='" . $conf_key . "'");
			    //$this->settings['$conf_key'] = $conf_value;
		    }
		
		    /* Rebuild Caches and Bounce */
		    $this->settingsRebuildCache();
		    $this->flashchatForm($fc_row);
    	}
	    else
	    {
		    $fc_row['fc_error']=implode('<br />',$error);
		    $this->flashchatForm($fc_row);
	    }
	}	
	
	/**
	 * Rebuild settings cache
	 *
	 * @access	public
	 * @return	void
	 */
	public function settingsRebuildCache()
	{
		$settings = array();
		
		$this->DB->build( array( 'select' => '*', 'from' => 'core_sys_conf_settings', 'where' => 'conf_add_cache=1' ) );
		$info = $this->DB->execute();
	
		while ( $r = $this->DB->fetch($info) )
		{	
			$value = $r['conf_value'] != "" ?  $r['conf_value'] : $r['conf_default'];
			
			if ( $value == '{blank}' )
			{
				$value = '';
			}

			$settings[ $r['conf_key'] ] = $value;
		}
		
		$this->cache->setCache( 'settings', $settings, array( 'array' => 1, 'deletefirst' => 1 ) );
	}
	/**
	 * Add/Edit 123flashchat Form
	 *
	 * @access	public
	 * @param	string	$type	Either new or edit
	 * @return	void
	 */
	public function flashchatForm($fc_row)
	{
		//-----------------------------------------
		// Init Vars
		//-----------------------------------------
	    if (empty($fc_row))
	    {
			$fc_row['fc_error']  = '';
	        $fc_row['fc_server'] = $this->settings['fc_server'];
	        $fc_row['fc_client_loc'] = $this->settings['fc_client_loc'];
	        $fc_row['fc_room'] = $this->settings['fc_room'];
	        $fc_row['fc_status'] = $this->settings['fc_status'];
	        $fc_row['fc_room_list'] = $this->settings['fc_room_list'];
	        $fc_row['fc_user_list'] = $this->settings['fc_user_list'];
	       
	        $fc_row['fc_server_host'] = $this->settings['fc_server_host'];
	        $fc_row['fc_server_port'] = $this->settings['fc_server_port'];
	        $fc_row['fc_http_port'] = $this->settings['fc_http_port'];
	        $fc_row['fc_client'] = $this->settings['fc_client'];
	        $fc_row['fc_client_present'] =$this->settings['fc_client_present']; 
	        $fc_row['fc_client_width'] =$this->settings['fc_client_width'];
	        $fc_row['fc_client_height'] =$this->settings['fc_client_height']; 
	        $fc_row['fc_client_lang'] =$this->settings['fc_client_lang']; 

	        $fc_row['fc_client_skin'] =$this->settings['fc_client_skin']; 
	    }
		$form         = array();
        $server_array = array(
                    array('0'   , $this->lang->words['fc_server_own'])
                   ,array('1'   , $this->lang->words['fc_server_fc'])
                   ,array('2'   , $this->lang->words['fc_server_free'])
                            );
        $client_array = array(
                    array('0'   , $this->lang->words['fc_client_standard'])
                   ,array('1'   , $this->lang->words['fc_client_html'])
                   ,array('2'   , $this->lang->words['fc_client_avatar'])
                            );
        $lang_array = array(
                    array('*'    , 'Auto detect')
                   ,array('en'   , 'English')
                   ,array('zh-CN', 'GB Chinese')
                   ,array('zh-TW', 'Big5 Chinese')
                   ,array('fr'   , 'French')
                   ,array('it'   , 'Italian')
                   ,array('de'   , 'German')
                   ,array('nl'   , 'Dutch')
                   ,array('hu'   , 'Hungarian')
                   ,array('es'   , 'Spanish')
                   ,array('hr'   , 'Croatian')
                   ,array('tr'   , 'Turkish')
                   ,array('ar'   , 'Arabic')
                   ,array('pt'   , 'Portuguese')
                   ,array('ru'   , 'Russian')
                   ,array('ko'   , 'Korean')
                   ,array('serbian', 'Serbian')
                   ,array('no'   , 'Norwegian')
                   ,array('ja'   , 'Japanese')
                            );
        $skin_array = array(
                    array('default'   , 'Default')
                   ,array('green'   , 'Green')
                   ,array('orange'   , 'Orange')
                   ,array('red'   , 'Red')
                   ,array('black'   , 'Black')
                   ,array('beige'   , 'Beige')
                   ,array('standard', 'Standard')
                   ,array('clean'   , 'Clean')
                   ,array('artistic'   , 'Artistic')
                            );
        $server_js = " onchange=\"serverChange(this.options.selectedIndex)\";";
        $status_js = array(
                    'yes' => 'onclick="dE(\'status_block\', 1);document.getElementById(\'fc_room_list_yes\').checked = true;document.getElementById(\'fc_user_list_yes\').checked = true;"'
                   ,'no'  => 'onclick="dE(\'status_block\', -1);"'
                            );
        $client_js = " onchange=\"clientChange(this.options.selectedIndex)\";";
		
		
		/* Form Elements */
		$form['fc_error']            = $fc_row['fc_error'] ? "<li><span style='color:red;'>" . $fc_row['fc_error'] . "</span></li>" : "";
        $form['fc_server']           = $this->registry->output->formDropdown('fc_server', $server_array, $fc_row['fc_server'], '', $server_js );
		$form['fc_client_loc']       = $this->registry->output->formSimpleInput('fc_client_loc', IPSText::htmlspecialchars($fc_row['fc_client_loc'] ), 45 );
		$form['fc_room']             = $this->registry->output->formSimpleInput('fc_room', IPSText::htmlspecialchars( $fc_row['fc_room'] ), 10 );
		$form['fc_status']           = $this->registry->output->formYesNo('fc_status', $fc_row['fc_status'], '', $status_js );
		$form['fc_room_list']        = $this->registry->output->formYesNo('fc_room_list', $fc_row['fc_room_list'] );
		$form['fc_user_list']        = $this->registry->output->formYesNo('fc_user_list', $fc_row['fc_user_list'] );
		$form['fc_server_host']      = $this->registry->output->formSimpleInput('fc_server_host', IPSText::htmlspecialchars( $fc_row['fc_server_host'] ), 20 );
		$form['fc_server_port']      = $this->registry->output->formSimpleInput('fc_server_port', IPSText::htmlspecialchars( $fc_row['fc_server_port'] ) );
		$form['fc_http_port']        = $this->registry->output->formSimpleInput('fc_http_port', IPSText::htmlspecialchars( $fc_row['fc_http_port'] ) );
        $form['fc_client']           = $this->registry->output->formDropdown('fc_client', $client_array, $fc_row['fc_client'], '', $client_js );
        $form['fc_client_present']   = "<span class='yesno_yes' style='max-width:100px'><input type='radio' name='fc_client_present' value='1' id='fc_client_present_embed' onClick='dE(\"fc_client_full_span\", -1);document.getElementById(\"fc_client_wh\").checked=true;' " . ($fc_row['fc_client_present'] ? "checked" : "") . "/><label for='fc_client_present_embed'>" . $this->lang->words['fc_client_embed'] . "</label></span><span class='yesno_no' style='max-width:100px'><input type='radio' name='fc_client_present' value='0' id='fc_client_present_popup'  onClick='dE(\"fc_client_full_span\", 1);' " . ($fc_row['fc_client_present'] ? "" : "checked") . "/><label for='fc_client_present_popup'>" . $this->lang->words['fc_client_popup'] . "</label></span>";
        $form['fc_client_size']      = "<span style='" . (($fc_row['fc_server'] == 2) ? "display: none;" : "") . "' id='fc_client_full_span_out'><span class='yesno_yes' style='" . ($fc_row['fc_client_present'] ? "display: none;" : "") . "max-width:100px' id='fc_client_full_span'><input type='radio' name='fc_client_size' value='1' id='fc_client_full' " . (($fc_row['fc_client_height'] == '100%') ? 'checked' : '') . "/><label for='fc_client_full'>" . $this->lang->words['fc_client_full'] . "</label></span></span><span class='yesno_no' style='max-width:300px;height:16px'><input type='radio' name='fc_client_size' value='0' id='fc_client_wh' " . (($fc_row['fc_client_height'] != '100%') ? 'checked' : '') . "/>" . $this->lang->words['fc_client_w'] . $this->registry->output->formInput('fc_client_width', IPSText::htmlspecialchars( $fc_row['fc_client_width'] =='100%' ? '' : $fc_row['fc_client_width'] ), '' ,5 , '', '', '' ) . $this->lang->words['fc_client_h'] . $this->registry->output->formSimpleInput('fc_client_height', IPSText::htmlspecialchars( $fc_row['fc_client_height'] == '100%' ? '' : $fc_row['fc_client_height'] ) ) . "</span>";
		$form['fc_client_lang']      = $this->registry->output->formDropdown('fc_client_lang', $lang_array, $fc_row['fc_client_lang'] );
		$form['fc_client_skin']      = $this->registry->output->formDropdown('fc_client_skin', $skin_array, $fc_row['fc_client_skin'] );
		$form['fc_server_own_url']   = 'http://www.123flashchat.com/download-now.html?p=123flashchat.' . ((PATH_SEPARATOR==':') ? (stristr(PHP_OS, 'darwin') ?  'dmg' : 'sh') : 'exe');
		$form['fc_auth_url']         = substr($this->settings['upload_url'], 0, -7) . "index.php?app=123flashchat&amp;module=123flashchat&amp;section=login&amp;username=%username%&amp;password=%password%";
		$s_own_loc = $this -> validate_server('127.0.0.1', 51127);
		$c_own_loc = $this -> validate_client('http://127.0.0.1:35555/');
		$valid = $s_own_loc || $c_own_loc;
		
		/* Output */
		$this->registry->output->html .= $this->html->flashchatForm( $form, $valid, $fc_row );	
	}	

    /**
	* Check 123 FlashChat Server Config
	* @parm $host   Chat Server Host
	* @parm $port   Chat Server Port
	* @return integer config status
	*/
    function validate_server($host, $port)
    {   $s_own_loc = 1;
        $apiCommand = '<Init/>';
        $resultDoc = "";
        $fp = @fsockopen($host, $port, $errno, $errstr, 2);
        if($fp)
        {
            fputs($fp,$apiCommand."\0");
            while (!feof($fp))
            {
                $resultDoc .= fgets($fp, 1024);
                $resultDoc = rtrim($resultDoc);
            }
            $parser = xml_parser_create("UTF-8");
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            if (xml_parse_into_struct($parser, $resultDoc, $values, $tags))
            {
                if($values)
                {
                    $s_own_loc = 0;
                }
                xml_parser_free($parser);
                fclose($fp);
            }
        }
        return $s_own_loc;
    }

    /**
	* Check 123 FlashChat Data api
	* @parm $data_api   Chat Data Api
	* @return integer config status
	*/
    function validate_data_api($data_api)
    {   $da = 1;
        if(@file_get_contents($data_api))
        {
        	$da = 0;
    	}
        return $da;
    }
    
    /**
	* Check 123 FlashChat Client Location
	* @parm $client_loc   Chat Client Location
	* @return integer config status
	*/
    function validate_client($client_loc)
    {
		$c_own_loc = 1;
		$swf = $client_loc . '123flashchat.swf';
        if($headers = @get_headers($client_loc))
        {
            $c_own_loc = (substr($headers[0], 9, 3) == '200') ? 0 : 1;
        }
        return $c_own_loc;
    }
	
}
