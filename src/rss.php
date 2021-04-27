<?php
/**
 * @package MyBB 1.8
 * @author MyBB Group
 * @license Copyright 2014 MyBB Group, All Rights Reserved. See http://www.mybb.com/about/license *
 * Website: //www.mybb.com
 * License: //www.mybb.com/about/license
 */

/* Redirect traffic using old URI to new URI. */
$string = '';
if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '')
{
	$string .= '?'.str_replace(array("\n", "\r"), "", $_SERVER['QUERY_STRING']);
}

header('Location: syndication.php'.$string);

