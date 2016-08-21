<?php
/**
* @backupGlobals disabled
*/
class LoggerTest extends PHPUnit_Framework_TestCase {
    
    public function testLog() {
        $log = new Logger(Yaf_Registry::get('config')->application->logdir, Logger::INFO);
        $log->logInfo('Returned a million search results');
        $log->logError('Returned a million search results', 'error');
    }
}
?>