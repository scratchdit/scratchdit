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
 * Disk Cache Handler
 */
class diskCacheHandler
{
	/**
	 * Connect and initialize this handler.
	 *
	 * @return boolean TRUE if successful, FALSE on failure
	 */
	function connect($silent=FALSE)
	{
		if(!@is_writable(MYBB_ROOT."cache"))
		{
			return FALSE;
		}

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
		if(!@file_exists(MYBB_ROOT."/cache/{$name}.php"))
		{
			return FALSE;
		}

		if(!isset($this->cache[$name]) || $hard_refresh == TRUE)
		{
			@include(MYBB_ROOT."/cache/{$name}.php");
		}
		else
		{
			@include_once(MYBB_ROOT."/cache/{$name}.php");
		}

		// Return data
		return $$name;
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
		global $mybb;
		if(!is_writable(MYBB_ROOT."cache"))
		{
			$mybb->trigger_generic_error("cache_no_write");
			return FALSE;
		}

		$cache_file = fopen(MYBB_ROOT."cache/{$name}.php", "w") or $mybb->trigger_generic_error("cache_no_write");
		flock($cache_file, LOCK_EX);
		$cache_contents = "<?php\n\n/** MyBB Generated Cache - Do Not Alter\n * Cache Name: $name\n * Generated: ".gmdate("r")."\n*/\n\n";
		$cache_contents .= "\$$name = ".var_export($contents, TRUE).";\n\n ?>";
		fwrite($cache_file, $cache_contents);
		flock($cache_file, LOCK_UN);
		fclose($cache_file);

		return TRUE;
	}

	/**
	 * Delete a cache
	 *
	 * @param string The name of the cache
	 * @return boolean TRUE on success, FALSE on failure
	 */
	function delete($name)
	{
		return @unlink(MYBB_ROOT."/cache/{$name}.php");
	}

	/**
	 * Disconnect from the cache
	 */
	function disconnect()
	{
		return TRUE;
	}

	/**
	 * Select the size of the disk cache
	 *
	 * @param string The name of the cache
	 * @return integer the size of the disk cache
	 */
	function size_of($name='')
	{
		if($name != '')
		{
			return @filesize(MYBB_ROOT."/cache/{$name}.php");
		}
		else
		{
			$total = 0;
			$dir = opendir(MYBB_ROOT."/cache");
			while(($file = readdir($dir)) !== FALSE)
			{
				if($file == "." || $file == ".." || $file == ".svn" || !is_file(MYBB_ROOT."/cache/{$file}"))
				{
					continue;
				}

				$total += filesize(MYBB_ROOT."/cache/{$file}");
			}
			return $total;
		}
	}
}

?>
