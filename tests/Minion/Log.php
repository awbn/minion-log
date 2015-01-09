<?php

/**
 * Test case for Minion_Log
 *
 * @group minion
 * @group minion.log

 */
class Minion_LogTest extends Kohana_Unittest_TestCase
{

    /**
     * Can we instantiate the log?
     * 
     * @access public
     * @return void
     */
    function test_log()
    {
        $log = new Minion_Log;

        $this->assertInstanceOf('Kohana_Log', $log);
    }

    /**
     * Make sure we aren't overriding Kohana::log and that 
     * Make sure Minion_Log::instance() is an instance of Minion_Log (not just Log)
     *
     * @access public
     * @return void
     */
    function test_instance()
    {
        $log = Minion_Log::instance();

        $this->assertInstanceOf('Minion_Log', $log);

        // assertNotInstanceOf doesn't work here!?
        $this->assertFalse(is_a(Kohana::$log, 'Minion_Log'));
    }

    /**
     * Attach a regular writer and a write-on-add writer
     * 
     * @access public
     * @return void
     */
    function test_attach_writer()
    {
        $log = new Minion_Log;

        $writer         = $this->getMockForAbstractClass('Log_Writer');
        $writer_expects = array(
            spl_object_hash($writer) => array(
                'object' => $writer,
                'levels' => array(Log::NOTICE)));

        $on_add         = $this->getMockForAbstractClass('Log_Writer');
        $on_add_expects = array(
            spl_object_hash($on_add) => array(
                'object' => $on_add,
                'levels' => array(Log::NOTICE)));

        $this->assertSame($log, $log->attach($writer, array(Log::NOTICE)), "Minion_Log should not change after attaching a writer");
        $this->assertAttributeSame($writer_expects, '_writers', $log, "The writer should be added to the _writers array");

        $this->assertSame($log, $log->attach($on_add, array(Log::NOTICE), 0, TRUE), "Minion_Log should not change after attaching a write-on-add writer");
        $this->assertAttributeSame($writer_expects, '_writers', $log, "_writers should not be affected when adding a _write_on_add writer");
        $this->assertAttributeSame($on_add_expects, '_on_add_writers', $log, "_write_on_add_writers should be updated after attaching a new writer-on-add writer");
    }

    /**
     * Detach a regular writer and a write-on-add writer
     * 
     * @access public
     * @return void
     */
    function test_detach_writer()
    {
        $log    = new Minion_Log;
        $writer = $this->getMockForAbstractClass('Log_Writer');
        $on_add = $this->getMockForAbstractClass('Log_Writer');

        $log->attach($writer);
        $log->attach($on_add, array(), 0, TRUE);

        $this->assertAttributeNotEmpty('_writers', $log, "_writer should be attached");
        $this->assertAttributeNotEmpty('_on_add_writers', $log, "_on_add_writer should be attached");

        $log->detach($writer);
        $this->assertAttributeEmpty('_writers', $log, "_writer should not be attached");
        $this->assertAttributeNotEmpty('_on_add_writers', $log, "_on_add_writer should be attached");

        $log->detach($on_add);
        $this->assertAttributeEmpty('_writers', $log, "_writer should not be attached");
        $this->assertAttributeEmpty('_on_add_writers', $log, "_on_add_writers should not be attached");
    }

    /**
     * Test $log->add()
     * 
     * @access public
     * @return void
     */
    function test_add()
    {
        $log    = new Minion_Log;
        $writer = $this->getMockForAbstractClass('Log_Writer');
        $on_add = $this->getMockForAbstractClass('Log_Writer');

        $log->attach($writer);
        $log->attach($on_add, array(), 0, TRUE);

        // Should only be called on $log->write()
        $writer->expects($this->never())
                ->method('write');

        // Should only be called on $log->add()
        $on_add->expects($this->once())
                ->method('write');

        $log->add(Log::INFO, "foobar");
    }

    /**
     * Test $log->write()
     * 
     * @access public
     * @return void
     */
    function test_write()
    {
        $log    = new Minion_Log;
        $writer = $this->getMockForAbstractClass('Log_Writer');
        $on_add = $this->getMockForAbstractClass('Log_Writer');

        $log->attach($writer);
        $log->attach($on_add, array(), 0, TRUE);

        // Should be called on $log->write()
        $writer->expects($this->once())
                ->method('write');

        // Should be called on $log->add()
        $on_add->expects($this->once())
                ->method('write');

        $log->add(Log::INFO, "foobar");

        $log->write();
    }

}
