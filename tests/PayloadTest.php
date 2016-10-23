<?php
// @codingStandardsIgnoreFile

namespace Jnjxp\Xcmd;

class PayloadTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $payload = new Payload();
        $payload->setStatus(1);
        $this->assertTrue($payload->isError());
        $this->assertFalse($payload->isSuccess());

        $payload->setStatus(0);
        $this->assertTrue($payload->isSuccess());
        $this->assertFalse($payload->isError());

        $payload->setOutput('foo');
        $this->assertEquals('foo', (string) $payload);
    }

}
