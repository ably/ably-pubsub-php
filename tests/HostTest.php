<?php
namespace tests;

use Ably\PubSub\Host;
use Ably\PubSub\Models\ClientOptions;

/**
 * @testdox RSC15
 */
class HostTest extends \PHPUnit\Framework\TestCase {

    /**
     * @testdox RSA15a, RSC15b, RSC15e
     */
    public function testFallbacksInRandomOrder() {
        $clientOptions = new ClientOptions();
        $restHosts = new Host($clientOptions);
        // All expected hosts supposed to be tried upon
        $expectedFallbackHosts = [
			"main.a.fallback.ably-realtime.com",
			"main.b.fallback.ably-realtime.com",
			"main.c.fallback.ably-realtime.com",
			"main.d.fallback.ably-realtime.com",
			"main.e.fallback.ably-realtime.com",
		];

        $prefHost = $restHosts->getPreferredHost();
        self::assertEquals("main.realtime.ably.net", $prefHost);

        $fallbacks = iterator_to_array($restHosts->fallbackHosts($prefHost));
        self::assertNotEquals($expectedFallbackHosts, $fallbacks);

        sort($fallbacks);
        self::assertEquals($expectedFallbackHosts, $fallbacks);
    }

    /**
     * @testdox RSC15a, RSA15e, RSC15f
     */
    public function testFallbacksOtherThanPreferredHost() {
        $clientOptions = new ClientOptions();
        $restHosts = new Host($clientOptions);
        // All expected hosts supposed to be tried upon
        $expectedFallbackHosts = [
            "main.realtime.ably.net",
            "main.a.fallback.ably-realtime.com",
            "main.c.fallback.ably-realtime.com",
            "main.d.fallback.ably-realtime.com",
            "main.e.fallback.ably-realtime.com",
        ];

        $restHosts->setPreferredHost("main.b.fallback.ably-realtime.com");

        $prefHost = $restHosts->getPreferredHost();
        self::assertEquals("main.b.fallback.ably-realtime.com", $prefHost);

        $fallbacks = iterator_to_array($restHosts->fallbackHosts($prefHost));
        self::assertEquals("main.realtime.ably.net", $fallbacks[0]);

        sort($fallbacks);
        sort($expectedFallbackHosts);
        self::assertEquals($expectedFallbackHosts, $fallbacks);
    }

    /**
     * @testdox RSC15a
     */
    public function testGetAllFallbacksWithNoPreferredHost() {
        $clientOptions = new ClientOptions();
        $restHosts = new Host($clientOptions);
        // All expected hosts supposed to be tried upon
        $expectedFallbackHosts = [
            "main.realtime.ably.net",
            "main.b.fallback.ably-realtime.com",
            "main.a.fallback.ably-realtime.com",
            "main.c.fallback.ably-realtime.com",
            "main.d.fallback.ably-realtime.com",
            "main.e.fallback.ably-realtime.com",
        ];

        $fallbacks = iterator_to_array($restHosts->fallbackHosts(""));
        self::assertEquals("main.realtime.ably.net", $fallbacks[0]);

        sort($fallbacks);
        sort($expectedFallbackHosts);
        self::assertEquals($expectedFallbackHosts, $fallbacks);
    }

    /**
     * @testdox RSC15e
     */
    public function testGetPrimaryHostIfNothingIsCached() {
        $clientOptions = new ClientOptions();
        $restHosts = new Host($clientOptions);
        $prefHost = $restHosts->getPreferredHost();
        self::assertEquals("main.realtime.ably.net", $prefHost);
    }
}

