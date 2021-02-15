<?php
/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 * Website: http://mybb.com
 * License: http://mybb.com/about/license
 *
 * $Id$
 */

/**
 * eAccelerator Cache Handler
 */
class eacceleratorCacheHandler
{
	/**
	 * Unique identifier representing this copy of MyBB
	 */
	public $unique_id;

	function eacceleratorCacheHandler($silent=FALSE)
	{
		global $mybb;

		if(!function_exists("eaccelerator_get"))
		{
			// Check if our DB engine is loaded
			if(!extension_loaded("Eaccelerator"))
			{
				// Throw our super awesome cache loading error
				$mybb->trigger_generic_error("eaccelerator_load_error");
				die;
			}
		}
		return FALSE;
	}

	/**
	 * Connect and initialize this handler.
	 *
	 * @return boolean TRUE if successful, FALSE on failure
	 */
	function connect()
	{
		global $mybb;

		// Set a unique identifier for all queries in case other forums on this server also use this cache handler
		$this->unique_id = md5(MYBB_ROOT);

		return TRUE;
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @param string The name of the cache
	 * @param boolean TRUE if we should do a hard refresh
	 * @return mixed Cache data if successful, FALSE if failure
	 */

	function fetch($name, $hard_refresh=FALSE)
	{
		$data = eaccelerator_get($this->unique_id."_".$name);
		if($data === FALSE)
		{
			return FALSE;
		}

		return @unserialize($data);
	}

	/**
	 * Write an item to the cache.
	 *
	 * @param string The name of the cache
	 * @param mixed The data to write to the cache item
	 * @return boolean TRUE on success, FALSE on failure
	 */
	function put($name, $contents)
	{
		eaccelerator_lock($this->unique_id."_".$name);
		$status = eaccelerator_put($this->unique_id."_".$name, serialize($contents));
		eaccelerator_unlock($this->unique_id."_".$name);
		return $status;
	}

	/**
	 * Delete a cache
	 *
	 * @param string The name of the cache
	 * @return boolean TRUE on success, FALSE on failure
	 */
	function delete($name)
	{
		return eaccelerator_rm($this->unique_id."_".$name);
	}

	/**
	 * Disconnect from the cache
	 */
	function disconnect()
	{
		return TRUE;
	}

	function size_of($name)
	{
		global $lang;

		return $lang->na;
	}
}
?>
