<?php
/**
 * <pre>
 * Photo routines
 * Last Updated: $Date: 2011-05-25 19:51:19 -0400 (Wed, 25 May 2011) $
 * </pre>
 *
 * @author		$author$
 * @copyright	(c) 2001 - 2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @author		MattMecham
 * @link		http://www.invisionpower.com
 * @version		$Rev: 8893 $ 
 */

class upgrader_photos extends classes_member_photo
{
	/**
	 * We overwrite this method so as not to have photos class prepend /profile to all of our images, which
	 *  may or may not be in the profile folder
	 * 
	 * @return @e void
	 */
	protected function _getProfileUploadPaths()
	{
		/* Fix for bug 5075 */
		$this->settings['upload_dir'] = str_replace( '&#46;', '.', $this->settings['upload_dir'] );		

		$upload_path  = $this->settings['upload_dir'];
		
		return array( 'path' => $upload_path, 'dir' => '' );
	}
}