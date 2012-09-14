# minion-log

`minion-log` is an extension for [Kohana Minion](https://github.com/kohana-minion/core) to more easily handle logging from the command line.

## Installation

`minion-log` should be added to your Kohana `MODPATH` directory.  Then enable it in the `modules` section of your bootstrap.

## Requirements

* [kohana-minion](https://github.com/kohana-minion/core) is not strictly required, but this was written with minion/command line interfaces in mind.

## Compatibility
* Written for Kohana 3.2.

## Usage

This module extends the Kohana `Log` class and can be used in the same manner.  Remember that you'll need to attach `Log_Writer`s.

### Write On Add

The main addition in this module is the ability to write to a given writer as soon as the `$log->add()` method is called.  This is useful in CLI situations where you may be writing to multiple writers.  For example, you may want to have output written immediately to StdOut, but delay writing to a Log File to avoid hammering system I/O.

This works by passing an optional fourth additional parameter to `$log->attach()`.  If the parameter is `TRUE`, output to that log writer will be written immediatly as soon as $log->add() is called.  If the parameter is `FALSE` (default), output won't be written until $log->write() is called.

### Example  
This is an example of using Minion_Log:

	$log = Minion_Log::instance(); // Or, just new Minion_Log
	
	// Add the log file writer
	$log->attach(new Log_File(APPPATH.'logs'));
	
	// Add StdOut.  Pass the other default parameters and the new $write_on_add parameter
	$log->attach(new Log_StdOut, array(), 0, TRUE);
	
	// This will immediately write to StdOut.
	// Output will be buffered for the log file until $log->write() is called or the shutdown function is called
	$log->add('Foobar');
	
	// This will write to the log file, but not to StdOut (since that output was pushed immediately)
	$log->write();
	
## Testing

This module is unittested using the [unittest module](http://github.com/kohana/unittest).
You can use the `minion.log` group to only run minion log tests.  It is also grouped under `minion`.

i.e.

	phpunit --group minion.log

## Bugs?  Issues?

That's why this is hosted on github :).  Feel free to fork it, submit issues, or patches.

## License

This is licensed under the [same license as Kohana](http://kohanaframework.org/license).