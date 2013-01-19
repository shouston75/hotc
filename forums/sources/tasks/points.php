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
		//-----------------------------------------
		// Collect the interest
		//-----------------------------------------
		
		$this->ipsclass->DB->build_and_exec_query( array( 'update' => 'members',
														  'set'    => "deposited_points = FLOOR(deposited_points * ((100+".$this->ipsclass->vars['points_irate'].")/100))",
														  'where'  => 'deposited_points>0',
												 )		);
		
		//-----------------------------------------
		// Log the task
		//-----------------------------------------
		
		$this->class->append_task_log( $this->task, 'iPoints Bank Interest Collected!' );
		
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