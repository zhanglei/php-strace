<?php
namespace Tests\PhpStrace;

class RunnerTest extends \PHPUnit_Framework_TestCase
{


    public function testBootstrap ()
    {
        $runner = new \PhpStrace\Runner();
        $runner->bootstrap();

        $this->assertEquals('32M', ini_get('memory_limit'));
        $this->assertEquals('-1', ini_get('max_execution_time'));
    }

    public function testShowWelcomeMessage ()
    {
        $runner = new \PhpStrace\Runner();
        $commandLine = $this->getMock('\PhpStrace\CommandLine', array('stdout'));

        $msg = 'php-strace ' . \PhpStrace\Version::ID . ' by Markus Perl (http://www.github.com/markus-perl/php-strace)';
        $commandLine->expects($this->at(0))->method('stdout')->with($msg);
        $commandLine->expects($this->at(1))->method('stdout')->with('');

        $runner->setCommandLine($commandLine);
        $runner->showWelcomeMessage();
    }

    /**
     * @expectedException \Zend\Console\Exception\RuntimeException
     */
    public function testParseGetOptHelp ()
    {
        $argv = array(
            'php-strace',
            '-h'
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);
    }

    public function testParseGetOptLines ()
    {
        $argv = array(
            'php-strace',
            '-l',
            '200'
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);
        $this->assertEquals(200, $runner->getStrace()->getLines());
    }

    public function testParseGetOptLineLength ()
    {
        $argv = array(
            'php-strace',
            '--line-length',
            '300'
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);
        $this->assertEquals(300, $runner->getStrace()->getLineLength());
    }

    public function testParseGetOptProcessName ()
    {
        $argv = array(
            'php-strace',
            '--process-name',
            'php54-cgi'
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);
        $this->assertEquals('php54-cgi', $runner->getProcessStatus()->getProcessName());
    }

    public function testParseGetOptLive()
    {
        $argv = array(
            'php-strace',
            '--live',
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);
        $this->assertTrue($runner->getLive());
    }

    /**
     * @expectedException \PhpStrace\ExitException
     * @expectedExceptionMessage cannot open file /foo/bar for writing.
     */
    public function testParseGetOptOutputInvalidPath()
    {
        $argv = array(
            'php-strace',
            '-o',
            '/foo/bar'
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);
    }

    /**
     */
    public function testParseGetOptOutputValidPath()
    {
        $argv = array(
            'php-strace',
            '-o',
            $path = '/tmp/php-strace.phpunit.log'
        );

        $runner = new \PhpStrace\Runner();
        $runner->parseGetOpt($argv);

        $observers = $runner->getCommandLine()->getObservers('stdout');
        $this->assertInstanceOf('\PhpStrace\FileOutput', $observers[0]);
        $this->assertEquals($path, $observers[0]->getFilePath());
    }



    public function testSetGetCommandLine ()
    {
        $runner = new \PhpStrace\Runner();
        $this->assertInstanceOf('\PhpStrace\CommandLine', $runner->getCommandLine());

        $commandLine = new \PhpStrace\CommandLine();
        $runner->setCommandLine($commandLine);

        $this->assertEquals($commandLine, $runner->getCommandLine());
    }

    public function testGetProcessStatus ()
    {
        $runner = new \PhpStrace\Runner();
        $this->assertInstanceOf('\PhpStrace\ProcessStatus', $runner->getProcessStatus());
    }

    public function testGetStrace ()
    {
        $runner = new \PhpStrace\Runner();
        $this->assertInstanceOf('\PhpStrace\Strace', $runner->getStrace());
    }

    /**
     * @expectedException PhpStrace\ExitException
     */
    public function testcheckRequirements ()
    {
        $runner = new \PhpStrace\Runner();

        $commandLine = $this->getMock('\PhpStrace\CommandLine', array('stderr'));
        $commandLine->expects($this->at(0))->method('stderr')->with('The following Requirements did not met:');
        $commandLine->expects($this->at(1))->method('stderr')->with('root access required. please execute this script as root.');
        $runner->setCommandLine($commandLine);

        $runner->checkRequirements();
    }


    public function testSetGetLive ()
    {
        $runner = new \PhpStrace\Runner();
        $runner->setLive(true);
        $this->assertTrue($runner->getLive());
    }

}