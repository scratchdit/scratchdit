<?php
/**
 * Exception handler for the Text_Diff package.
 *
 * Copyright 2011-2017 Horde LLC (//www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see //www.horde.org/licenses/lgpl21.
 *
 * @author   Jan Schneider <jan@horde.org>
 * @category Horde
 * @license  //www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Text_Diff
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class Horde_Text_Diff_Exception extends Horde_Exception_Wrapped
{
}
