<?php
//-----------------------------------------------
// (T23) Mass PM Groups
//-----------------------------------------------
// Components ACP file
//-----------------------------------------------
// Author: Terabyte
// Site: http://www.invisionbyte.net/
// Written on: 08 / 08 / 2007
// Updated on: 21 / 06 / 2008
//-----------------------------------------------
// Copyright (©) 2007 Terabyte
// All Rights Reserved
//-----------------------------------------------

if (!defined('IN_ACP'))
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_t23_mpg
{
	var $ipsclass;
	var $version  = "1.2.2";
	var $mod_name = "(T23) Mass PM Groups";
	var $editor_loaded = 0;
	
	function auto_run()
	{
		//-----------------------------------------
		// Nav info
		//-----------------------------------------
		$this->ipsclass->admin->page_title  = "(T23) Mass PM Groups";
		$this->ipsclass->admin->page_detail = "Mass PM User Groups";
		$this->ipsclass->admin->nav[] = array($this->ipsclass->form_code, '(T23) Mass PM Groups');
		
		//-----------------------------------------
		// Load libby :l
		//-----------------------------------------
		if ( !$this->editor_loaded )
		{
			//-----------------------------------------
			// Load and config the std/rte editors
			//-----------------------------------------
			require_once( ROOT_PATH."sources/handlers/han_editor.php" );
			$this->han_editor           = new han_editor();
			$this->han_editor->ipsclass =& $this->ipsclass;
			$this->han_editor->from_acp = 1;
			$this->han_editor->init();
			
			//-----------------------------------------
			// Load and config the post parser
			//-----------------------------------------
			
			require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
			$this->parser                      =  new parse_bbcode();
			$this->parser->ipsclass            =& $this->ipsclass;
			
			$this->parser->allow_update_caches = 1;
			$this->parser->bypass_badwords = 1;
			$this->parser->parse_html    = $this->ipsclass->vars['msg_allow_html'];
			$this->parser->parse_nl2br   = 1;
			$this->parser->parse_smilies = 1;
			$this->parser->parse_bbcode  = $this->ipsclass->vars['msg_allow_code'];
			
			$this->editor_loaded = 1;
		}
		
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		switch ($this->ipsclass->input['code'])
		{
			case 'view':
				$this->main_page();
				break;
			case 'new':
				$this->form('add');
				break;
			case 'edit':
				$this->form('edit');
				break;
			case 'delete':
				$this->delete();
				break;
			case 'del_all':
				$this->del_all_pms();
				break;
			case 'preview':
				$this->preview();
				break;
			case 'create':
				$this->create();
				break;
			case 'start':
				$this->process_start();
				break;
			case 'send':
				$this->process_run();
				break;
			default:
				$this->main_page();
				break;
		}
	}
	
	function _error($msg="")
	{
		//-----------------------------------------
		// Show the error!!
		//-----------------------------------------
		$this->ipsclass->html .= "
			<div class='warning-box'>
			  <img src='{$this->ipsclass->skin_acp_url}/images/icon_warning.png' alt='Error' />
			  <h2>(T23) Mass PM Groups -> Error</h2>
			  <p><br />{$msg}<br />&nbsp;</p>
			</div><br /><br />".$this->co();
		
		$this->ipsclass->admin->output();
	}
	
	function process_run()
	{
		//-----------------------------------------
		// Do some check X_x
		//-----------------------------------------
		$id = intval($this->ipsclass->input['id']);
		if ( !$id )
		{
			# No MPM ID?
			$this->_error("No Mass PM ID provided.");
			return;
		}
		
		# Select Mass PM
		$data = $this->ipsclass->DB->simple_exec_query(array('select' => '*', 'from'   => 't23_mpg', 'where'  => 'pm_id='.$id) );
		
		if ( !$data['pm_id'] )
		{
			$this->_error("No Mass PM found in the database with the provided ID. Couldn't run the mass pm process.");
			return;
		}
		
		# Resending? Reset values
		if ( isset($this->ipsclass->input['resend']) && intval($this->ipsclass->input['resend']) == 1 )
		{
			$this->ipsclass->DB->do_update( 't23_mpg', array( 'pm_msg_id' => 0, 'pm_totalsent' => 0 ), 'pm_id='.$id );
			
			$data['pm_msg_id']     = 0;
			$data['pm_totalsent'] = 0;
		}
		
		if ( $data['pm_groups'] != "" )
		{
			$mass_groups = explode(",", $data['pm_groups']);
		}
		
		if ( !count($mass_groups) || !is_array($mass_groups) )
		{
			$this->_error("No Group selected to Mass PM.");
			return;
		}
		
		//-----------------------------------------
		// Init vars
		//-----------------------------------------
		$cc_array = array();
		$done  = 0;
		$max   = 0;
		$start = (intval($this->ipsclass->input['st']) > 0) ? intval($this->ipsclass->input['st']) : 0;
		$end   = (intval($this->ipsclass->input['pergo']) && intval($this->ipsclass->input['pergo']) < 501) ? intval( $this->ipsclass->input['pergo']) : 50;
		
		//-----------------------------------------
		// Unserialize Options
		//-----------------------------------------
		$opts = unserialize(stripslashes($data['pm_options']));
		
		foreach( $opts as $k => $v )
		{
			$data[ $k ] = $v;
		}
		
		# Format the query
		$query = $this->_build_members_query( $data );
		
		//-----------------------------------------
		// Load members to PM
		//-----------------------------------------
		$tmp = $this->ipsclass->DB->simple_exec_query(array('select' => 'id',
															'from'   => 'members',
															'where'  => 'id > '.$start.' AND '.$query,
															'limit'  => array(0, 1)
													  )		);
		$max = intval($tmp['id']);
		
		$this->ipsclass->DB->build_query( array( 'select'	=> 'm.id, m.mgroup_others, m.members_display_name, m.msg_total, m.view_pop, me.vdirs, g.g_max_messages',
												 'from'		=> array( 'members'	=> 'm' ),
												 'order'    => 'm.id ASC',
												 'where'	=> 'm.id > '.$start.' AND '.$query,
												 'add_join'	=> array( 0 => array('from'   => array( 'member_extra' => 'me' ),
																				 'where'  => 'me.id=m.id',
																				 'type'   => 'left' ),
																	  1 => array('from'   => array( 'groups'  => 'g' ),
																				 'where'  => 'g.g_id=m.mgroup',
																				 'type'   => 'left' ) ),
												 'limit'  => array($end)
										)	  	);
		$outer = $this->ipsclass->DB->exec_query();
		
		if ( $this->ipsclass->DB->get_num_rows($outer) )
		{
			//-----------------------------------------
			// Start Mass PM Process
			//-----------------------------------------
			while ( $r = $this->ipsclass->DB->fetch_row($outer) )
			{
				$cc_array[ $r['id'] ] = $r;
			}
			
			//-----------------------------------------
			// Override PM Blocklist?
			//-----------------------------------------
			if ( !$data['pm_override_block'] )
			{
				$this->ipsclass->DB->simple_construct(array('select' => '*',
															'from'   => 'contacts',
															'where'  => 'contact_id='.$this->ipsclass->member['id']));
				$this->ipsclass->DB->simple_exec();
				
				while ($r = $this->ipsclass->DB->fetch_row())
				{
					if ( $r['member_id'] && isset($cc_array[$r['member_id']]) && $r['allow_msg'] == 0 )
					{
						unset($cc_array[$r['member_id']]);
					}
				}
			}
			
			//-----------------------------------------
			// We have members to PM? Go on!
			//-----------------------------------------
			if ( count($cc_array) )
			{
				//-----------------------------------------
				// Override Full Messenger?
				//-----------------------------------------
				if ( !$data['pm_override_full'] )
				{
					foreach ($cc_array as $a => $b)
					{
						$groups_id = explode(",", $b['mgroup_others']);
						
						if ( count($groups_id) )
						{
							foreach ( $groups_id as $gid )
							{
								if ( !$this->ipsclass->cache['group_cache'][$gid]['g_id'] )
								{
									continue;
								}
								
								if ( $this->ipsclass->cache['group_cache'][$gid]['g_max_messages'] > $b['g_max_messages'] )
								{
									$b['g_max_messages'] = $this->ipsclass->cache['group_cache'][$gid]['g_max_messages'];
								}
							}
						}
						
						if ( $b['msg_total'] >= $b['g_max_messages'] && $b['g_max_messages'] > 0 )
						{
							unset($cc_array[$b['id']]);
						}
					}
				}
				
				//-----------------------------------------
				// Finally Add PM ;P
				//-----------------------------------------
				if ( !$data['pm_msg_id'] )
				{
					# Load right number of members
					$finalcount = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as total', 'from' => 'members', 'where' => $query ) );
		
					$this->ipsclass->DB->do_insert( 'message_text',
													array('msg_date'		  => time(),
														  'msg_post'		  => $data['pm_message'],
														  'msg_sent_to_count' => intval($finalcount['total']),
														  'msg_post_key'	  => md5(microtime()),
														  'msg_author_id'	  => $this->ipsclass->member['id'],
														 )
												  );
					$msg_id = $this->ipsclass->DB->get_insert_id();
					
					$this->ipsclass->DB->do_update('t23_mpg', array('pm_msg_id' => $msg_id), 'pm_id='.$id);
				}
				else
				{
					$msg_id = $data['pm_msg_id'];
				}
				
				foreach ($cc_array as $uid => $to)
 				{
					//-----------------------------------------
					// Force My-Ass PopUp?
					//-----------------------------------------
					if ( $data['pm_myass_pop'] )
					{
						$show_popup = 1;
					}
					else
					{
						$show_popup = $to['view_pop'];
					}
					
					#Replace the name in PM Subject
					$pm_subject = str_replace("{name}", $to['members_display_name'], $data['pm_subject']);
					
					$this->ipsclass->DB->do_insert('message_topics', array(
										'mt_msg_id'     => $msg_id,
										'mt_date'       => time(),
										'mt_title'      => $pm_subject,
										'mt_from_id'    => $this->ipsclass->member['id'],
										'mt_to_id'      => $to['id'],
										'mt_vid_folder' => 'in',
										'mt_tracking'   => 0,
										'mt_hasattach'  => 0,
										'mt_owner_id'   => $to['id'],
										'mt_hide_cc'    => 1,
												   )					   );
					
					$to['vdirs'] = $to['vdirs'] ? $to['vdirs'] : 'in:Inbox;0|sent:Sent Items;0';
					
					$inbox_count = $this->get_dir_count($to['vdirs']);
					$this->rebuild_dir_count($to['id'], $to['vdirs'], $inbox_count, $show_popup);
					
					#Get last Member ID PMed
					$last = $to['id'];
					
					$done++;
				}
			}
		}
		else
		{
			if ( !$data['pm_totalsent'] )
			{
				$this->_error("Selected Groups have no members that can receive the PM.<br />Select more/other Groups.");
				return;
			}
		}
		
		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------
		if ( !$done && !$max )
		{
		 	//-----------------------------------------
			// Done?
			//-----------------------------------------
			$text = "<b>Mass PM Completed</b><br />Successfully sent <u>".$data['pm_totalsent']."</u> Mass PMs";
			$url  = "view";
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------
			$data['pm_totalsent'] = $data['pm_totalsent'] + $done;
			$this->ipsclass->DB->do_update('t23_mpg', array('pm_totalsent' => $data['pm_totalsent']), 'pm_id='.$id);
			
			$text = "<b>{$data['pm_totalsent']} PMs processed so far, continuing...</b>";
			$url  = "send&amp;id=".$id."&amp;pergo=".$end."&amp;st=".$last;
		}
		
		//-----------------------------------------
		// Redirect
		//-----------------------------------------
		$this->ipsclass->admin->redirect( $this->ipsclass->form_code."&amp;code=".$url, $text, 0, 1 );
	}
	
	function delete()
	{
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		
		if ( !$this->ipsclass->input['id'] )
		{
			$this->_error("No Mass PM ID provided.");
			return;
		}
		
		//-----------------------------------------
		// Delete Mass PM :(
		//-----------------------------------------
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 't23_mpg',
													   'where' => 'pm_id='.$this->ipsclass->input['id']
											  )		 );
		
		$this->ipsclass->admin->save_log("Deleted Mass PM (ID: {$this->ipsclass->input['id']})");
		$this->ipsclass->main_msg = "Mass PM deleted";
		$this->main_page();
	}
	
	function create()
	{
		$groups = array();
		$type = 'add';
		
		//-----------------------------------------
		// We are editing?
		//-----------------------------------------
		if ( $this->ipsclass->input['type'] == 'edit' )
		{
			$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
			if ( !$this->ipsclass->input['id'] )
			{
				$this->_error("No Mass PM ID provided.");
				return;
			}
			
			$this->ipsclass->DB->build_and_exec_query(array('select'	=> 'pm_id',
															'from'		=> 't23_mpg',
															'where'		=> 'pm_id='.$this->ipsclass->input['id']
													 )		);
			
			if ( !$this->ipsclass->DB->get_num_rows() )
			{
				$this->_error("Mass PM not found in the database with the provided ID. Couldn't save edits.");
				return;
			}
			
			$type = 'edit';
		}
		
		if ( !strlen($this->ipsclass->input['pm_subject']) || !strlen($this->ipsclass->input['pm_message']) )
		{
			$this->ipsclass->main_msg = "You must enter a Subject and a Message before sending the PM.";
			$this->form($type);
		}
		
		foreach ($this->ipsclass->input as $k => $v)
 		{
 			if ( preg_match("/^t23_mpg_(\d+)$/", $k, $m) )
 			{
 				if ( $this->ipsclass->input[$k] )
				{
					if ( $this->ipsclass->cache['group_cache'][$m[1]]['g_use_pm'] )
					{
						$groups[] = $m[1];
					}
				}
			}
		}
		
		$groups = $this->ipsclass->clean_int_array( $groups );
		
		if ( !count($groups) )
		{
			$this->ipsclass->main_msg = "You must select at least one Group to Mass PM, or the Groups you have selected can't use the PM System.";
			$this->form($type);
		}
		
 		//-----------------------------------------
 		// Format the query
 		//-----------------------------------------
 		$query = $this->_build_members_query( array( 'pm_opts_post_ltmt'     => $this->ipsclass->input['pm_opts_post_ltmt'],
													 'pm_opts_filter_post'   => $this->ipsclass->input['pm_opts_filter_post'],
													 'pm_opts_visit_ltmt'    => $this->ipsclass->input['pm_opts_visit_ltmt'],
													 'pm_opts_filter_visit'  => intval($this->ipsclass->input['pm_opts_filter_visit']),
													 'pm_opts_joined_ltmt'   => $this->ipsclass->input['pm_opts_joined_ltmt'],
													 'pm_opts_filter_joined' => intval($this->ipsclass->input['pm_opts_filter_joined']),
													 'pm_opts_other'         => intval($this->ipsclass->input['pm_opts_other']),
													 'pm_groups'             => implode(",", $groups),
											)      );
		
		#Check if we have members
		$count = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as tot', 'from' => 'members', 'where' => $query ) );
		
		if ( !intval($count['tot']) )
		{
			$this->ipsclass->main_msg = "There aren't any members with selected Groups that can receive the Mass PM. Choose more/other Groups.";
			$this->form($type);
		}
		
		$message = $this->han_editor->process_raw_post( 'pm_message' );
		
		//-----------------------------------------
		// Set parser :)
		//-----------------------------------------
		$this->parser->parse_html    = $this->ipsclass->vars['msg_allow_html'];
		$this->parser->parse_smilies = 1;
		$this->parser->parse_bbcode  = $this->ipsclass->vars['msg_allow_code'] ;
		
		$message = $this->parser->pre_display_parse( $this->parser->pre_db_parse( $message ) );
		$message = $this->ipsclass->txt_stripslashes( str_replace( "\r", "", $message ) );
		$message = str_replace( "&#39;", "'", $this->ipsclass->txt_stripslashes( $message ) );
		
		//-----------------------------------------
		// Save array
		//-----------------------------------------
		$save_array = array( 'pm_subject'		=> str_replace("&#039;", "'", $this->ipsclass->txt_stripslashes( $_POST['pm_subject'] )),
							 'pm_message'		=> $message,
							 'pm_groups'		=> implode(",", $groups),
							 'pm_date'			=> time(),
							 'pm_override_block'=> $this->ipsclass->input['pm_override_block'],
							 'pm_override_full'	=> $this->ipsclass->input['pm_override_full'],
							 'pm_myass_pop'		=> $this->ipsclass->input['pm_myass_pop'],
							 'pm_options'		=> serialize( array( 'pm_opts_post_ltmt'     => $_POST['pm_opts_post_ltmt'],
																	 'pm_opts_filter_post'   => $_POST['pm_opts_filter_post'],
																	 'pm_opts_visit_ltmt'    => $_POST['pm_opts_visit_ltmt'],
																	 'pm_opts_filter_visit'  => $_POST['pm_opts_filter_visit'],
																	 'pm_opts_joined_ltmt'   => $_POST['pm_opts_joined_ltmt'],
																	 'pm_opts_filter_joined' => $_POST['pm_opts_filter_joined'],
																	 'pm_opts_other'         => $_POST['pm_opts_other'],
														   )	  )
							);
		
		if ($type == 'add')
		{
			$this->ipsclass->DB->do_insert( 't23_mpg', $save_array);
			$this->ipsclass->input['id'] = $this->ipsclass->DB->get_insert_id();
			
			$this->ipsclass->admin->save_log("Mass PM: '{$this->ipsclass->input['pm_subject']}' (ID: {$this->ipsclass->input['id']}) Created");
			$this->process_start();
		}
		else
		{
			$save_array['pm_updated'] = time();
			$save_array['pm_totalsent'] = 0;
			
			$this->ipsclass->DB->do_update( 't23_mpg', $save_array, 'pm_id='.$this->ipsclass->input['id'] );
			
			$this->ipsclass->admin->save_log("Mass PM: '{$this->ipsclass->input['pm_subject']}' (ID: {$this->ipsclass->input['id']}) Edited");
			$this->ipsclass->main_msg = "Mass PM edited";
			
			//-----------------------------------------
			// Where redirect?
			//-----------------------------------------
			if ( $this->ipsclass->input['resend'] )
			{
				$this->process_start();
			}
			else
			{
				$this->main_page();
			}
		}
	}
	
	function process_start()
	{
		$id = intval($this->ipsclass->input['id']);
		if (!$id)
		{
			$this->_error("No Mass PM ID provided.");
			return;
		}
		
		#Load Mass PM
		$data = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*',
															   'from' => 't23_mpg',
															   'where' => 'pm_id='.$id
													  )		 );
		
		if ( !strlen($data['pm_subject']) || !strlen($data['pm_message']) )
		{
			$this->_error("Couldn't send Mass PMs due to Invalid PM Subject or Message.");
			return;
		}
		
		if ( !strlen($data['pm_groups']) )
		{
			$this->_error("Couldn't send Mass PMs because of no Groups Selected.");
			return;
		}
		
		//-----------------------------------------
		// Unserialize Options
		//-----------------------------------------
		$opts = unserialize(stripslashes( $data['pm_options'] ) );
		
		foreach( $opts as $k => $v )
		{
			$data[ $k ] = $v;
		}
		
		# Format the query
		$query = $this->_build_members_query( $data );
		
		#Check if we have members
		$count = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as tot', 'from' => 'members', 'where' => $query ) );
		
		$total = intval($count['tot']);
		
		if ( !$total )
		{
			$this->_error = "There aren't any members with selected Groups that can receive the Mass PM. Choose more/other Groups.";
		}
		
		#Nav
		$this->ipsclass->admin->nav[] = array( '', 'Start Process');
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form(array( 0 => array('section', $this->ipsclass->section_code),
																			1 => array('act', 't23_mpg'),
																			2 => array('code', 'send'),
																			3 => array('id', $this->ipsclass->input['id']),
																			4 => array('resend', ($this->ipsclass->input['resend']) ? 1 : 0 ),
																			5 => array('si', 0)
																	 )		);
		
		//-----------------------------------------
		// Build the table
		//-----------------------------------------
		$this->ipsclass->html .="
<div class='tableborder'>
 <div class='tableheaderalt'>
  <table border='0' cellpadding='0' cellspacing='0' width='100%'>
   <tbody>
    <tr>
     <td style='font-size: 12px; vertical-align: middle; font-weight: bold; color: rgb(255, 255, 255);' align='left' width='95%'>(T23) Mass PM Groups -> Start Process</td>
     <td align='right' width='5%' nowrap='nowrap'><img id='menumainone' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /> &nbsp;</td>
    </tr>
   </tbody>
  </table>
 </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
  <tr>
   <td class='tablesubheader' colspan='2' align='center'>PM Details</td>
  </tr>
  <tr>
   <td class='tablerow1' width='15%' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Members to PM</td>
   <td class='tablerow2' width='85%' align='left' valign='top'>{$total}</td>
  </tr>
  <tr>
   <td class='tablerow1' width='15%' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Subject</td>
   <td class='tablerow2' width='85%' align='left' valign='top'>{$data['pm_subject']}</td>
  </tr>
  <tr>
   <td class='tablerow1' width='15%' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Message</td>
   <td class='tablerow2' width='85%' align='left' valign='top'>{$data['pm_message']}</td>
  </tr>
  <tr>
   <td class='tablesubheader' colspan='2' align='center'>Send Mass PM</td>
  </tr>
  <tr>
   <td class='tablerow1' colspan='2' align='center'>Clicking <u>Begin Mass PM Process</u> button will start the process of Mass PMing members in the selected groups.<br /><br /><span style='color:red;'><b>I strongly recommend that you send no more than 50 PM per process if you don't want to overload the server<br /><br />[If you insert a value greater than 500 then the value will be set to 50 by default!]</b></span></td>
  </tr>
  <tr>
   <td class='tablerow2' colspan='2' align='center' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Note: If you are PMing a large number of members, it could take away to process and send out all PMs.</td>
  </tr>
  <tr>
   <td class='tablesubheader' colspan='2' align='center'><b>PMs to send per cycle:</b> <input type='text' class='realbutton' size='5' name='pergo' value='50'> &nbsp; <input type='submit' value='Begin Mass PM Process' class='realbutton'></td>
  </tr>
 </table>
</div>
</form><br />";
		
		$this->ipsclass->html .= <<<EOF
<script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_edit+" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit&resend=1&id=$id' onclick='menu_action_close();'>Edit this PM</a>",
			 img_delete + "<a href=\"javascript:confirm_action('{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=delete&id=$id', 'Are you sure you want to delete this Mass PM?')\">Remove this PM</a>"
			) );
 </script>
EOF;
		
		$this->ipsclass->html .= $this->co();
		
		$this->ipsclass->admin->output();
	}
	
	function form($type='add')
	{
		$button = "Create Mass PM";
		$pmm = $this->ipsclass->input['pm_message'];
		$pms = $this->ipsclass->input['pm_subject'];
		
		if ($type == 'edit')
		{
			$id = intval($this->ipsclass->input['id']);
			if (!$id)
			{
				$this->_error("No Mass PM ID provided.");
				return;
			}
			
			$button = "Edit Mass PM";
			$data = $this->ipsclass->DB->simple_exec_query(array('select' => '*',
																 'from' => 't23_mpg',
																 'where' => 'pm_id='.$id
														  )		);
			
			if ( !$this->ipsclass->DB->get_num_rows() )
			{
				$this->_error("No PM found in the database with the provided ID.");
				return;
			}
			
			//-----------------------------------------
			// Unserialize Options
			//-----------------------------------------
			$tmp = unserialize( stripslashes( $data['pm_options'] ) );
			
			if ( is_array($tmp) && count($tmp) )
			{
				foreach( $tmp as $k => $v )
				{
					if ( !$data[ $k ] )
					{
						$data[ $k ] = $v;
					}
				}
			}
			
			$pms = $data['pm_subject'];
			
			//-------------------------------
			// Load Post Screen skin
			//-------------------------------
			if ( $this->han_editor->method == 'rte' )
			{
				$pmm = $this->parser->pre_display_parse( $this->parser->pre_db_parse( $data['pm_message'] ) );
				$pmm = $this->parser->convert_ipb_html_to_html( $pmm );
			}
			else
			{
				$pmm = $this->parser->pre_edit_parse( $data['pm_message'] );
			}
		}
		
		//-----------------------------------------
		// Show BBCODE/HTML options
		//-----------------------------------------
		$options = ( $this->ipsclass->vars['msg_allow_code'] == 1 ) ? "<span style='color: green;'>BBCODE ON</span>" : "<span style='color: red;'>BBCODE OFF</span>";
		$options .= " - ";
		$options .= ( $this->ipsclass->vars['msg_allow_html'] == 1 ) ? "<span style='color: green;'>HTML ON</span>" : "<span style='color: red;'>HTML OFF</span>";
		
		$ltmt = array( 0 => array( 'lt' , "less than" ), 1 => array( 'mt' , "more than") );
		
		//-----------------------------------------
		// Show form
		//-----------------------------------------
		$this->ipsclass->html .= "<form id='postingform' onsubmit='return ValidateForm()' action='{$this->ipsclass->base_url}' method='post' name='REPLIER'>
 <input type='hidden' name='id' value='{$this->ipsclass->input['id']}'>
 <input type='hidden' name='resend' value='{$this->ipsclass->input['resend']}'>
 <input type='hidden' name='type' value='$type'>
 <input type='hidden' name='code' value='create'>
 <input type='hidden' name='act' value='t23_mpg'>
 <input type='hidden' name='section' value='{$this->ipsclass->section_code}'>
  <div class='tableborder'>
  <div class='tableheaderalt'>$button ( $options )</div>
  <table class='ipbtable' cellpadding='0' cellspacing='0' width='100%'>
   <tr>
    <td colspan='2'>
     <table class='ipbtable' cellpadding='0' cellspacing='0' width='100%'>
      <tr>
       <td class='tablesubheader' colspan='2'>Send Settings</td>
      </tr>
      <tr>
       <td class='tablerow1' width='25%' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Override PM Block List?</td>
       <td class='tablerow1' width='75%' align='left' valign='top'>".$this->ipsclass->adskin->form_yes_no("pm_override_block", $data['pm_override_block'] )."</td>
      </tr>
      <tr>
       <td class='tablerow1' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Override Full Inbox?</td>
       <td class='tablerow1' align='left' valign='top'>".$this->ipsclass->adskin->form_yes_no("pm_override_full", $data['pm_override_full'] )."</td>
      </tr>
      <tr>
       <td class='tablerow1' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Force My Assistant PopUp?</td>
       <td class='tablerow1' align='left' valign='top'>".$this->ipsclass->adskin->form_yes_no("pm_myass_pop", $data['pm_myass_pop'] )."</td>
      </tr>
      <tr>
       <td class='tablerow1' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Groups</td>
       <td class='tablerow1' align='left' valign='top'>";
		
		foreach ($this->ipsclass->cache['group_cache'] as $id => $g)
		{
			if ( $g['g_id'] == $this->ipsclass->vars['guest_group'] || $g['g_id'] == $this->ipsclass->vars['banned_group'] )
			{
				continue;
			}
			
			if ($type == 'edit')
			{
				$c = '';
				if ( in_array($g['g_id'], explode(",", $data['pm_groups'])) )
				{
					$c = ' checked';
				}
			}
			
			$this->ipsclass->html .= "<input type='checkbox' name='t23_mpg_{$g['g_id']}' value='1'$c />&nbsp;&nbsp;<b>".$g['prefix'].$g['g_title'].$g['suffix']."</b><br />";
		}
		
		$this->ipsclass->html .= "</td>
      </tr>
      <tr>
       <td class='tablerow1' width='25%' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Check Secondary Groups?</td>
       <td class='tablerow1' width='75%' align='left' valign='top'>".$this->ipsclass->adskin->form_yes_no("pm_opts_other", $_POST['pm_opts_other'] ? $_POST['pm_opts_other'] : $data['pm_opts_other'])."</td>
      </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td colspan='2'>
     <table class='ipbtable' cellpadding='0' cellspacing='0' width='100%'>
      <tr>
       <td class='tablesubheader' colspan='2'>Additional Filters</td>
      </tr>
      <tr>
       <td class='tablerow1' width='50%' valign='top'><div style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Send to members with <i><b>n</b></i> posts</div><div class='graytext'>Leave blank to not filter</div></td>
       <td class='tablerow1' width='50%' align='left' valign='top'>". $this->ipsclass->adskin->form_dropdown('pm_opts_post_ltmt', $ltmt, $_POST['pm_opts_post_ltmt'] ? $_POST['pm_opts_post_ltmt'] : $data['pm_opts_post_ltmt'] ).' '.
     $this->ipsclass->adskin->form_simple_input( "pm_opts_filter_post", $_POST['pm_opts_filter_post'] ? $_POST['pm_opts_filter_post'] : $data['pm_opts_filter_post'], 7 )."</td>
      </tr>
      <tr>
       <td class='tablerow1' valign='top'><div style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Send to members who's last visit was <i><b>n</b></i> days ago</div><div class='graytext'>Leave blank to not filter</div></td>
       <td class='tablerow1' align='left' valign='top'>". $this->ipsclass->adskin->form_dropdown('pm_opts_visit_ltmt', $ltmt, $_POST['pm_opts_visit_ltml'] ? $_POST['pm_opts_visit_ltml'] : $data['pm_opts_visit_ltmt'] ).' '.
     $this->ipsclass->adskin->form_simple_input( "pm_opts_filter_visit", $_POST['pm_opts_filter_visit'] ? $_POST['pm_opts_filter_visit'] : $data['pm_opts_filter_visit'], 7 )."</td>
      </tr>
      <tr>
       <td class='tablerow1' valign='top'><div style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Send to members who joined <i><b>n</b></i> days ago</div><div class='graytext'>Leave blank to not filter</div></td>
       <td class='tablerow1' align='left' valign='top'>". $this->ipsclass->adskin->form_dropdown('pm_opts_joined_ltmt', $ltmt, $_POST['pm_opts_joined_ltml'] ? $_POST['pm_opts_joined_ltml'] : $data['pm_opts_joined_ltmt'] ).' '.
     $this->ipsclass->adskin->form_simple_input( "pm_opts_filter_joined", $_POST['pm_opts_filter_joined'] ? $_POST['pm_opts_filter_joined'] : $data['pm_opts_filter_joined'], 7 )."</td>
      </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td class='tablesubheader' colspan='2'>PM Settings</td>
   </tr>
   <tr>
    <td class='tablerow1' valign='middle' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Subject</td>
    <td class='tablerow1' align='left' valign='top'>In the subject you can use the tag <b>{name}</b> that will be replaced with the member name.<br />".$this->ipsclass->adskin->form_simple_input('pm_subject', $pms, 45)."</td>
   </tr>
   <tr>
    <td class='tablerow1' valign='top' style='color: #3A4F6C; font-size: 11px; font-weight: bold; padding: 5px;'>Message</td>
    <td class='tablerow1' align='left' valign='top'>".$this->han_editor->show_editor( $pmm, 'pm_message' )."</td>
   </tr>
   <tr>
    <td class='tablerow1' colspan='2' align='center'>
     <input type='submit' value='$button' />
    </td>
   </tr>
  </table>
  </div>
  </div>
 </form><br />".$this->co();
		
		$this->ipsclass->admin->output();
	}
	
	function main_page()
	{
		//-----------------------------------------
		// Build the table
		//-----------------------------------------
		$title = "<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr>
				<td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>(T23) Mass PM Groups</td>
				<td align='right' width='5%' nowrap='nowrap'><img id='menumainone' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /> &nbsp;</td>
			</tr>
		</table>";
		
		$this->ipsclass->adskin->td_header[] = array("Subject", "40%");
		$this->ipsclass->adskin->td_header[] = array("Date"   , "20%");
		$this->ipsclass->adskin->td_header[] = array("Updated", "20%");
		$this->ipsclass->adskin->td_header[] = array("Sent To", "15%");
		$this->ipsclass->adskin->td_header[] = array("Options", "5%");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $title );
		
		//-----------------------------------------
		// Load Mass PMs
		//-----------------------------------------
		$this->ipsclass->DB->simple_construct(array('select' => 'pm_id, pm_subject, pm_date, pm_updated, pm_totalsent',
													'from'   => 't23_mpg',
													'order'  => 'pm_date DESC'
													));
		$this->ipsclass->DB->simple_exec();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ($r = $this->ipsclass->DB->fetch_row())
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row(array(
										"<b>{$r['pm_subject']}</b>",
										"<div align='center'>".$this->ipsclass->get_date($r['pm_date'], 'SHORT')."</div>",
										"<div align='center'>".$this->ipsclass->get_date($r['pm_updated'], 'SHORT')."</div>",
										"<div align='center'>".$this->ipsclass->do_number_format($r['pm_totalsent']).' Members</div>',
										"<div align='center'><img id='t23_mpg-{$r['pm_id']}' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></div>
				<script type='text/javascript'>
				menu_build_menu
				(
					't23_mpg-{$r['pm_id']}',
					new Array
					(
						img_edit+\" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit&id={$r['pm_id']}' onclick='menu_action_close();'>Edit this PM</a>\",
						img_export+\" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=start&resend=1&id={$r['pm_id']}' onclick=\\\"menu_action_close();\\\">Re-Send this PM</a>\",
						img_delete+\" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=delete&id={$r['pm_id']}' onclick=\\\"menu_action_close();return confirm('Are you sure you want to delete this PM?')\\\">Remove this PM</a>\"
					)
				);
				</script>",
																			)     );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("<b>No mass PMs in the database!</b><br /><br />Want to <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=new'>create</a> one?", 'center', 'tablerow2');
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		$this->ipsclass->html .= "
<script type='text/javascript'>
menu_build_menu
(
	'menumainone',
	new Array
	(
		img_add+\" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=new' onclick='menu_action_close();'>Create Mass PM</a>\",
		img_delete+\" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=del_all' onclick=\\\"menu_action_close();return confirm('Are you sure you want to delete all Mass PMs created?')\\\">Remove all Mass PMs</a>\"
    )
);
</script>".$this->co();
		
		$this->ipsclass->admin->output();
	}
	
	function co()
	{
		return eval(base64_decode('cmV0dXJuICI8ZGl2IGFsaWduPSdjZW50ZXInIGNsYXNzPSdtZW51b3V0ZXJ3cmFwJyBzdHlsZT0ncGFkZGluZzo1cHgnPlBvd2VyZWQgQnk6IHskdGhpcy0+bW9kX25hbWV9IHskdGhpcy0+dmVyc2lvbn0gqSAiLmRhdGUoJ1knKS4iIDxhIGhyZWY9J2h0dHA6Ly93d3cuaW52aXNpb25ieXRlLm5ldC8nIHRhcmdldD0nX2JsYW5rJz5UZXJhYnl0ZTwvYT48L2Rpdj4iOw=='));
	}
	
	function del_all_pms()
	{
		//-----------------------------------------
		// Remove all Mass PMs :'(
		//-----------------------------------------
		$this->ipsclass->DB->do_delete('t23_mpg');
		
		$this->ipsclass->admin->save_log("Removed all Mass PMs");
		$this->ipsclass->admin->redirect($this->ipsclass->form_code, 'Removed all Mass PMs');
	}
	
	function get_dir_count($vdir)
	{
 		preg_match("#(?:^|\|)in:.+?;(\d+)(?:\||$)#i", $vdir, $match);
		return intval($match[1]);
 	}
	
	function rebuild_dir_count($mid, $vdir, $new_count, $show_popup)
	{
		$rebuild = array();
		
		foreach (explode("|", $vdir) as $dir)
		{
			list ($id, $data) = explode(":", $dir);
			list ($real, $count) = explode(";", $data);
			
			if (!$id)
			{
				continue;
			}
			
			if ($id == 'in')
			{
				$count = $new_count + 1;
				$count = $count < 1 ? 0 : $count;
			}
			
			$rebuild[$id] = $id.':'.$real.';'.intval($count);
		}
		
		$final = implode( '|', $rebuild );
		
		$this->ipsclass->DB->simple_construct( array( 'update' =>  'member_extra', 'set' => "vdirs='".$final."'", 'where' => 'id='.$mid ) );
		$this->ipsclass->DB->simple_exec();
		
		$this->ipsclass->DB->simple_construct( array( 'update' =>  'members', 'set' => "msg_total=msg_total+1,new_msg=new_msg+1,show_popup=".$show_popup, 'where' => 'id='.$mid ) );
		$this->ipsclass->DB->simple_exec();
	}
	
	function _build_members_query( $args = array() )
	{
		$query = array();
		# Leave member with Messenger disabled
		$query[] = "members_disable_pm=0";
		
		if ( is_numeric($args['pm_opts_filter_post']) )
		{
			$ltmt    = $args['pm_opts_post_ltmt'] == 'lt' ? '<' : '>';
			$query[] = "posts ".$ltmt." ".intval($args['pm_opts_filter_post']);
		}
		
		if ( $args['pm_opts_filter_visit'] )
		{
			$ltmt    = $args['pm_opts_visit_ltmt'] == 'lt' ? '>' : '<';
			$time    = time() - ( $args['pm_opts_filter_visit'] * 86400 );
			$query[] = "last_visit ".$ltmt." ". $time;
		}
		
		if ( $args['pm_opts_filter_joined'] )
		{
			$ltmt    = $args['pm_opts_joined_ltmt'] == 'lt' ? '>' : '<';
			$time    = time() - ( $args['pm_opts_filter_joined'] * 86400 );
			$query[] = "joined ".$ltmt." ". $time;
		}
		
		if ( $args['pm_groups'] )
		{
			$tmp_q = '(mgroup IN ('. $args['pm_groups'] .')';
			
			if ( $args['pm_opts_other'] )
			{
				$temp  = explode( ',', $args['pm_groups'] );
				
				if ( is_array($temp) && count($temp) )
				{
					$tmp = array();
					
					foreach( $temp as $id )
					{
						$tmp[] = "CONCAT(',',mgroup_others,',') LIKE '%,$id,%'";
					}
					
					$tmp_q .= " OR ( ".implode( ' OR ', $tmp ). " )";
				}
			}
			
			$tmp_q .= ")";
			$query[] = $tmp_q;
		}
		
		return implode( ' AND ', $query );
	}
}
?>