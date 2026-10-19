<?php
namespace tests;
use Ably\PubSub\Defaults;

class DefaultsTest extends \PHPUnit\Framework\TestCase {

    /**
     * @testdox REC2c1
     */
    public function testFallbackHosts() {
        $expectedFallbackHosts = [
            "main.a.fallback.ably-realtime.com",
            "main.b.fallback.ably-realtime.com",
            "main.c.fallback.ably-realtime.com",
            "main.d.fallback.ably-realtime.com",
            "main.e.fallback.ably-realtime.com"
        ];
        $this->assertEquals($expectedFallbackHosts, Defaults::$fallbackHosts);
        $this->assertEquals($expectedFallbackHosts, Defaults::getEndpointFallbackHosts(Defaults::$endpoint));
    }

    /**
     * @testdox REC1a
     */
    public function testDefaultEndpoint() {
        $this->assertEquals('main', Defaults::$endpoint);
        $this->assertEquals('main.realtime.ably.net', Defaults::getPrimaryDomain(Defaults::$endpoint));
    }
}
