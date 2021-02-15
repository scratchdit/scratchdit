<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: //www.mybb.com
 * License: //www.mybb.com/about/license
 *
 */

/**
 * Cache Handler Interface
 */
interface CacheHandlerInterface
{
	/**
	 * Connect and initialize this handler.
	 *
	 * @return boolean TRUE if successful, FALSE on failure
	 */
	function connect();

	/**
	 * Connect and initialize this handler.
	 *
	 * @param string $name
	 * @return boolean TRUE if successful, FALSE on failure
	 */
	function fetch($name);

	/**
	 * Write an item to the cache.
	 *
	 * @param string $name The name of the cache
	 * @param mixed $contents The data to write to the cache item
	 * @return boolean TRUE on success, FALSE on failure
	 */
	function put($name, $contents);

	/**
	 * Delete a cache
	 *
	 * @param string $name The name of the cache
	 * @return boolean TRUE on success, FALSE on failure
	 */
	function delete($name);

	/**
	 * Disconnect from the cache
	 *
	 * @return bool
	 */
	function disconnect();

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	function size_of($name='');
}
