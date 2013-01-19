<?php

/**
 * (e32) ibEconomy
 * Task: Lottery
 * + Ends current lottery (if time) and starts new one
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/**
	 * Parent task manager class
	 */
	protected $class;

	/**
	 * This task data
	 */
	protected $task			= array();

	/**
	 * Prevent logging
	 */
	protected $restrict_log	= false;
	
	/**#@+
	 * Registry Object Shortcuts
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/
	
	#count to hold where to split the lotto tix
	protected $splitter;
	
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	= $registry;
		$this->DB		= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		= $this->registry->getClass('class_localization');
		$this->member	= $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	 * Run this task
	 *
	 * @access	public
	 * @return	void
	 */
	public function runTask()
	{
		#init
		$restockThese = array();
		$liveLotto 	  = null;
		
		#ibEconomy not on?
		if ( !$this->settings['eco_general_on'] || !$this->settings['eco_lotterys_on'] )
		{
			$this->class->unlockTask( $this->task );
			return;
		}
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );
		
		#master ibEconomy SQL Queries
		if ( ! $this->registry->isClassLoaded( 'mysql_ibEconomy' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
			$this->registry->setClass( 'mysql_ibEconomy', new ibEconomyMySQL( $this->registry ) );
		}

		#master ibEconomy Class
		if ( ! $this->registry->isClassLoaded( 'ecoclass' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
			$this->registry->setClass( 'ecoclass', new class_ibEconomy( $this->registry ) );
		}
		
		#need live lotto cache?
		if( !$this->caches['ibEco_live_lotto'] )
		{
			$this->caches['ibEco_live_lotto'] = $this->cache->getCache('ibEco_live_lotto');
		}
		
		#init cached current lotto
		$liveLotto 	= $this->caches['ibEco_live_lotto'];

		if ($liveLotto['l_id'])
		{
			#need to conclude this live lotto?
			if (time() > $liveLotto['l_draw_date'])
			{
				#lottery is done, finish things up eh
				$rolloverMoneyz = $this->finalizeLotto($liveLotto);
				
				#start new lotto
				$this->doNewLotto($rolloverMoneyz);
			}
			else
			{
				#we need to do nothing at the moment, lets leave
				$this->class->unlockTask( $this->task );
				return;	
			}			
		}
		else
		{
			$this->doNewLotto();
			
			$this->class->unlockTask( $this->task );
			return;
		}

		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['lottery_finished_and_started'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}

	/**
	 * Finalize Lottery (pick winner, update lottery with winning details, send PM, deleted/refund unused tix, etc)
	 */
	public function finalizeLotto($lotto)
	{
		#generate num_balls number of random numbers up to top_num setting
		$winningBalls = $this->registry->ecoclass->generateRandomBallNumbers($lotto);
		
		// echo "winningBalls: <br /><br />";
		// echo"<pre>";
		// print_r($winningBalls);
		// echo"</pre>";
		// $this->class->unlockTask( $this->task );
		// exit;		
		
		#init
		sort($winningBalls);
		$winningString = implode(",", $winningBalls);
		$numWinners = 0;
		$rollOver	= 0;
		
		#grab all tickets for this particular lottery
		$playersTickets = $this->registry->mysql_ibEconomy->grabUsersPickedLottoTix(0, $lotto['l_id']);

		#got any winners?
		if (is_array($playersTickets) && count($playersTickets))
		{
			#generate winners possibly
			$winningPlayers = $this->generateWinner($playersTickets, $winningString);
			
			#got winner?
			$numWinners = count($winningPlayers);
			
			if ($numWinners > 0)
			{
				$winningsPerPerson = ($numWinners > 1) ? $lotto['l_final_pot_size']/$numWinners : $lotto['l_final_pot_size'];
				$winningsPerPersonFormatted = $this->registry->getClass('class_localization')->formatNumber( $winningsPerPerson, $this->registry->ecoclass->decimal );
				
				foreach ($winningPlayers as $winnerID)
				{
					#award money/winners
					$this->registry->mysql_ibEconomy->updateMemberPts( $winnerID, $winningsPerPerson, '+', TRUE );
					
					#send PM?
					if ($this->settings['eco_lotto_send_pm'])
					{
						#grab name of winner, and PM Sender
						$winner = IPSMember::load( $winnerID, 'core' );
						$PMSender = ( $winner['member_id'] != $this->settings['eco_lotto_pm_sender'] ) ? $this->settings['eco_lotto_pm_sender'] : $this->settings['eco_shopitems_pm_sender'];

						if ( $winner['member_id'] != $PMSender )
						{
							$this->registry->ecoclass->sendPM($winner['member_id'], $winner['members_display_name'], $winningsPerPersonFormatted, "", "lottery_winner", '', '', $PMSender);
						}
					}
				}			
			}
			else
			{
				$rollOver = $lotto['l_final_pot_size'];
			}
		}
		
		#clear cart for all lottery items with this l_id
		$this->registry->mysql_ibEconomy->deleteOldLottoTicketsFromCart($lotto['l_id']);
				
		#gather unused tickets from all member's portfolios	
		$unusedTickets = $this->registry->mysql_ibEconomy->findUnusedOldLottoTickets();
		
		#determine refunds
		$refunds 	= array();
		
		if (is_array($unusedTickets) && count($unusedTickets))
		{
			foreach ($unusedTickets AS $ticket)
			{
				$refunds[ $ticket['p_member_id'] ] = $ticket['p_amount'] * $lotto['l_tix_price'];
			}
			
			if (is_array($refunds) && count($refunds))
			{
				#issue refunds
				foreach ($refunds as $memberID => $refund)
				{		
					$this->registry->mysql_ibEconomy->updateMemberPts( $memberID, $refund, '+', TRUE );
				}
				
				#delete those unused tickets, now that member haver received their refunds
				$this->registry->mysql_ibEconomy->deleteOldLottoTicketsFromPortfolio();	

				$this->registry->ecoclass->acm('portfolios');	
			}
		}
		
		#finalize actual lottery
		$this->registry->mysql_ibEconomy->finalizeLottery($winningPlayers, $numWinners, $lotto['l_id'], $winningString);	
	
		#return lottery total for rolling over into next lotto
		return $rollOver;
	}

	/**
	* Generate a winner since there were no winning numbers
	* Add in the members group winning chances
	*/
	public function generateWinner($playersTickets, $winningString)
	{
		$winningPlayers = array();

		foreach ($playersTickets as $ticket)
		{
			if ($ticket['ltix_numbers'] == $winningString)
			{
				if (!in_array($ticket['ltix_member_id'], $winningPlayers))
				{
					$winningPlayers[] = $ticket['ltix_member_id'];
				}
			}
		}
		
		#if setting is that we need a winner, if none were declared, we need to take the array of tickets and randomly pick one
		if (empty($winningPlayers))
		{
			if ($this->settings['eco_lotto_force_winner'])
			{
				$playersTickets 	= $this->addExtraTicketEntries($playersTickets);
				$winningTicket  	= $playersTickets[ rand(1, count($playersTickets)) ];
				
				$winningPlayers[0] 	= $winningTicket['ltix_member_id'];
				
				$this->registry->mysql_ibEconomy->adjustLottoTicketsNumbers($winningTicket['ltix_id'], $winningString);				
			}
		}

		return $winningPlayers;
	}
	
	/**
	* Simple function to add multiple entries in the ticket array
	* based on group odds
	*
	*/
	public function addExtraTicketEntries($playersTickets)
	{
		$fluffedPlayersTickets = $playersTickets;
		$ticketCount = count($playersTickets);
		
		for ($i = 2; $i < 10; $i++)
		{
			foreach ($playersTickets AS $id => $ticket)
			{
				if ($ticket['group_odds'] == $i)
				{
					for ($j = 1; $j < $i; $j++)
					{
						$fluffedPlayersTickets[++$ticketCount] = $ticket;
					}
				}
			}
		}
		
		return $fluffedPlayersTickets;
	}

	/**
	 * Finalize Lottery (pick winner, update lottery with winning details, send PM
	 */
	public function doNewLotto($rollOver=0)
	{
		#rollover unwon funds to next lotto?
		$rollOver = ($this->settings['eco_lotto_rollover']) ? $rollOver : 0;
		
		#all about levels of abstraction
		$this->registry->mysql_ibEconomy->newLotto($rollOver);

		#update block once block is made???
		
		$this->registry->ecoclass->acm('live_lotto');
		$this->registry->ecoclass->acm('blocks');		
	}	
}