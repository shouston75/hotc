<?php
class cp_skin_iArcade extends output
{

public function __destruct()
{
}

public function adminWrapper( $rows ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>Game Descriptions</h2>
</div>
<form action='{$this->settings['base_url']}{$this->form_code}' method='post' name='bioForm' id='bioForm'>
	<div class='acp-box'>
		<h3>Description Editor</h3>
		<ul class='alternate_rows'>
			{$rows}
		</ul>
		<div class='acp-actionbar'>
			<input type='hidden' name='do' value='saveform' />
			<input type='submit' value='Save Changes' class='button primary' />
		</div>
	</div>
</form>
HTML;

//--endhtml--//
return $IPBHTML;
}

public function adminRow( $member ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<li>
			<table width='100%' cellpadding='0' cellspacing='0'>
				<tr><th colspan='2'>Game: {$member['name']}</th></tr>
				<tr>
					<td width='20%'>
						<strong>Description:</strong><br />
						<span class='desctext'>This is the 'description' text shown to users. It should be about the game, or controls of the game, or how to play, or something like that.</span>
					</td>
					<td>{$member['editorHTML']}</td>
				</tr>
			</table>
		</li>
HTML;

//--endhtml--//
return $IPBHTML;
}








public function gameedit( $game ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<li>
			<table width='100%' cellpadding='0' cellspacing='0'>
				<tr><th colspan='2'>Game: {$game['name']}</th></tr>
				<tr>
					<td width='20%'>
						<strong>Description:</strong><br />
						<span class='desctext'>This is the 'description' text shown to users. It should be about the game, or controls of the game, or how to play, or something like that.</span>
					</td>
					<td>{$game['editorHTML']}</td>
				</tr>
			</table>

	<h2>Game Info4: {$game[name]}</h2>
</div>

<input type='hidden' name='gameid' value='{$game[id]}' />

Name: <input type='text' name='gamename' value='{$game[name]}
' />
<br>
Height: <input type='text' name='height' value='{$game[height]}
' />
<br>
Width: <input type='text' name='width' value='{$game[width]}
' />
<br>
SWF Name: <input type='text' name='swfname' value='{$game[swf]}
' />
<br>
<hr>
<a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=games&section=gameedit&id={$game[id]}&gname={$game[gname]}&do=delete'/>DELETE {$game[name]}</a><br>
		</li>


HTML;

//--endhtml--//
return $IPBHTML;
}





public function managewrap( $info ) {


$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=games&section=gameedit&id={$info[id]}'/>{$info[name]}</a><br>

HTML;

//--endhtml--//
return $IPBHTML;
}


public function delete( $outpt ) {


$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<b>DELETE TAR ENTRY</b><br>
This option will remove the entry for the tar file from iarcade_tars.<br>
You should select this option if you uploaded the tar via FTP, and wish to reinstall it via the FTP import.
<br>
<a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=games&section=gameedit&id={$_GET[id]}&do=deletetarfromdb&tarfile_name={$_GET[gname]}'/> -- Select -- </a><br>
<br><br><br>

<b>DELETE GAME ENTRY</b><br>
This option will remove the entry for the game under iarcade_games.<br>
You should select this option to remove the listing for the game in your iArcade. If you wish to reinstall a game, you should remove this listing to prevent having duplicate games.
<br>
<a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=games&section=gameedit&id={$_GET[id]}&do=deletegamefromdb&tarfile_name={$_GET[gname]}'/> -- Select -- </a><br>
<br><br><br>

<b>REMOVE GAME FILES FROM FTP</b><br>
This option will remove the game files from your FTP.<br>
You should choose this option if you will be re-uploading new game files via the uploader.
<br>
<a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=games&section=gameedit&id={$_GET[id]}&do=deletegamefromftp&tarfile_name={$_GET[gname]}'/> -- Select -- </a><br>
<br><br><br>

<b>REMOVE TAR FILE FROM FTP</b><br>
This option will remove the tar file from your FTP.<br>
You should select this option if you uploaded the game via FTP.
<br>
<a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=games&section=gameedit&id={$_GET[id]}&do=deletetarfromftp&tarfile_name={$_GET[gname]}'/> -- Select -- </a><br>
<br><br><br>

HTML;

//--endhtml--//
return $IPBHTML;
}





public function homepage($i,$c,$regkey) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>iArcade Management</h2>
</div>
<table>
<tr>
<td width='60%' valign='top'>
<div class='acp-box'>
	<h3>Arcade Info</h3>
<table cellpadding='0' cellspacing='0' border='0' class='header'>
<tr>
<th width='100%' colspan='2'></th>
</tr>
	<tr>
 <td align='right' width='20%'>Games Installed:</td>
<td align='left' width='80%'><b>{$i}</b></td>
	</tr>

	<tr>
 <td align='right' width='20%'>Times Played:</td>
<td align='left' width='80%'><b>{$c}</b></td>
	</tr>


</table>
</div>
<br />
</td>
<td width='40%' valign='top'>
<div class='acp-box'>
	<h3> iArcade Info </h3>
<table cellpadding='0' cellspacing='0' border='0' class='header'>
<tr>
<th width='100%' colspan='2'> Your install </th>
</tr>
	<tr>
<td align='right' width='60%'>Your version: </td>
<td align='left' width='40%'><b>1.0.0 FINAL</b></td>
	</tr>

	<tr>
<td align='right' width='60%'>Your Registration: </td>
<td align='left' width='40%'><b>{$regkey}</b></td>
	</tr>

	<tr>
<td align='right' width='60%'>Most recent version: </td>
<td align='left' width='40%'><b><iframe src='http://www.iarcademod.com/callback/ver.php?c=latest' width='150' height='50' scrolling='no' frameborder='0'/></iframe></b></td>

	</tr>


	<tr>
<td align='right' width='60%'>News and Updates: </td>
<td align='left' width='40%'><b><iframe src='http://www.iarcademod.com/callback/ver.php?v=1.0.0_FINAL&key={$this->settings["iArcade-regkey"]}' width='150' height='200' scrolling='auto' frameborder='0'/></iframe></b></td>

	</tr>

	</table>
</div>
<br />
<div class='acp-box'>
	<h3>Donate</h3>
<table cellpadding='0' cellspacing='0' border='0' class='header'>

	<tr>
  		<td align='center' width='100%'>
<br />
<a href='http://www.iarcademod.com/faq.php#love' window='_new'/><img src='https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif'/></a>
<br />
</td>
	</tr>
</table>
</div>
</td>
</tr>
</table>
HTML;

//--endhtml--//
return $IPBHTML;
}

function import_page($formcode,$title,$button,$form,$location,$error_message) {
$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<HTML
<form enctype="multipart/form-data" id='adminform' action='{$this->settings['base_url']}{$this->form_code}&do=$formcode' method='post'>
<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
<input type='hidden' name='_admin_auth_key' value='{$this->registry->getClass('adminFunctions')->_admin_auth_key}' />

        <div class='acp-box'>
 <h3>Manual Upload</h3>
HTML;
                if ( $error_message != "" )
                                {
                                        $IPBHTML .= <<<HTML
  <br>                                              <div class='warning'>
                                                        {$error_message}
                                                </div><br>
HTML;
                                }
$IPBHTML .= <<<HTML
                <ul class='acp-form alternate_rows'>
                        <li>
                                <label>Upload a .tar game file:</label>
  <input class='textinput' type='file'  size='30' name='FILE_UPLOAD'>                        </li>
                </ul>
                <div class='acp-actionbar'>
                        <div class='centeraction'>
                                <input type='submit' class='button primary' value='$button' />
                        </div>
                </div>
        </div>
               <br><hr><br>

     <div class='acp-box'>
                <h3>FTP Uploads</h3>
                <ul class='acp-form alternate_rows'>
                        <li>
<label>FTP Upload Path:</label>
  <input class='path' size='60' name='ftpapath' value='$location'>                        </li>

<center><b><a href='index.php?adsess={$_GET[adsess]}&amp;app=iArcade&amp;module=games&amp;section=import&amp;do=ftp_list' />Click here to view files uploaded.....</a></center></b><br>
                 </li>
                </ul>


        </div>
</form>

HTML;

//--endhtml--//
return $IPBHTML;
}





function ftp_list($file,$sname) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML


<li><a href='index.php?adsess={$_GET[adsess]}&amp;app=iArcade&amp;module=games&amp;section=import&amp;do=untar&amp;tarfile_name=$sname&amp;tarfile=$file&fromftp=true'>$file\n</a></li><br>

HTML;

//--endhtml--//
return $IPBHTML;
}




function tar_page($tarfiles,$formcode,$button,$row) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<form enctype="multipart/form-data" id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=$formcode' method='post'>
<input type='hidden' name='_admin_auth_key' value=' {$this->registry->getClass('adminFunctions')->_admin_auth_key} ' />
        <div class='acp-box'>
                <h3>$title</h3>
HTML;
                if ( $this->error_message != "" )
                                {
                                        $IPBHTML .= <<<HTML
                                                <div class='warning'>
                                                        {$this->error_message}
                                                </div>
HTML;
                                }
$i='0';
$IPBHTML .= <<<HTML
                        <li>
 <label>
<a href="index.php?adsess={$this->request['adsess']}&app=iArcade&module=games&section=import&do=untar&tarfile_name={$row['tarfile_name']}&tarfile={$row['tarfile']}"/> Click to continue install for {$row['tarfile_name']} </a>
</label>
                        </li>
HTML;
$IPBHTML .= <<<HTML
                       </div>
</form>
HTML;

//--endhtml--//
return $IPBHTML;
}

}

