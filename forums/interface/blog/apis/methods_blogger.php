<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.2.2
 * Defines blogger API parameters
 * Last Updated: $Date: 2010-12-17 08:16:34 -0500 (Fri, 17 Dec 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Blog
 * @link		http://www.invisionpower.com
 * @version		$Rev: 7447 $
 *
 */

$_METAWEBLOG_ALLOWED_METHODS = array();

/**
* getUsersBlogs
* return basic Blog information about the users Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['getUsersBlogs'] = array(
													   'in'  => array(
													   					'param0'	=> 'string',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* getUserInfo
* return basic information about the user
*/
$_METAWEBLOG_ALLOWED_METHODS['getUserInfo'] = array(
													   'in'  => array(
													   					'param0'	=> 'string',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* deletePost
* Deletes an entry from the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['deletePost'] = array(
													   'in'  => array(
													   					'param0'	=> 'string',
													   					'param1'	=> 'integer',
																		'param2'	=> 'string',
																		'param3'	=> 'string',
																		'param4'	=> 'bool',
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* newPost
* Adds a new entry to the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['newPost'] = array(
													   'in'  => array(
													   					'param0'	=> 'string',
													   					'param1'	=> 'integer',
																		'param2'	=> 'string',
																		'param3'	=> 'string',
																		'param4'	=> 'string',
																		'param5'	=> 'bool',
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* editPost
* Edit an entry in the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['editPost'] = array(
													   'in'  => array(
													   					'param0'	=> 'string',
													   					'param1'	=> 'integer',
																		'param2'	=> 'string',
																		'param3'	=> 'string',
																		'param4'	=> 'string',
																		'param5'	=> 'bool'
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );