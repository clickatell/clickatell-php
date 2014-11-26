<?php
namespace Clickatell\Api;

use Clickatell\Decoder;
use \PHPUnit_Framework_TestCase;
use \ReflectionClass;

class ClickatellHttpTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $uri = "http/sendmsg";
        $args = array(
            'user'      => 'username',
            'password'  => 'password',
            'api_id'    => '123456'
        );

        $clickatell = $this->getMockBuilder('Clickatell\Api\ClickatellHttp')
            ->setMethods(array('curl'))
            ->setConstructorArgs(array('username', 'password', '123456'))
            ->getMock();

        $clickatell->expects($this->once())
            ->method('curl')
            ->with($uri, http_build_query($args));

        $class = new ReflectionClass($clickatell);
        $method = $class->getMethod('get');
        $method->setAccessible(true);
        $method->invokeArgs($clickatell, array($uri, array()));
    }

    public function testSendMessage()
    {
        $default = array(
            'to'        => "12345,123456",
            'text'      => 'message',
            'mo'        => false,
            'callback'  => true
        );

        $clickatell = $this->getMockBuilder('Clickatell\Api\ClickatellHttp')
            ->setMethods(array('unwrapLegacy', 'get'))
            ->disableOriginalConstructor()
            ->getMock();

        $response = new Decoder('ID: 123456789 To: 12345', 200);

        $clickatell->expects($this->once())
            ->method('get')
            ->with('http/sendmsg', $default)
            ->will($this->returnValue($response));

        $entries = $clickatell->sendMessage(array(12345, 123456), "message", array('mo' => false));

        $this->assertSame("123456789", $entries[0]->getApiMsgId());
        $this->assertEquals(12345, $entries[0]->getDestination());
        $this->assertSame(false, $entries[0]->getErrorCode());
        $this->assertSame(false, $entries[0]->getError());
    }
}