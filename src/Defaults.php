<?php
namespace Ably\PubSub;

class Defaults {
    const API_VERSION = '2';
    const LIB_VERSION = '2.0.0';

    static $endpoint = 'main';
    static $port = 80;
    static $tlsPort = 443;

    static $internetCheckUrl = "https://internet-up.ably-realtime.com/is-the-internet-up.txt";
    static $internetCheckOk = "yes\n";

    static $fallbackHosts = [
        'main.a.fallback.ably-realtime.com',
        'main.b.fallback.ably-realtime.com',
        'main.c.fallback.ably-realtime.com',
        'main.d.fallback.ably-realtime.com',
        'main.e.fallback.ably-realtime.com',
    ];

    const NONPROD_PREFIX = 'nonprod:';

    /**
     * An endpoint is taken as a hostname rather than a routing policy id when
     * it contains a `.` or `::`, or is `localhost` (REC1b2).
     */
    static function isHostname($endpoint) {
        return strpos($endpoint, '.') !== false
            || strpos($endpoint, '::') !== false
            || $endpoint === 'localhost';
    }

    /**
     * Resolves the primary domain from an `endpoint` client option (REC1).
     */
    static function getPrimaryDomain($endpoint) {
        if (self::isHostname($endpoint)) {
            return $endpoint; // REC1b2
        }
        if (strpos($endpoint, self::NONPROD_PREFIX) === 0) {
            return substr($endpoint, strlen(self::NONPROD_PREFIX)).'.realtime.ably-nonprod.net'; // REC1b3
        }
        return $endpoint.'.realtime.ably.net'; // REC1b4
    }

    /**
     * Resolves the default fallback domains for an `endpoint` client option (REC2c).
     */
    static function getEndpointFallbackHosts($endpoint) {
        if (self::isHostname($endpoint)) {
            return []; // REC2c2
        }
        if (strpos($endpoint, self::NONPROD_PREFIX) === 0) {
            $id = substr($endpoint, strlen(self::NONPROD_PREFIX));
            return self::fallbackHostsFor($id, 'ably-realtime-nonprod.com'); // REC2c3
        }
        return self::fallbackHostsFor($endpoint, 'ably-realtime.com'); // REC2c1, REC2c4
    }

    private static function fallbackHostsFor($id, $domain) {
        return array_map(function ($letter) use ($id, $domain) {
            return $id.'.'.$letter.'.fallback.'.$domain;
        }, ['a', 'b', 'c', 'd', 'e']);
    }
}
