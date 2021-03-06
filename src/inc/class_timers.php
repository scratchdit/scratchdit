<?php
/**
 * @package MyBB 1.8
 * @author MyBB Group
 * @license Copyright 2014 MyBB Group, All Rights Reserved. See http://www.mybb.com/about/license *
 * Website: //www.mybb.com
 * License: //www.mybb.com/about/license
 */

class timer {

	/**
	 * The timer name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The start time of this timer.
	 *
	 * @var int
	 */
	public $start;

	/**
	 * The end time of this timer.
	 *
	 * @var int
	 */
	public $end;

	/**
	 * The total time this timer has run.
	 *
	 * @var int
	 */
	public $totaltime;

	/**
	 * The formatted total time this timer has run.
	 *
	 * @var string
	 */
	public $formatted;

	/**
	 * Constructor of class.
	 *
	 */
	function __construct()
	{
		$this->add();
	}

	/**
	 * Starts the timer.
	 *
	 */
	function add()
	{
		if(!$this->start)
		{
			$this->start = microtime(TRUE);
		}
	}

	/**
	 * Gets the time for which the timer has run up until this point.
	 *
	 * @return string|boolean The formatted time up until now or false when timer is no longer running.
	 */
	function getTime()
	{
		if($this->end) // timer has been stopped
		{
			return $this->totaltime;
		}
		elseif($this->start && !$this->end) // timer is still going
		{
			$currenttime = microtime(TRUE);
			$totaltime = $currenttime - $this->start;
			return $this->format($totaltime);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Stops the timer.
	 *
	 * @return string The formatted total time.
	 */
	function stop()
	{
		if($this->start)
		{
			$this->end = microtime(TRUE);
			$totaltime = $this->end - $this->start;
			$this->totaltime = $totaltime;
			$this->formatted = $this->format($totaltime);
			return $this->formatted;
		}
		return '';
	}

	/**
	 * Removes the timer.
	 *
	 */
	function remove()
	{
		$this->name = "";
		$this->start = "";
		$this->end = "";
		$this->totaltime = "";
		$this->formatted = "";
	}

	/**
	 * Formats the timer time in a pretty way.
	 *
	 * @param string $string The time string.
	 * @return string The formatted time string.
	 */
	function format($string)
	{
		return number_format($string, 7);
	}
}
