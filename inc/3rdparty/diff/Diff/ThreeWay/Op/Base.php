<?php
/**
 * Copyright 2007-2017 Horde LLC (//www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see //www.horde.org/licenses/lgpl21.
 *
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class Horde_Text_Diff_ThreeWay_Op_Base
{
    public function __construct($orig = FALSE, $final1 = FALSE, $final2 = FALSE)
    {
        $this->orig = $orig ? $orig : array();
        $this->final1 = $final1 ? $final1 : array();
        $this->final2 = $final2 ? $final2 : array();
    }

    public function merged()
    {
        if (!isset($this->_merged)) {
            if ($this->final1 === $this->final2) {
                $this->_merged = &$this->final1;
            } elseif ($this->final1 === $this->orig) {
                $this->_merged = &$this->final2;
            } elseif ($this->final2 === $this->orig) {
                $this->_merged = &$this->final1;
            } else {
                $this->_merged = FALSE;
            }
        }

        return $this->_merged;
    }

    public function isConflict()
    {
        return $this->merged() === FALSE;
    }
}
