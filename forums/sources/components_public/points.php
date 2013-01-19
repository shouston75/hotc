<?php

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class component_public
{
	var $ipsclass;
	
	var $nav		= array();
	var $output	    = "";
	var $page_title = "";
	
	/*-------------------------------------------------------------------------*/
	// Auto run
	/*-------------------------------------------------------------------------*/
	
	function run_component()
	{
		$this->ipsclass->load_language('lang_points');
		$this->ipsclass->load_template('skin_points');
		
		if ( !$this->ipsclass->vars['points_on'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_points_offline'));
		}
		
		if ( ( !$this->ipsclass->member['id'] ) || ( !$this->ipsclass->member['g_access_pts'] ) )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_access_points'));
		}

		$current = $this->ipsclass->do_number_format($this->ipsclass->member['points']);
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->menu($current);
		
		$this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=points'>{$this->ipsclass->lang['pointssys']}</a>";
		
		switch ( $this->ipsclass->input['cmd'] )
		{
			case 'richest':
				$this->richest_members();
				break;
			case 'do_find_mem':
				$this->do_find_mem();
				break;
			case 'my_transactions':
				$this->my_transactions();
				break;	
			case 'donate':
				$this->donate_link();
				break;
			case 'do_donate':
				$this->do_donate();
				break;
			case 'global_transactions':
				$this->global_transactions();
				break;
			case 'bank':
				$this->bank();
				break;
			case 'deposit':
				$this->deposit_form();
				break;
			case 'withdraw':
				$this->withdraw_form();
				break;
			case 'do_withdraw':
				$this->do_withdraw();
				break;
			case 'do_deposit':
				$this->do_deposit();
				break;
			case 'change_display_name':
				$this->change_display_name();
				break;
			case 'do_change_name':
				$this->do_change_name();
				break;
			case 'change_title':
				$this->change_title();
				break;
			case 'do_change_title':
				$this->do_change_title();
				break;
			case 'do_find_mem2':
				$this->do_find_mem2();
				break;
			case 'find_member_to_edit':
				$this->find_mem2();
				break;
			case 'edit':
				$this->edit_link();
				break;
			case 'do_edit':
				$this->do_edit();
				break;
			case 'do_find_mem3':
				$this->do_find_mem3();
				break;
			case 'change_other_members_title':
				$this->find_mem3();
				break;
			case 'change_m_title_link':
				$this->change_m_title_link();
				break;
			case 'do_change_m_title':
				$this->do_change_m_title();
				break;
			case 'points_edit_log':
				$this->points_edit_log();
				break;
			case 'find_member':
				$this->find_member();
				break;
			case 'points_rundown':
				$this->total_pts();
				break;
			case 'lotto_status':
				$this->lotto_status();
				break;
			case 'purchase_ticket':
				$this->purchase_ticket();
				break;
			case 'do_purchase_ticket':
				$this->do_purchase_ticket();
				break;
			case 'winners':
				$this->lotto_winners();
				break;
			case 'buy_avatar':
				$this->buy_ava();
				break;
			case 'do_buy_avatar':
				$this->do_buy_ava();
				break;
			case 'buy_signature':
				$this->buy_sig();
				break;
			case 'do_buy_signature':
				$this->do_buy_sig();
				break;
			case 'tools':
				$this->tools();
				break;
			case 'mass_donate':
				$this->mass_donate();
				break;
			case 'dump_transactions':
				$this->dtrans();
				break;
			case 'dump_lotto_champs':
				$this->dchamps();
				break;
			case 'group_donate':
				$this->group_donate();
				break;
			case 'dump_avatar':
				$this->d_ava();
				break;
			case 'dump_signature':
				$this->d_sig();
				break;
			case 'reset_points':
				$this->reset_points();
				break;
			case 'reset_bank':
				$this->reset_bank();
				break;
			default:
				$this->points_stats();
				break;
		}
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->footer();

    	//-----------------------------------------
    	// If we have any HTML to print, do so...
    	//-----------------------------------------
    	
    	$this->ipsclass->print->add_output("$this->output");
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav ) );
	
	}

	/*-------------------------------------------------------------------------*/
	// Show Page
	/*-------------------------------------------------------------------------*/
	
	function points_stats()
	{

        $this->ipsclass->DB->simple_construct( array(
        'select' => 'count(id) as members, sum(points) as total_points',
        'from' => 'members',
        ) );

        $this->ipsclass->DB->simple_exec();

        $data = $this->ipsclass->DB->fetch_row();

        $data['per_user'] = ( ($data['total_points'] / $data['members']) );
		$data['members'] = $this->ipsclass->do_number_format($data['members']);
        $data['total_points'] = $this->ipsclass->do_number_format($data['total_points']);
        $data['per_user'] = $this->ipsclass->do_number_format($data['per_user']);

        $this->ipsclass->DB->simple_construct( array(
        'select' => 'count(deposited_points) as bank_users, sum(deposited_points) as in_bank',
        'where' => 'deposited_points > 0',
        'from' => 'members',
        ) );

        $this->ipsclass->DB->simple_exec();

        $row = $this->ipsclass->DB->fetch_row();

        $row['in_bank'] = $this->ipsclass->do_number_format($row['in_bank']);

        $this->ipsclass->DB->simple_construct( array(
        'select' => 'count(tid) as your_trans, sum(amount) as total_trans',
        'where' => 'sentfrom='.$this->ipsclass->member['id'],
        'from' => 'transactions',
        ) );

        $this->ipsclass->DB->simple_exec();

        $trans = $this->ipsclass->DB->fetch_row();

        $trans['total'] = $this->ipsclass->do_number_format($trans['total_trans']);
					 
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->points_stats( $data, $row, $trans );	
		
		$this->page_title = $this->ipsclass->lang['p_stats'];
		$this->nav[] = $this->ipsclass->lang['navigation'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Find Member
	/*-------------------------------------------------------------------------*/
	
	function find_member()
	{
	
		if( $this->ipsclass->vars['donate_on'] == 0 )
		{
			$title = "<{CAT_IMG}>&nbsp;{$this->ipsclass->lang['find_member']}";
			$data = "<i>{$this->ipsclass->lang['donate_off']}</i>";
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->none( $title, $data );
		}
		else if( $this->ipsclass->vars['donate_on'] == 1 )
		{
			$data = array( 'title'     => $this->ipsclass->lang['find_member'],
					   'form_code' => 'do_find_mem',
					   'text'      => $this->ipsclass->lang['donate_to'],
					 );
					 
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->find_member( $data );
		}
		
			$this->page_title = $this->ipsclass->lang['find_member'];
			$this->nav[] = $this->ipsclass->lang['navigation'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Richest Members
	/*-------------------------------------------------------------------------*/
	
	function richest_members()
	{
	$this->ipsclass->DB->build_query( array( 'select' => 'members_display_name, points, mgroup, id',
		'from'   => 'members',
		'order'  => 'points DESC',
		'limit'  => array( 0, 10 )
	) );		 
		$this->ipsclass->DB->exec_query();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			$content = array();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$r['name']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['members_display_name'], $r['mgroup'] ), $r['id'] );
				$r['group'] = $this->ipsclass->make_name_formatted( $this->ipsclass->cache['group_cache'][$r['mgroup']]['g_title'], $r['mgroup'] );
				$r['points'] = $this->ipsclass->do_number_format( $r['points'] );
				
				$content[] = $r;
			}
			
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->richest_members( $content );
		}
		else
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_richest'));
		}
		
		$this->page_title = $this->ipsclass->lang['richest'];
		$this->nav[] = $this->ipsclass->lang['richest'];
	}
	
	/*-------------------------------------------------------------------------*/
	// My Transactions
	/*-------------------------------------------------------------------------*/
	
	function my_transactions()
	{
		if ( isset( $this->ipsclass->input['st'] ) )
		{
			$this->first = intval( $this->ipsclass->input['st'] );
		}
		
		if ( !isset( $this->first ) )
		{
			$this->first = 0;
		}
		
		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) AS total',
																   'from'   => 'transactions',
																   'where'  => 'sentfrom='.$this->ipsclass->member['id'],
														  )		 );
		
		if ( !$count['total'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_transactions'));
		}
		else
		{
$this->ipsclass->DB->build_query( array( 'select' => 't.*',
												 'from'   => array( 'transactions' => 't' ),
												 'where'  => 't.sentfrom='.$this->ipsclass->member['id'],
												'add_join' => array( 
												0 => array( 'select' => 'm.members_display_name, m.id, m.mgroup',
												'from'   => array( 'members' => 'm' ),
												'where'  => 't.sentto=m.id',
												'type'   => 'left' ) ),				
												 'order'  => 't.time DESC',
												 'limit'  => array( 0, 25 )
										)	   );
			$this->ipsclass->DB->exec_query();
			
			$content = array();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{  
				$r['amount'] = $this->ipsclass->do_number_format( $r['amount'] );
				$r['time']   = $this->ipsclass->get_date( $r['time'], 'LONG' );
				$r['name']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['members_display_name'], $r['mgroup'] ), $r['id'] );
				
				$content[] = $r;
			}
			
			$links = $this->ipsclass->build_pagelinks( array( 'TOTAL_POSS' => $count['total'],
															  'PER_PAGE'   => 25,
															  'CUR_ST_VAL' => $this->first,
															  'L_SINGLE'   => "",
															  'L_MULTI'    => $this->ipsclass->lang['pages'],
															  'BASE_URL'   => $this->ipsclass->base_url."autocom=points&amp;cmd=my_transactions",
													 )		);
			
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->my_transactions( $content, $links );
		}
		
		$this->page_title = $this->ipsclass->lang['my_trans'];
		$this->nav[]      = $this->ipsclass->lang['my_trans'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Global Transactions
	/*-------------------------------------------------------------------------*/
	
	function global_transactions()
	{

		if ( !$this->ipsclass->member['g_gtrans_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_gtrans_pts'));
		}

		if ( isset( $this->ipsclass->input['st'] ) )
		{
			$this->first = intval( $this->ipsclass->input['st'] );
		}
		
		if ( !isset( $this->first ) )
		{
			$this->first = 0;
		}
		
		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) AS total',
																   'from'   => 'transactions',
														  )		 );
		
		if ( !$count['total'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_gtransactions'));
		}
		else
		{
			$this->ipsclass->DB->build_query( array( 'select' => 't.*',
												 'from'   => array( 'transactions' => 't' ),
												'add_join' => array( 
												0 => array( 'select' => 'm.members_display_name as ebn, m.id as ebi, m.mgroup as ebg',
												'from'   => array( 'members' => 'm' ),
												'where'  => 't.sentfrom=m.id',
												'type'   => 'left' ),
												1 => array( 'select' => 'mm.members_display_name as mn, mm.id as mi, mm.mgroup as mg',
												'from'   => array( 'members' => 'mm' ),
												'where'  => 't.sentto=mm.id',
												'type'   => 'left' ) ),				

												 'order'  => 't.time DESC',
												 'limit'  => array( 0, 25 )
										)	   );
			$this->ipsclass->DB->exec_query();
			
			$content = array();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{  
				$r['amount'] = $this->ipsclass->do_number_format( $r['amount'] );
				$r['time']   = $this->ipsclass->get_date( $r['time'], 'LONG' );
				$r['ename']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['ebn'], $r['ebg'] ), $r['ebi'] );
				$r['name']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['mn'], $r['mg'] ), $r['mi'] );
				
				$content[] = $r;
			}
			
			$links = $this->ipsclass->build_pagelinks( array( 'TOTAL_POSS' => $count['total'],
															  'PER_PAGE'   => 25,
															  'CUR_ST_VAL' => $this->first,
															  'L_SINGLE'   => "",
															  'L_MULTI'    => $this->ipsclass->lang['pages'],
															  'BASE_URL'   => $this->ipsclass->base_url."autocom=points&amp;cmd=global_transactions",
													 )		);
			
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->global_transactions( $content, $links );
		}
		
		$this->page_title = $this->ipsclass->lang['g_trans'];
		$this->nav[]      = $this->ipsclass->lang['g_trans'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Donation Link
	/*-------------------------------------------------------------------------*/
	
	function donate_link()
	{
	
		if( $this->ipsclass->vars['donate_on'] == 0 )
		{
			$title = "<{CAT_IMG}>&nbsp;{$this->ipsclass->lang['find_member']}";
			$data = "<i>{$this->ipsclass->lang['donate_off']}</i>";
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->none( $title, $data );
		}
		else if( $this->ipsclass->vars['donate_on'] == 1 )
		{
			$this->ipsclass->input['userid'] = intval($this->ipsclass->input['userid']);

			if ( $this->ipsclass->input['userid'] == $this->ipsclass->member['id'] )
			{
				$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_retards'));
			}
		
			if ( !$this->ipsclass->input['userid'] )
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
			}
		
			$r = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->input['userid'],
													  )		 );
		
			if ( $r['id'] )
			{  
				$r['points'] = $this->ipsclass->do_number_format( $r['points'] );
				$r['name']   = $r['members_display_name'];
			
				$this->output .= $this->ipsclass->compiled_templates['skin_points']->donate_link( $r );
			}
			else
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
			}
		
		}
		
		$this->page_title = $this->ipsclass->lang['mkdonation'];
		$this->nav[]      = $this->ipsclass->lang['mkdonation'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Check Donation
	/*-------------------------------------------------------------------------*/
	
	function do_donate()
	{
	
		if( $this->ipsclass->vars['donate_on'] == 0 )
		{
			$title = "<{CAT_IMG}>&nbsp;{$this->ipsclass->lang['find_member']}";
			$data = "<i>{$this->ipsclass->lang['donate_off']}</i>";
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->none( $title, $data );
		}
		else if( $this->ipsclass->vars['donate_on'] == 1 )
		{
			$this->ipsclass->input['userid'] = intval( $this->ipsclass->input['userid'] );
		$this->ipsclass->input['amount'] = str_replace( ',', '', $this->ipsclass->input['amount'] );
		$this->ipsclass->input['amount'] = str_replace( '-', '', intval($this->ipsclass->input['amount']) );
		$amt = intval($this->ipsclass->input['amount']);
			$username = trim( $this->ipsclass->input['username'] );
			$msg = trim( $this->ipsclass->input['message'] );
		
			if ( !$this->ipsclass->input['userid'] )
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
			}
		
			if ( $this->ipsclass->input['userid'] == $this->ipsclass->member['id'] )
			{
				$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_retards'));
			}
		
			if ( $amt > $this->ipsclass->member['points'] )
			{
				$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough'));
			}
		
			$this->ipsclass->DB->build_query( array( 'update' => 'members',
												 'set'    => "points=points+".$amt,
												 'where'  => 'id='.$this->ipsclass->input['userid']
										)	   );
			$this->ipsclass->DB->exec_query();
		
			$this->ipsclass->DB->do_update( 'members', array( 'points' => $this->ipsclass->member['points'] - $amt ), "id=".$this->ipsclass->member['id'] );
		
			$this->ipsclass->DB->do_insert( 'transactions', array( 'sentfrom'       => $this->ipsclass->member['id'],
															   'sentto'         => $this->ipsclass->input['userid'],
															   'amount'         => $amt,
															   'reason'			=> $msg,
															   'time'           => time(),
									  )						 );
		
			if ( $this->ipsclass->vars['points_pm'] )
			{
				$this->do_pm( $msg, $this->ipsclass->input['userid'], $amt );
			}
		
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['p_donated'], "&autocom=points" );
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Do a PM
	/*-------------------------------------------------------------------------*/
	
	function do_pm( $msg="", $userid=0, $amount=0 )
	{
		require_once( ROOT_PATH.'sources/lib/func_msg.php' );
		$msglib           =  new func_msg();
		$msglib->ipsclass =& $this->ipsclass; 	
		$msglib->init();
		
		if( $msg != "" )
		{
			$pm = sprintf( $this->ipsclass->lang['pm_contentm'], $amount, $this->ipsclass->member['members_display_name'], $msg );
		}
		else
		{
			$pm = sprintf( $this->ipsclass->lang['pm_content'], $amount, $this->ipsclass->member['members_display_name'] );
		}
		
		$msglib->to_by_id	 = $userid;
		$msglib->from_member = $this->ipsclass->member;
		$msglib->msg_title   = $this->ipsclass->lang['pm_subject'];
		$msglib->msg_post	 = $pm;
		$msglib->force_pm	 = 1;
		
		$msglib->send_pm();
	}
	
	/*-------------------------------------------------------------------------*/
	// Find a member
	/*-------------------------------------------------------------------------*/
		
	function do_find_mem()
	{
		$this->ipsclass->input['mem_name'] = strtolower( trim( $this->ipsclass->input['mem_name'] ) );
		
		if ( !$this->ipsclass->input['mem_name'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$memb = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'members_display_name, id',
																  'from'   => 'members',
																  'where'  => "members_l_display_name='".$this->ipsclass->input['mem_name']."'",
														 )		);
		
		if ( !$memb['id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		if ( $memb['id'] == $this->ipsclass->member['id'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_retards'));
		}
		
		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&cmd=donate&amp;userid=".$memb['id'] );
	}
	
	/*-------------------------------------------------------------------------*/
	// Show the bank splash page
	/*-------------------------------------------------------------------------*/
	
	function bank()
	{
		$data['points'] = $this->ipsclass->do_number_format($this->ipsclass->member['points']);
		$data['bank'] = $this->ipsclass->do_number_format($this->ipsclass->member['deposited_points']);
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->bank( $data );
		
		$this->page_title = $this->ipsclass->lang['bank'];
		$this->nav[]      = $this->ipsclass->lang['bank'];
	}

	/*-------------------------------------------------------------------------*/
	// Show the withdraw form
	/*-------------------------------------------------------------------------*/
	
	function withdraw_form()
	{
		if ( !$this->ipsclass->member['deposited_points'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_moneyinbank'));
		}
		else
		{
		
		$data['points'] = $this->ipsclass->do_number_format($this->ipsclass->member['points']);
		$data['bank'] = $this->ipsclass->do_number_format($this->ipsclass->member['deposited_points']);
		
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->bank_withdraw( $data );
		}
		
		$this->page_title = $this->ipsclass->lang['bank'];
		$this->nav[]      = $this->ipsclass->lang['bank'];
	}

	/*-------------------------------------------------------------------------*/
	// Show the deposit form
	/*-------------------------------------------------------------------------*/
	
	function deposit_form()
	{
		if ( !$this->ipsclass->vars['points_bank'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_bank_offline'));
		}
		
		if ( !$this->ipsclass->member['points'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_points'));
		}
		
		$data['points'] = $this->ipsclass->do_number_format($this->ipsclass->member['points']);
		$data['bank'] = $this->ipsclass->do_number_format($this->ipsclass->member['deposited_points']);
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->bank_depo( $data );
		
		$this->page_title = $this->ipsclass->lang['bank'];
		$this->nav[]      = $this->ipsclass->lang['bank'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Do a deposit
	/*-------------------------------------------------------------------------*/
	
	function do_deposit()
	{
		$this->ipsclass->input['points'] = str_replace( ',', '', $this->ipsclass->input['points'] );
		$this->ipsclass->input['pts'] = str_replace( '-', '', intval($this->ipsclass->input['points']) );
		$pts = intval($this->ipsclass->input['points']);
		
		if ( !$this->ipsclass->vars['points_bank'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_bank_offline'));
		}
		
		if ( !$this->ipsclass->member['points'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_points'));
		}
		
		if ( $pts > $this->ipsclass->member['points'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_points'));
		}

		if ( !$pts )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_amount'));
		}
		
		$this->ipsclass->DB->do_update( 'members', array( 'deposited_points' => $this->ipsclass->member['deposited_points'] + $pts,
														  'points'           => $this->ipsclass->member['points'] - $pts,
														), 'id='.$this->ipsclass->member['id']
									  );
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['depoed'], 'autocom=points' );
	}
	
	/*-------------------------------------------------------------------------*/
	// Do a withdrawal
	/*-------------------------------------------------------------------------*/
	
	function do_withdraw()
	{
		$this->ipsclass->input['points'] = str_replace( ',', '', $this->ipsclass->input['points'] );
		$this->ipsclass->input['pts'] = str_replace( '-', '', intval($this->ipsclass->input['points']) );
		$pts = intval($this->ipsclass->input['points']);
		
		if ( !$this->ipsclass->vars['points_bank'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_bank_offline'));
		}
		
		if ( !$this->ipsclass->member['deposited_points'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_moneyinbank'));
		}
		
		if ( $this->ipsclass->member['deposited_points'] < $pts )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_wd'));
		}

		if ( !$pts )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_amount'));
		}
		
		$this->ipsclass->DB->do_update( 'members', array( 'deposited_points' => $this->ipsclass->member['deposited_points'] - $pts,
														  'points'           => $this->ipsclass->member['points'] + $pts,
														), 'id='.$this->ipsclass->member['id']
									  );
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['withdrawed'], 'autocom=points' );
	}
	
	/*-------------------------------------------------------------------------*/
	// Show the shop splash page
	/*-------------------------------------------------------------------------*/
	
	function change_display_name()
	{
		if ( !$this->ipsclass->vars['shop_on'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_offline'));
		}
		if ( !$this->ipsclass->vars['change_dname'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_func_off'));
		}
		
		$shop = array( 'name'  => $this->ipsclass->member['members_display_name'],
					   'price' => $this->ipsclass->do_number_format( $this->ipsclass->vars['change_dname_amt'] ),
					 );
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->shop( $shop );
		
		$this->page_title = $this->ipsclass->lang['shop'];
		$this->nav[]      = $this->ipsclass->lang['shop'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Change the name
	/*-------------------------------------------------------------------------*/
	
	function do_change_name()
	{
		$new_dname = trim( $this->ipsclass->input['change_name'] );
		
		$new_l_name = strtolower( trim( $this->ipsclass->input['change_name'] ) );
		
		if ( !$new_dname )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_name_entered'));
		}
		
		if ( $this->ipsclass->member['points'] < $this->ipsclass->vars['change_dname_amt'])
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough'));
		}
		
		$this->ipsclass->DB->do_update( 'members', array( 'members_display_name' => $new_dname, 'members_l_display_name' => $new_l_name,
														  'points'               => $this->ipsclass->member['points'] - $this->ipsclass->vars['change_dname_amt'],
														), 'id='.$this->ipsclass->member['id']
									  );
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['name_changed'] , 'autocom=points' );
	}
	
	/*-------------------------------------------------------------------------*/
	// Change the title
	/*-------------------------------------------------------------------------*/
	
	function change_title()
	{
		if ( !$this->ipsclass->vars['shop_on'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_offline'));
		}
		
		if ( !$this->ipsclass->vars['shop_title'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_func_off'));
		}

		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'title',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->member['id'],
													  )		 );
		$data['price'] = $this->ipsclass->do_number_format( $this->ipsclass->vars['shop_title_amt'] );
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->change_title( $data );
		
		$this->page_title = $this->ipsclass->lang['shop'];
		$this->nav[]      = $this->ipsclass->lang['shop'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Change the title, take 2
	/*-------------------------------------------------------------------------*/
	
	function do_change_title()
	{
		$new_title = trim( $this->ipsclass->input['change_title'] );
		
		if ( !$new_title )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_title_entered'));
		}
		
		if ( $this->ipsclass->member['points'] < $this->ipsclass->vars['shop_title_amt'])
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough'));
		}
		
		$this->ipsclass->DB->do_update( 'members', array( 'title'  => $new_title,
														  'points' => $this->ipsclass->member['points'] - $this->ipsclass->vars['shop_title_amt'],
														), 'id='.$this->ipsclass->member['id']
									  );
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['title_changed'] , 'autocom=points' );
	}
	
	/*-------------------------------------------------------------------------*/
	// Another find member function
	/*-------------------------------------------------------------------------*/
	
	function find_mem2()
	{

		if ( !$this->ipsclass->member['g_admin_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin_points'));
		}
		
		$data = array( 'title'     => $this->ipsclass->lang['find_edit'],
					   'form_code' => 'do_find_mem2',
					   'text'      => $this->ipsclass->lang['m_to_edit'],
					 );
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->find_member( $data );	
		
		$this->page_title = $this->ipsclass->lang['shop'];
		$this->nav[]      = $this->ipsclass->lang['navigation'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Another do find member function
	/*-------------------------------------------------------------------------*/
	
	function do_find_mem2()
	{

		if ( !$this->ipsclass->member['g_admin_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin_points'));
		}

		$this->ipsclass->input['mem_name'] = strtolower( trim( $this->ipsclass->input['mem_name'] ) );
		
		if ( !$this->ipsclass->input['mem_name'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$memb = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'members_display_name, id',
																  'from'   => 'members',
																  'where'  => "members_l_display_name='".$this->ipsclass->input['mem_name']."'",
														 )		);
		
		if ( !$memb['id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=edit&amp;userid=".$memb['id'] );
	}
	
	/*-------------------------------------------------------------------------*/
	// Edit Link
	/*-------------------------------------------------------------------------*/
	
	function edit_link()
	{
		$this->ipsclass->input['userid'] = intval( $this->ipsclass->input['userid'] );
		
		if ( !$this->ipsclass->member['g_admin_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin_points'));
		}
		
		if ( !$this->ipsclass->input['userid'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$r = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'id, members_display_name, points, deposited_points, mgroup',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->input['userid'],
													  )		 );
		
		if ( !$r['id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$r['points'] = $this->ipsclass->do_number_format( $r['points'] );
		$r['deposited_points'] = $this->ipsclass->do_number_format( $r['deposited_points'] );
		$r['name']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['members_display_name'], $r['mgroup'] ), $r['id'] );
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->edit_link( $r );
		
		$this->page_title = $this->ipsclass->lang['edit_member'];
		$this->nav[]      = $this->ipsclass->lang['edit_member'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Edit Link
	/*-------------------------------------------------------------------------*/
	
	function do_edit()
	{

		$this->ipsclass->input['userid'] = intval( $this->ipsclass->input['userid'] );
		$this->ipsclass->input['points'] = str_replace( ',', '', $this->ipsclass->input['points'] );
		$this->ipsclass->input['pts'] = str_replace( '-', '', intval($this->ipsclass->input['points']) );
		$pts = intval($this->ipsclass->input['points']);
		$this->ipsclass->input['bank'] = str_replace( ',', '', $this->ipsclass->input['bank'] );
		$this->ipsclass->input['bank'] = str_replace( '-', '', intval($this->ipsclass->input['bank']) );
		$bnk = intval($this->ipsclass->input['bank']);

		$username                        = trim( $this->ipsclass->input['username'] );
		$reason                          = trim( $this->ipsclass->input['reason'] );
		
		if ( !$this->ipsclass->member['g_admin_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin_points'));
		}
		
		if ( !$this->ipsclass->input['userid'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$r = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'members_display_name, points, deposited_points, id',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->input['userid'],
													  )		 );
		$this->ipsclass->DB->do_insert( 'points_log', array( 'editedby_id'   => $this->ipsclass->member['id'],
															 'member_id'     => $this->ipsclass->input['userid'],
															 'opoints'  => $r['points'],
															 'npoints'       => $pts,

'obank'  => $r['deposited_points'],
															 'nbank'       => $bnk,
															 'reason'        => $reason,
															 'time'          => time(),
									  )					   );
		
		$this->ipsclass->DB->do_update( 'members', array( 'points' => $pts, 'deposited_points' => $bnk ),'id='.$this->ipsclass->input['userid'] );
		
		if ( $this->ipsclass->vars['points_edit_pm'] )
		{
			$this->do_pm1( $reason, $this->ipsclass->input['userid'] );
		}
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['member_edited'], "&autocom=points" );
	}
	
	/*-------------------------------------------------------------------------*/
	// Another PM function
	/*-------------------------------------------------------------------------*/
	
	function do_pm1( $reason="", $userid=0 )
	{
		require_once( ROOT_PATH.'sources/lib/func_msg.php' );
		$msglib           = new func_msg();
		$msglib->ipsclass =& $this->ipsclass; 	
		$msglib->init();
		
		if ( $reason )
		{
			$pm = sprintf( $this->ipsclass->lang['pm_contentm1'], $this->ipsclass->member['members_display_name'], $this->ipsclass->input['reason'] );
		}
		else
		{
			$pm = sprintf( $this->ipsclass->lang['pm_content1'], $this->ipsclass->member['members_display_name'] );
		}
		
		$msglib->to_by_id	 = $userid;
		$msglib->from_member = $this->ipsclass->member;
		$msglib->msg_title   = $this->ipsclass->lang['pm_subject1'];
		$msglib->msg_post	 = $pm;
		$msglib->force_pm	 = 1;
		$msglib->send_pm();
	}
	
	/*-------------------------------------------------------------------------*/
	// Yet another find member function
	/*-------------------------------------------------------------------------*/
	
	function find_mem3()
	{
		if ( !$this->ipsclass->vars['shop_on'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_offline'));
		}
		
		if ( !$this->ipsclass->vars['shop_change_title'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_func_off'));
		}
		$data = array( 'title'     => $this->ipsclass->lang['findmem'],
					   'form_code' => 'do_find_mem3',
					   'text'      => $this->ipsclass->lang['member2changetitle'],
					 );
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->find_member( $data );	
		
		$this->page_title = $this->ipsclass->lang['shop'];
		$this->nav[]      = $this->ipsclass->lang['shop'];
	}
	
	/*-------------------------------------------------------------------------*/
	// ...And its 'do' counterpart
	/*-------------------------------------------------------------------------*/
	
	function do_find_mem3()
	{
		$this->ipsclass->input['mem_name'] = strtolower( trim( $this->ipsclass->input['mem_name'] ) );
		
		if ( !$this->ipsclass->input['mem_name'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		$memb = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'members_display_name, id, title, mgroup',
																  'from'   => 'members',
																  'where'  => "members_l_display_name='".$this->ipsclass->input['mem_name']."'",
														 )		);
		
		if ( !$memb['id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$admin_groups = array();
		
		foreach ( $this->ipsclass->cache['group_cache'] as $k => $v )
		{
			if ( $v['g_access_cp'] )
			{
				$admin_groups[] = $v['g_id'];
			}
		}
		
		if ( in_array( $memb['mgroup'], $admin_groups ) )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin'));
		}
		
		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=change_m_title_link&amp;userid=".$memb['id'] );
	}
	
	/*-------------------------------------------------------------------------*/
	// Change title link
	/*-------------------------------------------------------------------------*/
	
	function change_m_title_link()
	{
		$this->ipsclass->input['userid'] = intval( $this->ipsclass->input['userid'] );
		
		if ( !$this->ipsclass->vars['shop_on'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_offline'));
		}
		
		if ( !$this->ipsclass->vars['shop_change_title'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_func_off'));
		}
		
		if ( !$this->ipsclass->input['userid'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$r = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->input['userid'],
													  )		 );
		if ( !$r['id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$r['points'] = $this->ipsclass->do_number_format( $r['points'] );
		$r['name']   = $r['members_display_name'];
		$r['price']  = $this->ipsclass->do_number_format( $this->ipsclass->vars['change_mem_title_amt'] );
		
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->change_m_title( $r );
		
		$this->page_title = $this->ipsclass->lang['shop'];
		$this->nav[]      = $this->ipsclass->lang['shop'];
	}
	
	/*-------------------------------------------------------------------------*/
	// Change the title
	/*-------------------------------------------------------------------------*/
	
	function do_change_m_title()
	{
		$userid    = intval( $this->ipsclass->input['userid'] );
		$new_title = trim( $this->ipsclass->input['title'] );
		
		if ( !$this->ipsclass->vars['shop_on'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_offline'));
		}
		
		if ( !$this->ipsclass->vars['shop_change_title'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_shop_func_off'));
		}	   
		
		if ( !$userid )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		if ( !$new_title )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_title_entered'));
		}
		
		if ( $this->ipsclass->member['points'] < $this->ipsclass->vars['change_mem_title_amt'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough'));
		}
		
		$m = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'points,title,id',
															   'from'   => 'members',
															   'where'  => 'id='.$userid,
													  )		 );
		
		if ( !$m['id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_user' ) );
		}
		
		$this->ipsclass->DB->do_update( 'members', array( 'title' => $new_title ), 'id='.$userid );

		$this->ipsclass->DB->do_update( 'members', array( 'points' => $this->ipsclass->member['points'] - $this->ipsclass->vars['change_mem_title_amt'] ), 'id='.$this->ipsclass->member['id'] );
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['title_changed'] , 'autocom=points' );
	}
	
	/*-------------------------------------------------------------------------*/
	// Points Edit Log
	/*-------------------------------------------------------------------------*/
	
	function points_edit_log()
	{

		if ( !$this->ipsclass->member['g_admin_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin_points'));
		}

		if ( isset( $this->ipsclass->input['st'] ) )
		{
			$this->first = intval( $this->ipsclass->input['st'] );
		}
		
		if ( !isset( $this->first ) )
		{
			$this->first = 0;
		}
		
		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) AS total',
																   'from'   => 'points_log',
														  )		 );
		
		if ( !$count['total'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_edit_logs'));
		}
		else
		{
			$this->ipsclass->DB->build_query( array( 'select' => 'p.*',
												 'from'   => array( 'points_log' => 'p' ),
												'add_join' => array( 
												0 => array( 'select' => 'm.members_display_name as ebn, m.id as ebi, m.mgroup as ebg',
												'from'   => array( 'members' => 'm' ),
												'where'  => 'p.editedby_id=m.id',
												'type'   => 'left' ),
												1 => array( 'select' => 'mm.members_display_name as mn, mm.id as mi, mm.mgroup as mg',
												'from'   => array( 'members' => 'mm' ),
												'where'  => 'p.member_id=mm.id',
												'type'   => 'left' ) ),				

												 'order'  => 'p.time DESC',
												 'limit'  => array( 0, 10 )
										)	   );
			$this->ipsclass->DB->exec_query();
			
			$content = array();
			
			while( $r = $this->ipsclass->DB->fetch_row() )
			{  
				$r['opoints'] = $this->ipsclass->do_number_format( $r['opoints'] );
				$r['npoints']      = $this->ipsclass->do_number_format( $r['npoints'] );
				$r['obank'] = $this->ipsclass->do_number_format( $r['obank'] );
				$r['nbank']      = $this->ipsclass->do_number_format( $r['nbank'] );
				$r['time']     = $this->ipsclass->get_date( $r['time'], 'LONG' );
				$r['ename']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['ebn'], $r['ebg'] ), $r['ebi'] );
				$r['name']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['mn'], $r['mg'] ), $r['mi'] );
				
				$content[] = $r;
			}
			
			$links = $this->ipsclass->build_pagelinks( array( 'TOTAL_POSS' => $count['total'],
															  'PER_PAGE'   => 15,
															  'CUR_ST_VAL' => $this->first,
															  'L_SINGLE'   => "",
															  'L_MULTI'    => $this->ipsclass->lang['pages'],
															  'BASE_URL'   => $this->ipsclass->base_url."autocom=points&amp;cmd=points_edit_log",
													 )		);
			
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->points_edit_log( $content, $links );
		}
		
		$this->page_title = $this->ipsclass->lang['e_log'];
		$this->nav[]      = $this->ipsclass->lang['e_log'];
	}

	/*-------------------------------------------------------------------------*/
	// Total Points Rundown
	/*-------------------------------------------------------------------------*/
	
	function total_pts()
	{

		if ( !$this->ipsclass->member['g_admin_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_admin_points'));
		}

		if ( isset( $this->ipsclass->input['st'] ) )
		{
			$this->first = intval( $this->ipsclass->input['st'] );
		}
		
		if ( !isset( $this->first ) )
		{
			$this->first = 0;
		}
		
		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(points) AS total',
																   'from'   => 'members',
														  )		 );
		
		if ( !$count['total'] )
		{	
			$title = "<{CAT_IMG}>&nbsp;{$this->ipsclass->lang['p_rundown']}";
			$data = "$this->ipsclass->lang['no_rundown']";
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->none( $title, $data );
		}
		else
		{
		$this->ipsclass->DB->build_query( array( 
			'select' => 'members_display_name, points, deposited_points, mgroup, id',
			'from'   => 'members',
			'order'  => 'points DESC',
			'limit'  => array( $this->first, 25 )
		) );		 
		$this->ipsclass->DB->exec_query();
			
			$content = array();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{  
				$r['points'] = $this->ipsclass->do_number_format( $r['points'] );
				$r['deposited_points'] = $this->ipsclass->do_number_format( $r['deposited_points'] );
				$r['name']   = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['members_display_name'], $r['mgroup'] ), $r['id'] );
				$r['group'] = $this->ipsclass->make_name_formatted( $this->ipsclass->cache['group_cache'][$r['mgroup']]['g_title'], $r['mgroup'] );
				
				$content[] = $r;
			}
			
			$links = $this->ipsclass->build_pagelinks( array( 'TOTAL_POSS' => $count['total'],
															  'PER_PAGE'   => 25,
															  'CUR_ST_VAL' => $this->first,
															  'L_SINGLE'   => "",
															  'L_MULTI'    => $this->ipsclass->lang['pages'],
															  'BASE_URL'   => $this->ipsclass->base_url."autocom=points&amp;cmd=points_rundown",
													 )		);
			
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->total_pts( $content, $links );
		}
		
		$this->page_title = $this->ipsclass->lang['p_rundown'];
		$this->nav[]      = $this->ipsclass->lang['p_rundown'];
	}

	/*-------------------------------------------------------------------------*/
	// Lotto Status
	/*-------------------------------------------------------------------------*/
	
	function lotto_status()
	{

		$q = $this->ipsclass->DB->build_and_exec_query( array( 'select'   => 'l.*',
                                                       'from'      => array( 'lotto_champs' => 'l' ),
                                                       'add_join' => array( 0 => array( 'select' => 'm.id, m.members_display_name, m.mgroup',
                                                                                        'from'   => array( 'members' => 'm' ),
                                                                                        'where'  => 'm.id=l.c_id',
                                                                                        'type'   => 'left' ) ),
																						'order'  => 'l.round DESC',
                                              )         );

                $new_round = $q['round'] + 1;

        $this->ipsclass->DB->simple_construct( array(
        'select' => 'count(m_id) as tickets, sum(ticket_price) as amount',
        'from' => 'lotto',
        'where'  => 'round='.$new_round,
        ) );

        $this->ipsclass->DB->simple_exec();

        $data = $this->ipsclass->DB->fetch_row();

        $this->ipsclass->vars['lotto_amt'] = str_replace( ',', '', $this->ipsclass->vars['lotto_amt'] );
        $this->ipsclass->vars['lotto_amt'] = str_replace( '-', '', intval($this->ipsclass->vars['lotto_amt']) );
        $data['base'] = $this->ipsclass->vars['lotto_amt'];
        $data['tickets'] = $this->ipsclass->do_number_format($data['tickets']);
        $data['in_pot'] = $this->ipsclass->do_number_format(( $data['base'] + $data['amount'] ));
        $data['base'] = $this->ipsclass->do_number_format($data['base']);
        $data['amount'] = $this->ipsclass->do_number_format($data['amount']);

        if( !$q['c_id'] )
        {
               $data['name']  = "<i>No Winner</i>";
        }
        else if( $q['c_id'] )
        {
               $data['name']  = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $q['members_display_name'], $q['mgroup'] ), $q['id'] );
        }
		

		if ( isset( $this->ipsclass->input['st'] ) )
		{
			$this->first = intval( $this->ipsclass->input['st'] );
		}
		
		if ( !isset( $this->first ) )
		{
			$this->first = 0;
		}
		
		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) AS total',
																   'from'   => 'lotto',
																   'where'  => 'round='.$new_round,
														  )		 );
		
			$this->ipsclass->DB->build_query(array(
											'select'   => 'l.*',
											'from'     => array('lotto' => 'l'),
											'where'  => 'round='.$new_round,
											'add_join' => array(
											0 => array('select' => 'm.id, m.members_display_name, m.mgroup',
													   'from'   => array('members' => 'm'),
													   'where'  => 'm.id=l.m_id',
													   'type'   => 'left' ) ),
													   'order'  => 'l.time DESC',
													 'limit'  => array( $this->first, 15 )
											)    );
											
			$this->ipsclass->DB->exec_query();
			
			$content = array();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{  
				$r['time']   = $this->ipsclass->get_date( $r['time'], 'LONG' );
				$r['ticket_price'] = $this->ipsclass->do_number_format( $r['ticket_price'] );
				$r['name']  = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['members_display_name'], $r['mgroup'] ), $r['id'] );
				
				$content[] = $r;
			}
			
			$links = $this->ipsclass->build_pagelinks( array( 'TOTAL_POSS' => $count['total'],
															  'PER_PAGE'   => 15,
															  'CUR_ST_VAL' => $this->first,
															  'L_SINGLE'   => "",
															  'L_MULTI'    => $this->ipsclass->lang['pages'],
															  'BASE_URL'   => $this->ipsclass->base_url."autocom=points&amp;cmd=lotto_status",
													 )		);

					 
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->lotto_status( $data, $content, $links );	
		
		$this->page_title = $this->ipsclass->lang['l_status'];
		$this->nav[] = $this->ipsclass->lang['navigation'];
	}


	/*-------------------------------------------------------------------------*/
	// Purchase Ticket
	/*-------------------------------------------------------------------------*/
	
	function purchase_ticket()
	{

                $r = $this->ipsclass->DB->build_and_exec_query( array( 'select' =>                             			'MAX(round) as round',
			'from' => 'lotto_champs'
) );
$new_round = $r['round'] + 1;

			$count = $this->ipsclass->DB->build_and_exec_query( array( 			'select' => 'COUNT(round) AS total',
			'from' => 'lotto',
			'where' => 'm_id='.$this->ipsclass->member['id'].' AND round='.$new_round,
			) );

         if($count['total'] >= $this->ipsclass->vars['lotto_limit'])
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_max_tickets'));
		}

        if($this->ipsclass->vars['lotto_price'] > $this->ipsclass->member['points'])
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_for_ticket'));
		}
					 
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->buy_tickets();	
		
		$this->page_title = $this->ipsclass->lang['buy_ticket'];
		$this->nav[] = $this->ipsclass->lang['navigation'];
	}

	/*-------------------------------------------------------------------------*/
	// Do Purchase Ticket
	/*-------------------------------------------------------------------------*/
	
	function do_purchase_ticket()
	{

                $r = $this->ipsclass->DB->build_and_exec_query( array( 'select' =>                             			'MAX(round) as round',
			'from' => 'lotto_champs'
) );
$new_round = $r['round'] + 1;

			$count = $this->ipsclass->DB->build_and_exec_query( array( 			'select' => 'COUNT(round) AS total',
			'from' => 'lotto',
			'where' => 'm_id='.$this->ipsclass->member['id'].' AND round='.$new_round,
			) );

         if($count['total'] >= $this->ipsclass->vars['lotto_limit'])
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_max_tickets'));
		}

        if($this->ipsclass->vars['lotto_price'] > $this->ipsclass->member['points'])
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_for_ticket'));
		}
		
		$current_points = $this->ipsclass->member['points'];
		$ticket_price = $this->ipsclass->vars['lotto_price'];
		$new_points = ( $current_points - $ticket_price );

		$this->ipsclass->DB->do_update( 'members', array( 'points' => $new_points ), "id=".$this->ipsclass->member['id'] );
		
		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'round',
																  'from'   => 'lotto_champs',
																  'order'  => 'round DESC',
														 )		);

		$round = $data['round'] + 1;
														 
		$this->ipsclass->DB->do_insert( 'lotto', array( 'm_id'       => $this->ipsclass->member['id'],
															   'round'   => $round,
															   'ticket_price'         => $ticket_price,
															   'time'           => time(),
									  )						 );
					 
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ticket_purchased'], "autocom=points&amp;cmd=lotto_status" );
	}

	/*-------------------------------------------------------------------------*/
	// Lotto Winners
	/*-------------------------------------------------------------------------*/
	
	function lotto_winners()
	{
		if ( isset( $this->ipsclass->input['st'] ) )
		{
			$this->first = intval( $this->ipsclass->input['st'] );
		}
		
		if ( !isset( $this->first ) )
		{
			$this->first = 0;
		}
		
		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) AS total',
																   'from'   => 'lotto_champs',
														  )		 );
		
		if ( !$count['total'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_champs'));
		}
		else
		{
			$this->ipsclass->DB->build_query(array(
											'select'   => 'l.*',
											'from'     => array('lotto_champs' => 'l'),
											'add_join' => array(
											0 => array('select' => 'm.id, m.members_display_name, m.mgroup',
													   'from'   => array('members' => 'm'),
													   'where'  => 'm.id=l.c_id',
													   'type'   => 'left' ) ),
													   'order'  => 'l.round DESC',
													   'limit'  => array( $this->first, 15 )
											)    );
			
			$this->ipsclass->DB->exec_query();
			
			$content = array();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{  
				$r['c_amount'] = $this->ipsclass->do_number_format( $r['c_amount'] );
				$r['round'] = $this->ipsclass->do_number_format( $r['round'] );
				$r['time']   = $this->ipsclass->get_date( $r['time'], 'LONG' );
				$r['name'] = $this->ipsclass->make_profile_link( $this->ipsclass->make_name_formatted( $r['members_display_name'], $r['mgroup'] ), $r['id'] );

				
				$content[] = $r;
			}
			
			$links = $this->ipsclass->build_pagelinks( array( 'TOTAL_POSS' => $count['total'],
															  'PER_PAGE'   => 15,
															  'CUR_ST_VAL' => $this->first,
															  'L_SINGLE'   => "",
															  'L_MULTI'    => $this->ipsclass->lang['pages'],
															  'BASE_URL'   => $this->ipsclass->base_url."autocom=points&amp;cmd=winners",
													 )		);
			
			$this->output .= $this->ipsclass->compiled_templates['skin_points']->lotto_winners( $content, $links );
		}
		
		$this->page_title = $this->ipsclass->lang['lotto_winners'];
		$this->nav[]      = $this->ipsclass->lang['lotto_winners'];
	}

	/*-------------------------------------------------------------------------*/
	// Buy Avatar
	/*-------------------------------------------------------------------------*/
	
	function buy_ava()
	{

		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'pava',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->member['id'],
													  )		 );

        if( $data['pava'] == 1 )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_already_own_perm'));
		}
		
        if( $this->ipsclass->member['points'] < $this->ipsclass->vars['buy_ava_cost'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_for_perm'));
		}
					 
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->buy_ava();	
		
		$this->page_title = $this->ipsclass->lang['buy_ava'];
		$this->nav[] = $this->ipsclass->lang['navigation'];
	}

	/*-------------------------------------------------------------------------*/
	// Do Purchase Avatar
	/*-------------------------------------------------------------------------*/
	
	function do_buy_ava()
	{

		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'pava',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->member['id'],
													  )		 );

        if( $data['pava'] == 1 )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_already_own_perm'));
		}
		
        if( $this->ipsclass->member['points'] < $this->ipsclass->vars['buy_ava_cost'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_for_perm'));
		}
		
		$current_points = $this->ipsclass->member['points'];
		$ava_price = $this->ipsclass->vars['buy_ava_cost'];
		$new_points = ( $current_points - $ava_price );

		$this->ipsclass->DB->do_update( 'members', array( 
		'points' => $new_points,
		'pava'	 => 1
		), "id=".$this->ipsclass->member['id'] );
					 
		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points" );
	}

	/*-------------------------------------------------------------------------*/
	// Buy Signature
	/*-------------------------------------------------------------------------*/
	
	function buy_sig()
	{

		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'psig',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->member['id'],
													  )		 );

        if( $data['psig'] == 1 )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_already_own_perm'));
		}
		
        if( $this->ipsclass->member['points'] < $this->ipsclass->vars['buy_sig_cost'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_for_perm'));
		}
					 
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->buy_sig();	
		
		$this->page_title = $this->ipsclass->lang['buy_sig'];
		$this->nav[] = $this->ipsclass->lang['navigation'];
	}

	/*-------------------------------------------------------------------------*/
	// Do Purchase Signature
	/*-------------------------------------------------------------------------*/
	
	function do_buy_sig()
	{

		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'psig',
															   'from'   => 'members',
															   'where'  => 'id='.$this->ipsclass->member['id'],
													  )		 );

        if( $data['psig'] == 1 )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_already_own_perm'));
		}
		
        if( $this->ipsclass->member['points'] < $this->ipsclass->vars['buy_sig_cost'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_not_enough_for_perm'));
		}
		
		$current_points = $this->ipsclass->member['points'];
		$sig_price = $this->ipsclass->vars['buy_sig_cost'];
		$new_points = ( $current_points - $sig_price );

		$this->ipsclass->DB->do_update( 'members', array( 
		'points' => $new_points,
		'psig'	 => 1
		), "id=".$this->ipsclass->member['id'] );
					 
		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points" );
	}

	/*-------------------------------------------------------------------------*/
	// Admin Tools
	/*-------------------------------------------------------------------------*/
	
	function tools()
	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}

 		 $s = intval($this->ipsclass->input['s']);

 		 if( $s == 1 ){$status = "{$this->ipsclass->lang['md_com']}";}
 		 else if( $s == 2  ){$status = "{$this->ipsclass->lang['gd_com']}";}
 		 else if( $s == 3  ){$status = "{$this->ipsclass->lang['tp']}";}
 		 else if( $s == 4  ){$status = "{$this->ipsclass->lang['lp']}";}
 		 else if( $s == 5  ){$status = "{$this->ipsclass->lang['ap']}";}
 		 else if( $s == 6  ){$status = "{$this->ipsclass->lang['sp']}";}
 		 else if( $s == 7  ){$status = "{$this->ipsclass->lang['prest']}";}
 		 else if( $s == 8  ){$status = "{$this->ipsclass->lang['brest']}";}

 		 $this->ipsclass->DB->simple_construct( array( 'select' => '*',

 									  'from'     => 'groups',

 									  'where'     => 'g_id !='.$this->ipsclass->vars['guest_group'],	

 									  'order'  => 'g_id ASC',

 							 )      );
	
 		 $this->ipsclass->DB->simple_exec();

	$groups .= '<select name="g_id">';
        $groups .= "<option selected>--Select Group--</option>";

      while( $g = $this->ipsclass->DB->fetch_row() )
      {

        $groups .= "<option value='{$g['g_id']}'>{$g['g_title']}</option>";

      }

      $groups .= '</select>';
					 
		$this->output .= $this->ipsclass->compiled_templates['skin_points']->tools( $groups, $status );	
		
		$this->page_title = $this->ipsclass->lang['atools'];
		$this->nav[] = $this->ipsclass->lang['navigation'];
	}

	/*-------------------------------------------------------------------------*/
	// Mass Donate
	/*-------------------------------------------------------------------------*/
	 
 	function mass_donate()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}

		$this->ipsclass->input['points'] = str_replace( ',', '', $this->ipsclass->input['points'] );
		$this->ipsclass->input['points'] = str_replace( '-', '', intval($this->ipsclass->input['points']) );
		$pts = intval($this->ipsclass->input['points']);
	
		$this->ipsclass->DB->build_query( array( 'update' => 'members', 'set' => "points=points+".$pts ) );
		$this->ipsclass->DB->exec_query();

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

	/*-------------------------------------------------------------------------*/
	// Remove Transactions
	/*-------------------------------------------------------------------------*/
	 
 	function dtrans()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}
	
		$this->ipsclass->DB->query( 'TRUNCATE TABLE '.SQL_PREFIX.'transactions' );

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

	/*-------------------------------------------------------------------------*/
	// Remove Lotto Champs
	/*-------------------------------------------------------------------------*/
	 
 	function dchamps()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}
	
		$this->ipsclass->DB->query( 'TRUNCATE TABLE '.SQL_PREFIX.'lotto_champs' );

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

	/*-------------------------------------------------------------------------*/
	// Group Donate
	/*-------------------------------------------------------------------------*/
	 
 	function group_donate()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}

		$this->ipsclass->input['points'] = str_replace( ',', '', $this->ipsclass->input['points'] );
		$this->ipsclass->input['points'] = str_replace( '-', '', intval($this->ipsclass->input['points']) );
		$pts = intval($this->ipsclass->input['points']);
		$gid = intval($this->ipsclass->input['g_id']);

		if ( !$pts OR $pts == 0 )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_input_pts'));
		}

		if ( !$gid OR $gid == 0 )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_input_grp'));
		}
	
		$this->ipsclass->DB->build_query( array( 'update' => 'members', 'set' => "points=points+".$pts, 'where'  => 'mgroup='.$gid ) );
		$this->ipsclass->DB->exec_query();

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}
	/*-------------------------------------------------------------------------*/
	// Dump Avatars
	/*-------------------------------------------------------------------------*/
	 
 	function d_ava()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}
		
		$this->ipsclass->DB->build_query( array( 'update' => 'member_extra', 'set' => "avatar_location='', avatar_size='',avatar_type=''" ) );
		$this->ipsclass->DB->exec_query();

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

	/*-------------------------------------------------------------------------*/
	// Dump Signatures
	/*-------------------------------------------------------------------------*/
	 
 	function d_sig()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}
	
		$this->ipsclass->DB->build_query( array( 'update' => 'member_extra', 'set' => "signature=''" ) );
		$this->ipsclass->DB->exec_query();

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

	/*-------------------------------------------------------------------------*/
	// Reset On-Hand Points
	/*-------------------------------------------------------------------------*/
	 
 	function reset_points()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}
	
		$this->ipsclass->DB->build_query( array( 'update' => 'members', 'set' => "points=0" ) );
		$this->ipsclass->DB->exec_query();

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

	/*-------------------------------------------------------------------------*/
	// Reset In-Bank Points
	/*-------------------------------------------------------------------------*/
	 
 	function reset_bank()
 	{

		if ( !$this->ipsclass->member['g_tools_pts'] )
		{
			$this->ipsclass->Error(array(LEVEL => 1, MSG => 'error_no_tools_pts'));
		}
	
		$this->ipsclass->DB->build_query( array( 'update' => 'members', 'set' => "deposited_points=0" ) );
		$this->ipsclass->DB->exec_query();

		$s = intval($this->ipsclass->input['status']);

		$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=points&amp;cmd=tools&amp;s=".$s );

 	}

}

?>