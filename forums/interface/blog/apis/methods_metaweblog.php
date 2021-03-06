<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.2.2
 * Defines metaweblog API parameters
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
* getCategories
* return the categories used in the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['getCategories'] = array(
													   'in'  => array(
													   					'param0'	=> 'integer',
																		'param1'	=> 'string',
																		'param2'	=> 'string'
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* getRecentPosts
* return the most recent entries of the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['getRecentPosts'] = array(
													   'in'  => array(
													   					'param0'	=> 'integer',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																		'param3'	=> 'integer'
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* newPost
* Post a new entry to the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['newPost'] = array(
													   'in'  => array(
													   					'param0'	=> 'integer',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																		'param3'	=> 'struct',
																		'param4'	=> 'bool'
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
													   					'param0'	=> 'integer',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																		'param3'	=> 'struct',
																		'param4'	=> 'bool'
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* getPost
* Get an entry from the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['getPost'] = array(
													   'in'  => array(
													   					'param0'	=> 'integer',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );

/**
* newMediaObject
* Post a media object to the Blog
*/
$_METAWEBLOG_ALLOWED_METHODS['newMediaObject'] = array(
													   'in'  => array(
													   					'param0'	=> 'integer',
																		'param1'	=> 'string',
																		'param2'	=> 'string',
																		'param3'	=> 'struct',
																     ),
													   'out' => array(
																		'response' => 'xmlrpc'
																	 )
													 );