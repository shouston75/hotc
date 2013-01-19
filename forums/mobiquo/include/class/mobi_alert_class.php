<?php
/*======================================================================*\
|| #################################################################### ||
|| # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # This file is part of the Tapatalk package and should not be used # ||
|| # and distributed for any other purpose that is not approved by    # ||
|| # Quoord Systems Ltd.                                              # ||
|| # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
|| #################################################################### ||
\*======================================================================*/
defined('IN_MOBIQUO') or exit;
class mobi_alert {
	
	private $DB;
	private $registry;
	private $settings;
	private $lang;
	private $memberData;
	private $page;
	private $perPage;
	private $convrsation;
	
	public function __construct( ipsRegistry $registry )
    {
    	require_once 'lang/en/tapatalk_push.php';
        /* Make registry objects */
        $this->registry     =  $registry;
        $this->DB           =  $this->registry->DB();
        $this->settings     =& $this->registry->fetchSettings();
        $this->lang         =  $lang;
        $this->memberData   =& $this->registry->member()->fetchMemberData();
        if(empty($this->page) || $this->page < 1)
        $this->page=1;
        if(empty($this->perPage))
        $this->perPage = 20;
    }
    
    public function getAlert()
    {
    	$table="tapatalk_push_data";
    	if(!$this->DB->checkForTable($table))
    	{
    		get_error('tapatalk_push_data table not exist');
    		return ;
    	}
    	if(empty($this->memberData['member_id']))
    	{
    		get_error('You need to login');
    		return ;
    	}
    	$nowtime = time();
    	$monthtime = 30*24*60*60;
    	$preMonthtime = $nowtime-$monthtime;
    	$startNum = ($this->page-1) * $this->perPage;
    	$this->DB->delete($table,'create_time < ' . $preMonthtime . ' and user_id = ' . $this->memberData['member_id']);
    	if($this->DB->checkForField('sub_id',$table))
    	{
    		$this->DB->delete($table,"data_type ='conv' and sub_id = 0 and user_id = " . $this->memberData['member_id']);
    	}
    	$_joins	= array( array( 'select'	=> 'm.member_id as author_id',
							 	'from'		=> array( 'members' => 'm' ),
    							'where'     => 'p.author = m.members_display_name',
    	) );
    	$this->DB->build( 
				    	array( 'select' => 'p.*', 
				    	'from' => array($table => 'p'), 
				    	'where' => 'p.user_id=' . intval($this->memberData['member_id']),
				    	'order' => 'p.create_time DESC',
				    	'limit' => $startNum . ',' . $this->perPage,
				    	'add_join' => $_joins,
				    	) 
		);
		$query = $this->DB->execute();
		while($data = $this->DB->fetch($query))
		{
			$data['icon_url'] = get_avatar($data['author_id']);
			switch ($data['data_type'])
			{
				case 'sub':
					$data['message'] = sprintf($this->lang['reply_to_you'],$data['author'],$data['title']);
					break;
				case 'tag':
					$data['message'] = sprintf($this->lang['tag_to_you'],$data['author'],$data['title']);
					break;
				case 'newtopic':
					$data['message'] = sprintf($this->lang['post_new_topic'],$data['author'],$data['title']);
					break;
				case 'quote':
					$data['message'] = sprintf($this->lang['quote_to_you'],$data['author'],$data['title']);
					break;
				case 'conv':
					$data['position'] = $data['sub_id'];
					$data['message'] = sprintf($this->lang['pm_to_you'],$data['author'],$data['title']);
					break;
				case 'like':
					$data['message'] = sprintf($this->lang['like_your_thread'],$data['author'],$data['title']);
					break;
			}
			$return_data[] = $data;
		}
		if(empty($return_data))
		{
			$return_data = array();
		}
		return $return_data;
    }
    public function setPage($page)
    {
    	$this->page = $page;
    }
    
    public function setPerPage($perPage)
    {
    	$this->perPage = $perPage;
    }
    
    public function getPage()
    {
    	return $this->page;
    }
    
    public function getPerPage()
    {
    	return $this->perPage;
    }
	public function __get($name) {
		return $this->{'get' . ucfirst($name)}();
	}
	
	public function __set($name, $value) {
		return $this->{'set' . ucfirst($name)}($value);
	}
}