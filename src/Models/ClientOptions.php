<?php
namespace Ably\PubSub\Models;

use Ably\PubSub\Defaults;
use Ably\PubSub\Log;

/**
 * Client library options
 */
class ClientOptions extends AuthOptions {

    /**
     * @var boolean value indicating whether or not a TLS (“SSL”) secure connection should be used.
     */
    public $tls = true;

    /**
     * integer a number controlling the verbosity of the output from 1 (minimum, errors only) to 4 (most verbose);
     * @see \Ably\PubSub\Log
     */
    public $logLevel = Log::WARNING;

    /**
     * @var function|null a function to handle each line of log output. If handler is not specified, STDOUT is used.
     * Note that the log level and log handler have global scope in the library and will thus not act independently between library instances when multiple library instances are existing concurrently.
     * @see \Ably\PubSub\Log
     */
    public $logHandler;

    /**
     * TO3f
     * @var bool If true, msgpack is used for communication, otherwise JSON is used

     */
    public $useBinaryProtocol = true;

    /**
     * @var string|null where the client connects (REC1), default `main`.
     * One of: a routing policy id such as `main` or a dedicated cluster id,
     * which resolves to `[id].realtime.ably.net`; `nonprod:[id]`, which
     * resolves to `[id].realtime.ably-nonprod.net`; or a hostname (anything
     * containing `.` or `::`, or `localhost`), which is used as given and has
     * no default fallback hosts.
     */
    public $endpoint;

    /**
     * @var integer Allows a non-default Ably non-TLS port to be used.
     * For development environments only.
     */
    public $port;

    /**
     * @var integer Allows a non-default Ably TLS port to be used.
     * For development environments only.
     */
    public $tlsPort;

    /**
     * @var string[] fallback hosts, used when connection to the primary domain fails; when empty, derived from $endpoint
     */
    public $fallbackHosts = [];

    /**
     * @var integer – default 600000 (10 minutes) the period in milliseconds
     * before HTTP requests are retried against the primary domain
     */
    public $fallbackRetryTimeout = 600000;

    /**
     * @var \Ably\PubSub\Models\TokenParams defaultTokenParams – overrides the client library defaults described in TokenParams
     */
    public $defaultTokenParams;

    /**
     * @var integer Timeout for opening the connection
     * Warning: may be rounded down on some OSes and values < 1000 will always fail in that case.
     */
    public $httpOpenTimeout = 4000;

    /**
     * @var integer connection timeout after which a next fallback host is used
     */
    public $httpRequestTimeout = 10000;

    /**
     * @var integer Max number of fallback host retries for HTTP requests that fail due to network issues or server problems
     */
    public $httpMaxRetryCount = 3;

    /**
     * @var integer Max elapsed time in which fallback host retries for HTTP requests will be attempted
     */
    public $httpMaxRetryDuration = 15000;

    /**
     * @var string a class that should be used for making HTTP connections
     * To allow mocking in tests.
     */
    public $httpClass = 'Ably\PubSub\Http';

    /**
     * @var bool defaults to false for clients with version < 1.2, otherwise true
     */
    public $idempotentRestPublishing = true;

    /**
     * @var string a class that should be used for Auth
     * To allow mocking in tests.
     */
    public $authClass = 'Ably\PubSub\Auth';

    /**
     * Additional agent entries appended to the `Ably-Agent` request header.
     *
     * This should only be used by Ably-authored SDKs and wrappers layered on
     * top of this package. An identifier used here has to be registered in
     * the ably-common agents registry first:
     * https://github.com/ably/ably-common/blob/main/protocol/agents.json
     *
     * Keys are agent identifiers, values are agent versions. A `null` or
     * empty-string value renders the identifier as a bare flag carrying no
     * version, which is how the registry declares runtime flags such as
     * `browser` and `ably-pubsub-server`.
     *
     * @var array<string, string|null>
     */
    public $agents = [];

    /**
     * Normalises the single argument that the client constructor and the
     * factory door both accept into something ClientOptions can be built from.
     *
     * A bare string is an API key when it contains a colon and a token
     * otherwise. An array or an existing ClientOptions instance passes through
     * unchanged, so an argument of any other type reaches the constructor and
     * raises the constructor's own error rather than a vaguer failure later.
     *
     * @param \Ably\PubSub\Models\ClientOptions|array|string $options
     * @return \Ably\PubSub\Models\ClientOptions|array
     */
    public static function normalizeConstructorArgument( $options ) {
        if ( is_string( $options ) ) {
            return strpos( $options, ':' ) === false
                ? [ 'token' => $options ]
                : [ 'key' => $options ];
        }

        return $options;
    }

    private function activePort() {
        return $this->tls ? $this->tlsPort : $this->port;
    }

    /**
     * The domain both REST requests and, in SDKs that have one, the realtime
     * connection use (REC1, RSC25).
     */
    public function getPrimaryDomain() {
        return Defaults::getPrimaryDomain($this->endpoint);
    }

    /**
     * An explicit, non-empty `fallbackHosts` replaces the defaults (REC2a2);
     * otherwise the defaults are derived from `endpoint` (REC2c).
     */
    public function getFallbackHosts() {
        if (!empty($this->fallbackHosts)) {
            return $this->fallbackHosts;
        }
        return Defaults::getEndpointFallbackHosts($this->endpoint);
    }

    public function getHostUrl($host) {
        return ($this-> tls ? 'https://' : 'http://') . $host. ':' .$this->activePort();
    }

    public function __construct( $options = [] ) {
        parent::__construct( $options );
        if (empty($this->endpoint)) {
            $this->endpoint = Defaults::$endpoint;
        }
        if (empty($this->port)) {
            $this->port = Defaults::$port;
        }
        if (empty($this->tlsPort)) {
            $this->tlsPort = Defaults::$tlsPort;
        }
        if (empty($this->defaultTokenParams)) {
            $this->defaultTokenParams = new TokenParams();
        }
    }
}
