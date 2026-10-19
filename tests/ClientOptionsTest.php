<?php
namespace tests;
use Ably\PubSub\Defaults;
use Ably\PubSub\Models\ClientOptions;

/**
 * @testdox RSC15b
 */
class ClientOptionsTest extends \PHPUnit\Framework\TestCase {
    /**
     * @testdox RSC15e REC1a REC2c1
     */
    public function testWithDefaultOptions() {
        $clientOptions = new ClientOptions();
        self::assertEquals('main', $clientOptions->endpoint);
        self::assertEquals('main.realtime.ably.net', $clientOptions->getPrimaryDomain());
        self::assertTrue($clientOptions->tls);
        self::assertEquals(443, $clientOptions->tlsPort);
        $fallbackHosts = $clientOptions->getFallbackHosts();
        sort($fallbackHosts);
        $this->assertEquals(Defaults::$fallbackHosts, $fallbackHosts);
    }

    /**
     * @testdox REC1b4 REC2c4
     */
    public function testWithRoutingPolicyEndpoint() {
        $clientOptions = new ClientOptions(['endpoint' => 'acme']);
        self::assertEquals('acme.realtime.ably.net', $clientOptions->getPrimaryDomain());
        self::assertEquals([
            'acme.a.fallback.ably-realtime.com',
            'acme.b.fallback.ably-realtime.com',
            'acme.c.fallback.ably-realtime.com',
            'acme.d.fallback.ably-realtime.com',
            'acme.e.fallback.ably-realtime.com',
        ], $clientOptions->getFallbackHosts());
    }

    /**
     * @testdox REC1b3 REC2c3
     */
    public function testWithNonprodEndpoint() {
        $clientOptions = new ClientOptions(['endpoint' => 'nonprod:sandbox']);
        self::assertEquals('sandbox.realtime.ably-nonprod.net', $clientOptions->getPrimaryDomain());
        self::assertEquals([
            'sandbox.a.fallback.ably-realtime-nonprod.com',
            'sandbox.b.fallback.ably-realtime-nonprod.com',
            'sandbox.c.fallback.ably-realtime-nonprod.com',
            'sandbox.d.fallback.ably-realtime-nonprod.com',
            'sandbox.e.fallback.ably-realtime-nonprod.com',
        ], $clientOptions->getFallbackHosts());
    }

    public static function hostnameEndpointProvider() {
        return [
            ['foo.example.com'],
            ['localhost'],
            ['127.0.0.1'],
            ['::1'],
        ];
    }

    /**
     * @testdox REC1b2 REC2c2
     * @dataProvider hostnameEndpointProvider
     */
    public function testWithHostnameEndpoint($endpoint) {
        $clientOptions = new ClientOptions(['endpoint' => $endpoint]);
        self::assertEquals($endpoint, $clientOptions->getPrimaryDomain());
        self::assertEmpty($clientOptions->getFallbackHosts());
    }

    /**
     * Non-default ports do not suppress the default fallback hosts; only a
     * hostname endpoint does (REC2c).
     * @testdox REC2c1
     */
    public function testWithNonDefaultPorts() {
        $clientOptions = new ClientOptions(['port' => 8080, 'tlsPort' => 8081]);
        self::assertEquals(8080, $clientOptions->port);
        self::assertEquals(8081, $clientOptions->tlsPort);
        self::assertEquals('main.realtime.ably.net', $clientOptions->getPrimaryDomain());
        self::assertEquals(Defaults::$fallbackHosts, $clientOptions->getFallbackHosts());
    }

    /**
     * @testdox RSC15g1 REC2a2
     */
    public function testWithFallbacks() {
        foreach (['main', 'nonprod:sandbox', 'foo.example.com'] as $endpoint) {
            $clientOptions = new ClientOptions([
                'endpoint' => $endpoint,
                'fallbackHosts' => ["a.example.com", "b.example.com"],
            ]);
            self::assertEquals(["a.example.com", "b.example.com"], $clientOptions->getFallbackHosts());
        }
    }
}
