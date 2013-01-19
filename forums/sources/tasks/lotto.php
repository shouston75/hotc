<?php



if ( !defined( 'IN_IPB' ) )

{

	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";

	exit();

}



class task_item

{

	var $class     = "";

	var $root_path = "";

	var $task      = "";

	

	/*-------------------------------------------------------------------------*/

	// Our 'auto_run' function

	/*-------------------------------------------------------------------------*/

	

	function run_task()

	{

	

	$this->ipsclass->load_language('lang_points');

    $this->ipsclass->load_template('skin_points');

	

    require_once( ROOT_PATH.'sources/classes/post/class_post.php' );

    $this->postlib           =  new class_post();

    $this->postlib->ipsclass =& $this->ipsclass;

    $this->postlib->load_classes();



	require_once(ROOT_PATH.'sources/handlers/han_parse_bbcode.php');

	$this->parser                      =  new parse_bbcode();

	$this->parser->ipsclass            =& $this->ipsclass;

	$this->parser->allow_update_caches = 1;



	$this->parser->parse_smilie  = 1;

	$this->parser->parse_bbcode  = 1;

	$this->parser->parse_html  = 1;

	



		//-----------------------------------------

		// Choose Lotto Winner

		//-----------------------------------------



		$data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',

																  'from'   => 'lotto_champs',

																  'order'    => 'round DESC',

														 )		);



                $new_round = $data['round'] + 1;



		$count = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(m_id) AS total, sum(ticket_price) as ticket_points',

																   'from'   => 'lotto',

																   'where'  => 'round='.$new_round,

														  )		 );



       $ticket_points = $count['ticket_points'];

       $base_points = $this->ipsclass->vars['lotto_amt'];

       $total_points = ($ticket_points + $base_points);



		if ( $count['total'] )

		{



		$row = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',

																  'from'   => 'lotto',



  'where'  => 'round='.$new_round,

																  'order'  => 'RAND()',

														 )		);



       $winner_id = $row['m_id'];



		$get = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',

																  'from'   => 'members',



  'where'  => 'id='.$winner_id,

														 )		);



		$members_current_points = $get['points'];

		$points = ($members_current_points + $total_points);



		$this->ipsclass->DB->do_update( 'members', array( 'points' => $points ), "id=".$row['m_id'] );





		$this->ipsclass->DB->do_insert( 'lotto_champs', array( 'c_id'       => $row['m_id'],

															   'c_amount'   => $total_points,

															   'time'           => time(),

									  )						 );



		//-----------------------------------------

		// Send A PM

		//-----------------------------------------

		

			$total_points = $this->ipsclass->do_number_format($total_points);

			

  			$subject  = "{$this->ipsclass->lang['lotto_pm_title']}";

		

   			$message  = "{$this->ipsclass->lang['msg_content']}<strong>{$total_points}</strong>";

 

			$this->ipsclass->DB->do_insert( 'message_text', array(

			'msg_date'	        => time(),

			'msg_post'          => $this->parser->pre_db_parse($message),

			'msg_cc_users'      => "",

			'msg_sent_to_count' => 1,

			'msg_post_key'      => md5(microtime()),

			'msg_author_id'     => $this->ipsclass->vars['lotto_pm_id'] ) );

			

			$msg_id = $this->ipsclass->DB->get_insert_id();

					

			$this->ipsclass->DB->do_insert( 'message_topics', array(

			'mt_msg_id'     => $msg_id,

			'mt_date'       => time(),

			'mt_title'      => $this->parser->pre_db_parse($subject),

			'mt_from_id'    => $this->ipsclass->vars['lotto_pm_id'],

			'mt_to_id'      => $winner_id,

			'mt_vid_folder' => 'in',

			'mt_hide_cc'    => 1,

			'mt_tracking'   => 0,

			'mt_hasattach'  => 0,

			'mt_owner_id'   => $winner_id ) );

		

			$this->ipsclass->DB->do_update( 'members', array(

			'new_msg'		=> new_msg + 1,

			'msg_total'		=> msg_total + 1,

			'show_popup'	=> 1 ), 'id='.intval( $winner_id ) );



		}

		

		//-----------------------------------------

		// Log the task

		//-----------------------------------------

		

		$this->class->append_task_log( $this->task, 'iLotto Winner Chosen!' );

		

		//-----------------------------------------

		// Unlock the task

		//-----------------------------------------

		

		$this->class->unlock_task( $this->task );

	}

	

	/*-------------------------------------------------------------------------*/

	// register_class

	/*-------------------------------------------------------------------------*/

	

	function register_class( &$class )

	{

		$this->class     =& $class;

		$this->ipsclass  =& $class->ipsclass;

		$this->root_path =  $this->class->root_path;

	}

	

	/*-------------------------------------------------------------------------*/

	// pass_task

	/*-------------------------------------------------------------------------*/

	

	function pass_task( $this_task )

	{

		$this->task = $this_task;

	}

}



?>