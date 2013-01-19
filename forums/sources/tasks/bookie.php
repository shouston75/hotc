<?php
/*e$ Bookie Mod 1.0.1 Delete Old Games Task*/
/*emoney isn't a coder*/
/*Created for Fantasy Football Haven (http://fantasyfootballhaven.com)*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	var $class     = "";
	var $root_path = "";
	var $task      = "";
	
	function run_task()
	{
		//-----------------------------------------
		// Task enabled?
		//-----------------------------------------		
		if ( intval($this->ipsclass->vars['ibbookie_prune_games']) > 0 )
		{
			//-----------------------------------------
			// Init vars
			//-----------------------------------------
			$deleted = 0;
			$ids   = array();
			
			$less_than = time() - $this->ipsclass->vars['ibbookie_prune_games'] * 86400;
			$zero = 0;
			
			$this->ipsclass->DB->build_query( array( 'select' => 'id',
													 'from'	  => 'bookie_games',
													 'where'  => 'time_paid < '.$less_than,
											)		);
			$this->ipsclass->DB->exec_query();
			
			if ( $this->ipsclass->DB->get_num_rows() )
			{
				while( $r = $this->ipsclass->DB->fetch_row() )
				{
					$ids[] = $r['id'];
				}
				
				if ( count($ids) )
				{

				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'bookie_games', 'where' => "id IN (".implode(",", $ids).")" ) );
	
					$deleted = $this->ipsclass->DB->get_affected_rows();
				}
			}
			
			//-----------------------------------------
			// Log to log table - modify but dont delete
			//-----------------------------------------
			$this->class->append_task_log( $this->task, "Paid games deleted ($deleted)" );
		}
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		$this->class->unlock_task( $this->task );
	}
	
	/*-------------------------------------------------------------------------*/
	// register_class
	// LEAVE ALONE
	/*-------------------------------------------------------------------------*/
	
	function register_class(&$class)
	{
		$this->class     = &$class;
		$this->ipsclass  =& $class->ipsclass;
		$this->root_path = $this->class->root_path;
	}
	
	/*-------------------------------------------------------------------------*/
	// pass_task
	// LEAVE ALONE
	/*-------------------------------------------------------------------------*/
	
	function pass_task( $this_task )
	{
		$this->task = $this_task;
	}
}
?>