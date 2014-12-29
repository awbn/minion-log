<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Extend the Log class to support write_on_add to individual log writers
 * 
 * @abstract
 * @extends Log
 */
abstract class Kohana_Minion_Log extends Log {
		
	/**
	 * @var array Log writers that should write out as soon as soon as messages are added
	 * @access protected
	 */
	protected $_on_add_writers = array();
	
	/**
	 * @var mixed Log writers that should write out as soon as soon as messages are added
	 * @static override instance
	 * @access protected
	 */
	protected static $_instance = NULL;
	
	/**
	 * Attaches a log writer, and optionally limits the levels of messages that
	 * will be written by the writer.
	 *
	 *     $log->attach($writer);
	 *
	 * @param   Log_Writer  $writer     	instance
	 * @param   mixed       $levels     	array of messages levels to write OR max level to write
	 * @param   integer     $min_level  	min level to write IF $levels is not an array
	 * @param	boolean		$write_on_add	Should we write to this log immediately?
	 * @return  Minion_Log
	 */
	public function attach(Log_Writer $writer, $levels = array(), $min_level = 0, $write_on_add = FALSE)
	{
		parent::attach($writer, $levels, $min_level);
		
		if ($write_on_add)
		{
			$this->_on_add_writers["{$writer}"] = $this->_writers["{$writer}"];
			unset($this->_writers["{$writer}"]);
		}
		
		return $this;
	}
	
	/**
	 * Detaches a log writer. The same writer object must be used.
	 *
	 *     $log->detach($writer);
	 *
	 * @param   Log_Writer  $writer instance
	 * @return  Minion_Log
	 */
	public function detach(Log_Writer $writer)
	{
		unset($this->_on_add_writers["{$writer}"]);
		return parent::detach($writer);
	}
	
	/**
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 *     $log->add(Log::ERROR, 'Could not locate user: :user', array(
	 *         ':user' => $username,
	 *     ));
	 *
	 * @param   string  $level      level of message
	 * @param   string  $message    message body
	 * @param   array   $values     values to replace in the message
	 * @return  Minion_Log
	 */
	public function add($level, $message, array $values = NULL)
	{
		parent::add($level, $message, $values);
		
		if ( ! empty($this->_on_add_writers))
		{
			// Get the last message
			$msg = end($this->_messages);
			reset($this->_messages);
			
			foreach ($this->_on_add_writers as $writer)
			{
				if (empty($writer['levels']) OR in_array($msg['level'], $writer['levels']))
				{
					$writer['object']->write(array($msg));
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Get the singleton instance of this class and enable writing at shutdown.
	 * This can be different from the default Kohana logger
	 *
	 *     $log = Minion_Log::instance();
	 *
	 * @return  Minion_Log
	 */
	public static function instance()
	{
		if (Minion_Log::$_instance === NULL)
		{
			// Create a new instance
			Minion_Log::$_instance = new Minion_Log;

			// Write the logs at shutdown
			register_shutdown_function(array(Minion_Log::$_instance, 'write'));
		}

		return Minion_Log::$_instance;
	}
}