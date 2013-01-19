<?php
if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_iArcade_games_import extends ipsCommand
{	
	public $html;
	public $registry;
	
	private $form_code;
	private $form_code_js;
	
	public function doExecute( ipsRegistry $registry )
	{

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_iArcade' );
		switch( $this->request['do'] )
		{
			case 'save':
				$this->saveForm();
			break;
			
			case 'untar':
				$this->UnpackTars();
			break;
	
			case 'unpack':
				$this->showUnpackForm();
			break;

			case 'ftp_list':
				$this->ftp_list();
			break;
			
			default:
			case 'upload':
				$this->showForm();
			break;		 
		}
		
		/* Output to page */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	
	public function showForm()
	{
		$this->html->form_code = 'module=games&section=import';
	$formcode = 'save';
		$title = 'iArcade Game Importer';
		$button = 'Upload File';
		$form['iArcade_upload'] = $this->registry->output->formUpload();

$location = IPSLib::getAppDir( 'iArcade' )."/sources/tars/";
$tarfolder = IPSLib::getAppDir( 'iArcade' )."/sources/tars/";
$storefolder = $this->settings['iArcade-path'];


if ( ! is_executable( $tarfolder ) ){
$error_message = "<br><b>ERROR:</b> Your tars directory is not writable!";
}
if ( ! is_executable( $storefolder ) ){
$error_message = "<br><b>ERROR:</b> The directory you are storing the files in either does not exist, or is not writable. Please check the location and permission before you continue.";
}


		$this->registry->output->html .= $this->html->import_page($formcode,$title,$button,$form,$location,$error_message);

	}






	public function ftp_list()
	{




//Begin FTP Hack
$location = IPSLib::getAppDir( 'iArcade' )."/sources/tars/";

if ($handle = opendir($location)) {

    while (false !== ($file = readdir($handle))) { 
        if ($file != "." && $file != ".." && $file != "index.html" && $file != "gamedata")   {

if (preg_match("/game/i", $file)) {

$sname=str_replace(".tar", "", $file);
$sname=str_replace("game_", "", $sname);

                $this->registry->output->html .= $this->html->ftp_list($file,$sname);

} else {

}
        }
    }
    closedir($handle);
}

//End FTP Hack

	}

	
	public function saveForm()
	{
		//-----------------------------------------
		// Upload
		//-----------------------------------------
		
		$FILE_NAME = $_FILES['FILE_UPLOAD']['name'];
		$FILE_TYPE = $_FILES['FILE_UPLOAD']['type'];
		
		//-----------------------------------------
		// Naughty Opera adds the filename on the end of the
		// mime type - we don't want this.
		//-----------------------------------------
		
		$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
		$content   = "";
		
		if ( $FILE_NAME AND ( $FILE_NAME != 'none' ) )
		{


				$location = IPSLib::getAppDir( 'iArcade' )."/sources/tars/";


$target_path = "$location";

$target_path = $target_path . basename( $_FILES['FILE_UPLOAD']['name']); 

if(move_uploaded_file($_FILES['FILE_UPLOAD']['tmp_name'], $target_path)) {
//    echo "The file ".  basename( $_FILES['FILE_UPLOAD']['name']). 
    " has been uploaded";
} else{
    echo "There was an error uploading the file, please try again!";
}




		}
		$return = array();
		/* Run checks and give user output...*/
		if ( file_exists( $location ) ){
			$this->html->error_message = "The file ".$FILE_NAME." has been uploaded";
			$return['tarfile'] = $FILE_NAME;
			$return['tarfile_name']=str_replace(".tar", "", $FILE_NAME);
			$return['tarfile_name']=str_replace("game_", "", $return['tarfile_name']);
			$return['added'] = 0;

			$this->tar_array = $return;

$this->DB->insert( 'iarcade_tars', array(
		'tarfile_name'		=> $return['tarfile_name'],
		'tarfile'		=> $return['tarfile'],
		'added'		=> '0',
		'timestamp' 	=> time(),
				)				);
   	

		} else {
			$this->registry->output->global_message = "There was an error uploading the file, please try again!";
		}
		
		unset($return);
		$this->showUnpackForm();
	}
	
	public function showUnpackForm()
	{


		// Tell the code that we are in the right place
		$this->html->form_code = 'module=games&section=import';
		$formcode = 'untar';
		$button = 'Unpack Tar File(s)';
		
		$array = array();
		// Check to see what tars we have in the tar directory


$row = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_tars', 
      'where'  => "added='0'",
	'order' => 'timestamp DESC',
	'limit' => array( 0, 1)
	) );
$row['formcode'] = ipsRegistry::getClass('output')->formCheckbox( $row['tarfile_name'] );

		$this->registry->output->html .= $this->html->tar_page($tarfiles,$formcode,$button,$row);
	}
	


	public function UnpackTars()
	{
		$this->registry->output->global_message = "";


$whatnow = $this->request["tarfile_name"];
$checkifadded = $this->DB->buildAndFetch( array( 
	'select' => '*', 
	'from' => 'iarcade_tars', 
      'where'  => "tarfile_name='$whatnow'",
	'order' => 'added DESC',
	'limit' => array( 0, 1)
	) );

if ($checkifadded['added']=="1") {
$this->registry->output->global_message .= "Installing that game has failed because it was already installed.";

} else {


$ftpmode = $this->request["fromftp"];
if ($ftpmode=="true") {
$this->DB->insert( 'iarcade_tars', array(
		'tarfile_name'		=> $this->request['tarfile_name'],
		'tarfile'		=> $this->request['tarfile'],
		'added'		=> '1',
		'timestamp' 	=> time(),
				)				);
}

$file = IPSLib::getAppDir( 'iArcade' )."/sources/tars/{$this->request['tarfile']}";
				$to = IPSLib::getAppDir( 'iArcade' )."/sources/tars/";
							
		if (substr($to,-1)!="/") $to.="/";  
		$o=fopen($file,"rb");
		if (!$o) return false;  
		while(!feof($o)){
			$d=unpack("a100fn/a24/a12size",fread($o,512));
			//print_r($d);
			if (!$d[fn]) break;
			$dir="";
			$e=explode("/",$d[fn]);
			array_pop($e);
			foreach($e as $v) {$dir.=$v."/";@mkdir($to.$dir);}
			$d[size]=octdec(trim($d[size]));
			$o2=fopen($to.$d[fn],"w");
			if(!$o2) return false;
			if ($d[size]) fwrite($o2,fread($o,$d[size]));
			fclose($o2);
			$t=512-($d[size]%512);
			if ($t&&$t!=512) fread($o,$t);
		}
		fclose($o);
		$untard = 'true';
		// I think untard is a funny word.

				if($untard == true) {
				
$appdirmove = IPSLib::getAppDir( 'iArcade' );
$tarfilemove = $this->request['tarfile_name'];
$pathmove = $this->settings['iArcade-path'];
//Yuky SWF bug took 2 hours to fix, the night of Beta5 "release".
//I hate moving files. Why cant there be a move function?
//I swear to god, sometimes, PHP is retarded.
rename("$appdirmove/sources/tars/$tarfilemove.swf", "$pathmove/$tarfilemove.swf");
					// We haz screen shot to move
rename(IPSLib::getAppDir( 'iArcade' )."/sources/tars/{$this->request['tarfile_name']}1.gif", "{$this->settings['iArcade-path']}/img/{$this->request['tarfile_name']}.gif");

					// Delete the second screenie
					@unlink(IPSLib::getAppDir( 'iArcade' )."/sources/tars/{$this->request['tarfile_name']}2.gif");
					// check and see if we have a php file for it
					if(file_exists(IPSLib::getAppDir( 'iArcade' )."/sources/tars/{$this->request['tarfile_name']}.php")) {
						require(IPSLib::getAppDir( 'iArcade' )."/sources/tars/{$this->request['tarfile_name']}.php");
						// Titles set by game
						$gamename = $config['gtitle'];
						$about = htmlspecialchars($config['gwords'], ENT_QUOTES);
						$gameheight = $config['gheight'];
						$gamewidth = $config['gwidth'];
						$idname = $config['gname'];
						$cat = $config['gcat'];
					$savestyle = $config['highscore_type'];
						$swf = "$idname.swf";
						$img = "$idname.gif";
						unset($config);
						// Delete the php file that was created
						@unlink(IPSLib::getAppDir( 'iArcade' )."/sources/tars/{$this->request['tarfile_name']}.php");
					} else {
						$this->html->error_message .= "No config php file was included in the release of {$this->request['tarfile']}. Due to this config settings have not been set correctly for this game, instead default settings have been used. <br />";
						// DEFAULT VALUES
				$gamename = $this->request['tarfile'];
				$about = htmlspecialchars("", ENT_QUOTES);
						$gameheight = 600;
						$savestyle = "high";
						$gamewidth = 800;
					$idname = $this->request['tarfile'];
						$cat = 1;
						$swf = "$idname.swf";
						$img = "$idname.gif";
					}
					
					// We need to tell iArcade we've just "created" a game
					$insert_array = array( 'swf' => $swf, 'height' => $gameheight, 'width' => $gamewidth, 'f1' => 0, 'description' => $about, 'name' => $gamename, 'playcount' => 0, 'imgname' => $img, 'gname' => $idname, 'savemethod' => $savestyle );
					$this->DB->insert("iarcade_games", $insert_array);
					
					// "Tell" iArcade that we've unpacked that tar file
					$this->DB->update("iarcade_tars", array( 'added' => 1 ), 'tarfile_name="'.$this->request["tarfile_name"].'"');
					
					// Tell the user that the game got imported sucessfully
					$this->registry->output->global_message .= "The game {$gamename} has been created in the iArcade system. A brief Description of the game is: {$about}. It's dimensions are {$gamewidth}x{$gameheight}. The filename of the game is {$idname}. <br />";
				} else {
					$this->html->error_message .= "We couldn't untar the file: {$this->request['tarfile']}. Make sure that the file is uploaded and try again.<br />";
				}
				}
			


//		$this->showUnpackForm();
	}
	
	public function untar($file,$to)
	{
		// Untar file
		// Function
		// From Jcink.com
		// Special Thanks!
		

	}
}
?>