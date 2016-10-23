<?php
// @codingStandardsIgnoreFile

namespace Jnjxp\Xcmd;

class ExternalCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $cmd = 'echo "foo"';
        $command = new ExternalCommand($cmd);
        $payload = $command();

        $extra = ['command' => $cmd, 'cwd' => null, 'env' => []];
        $this->assertTrue($payload->isSuccess());
        $this->assertEquals("foo", $payload->getOutput());
        $this->assertNull($payload->getInput());
        $this->assertEquals([], $payload->getMessages());
        $this->assertEquals($extra, $payload->getExtras());
    }

    public function testInvalid()
    {
        $errors = ['sh: 1: NonExistantCommand123: not found'];
        $command = new ExternalCommand('NonExistantCommand123');
        $payload = $command();
        $this->assertTrue($payload->isError());
        $this->assertFalse($payload->isSuccess());
        $this->assertSame(127, $payload->getStatus());
        $this->assertEquals($errors, $payload->getMessages());

        $command->throwException();
        $this->setExpectedException(Exception::class);
        $command();
    }

    public function testInput()
    {
        $cmd = 'cat';
        $command = new ExternalCommand($cmd);
        $payload = $command('foo');

        $extra = ['command' => $cmd, 'cwd' => null, 'env' => []];
        $this->assertTrue($payload->isSuccess());
        $this->assertEquals('foo', $payload->getOutput());
        $this->assertEquals('foo', $payload->getInput());
        $this->assertEquals([], $payload->getMessages());
        $this->assertEquals($extra, $payload->getExtras());
    }

    public function testCwd()
    {
        $cmd = 'pwd';
        $cwd = '/tmp';
        $command = new ExternalCommand($cmd);
        $command->cwd($cwd);
        $payload = $command();

        $extra = ['command' => $cmd, 'cwd' => $cwd, 'env' => []];
        $this->assertTrue($payload->isSuccess());
        $this->assertEquals($cwd, $payload->getOutput());
        $this->assertEquals([], $payload->getMessages());
        $this->assertEquals($extra, $payload->getExtras());
    }

    public function testEnv()
    {
        $cmd = 'echo $FOO';
        $env = ['FOO' => 'foobarbaz'];
        $command = new ExternalCommand($cmd);
        $command->env($env);
        $payload = $command();

        $extra = ['command' => $cmd, 'cwd' => null, 'env' => $env];
        $this->assertTrue($payload->isSuccess());
        $this->assertEquals('foobarbaz', $payload->getOutput());
        $this->assertEquals([], $payload->getMessages());
        $this->assertEquals($extra, $payload->getExtras());
    }
}
