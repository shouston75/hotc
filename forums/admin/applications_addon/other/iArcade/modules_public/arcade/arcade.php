<?php
class public_iArcade_arcade_arcade extends ipsCommand
{
	public 		$html;
	protected 	$version = "1.0.0";

	public function doExecute( ipsRegistry $registry )
	{
		//Check if the mod is activated
		if ( ! $this->settings['iArcade-enable'] )
		{
			$this->registry->output->showError( 'iArcade-notEnabled', 1 );
		}
		
		ipsRegistry::getClass( 'class_localization' )->loadLanguageFile( array( 'public_boards' ), 'forums' );
		
		
		$this->pageTitle = $this->settings['board_name'] . " " . $this->ipsclass->lang['iArcade-title'];
		
		$this->show_page();
		
		$this->registry->output->setTitle( $this->pageTitle );
		
		$this->registry->output->addNavigation( "Arcade", 'app=iArcade' );
		
		// Switch View
                switch ( ipsRegistry::$request['view'] )
                {
                
			//Comment System
					//Remove Comment (ADMIN)
					case 'deletecomment':
					 $this->removecomment();
                              		   break;
					//Submit Comment
					case 'submitcomment':
					 $this->submitcomment();
                               		  break;
					//Process/Update Comment
					case 'updatecomment':
					 $this->updatecomment();
					   break;
					//Edit Comment (ADMIN)
					case 'editcomment':
                             		 $this->editcomment();
                             		   break;

			//Scoring
					//Record a score
					case 'newscore':
      					 $this->output .= $this->registry->output->getTemplate( 'iArcade' )->redirect( $this->arcade );
                            		    break;
					//View Scores
					case 'viewscores':
					$this->viewscores();
                            		    break;

			//Challenges
					//Create A Challenge
					case 'chal_create':
                              		  $this->chal_create();
                             		   break;
					//List of Pending Challenges
					case 'chal_pending':
                            		    $this->chal_pending();
                             		   break;

			//Misc Stuff
					//View Catagories
					case 'cat':
					 $this->cat();
                              		   break;
					//Vote for something
					case 'vote':
					 $this->vote();
                             		    break;
					//Play A Game
					case 'playgame':
					 $this->play_game();
                               		  break;
                               		 //Fullscreen Mode
					case 'fullscreen':
					 $this->fullscreen();
                               		  break;
					//Goto
					case 'gotopage':
					 $this->gotopage();
                                	 break;
					//Listings
					case 'gamelist':
					 $this->gamelist();
                              		   break;
					//Report Game
					case 'reportgame':
					 $this->reportgame();
                              		   break;
					//Games CP
					case 'gamescp':
                              		  $this->gamescp();
                              		  break;
					//Arcade Info
					case 'arcadeinfo':
						     	 $this->output .= $this->registry->output->getTemplate( 'iArcade' )->show_page( $this->arcade );                              							  break;




					//Where should we send them by default?
					default:
	if ($this->settings['iArcade-defaultpage'] == "arcadeinfo") {
					$this->output .= $this->registry->output->getTemplate( 'iArcade' )->show_page( $this->arcade );
	}
	if ($this->settings['iArcade-defaultpage'] == "gamescp") {
					$this->gamescp();
	}
	if ($this->settings['iArcade-defaultpage'] == "gamelist") {
					$this->gamelist();
	}
                                break;
}

/* ============================================================== \
| Hey, check it out. Your using an awesome arcade mod for FREE.   |
| Totally free. All we ask is you leave this teeny credit in.     |
| Removing this makes you a horrible person. 				|
\ ============================================================== */ 
		$this->output .= "<div id='board_footer'><p id='copyright'>Arcade System Powered By <a href='http://www.iarcademod.com/?s=footer'>iArcade</a>  {$this->version} &copy; ".date('Y')."&nbsp;&nbsp;<a href='http://www.xtremeinvision.com/'>Andy Rixon</a> & <a href='http://www.Collin1000.com/'>Collin1000</a> <br> Games &copy; their Respective Author</p></div><br>";
		@$this->registry->output->addContent( $this->output );
		@$this->registry->output->sendOutput();
	}


/* ----------------------------------------
@ FUNCTION: Shows the game.   		 @
------------------------------------------ */
private function play_game()
    {
        $game         = intval( $this->request['gameid'] );
		

$info = $this->DB->buildAndFetch( array( 'select'   => 't.*',
          'from'     => array( 'iarcade_games' => 't' ),
          'where'  => "id=$game",
         )       );

//Update the playcounter
$plays = $info['playcount'];
$update = $plays+1;
$newgamedata  = array('playcount'		=> $update );
$this->DB->update( 'iarcade_games', $newgamedata, 'id='.$this->request['gameid'] );

//Show Best Score (Help from JoshD)
	if ($info['savemethod'] == "high") {
		$order = "DESC";
	} else {
		$order = "ASC";
	}

$gname = $info['gname'];
$ginfo['highscore'] = $this->DB->buildAndFetch( array( 
        'select' => '*', 
        'from' => 'iarcade_scores', 
  'where'  => "gname='$gname'",
        'order' => "score $order",
        'limit' => '1'
        ) );

	if ($ginfo['highscore']['score'] == NULL) {
		$ginfo['highscore']['score'] = "None Yet!";
		$ginfo['highscore']['member'] = "You?";
	}

// Begin Error Check
if (!$info) {
	$code="4";
	$this->error($code);
} else {
// No errors? Woo. Show them everything...
$this->output .= $this->registry->output->getTemplate('iArcade')->playgame ( $info,$ginfo );
} 
// End Error Check

    }


/* ----------------------------------------
@ FUNCTION: User can play in fullscreen mode @
------------------------------------------ */
private function fullscreen()
    {
        $game         = intval( $this->request['gameid'] );
		

$swf = $this->DB->buildAndFetch( array( 'select'   => 't.*',
          'from'     => array( 'iarcade_games' => 't' ),
          'where'  => "id=$game",
         )       );

// Begin Error Check
if (!$swf) {
	$code="4";
	$this->error($code);
} else {
// No errors? Woo. Show them everything...
// Cant send to a skin file, because then it loads the board header. We just want the game.
echo "
<embed src=\"{$this->settings['iArcade-webpath']}/{$swf['swf']}\" 
quality=\"high\" bgcolor=\"#000000\" width=\"{$swf['width']}\" height=\"{$swf['height']}\" 
name=\"games\" align=\"middle\" allowScriptAccess=\"sameDomain\"
 type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />

";
//Use Die to stop the board footer from loading. Just the game!
die();
} 
// End Error Check

    }



/* ----------------------------------------
@ FUNCTION: Reports a game.   		 @
------------------------------------------ */
private function reportgame()
    {
        $game         = intval( $this->request['gameid'] );
		

$info = $this->DB->buildAndFetch( array( 'select'   => 't.*',
          'from'     => array( 'iarcade_games' => 't' ),
          'where'  => "id=$game",
         )       );

//Send the PM


$gid = $game;
$gottenname = $info['name'];
$burl = $this->settings['base_url'];
$url = "[url='{$burl}app=iArcade&view=playgame&gameid={$gid}']View Game[/url]";
$member_id_to_send_to = $this->settings['iArcade-reportmemberid'];
$member_id_sent_from = $this->memberData['member_id'];
$pmsg = "A member has reported a problem with a game in iArcade.
 

Name: $gottenname
Game ID: $gid

$url





-------------------
[i]Powered by iArcade[/i]

";

require_once( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php' );
		$this->messenger	= new messengerFunctions( $this->registry );

		try
		{
			$this->messenger	= new messengerFunctions( $this->registry );
		 	$this->messenger->sendNewPersonalTopic( $member_id_to_send_to, 
											$member_id_sent_from, 
											array(), 
											"iArcade Reported Game!", 
											$pmsg, 
											array( 'origMsgID'			=> 0,
													'fromMsgID'			=> 0,
													'postKey'			=> md5(microtime()),
													'trackMsg'			=> 0,
													'addToSentFolder'	=> 0,
													'hideCCUser'		=> 0,
													'forcePm'			=> 1,
													'isSystem'		  => TRUE
												)
											);
		}
		catch( Exception $error )
		{
			$msg		= $error->getMessage();
			
			if( $msg != 'CANT_SEND_TO_SELF' )
			{
				$toMember	= IPSMember::load( $user['member_id'], 'core' );
			   
				if ( strstr( $msg, 'BBCODE_' ) )
				{
					$msg = str_replace( 'BBCODE_', $msg );

					$this->registry->output->showError( $msg );
				}
				else if ( isset($this->lang->words[ 'err_' . $msg ]) )
				{
					$this->lang->words[ 'err_' . $msg ] = $this->lang->words[ 'err_' . $msg ];
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#NAMES#'   , implode( ",", $this->messenger->exceptionData ), $this->lang->words[ 'err_' . $msg ] );
				$this->lang->words[ 'err_' . $msg ] = str_replace( '#TONAME#'  , $toMember['members_display_name']	, $this->lang->words[ 'err_' . $msg ] );
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#FROMNAME#', $this->memberData['members_display_name'], $this->lang->words[ 'err_' . $msg ] );
					
					$this->registry->output->showError( 'err_' . $msg );
				}
				else
				{
					$_msgString = $this->lang->words['err_UNKNOWN'] . ' ' . $msg;
					
					$this->registry->output->showError( 'err_UNKNOWN' );
				}
			}
		}

//End PM

$this->gamescp();

    }


/* ----------------------------------------
@ FUNCTION: Error Handler.  		      @
@ A substiture for the default IPB handler@
------------------------------------------ */
private function error($code)
    {
	
		if ($code=="1") {
			$errmsg = "You do not have any games in this category. Please add some games to this category and try again";
		}
		if ($code=="2") {
			$errmsg = "There are no games to show. Please go back and try again.";
		}
		if ($code=="3") {
			$errmsg = "Invalid comment data.";
		}
		if ($code=="4") {
			$errmsg = "Invalid game data. Please choose a new game.";
		}
		if ($code=="5") {
			$errmsg = "You do not have permission to access this function.";
		}

$this->output .= $this->registry->output->getTemplate('iArcade')->error ( $errmsg );

    }


/* ----------------------------------------
@ FUNCTION: Deleates a comment.	      @
------------------------------------------ */
private function removecomment()
    {
$removecomid=$this->request['com'];
		if ( $this->memberData['g_access_cp'] )
		{
			$this->DB->delete( 'iarcade_comments', "id='$removecomid'" );
		$this->gamelist();
		} elseif ( ! $this->memberData['g_access_cp'] ) {
	$code="5";
	$this->error($code);
		}
   } 

//
// Why are submit, delete, and update all different functions?
// permissions. for a future version. ;)
// 

/* ----------------------------------------
@ FUNCTION: Saves comment updates to DB.  @
------------------------------------------ */
private function updatecomment()
    {
		if ( $this->memberData['g_access_cp'] )
		{

	 $update = $this->request['Post'];
		$ud  = array('post'		=> $update );
   $this->DB->update( 'iarcade_comments', $ud, 'id='.$this->request['com'] );
   		$this->gamelist();
		} elseif ( ! $this->memberData['g_access_cp'] ) {
	$code="5";
	$this->error($code);
		}

   } 


/* ----------------------------------------
@ FUNCTION: Comment editor.		      @
------------------------------------------ */
private function editcomment()
    {
		if ( $this->memberData['g_access_cp'] )
		{

        $cid         = intval( $this->request['com'] );
			$info = $this->DB->buildAndFetch( array( 'select'   => 't.*',
       'from'     => array( 'iarcade_comments' => 't' ),
       'where'  => "id=$cid",
       )       );
$this->output .= $this->registry->output->getTemplate( 'iArcade' )->editcomment($info);
		} elseif ( ! $this->memberData['g_access_cp'] ) {
	$code="5";
	$this->error($code);
		}
    }


/* ----------------------------------------
@ FUNCTION: Basic redirector class,       @
@ Used when jumping from gname->gid format@
@ And for jumping the user to a div area  @
------------------------------------------ */
private function gotopage()
    {
		
        $gameid         = intval( $this->request['gameid'] );
	  $seoTitle="";
$info = $this->DB->buildAndFetch( array( 'select'   => 't.*',
       'from'     => array( 'iarcade_games' => 't' ),
       'where'  => "id='$gameid'",
       )       );

		if ($this->request['des'] == "comments") {
$url="index.php?app=iArcade&view=viewscores&game=$info[gname]#fast_reply";
    $this->registry->output->silentRedirect( $url, $seoTitle, false );
		}
		if ($this->request['des'] == "scores") {
$url="index.php?app=iArcade&view=viewscores&game=$info[gname]#scores";
    $this->registry->output->silentRedirect( $url, $seoTitle, false );
		}
    }


/* ----------------------------------------
@ FUNCTION: Takes all the votes. 	      @
@ Game ratings, reports, favorites.		@
------------------------------------------ */
private function vote()
    {
        $game         = intval( $this->request['gameid'] );
        $method         = $this->request['score'];

if ($method == "positive") {
//Add a vote

$info = $this->DB->buildAndFetch( array( 'select'   => 't.*',
       'from'     => array( 'iarcade_games' => 't' ),
       'where'  => "id=$game",
       )       );

	$currentrating = $info['f1'];
	$newrating = $currentrating+1;
	$newgamedata  = array('f1'		=> $newrating );				

$this->DB->update( 'iarcade_games', $newgamedata, 'id='.$this->request['gameid'] );
}

if ($method == "negative") {
//Add neg vote to game
//(subtract a vote)

$info = $this->DB->buildAndFetch( array( 'select'   => 't.*',
       'from'     => array( 'iarcade_games' => 't' ),
       'where'  => "id=$game",
       )       );

$currentrating = $info['f1'];
$newrating = $currentrating-1;
$newgamedata  = array('f1'		=> $newrating );				

$this->DB->update( 'iarcade_games', $newgamedata, 'id='.$this->request['gameid'] );

}

   if ($method == "favorite") {
	$fetchinfofav = $this->DB->buildAndFetch( array( 
		'select' => '*', 
		'from' => 'iarcade_games', 
		'where' => "id='$game'",
		) );
		$fgn=$fetchinfofav['gname'];
		$fgrn=$fetchinfofav['name'];

	$this->DB->insert( 'iarcade_favs', array(
				'member'		=> $this->memberData['name'],
				'gameid'		=> $this->request['gameid'],
				'gname'		=> $fgn,
				'name'		=> $fgrn,
				)				);
   }	

   if ($method == "un-favorite") {
	$user=$this->memberData['name'];
	$gid=$this->request['gameid'];
	$this->DB->delete( 'iarcade_favs', "gameid='$gid' AND member='$user'" );
   }	

$this->output .= $this->registry->output->getTemplate('iArcade')->vote ( $info );

    }

/* ----------------------------------------
@ FUNCTION: It does what it says it does. @
@ It lists the games. Doh. 	 		@
------------------------------------------ */
private function gamelist()
    {
$pagenum         = intval( $this->request['page'] );

			// No page number. Start them off on page 1.
			if ($pagenum == "") {
			$pagenum="1";
			}
			// PHP does not like doing math with the number zero, 
			// so we need to actually use page 1.
			if ($pagenum <= "0") {
			$pagenum="1";
			}

//Math time!
$perpage = $this->settings['iArcade-gamesperpage'];
$startnum = $pagenum*$perpage;
$stopnum = $startnum-$perpage;
$pageup = $pagenum+1;
$pagedown = $pagenum-1;

	//Custom sorting methods. Feel free to add your own!
	   if ($this->settings['iArcade-sortmethod'] == "Alphabetical") {
       	 $sort="name ASC";
	    }
	   if ($this->settings['iArcade-sortmethod'] == "OrderAdded") {
		 $sort="id DESC";
	    }


		//Get the games
		$this->DB->build( array( 'select' => 't.*',
			'from' => array( 'iarcade_games' => 't' ),
			'order' => $sort,
			'limit' => array( $stopnum, $perpage )
			)
		);

$this->DB->execute();

while( $r = $this->DB->fetch() )
{
        $info[ $r['id'] ] = $r;
}

//Now, get the TOP games, fizzbitch.
$numofgames = $this->settings['iArcade-topgamelistnum'];
$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_games' => 't' ),
        'order' => 'playcount DESC',
        'limit' => array( 0, $numofgames ) ) );
$this->DB->execute();
while( $rt = $this->DB->fetch() )
{
        $topgames[ $rt['id'] ] = $rt;
}

// Begin Error Check
if (!$info) {
	$code="2";
	$this->error($code);
} else {
// No errors? Woo. Show them everything...
$this->output .= $this->registry->output->getTemplate('iArcade')->gamelist ( $info, $topgames, $startnum, $stopnum, $pageup, $pagedown, $mostpos );
} 
// End Error Check

    }

/* ----------------------------------------
@ FUNCTION: Get all th data needed for the@
@ gamescp area, and display it out. 	@
------------------------------------------ */
private function gamescp()
    {
$ltgg=$this->memberData['name'];

$lastscore = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'order' => 'time DESC',
	'limit' => array( 0, 1)
	) );

//Searches
$sng = $this->request['searchname'];
$global['sgn'] = $this->DB->buildAndFetch( array( 
        'select' => '*', 
        'from' => 'iarcade_games', 
        'where' => "name LIKE '%".$sng."%'",
        ) );

	if ($global['sgn'] == "") {
					$global['sgn']['name'] = "No Results";
				        }
	if ($sng == "") {
				$global['sgn'] = "";
			    }

$game=$lastscore['gname'];
$fetchinfo['lastscoregame'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_games', 
	'where' => "gname='$game'",
	) );

$global['lastscore'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'order' => 'time DESC',
	'limit' => array( 0, 1)
	) );

$global['lastscore2'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'order' => 'time DESC',
	'limit' => array( 1, 2)
	) );

$global['lastscore3'] = $this->DB->buildAndFetch( array( 
'select' => '*', 
	'from' => 'iarcade_scores', 
'order' => 'time DESC',
	'limit' => array( 2, 3)

) );

$global['trophy'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'where' => "gname='$game'",
	'order' => 'score DESC',
	'limit' => array( 0, 1)
	) );

$personal['lastscore'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'where' => "member='$ltgg'",
	'order' => 'time DESC',
	'limit' => array( 0, 1)
	) );

$misc['newestgame'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_tars', 
	'where' => "added=1",
	'order' => 'timestamp DESC',
	'limit' => array( 0, 1)
	) );

$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_favs' => 't' ),
        'order' => 'gname DESC',
	  'where' => "member='$ltgg'"
	  ) );
$this->DB->execute();
while( $f = $this->DB->fetch() )
{

        $favs[ $f['gameid'] ] = $f;
}

if ($favs=="") {
$favs = "none";
}

$this->output .= $this->registry->output->getTemplate('iArcade')->gamescp ($lastscore,$fetchinfo,$personal,$favs,$misc,$global);
	}


/* ----------------------------------------
@ FUNCTION: Re-sort the games by catagory.@
@ This really should be in gameslist....  @
@ But it was less "bloaty" this way		@
------------------------------------------ */
private function cat()
    {
$cata = $this->request['cata'];


$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_games' => 't' ), 
	'where'  => "cat = '$cata'",
        'limit' => array( 0, 100 )
        )       );
$this->DB->execute();
while( $r = $this->DB->fetch() )
{
        $info[ $r['id'] ] = $r;
}

//Now, get the TOP games, fizzbitch.
$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_games' => 't' ),
	  'where'  => "cat = '$cata'",
        'order' => 'playcount DESC',
        'limit' => array( 0, 3 )

        )       );
$this->DB->execute();

while( $rt = $this->DB->fetch() )
{
        $topgames[ $rt['id'] ] = $rt;
}

// Begin Error Check
if (!$info) {
	$code="1";
	$this->error($code);
} else {
// No errors? Woo. Show them everything...
$this->output .= $this->registry->output->getTemplate('iArcade')->gamelist ( $info, $topgames );
} 
// End Error Check

	}

/* ----------------------------------------
@ FUNCTION: Error check, store comments.   @
------------------------------------------ */

private function submitcomment()
    {


// Begin Error Check
if (!$this->request['game']) {
	$code="3";
	$this->error($code);
}
if (!$this->request['Post']) {
	$code="3";
	$this->error($code);
} else {
// No errors? Carry on, my wayward son.
IPSText::getTextClass('bbcode')->parse_html		= 0;
IPSText::getTextClass('bbcode')->parse_nl2br	= 1;
IPSText::getTextClass('bbcode')->parse_smilies	= $this->request['enableemo'] == 'yes' ? 1 : 0;
IPSText::getTextClass('bbcode')->parse_bbcode	= 1;
$content = IPSText::getTextClass( 'editor' )->processRawPost( 'Post' );

$submitter = ipsRegistry::member()->getProperty('members_display_name');

$this->DB->insert( 'iarcade_comments', array(
	'username'			=> $submitter,
	'game'			=> $this->request['game'],
	'post'			=> $content,
	'ip'				=> '0',
	'time'				=> time(),
							)				);

//IPS Redirect method throws weird errors... Meta it is.
echo "<META http-equiv='refresh' content='0;URL=index.php?app=iArcade&view=viewscores&game={$this->request['game']}'>";




} 
  }



/* ----------------------------------------
@ FUNCTION: Show member's challenges.     @
------------------------------------------ */
private function chal_pending()
   {
$user=$this->memberData['member_id'];

$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_chal' => 't' ), 
	'where' => "chalto_id='$user' AND chalto_score='0'",
	'order' => 'chalid DESC'
        )       );
$this->DB->execute();

while( $chals = $this->DB->fetch() )
{
        $info[ $chals['g1'] ] = $chals;
}

//TODO: Add a list to show all a user's previous challenges.

$this->output .= $this->registry->output->getTemplate('iArcade')->chal_pending ($chals,$info);
    }



/* ----------------------------------------
@ FUNCTION: Create a challenge in the DB, @
@ PM the member about it, and redirect.   @
------------------------------------------ */
private function chal_create()
    {

$nameinput=$this->request['entered_name'];
$memdb = $this->DB->buildAndFetch( array( 'select'   => '*',
                 'from'     => array( 'members' => 't' ),
                 'where'  => "name='$nameinput'",
                                  )       );

$ltgg=$this->memberData['name'];
$game=$this->request['gname'];
$personal['lastscore'] = $this->DB->buildAndFetch( array( 
		'select' => '*', 
		'from' => 'iarcade_scores', 
		'where' => "member='$ltgg' AND gname='$game'",
		'order' => 'time DESC',
		'limit' => array( 0, 1)
		)				 );


$this->DB->insert( 'iarcade_chal', array(
		'chalfrom_id'		=> $this->memberData['member_id'],
		'chalto_id'		=> $memdb['member_id'],
		'chalfrom_score'		=> $personal['lastscore']['score'],
		'chalto_score'		=> '0',
		'g1'		=> $this->request['gameid'],
		)				);


// Define some PM stuff
// (Some weird bug does not like me using any $this-> calls in the 
// actual PM, so it has to be setup alone.)
$gid = $this->request['gameid'];
$burl = $this->settings['base_url'];
$url = "[url='{$burl}app=iArcade&view=chal_pending']View Your Challenges[/url]";
$member_id_to_send_to = $memdb['member_id'];
$member_id_sent_from = $this->memberData['member_id'];
$pmsg = "You have been challenged to a game in the arcade! 

$url





-------------------
[i]Powered by iArcade[/i]

";

//Send them a PM about it.
//Direct from IPS docs.
//http://community.invisionpower.com/resources/official.html?record=140

require_once( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php' );
		$this->messenger	= new messengerFunctions( $this->registry );

		try
		{
			$this->messenger	= new messengerFunctions( $this->registry );
		 	$this->messenger->sendNewPersonalTopic( $member_id_to_send_to, 
											$member_id_sent_from, 
											array(), 
											"iArcade Game Challenge!", 
											$pmsg, 
											array( 'origMsgID'			=> 0,
													'fromMsgID'			=> 0,
													'postKey'			=> md5(microtime()),
													'trackMsg'			=> 0,
													'addToSentFolder'	=> 0,
													'hideCCUser'		=> 0,
													'forcePm'			=> 1,
													'isSystem'		  => TRUE
												)
											);
		}
		catch( Exception $error )
		{
			$msg		= $error->getMessage();
			
			if( $msg != 'CANT_SEND_TO_SELF' )
			{
				$toMember	= IPSMember::load( $user['member_id'], 'core' );
			   
				if ( strstr( $msg, 'BBCODE_' ) )
				{
					$msg = str_replace( 'BBCODE_', $msg );

					$this->registry->output->showError( $msg );
				}
				else if ( isset($this->lang->words[ 'err_' . $msg ]) )
				{
					$this->lang->words[ 'err_' . $msg ] = $this->lang->words[ 'err_' . $msg ];
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#NAMES#'   , implode( ",", $this->messenger->exceptionData ), $this->lang->words[ 'err_' . $msg ] );
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#TONAME#'  , $toMember['members_display_name']	, $this->lang->words[ 'err_' . $msg ] );
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#FROMNAME#', $this->memberData['members_display_name'], $this->lang->words[ 'err_' . $msg ] );
					
					$this->registry->output->showError( 'err_' . $msg );
				}
				else
				{
					$_msgString = $this->lang->words['err_UNKNOWN'] . ' ' . $msg;
					
					$this->registry->output->showError( 'err_UNKNOWN' );
				}
			}
		}


$this->output .= $this->registry->output->getTemplate('iArcade')->chal_create ();
    }


/* ----------------------------------------
@ FUNCTION: Show scores, comments, stats. @
------------------------------------------ */
private function viewscores()
    {
	
        $game=$this->request['game'];
$toshow = $this->settings['iArcade-howmanyscores'];

$stats = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_games', 
	'where' => "gname='$game'"
	)			 );

if ($stats['savemethod'] == "high") { $scoreorder = "DESC"; }
if ($stats['savemethod'] != "high") { $scoreorder = "ASC"; }

//Get scores

$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_scores' => 't' ),                                                                                                                               	  'where'  => "gname = '$game'",
        'order' => "score $scoreorder",
 	  'limit' => array( 0, $toshow )
        )       );
$this->DB->execute();

while( $r = $this->DB->fetch() )
{
        $info[ $r['time'] ] = $r;
$whonow = $r['member'];
}

//End Scores

//Begin get comments

$this->DB->build( array( 'select' => 't.*',
        'from'  => array( 'iarcade_comments' => 't' ),                                                                                                                               	  'where'  => "game = '$game'",
        'order' => 'time DESC',
// TODO: comment limiting via ACP
// And Pagination :O
 'limit' => array( 0, 10 )
        )       );
$this->DB->execute();

while( $cr = $this->DB->fetch() )
{
        $coms[ $cr['time'] ] = $cr;
}


//Get the high score stats...
$ltgg=$this->memberData['name'];
$getsum = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'where' => "gname='$game' AND member='$ltgg'",
	'order' => 'score DESC',
	'limit' => array( 0, 1)
	) 				);

$personal['best'] = $getsum['score'];

$hcomp['topscore'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'where' => "gname='$game'",
	'order' => 'score DESC',
	'limit' => array( 0, 1)
)					 );


$personal['lastscore'] = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_scores', 
	'where' => "member='$ltgg' AND gname='$game'",
	'order' => 'time DESC',
	'limit' => array( 0, 1)
	) 		);


if (!$coms) {
$nocoms="true";
}

$this->output .= $this->registry->output->getTemplate('iArcade')->viewscores ( $info,$member,$coms,$nocoms,$stats,$personal,$hcomp );

    }




/* ----------------------------------------
@ FUNCTION: Andy has lots of old code.    @
@ It needs a home. Here it is.  		@
@ Soon to be depreicated ;)			@
------------------------------------------ */
	
	private function show_page()
	{
		
		//---------------------------------------
		// Lets sort out the permissions
		//---------------------------------------
		if ( strstr( ",{$this->settings['iArcade-allowedGroup']},", ",{$this->memberData['member_group_id']}," ) )
		{
			if ( ! $this->memberData['member_id'])
			{
				$this->iArcade['prinick'] 	= $this->settings['guest_name_pre'] . rand ( 100, 999);
				$this->iArcade['altnick']	= $this->settings['guest_name_pre'] . rand ( 100, 999);
				$this->iArcade['userid']	= $this->iArcade['prinick'];
			}
			else
			{
				$this->iAcarde['prinick'] 	= $this->memberData['members_display_name'];
				$this->iArcade['altnick']	= $this->memberData['members_display_name'] . rand ( 100, 999 );
				$this->iArcade['userid']	= $this->memberData['member_id'];
			}
		}
		else
		{			
			$this->registry->output->showError( 'no_permission', 2 );
		}


		//---------------------------------------
		// Array of required parameters
		//---------------------------------------
		
		$this->iArcade['param'] = array(
									'highlight' => 'true',
									'asl' => 'true',
									'style:highlightlinks' => 'true',
									'pixx:highlightnick' => 'true',
									'pixx:styleselector' => 'false',
									'pixx:setfontonstyle' => 'true',
									'pixx:displayentertexthere' => 'false',
									'pixx:timestamp' => 'true',
									'pixx:mouseurlopen' => '1 2',
									'pixx:mousechanneljoin' => '1 2',
									'pixx:configurepopup' => 'true',
									'pixx:popupmenustring1' => 'Whois',
									'pixx:popupmenustring2' => 'Query',
									'pixx:popupmenustring3' => 'Ban',
									'pixx:popupmenustring4' => 'Kick + Ban',
									'pixx:popupmenustring5' => '--',
									'pixx:popupmenustring6' => 'Op',
									'pixx:popupmenustring7' => 'DeOp',
									'pixx:popupmenustring8' => 'HalfOp',
									'pixx:popupmenustring9' => 'DeHalfOp',
									'pixx:popupmenustring10' => 'Voice',
									'pixx:popupmenustring11' => 'DeVoice',
									'pixx:popupmenustring12' => '--',
									'pixx:popupmenustring13' => 'Ping',
									'pixx:popupmenustring14' => 'Version',
									'pixx:popupmenustring15' => 'Time',
									'pixx:popupmenustring16' => 'Finger',
									'pixx:popupmenustring17' => '--',
									'pixx:popupmenustring18' => 'DCC Send',
									'pixx:popupmenustring19' => 'DCC Chat',
									'pixx:popupmenucommand1_1' => '/Whois %1',
									'pixx:popupmenucommand2_1' => '/Query %1',
									'pixx:popupmenucommand3_1' => '/mode %2 -o %1',
									'pixx:popupmenucommand3_2' => '/mode %2 +b %1',
									'pixx:popupmenucommand4_1' => '/mode %2 -o %1',
									'pixx:popupmenucommand4_2' => '/mode %2 +b %1',
									'pixx:popupmenucommand4_3' => '/kick %2 %1',
									'pixx:popupmenucommand6_1' => '/mode %2 +o %1',
									'pixx:popupmenucommand7_1' => '/mode %2 -o %1',
									'pixx:popupmenucommand8_1' => '/mode %2 +h %1',
									'pixx:popupmenucommand9_1' => '/mode %2 -h %1',
									'pixx:popupmenucommand10_1' => '/mode %2 +v %1',
									'pixx:popupmenucommand11_1' => '/mode %2 -v %1',
									'pixx:popupmenucommand13_1' => '/CTCP PING %1',
									'pixx:popupmenucommand14_1' => '/CTCP VERSION %1',
									'pixx:popupmenucommand15_1' => '/CTCP TIME %1',
									'pixx:popupmenucommand16_1' => '/CTCP FINGER %1',
									'pixx:popupmenucommand18_1' => '/DCC SEND %1',
									'pixx:popupmenucommand19_1' => '/DCC CHAT %1'
								);
		

		
		
		//---------------------------------------
		// Allowing smileys?
		//---------------------------------------
		
		if ( $this->settings['iArcade-smilies'] == 1 )
		{
			$smiley = array(
				array(		"sourire.gif"		,		":)"	,	":-)"		),
				array(		"content.gif"		,		":D"	, 	":-D"		),
				array(		"OH-2.gif"			,		":-O"					),
				array(		"OH-1.gif"			,		":o"					),
				array(		"langue.gif"		,		":P"	,	":-P"		),
				array(		"clin-oeuil.gif"	,		";)"	,	";-)"		),
				array(		"triste.gif"		,		":("	,	":-("		),
				array(		"OH-3.gif"			,		":|"	,	":-|"		),
				array(		"pleure.gif"		,		":'("					),
				array(		"rouge.gif"			,		":$"	, 	":-$"		),
				array(		"cool.gif"			,		"(H)"	,	"(h)"		),
				array(		"enerve1.gif"		,		":-@"					),
				array(		"enerve2.gif"		,		":@"					),
				array(		"roll-eyes.gif"		,		":s"	,	":-S"		),
			);
			
			$x=1;
			$imgpath    = 'img/';
			for ($i=0; isset($smiley[$i][0]); $i++)
			{
				for ($k=1; isset($smiley[$i][$k]); $k++)
				{
					$img 						= $imgPath . $smiley[ $i ][0];
					$smiley[$i][$k] 			= stripslashes( $smiley[ $i ][ $k ] );
					$key						= 'style:smiley' . $x;
					$value						= $smiley[ $i ][ $k ] . ' ' . $img;
					$this->iArcade['param'][ $key ] = $value;
					$x++;
				}
			}


		}
		
		
	

	}


}

?>