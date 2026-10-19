<?php
namespace tests;

use Ably\PubSub\HostCache;

/**
 * @testdox RSC15f
 */
class HostCacheTest extends \PHPUnit\Framework\TestCase {

    /**
     * @testdox RSC15f
     */
    public function testHost() {
        $hostCache = new HostCache(3000);
        $hostCache->put("main.realtime.ably.net");
        self::assertEquals("main.realtime.ably.net", $hostCache->get());
    }

    /**
     * @testdox RSC15f
     */
    public function testExpiredHost() {
        $hostCache = new HostCache(999);
        $hostCache->put("main.realtime.ably.net");
        sleep(1);
        self::assertEquals("", $hostCache->get());
    }
}
