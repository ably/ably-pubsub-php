![Ably Pub/Sub PHP Header](https://raw.githubusercontent.com/ably/ably-pubsub-php/main/images/php-SDK-github.png)

[![Packagist: ably/pubsub-server](https://img.shields.io/packagist/v/ably/pubsub-server?label=ably%2Fpubsub-server)](https://packagist.org/packages/ably/pubsub-server)
[![License](https://badgen.net/github/license/ably/ably-pubsub-php)](https://github.com/ably/ably-pubsub-php/blob/main/LICENSE)

# Ably Pub/Sub PHP SDK

The PHP SDK for [Ably Pub/Sub](https://ably.com/docs/basics): publish messages to channels, read
message history and presence, and issue tokens for your users, from servers over HTTP.

## Overview

This SDK provides the server package only, for trusted backends authenticating with an API key.
There is no device package; for end-user devices, use a device SDK such as
[JavaScript](https://github.com/ably/ably-pubsub-js), [Swift/Objective-C](https://github.com/ably/ably-pubsub-cocoa),
[Java/Android](https://github.com/ably/ably-pubsub-java), [.NET](https://github.com/ably/ably-pubsub-dotnet)
or [Flutter](https://github.com/ably/ably-pubsub-flutter).

| Package | Use it for | Entry point |
|---------|-----------|-------------|
| `ably/pubsub-server` | Servers and other trusted backend environments | `Ably\PubSub\Server::createHttpClient()` |

## Requirements

PHP 8.1 or later (tested on 8.1 to 8.5), with the `curl`, `json` and `openssl` extensions.

## Installation

```bash
composer require ably/pubsub-server
```

## Quick start

Use an API key on a server. The samples read it from the `ABLY_API_KEY` environment variable; set it
to an API key from your [Ably dashboard](https://ably.com/accounts/any/apps/any/app_keys). The
following code publishes a message over HTTP, then reads the channel's history.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Ably\PubSub\Server;

$ablyApiKey = getenv('ABLY_API_KEY');
$client = Server::createHttpClient(['key' => $ablyApiKey]);
$channel = $client->channels->get('test-channel');

$channel->publish('test-event', 'hello world');

$history = $channel->history();
foreach ($history->items as $message) {
    echo "Received: {$message->name}: {$message->data}\n";
}
```

A failed request throws an `Ably\PubSub\Exceptions\AblyException`.

Always create clients with `Server::createHttpClient()`. A client created with
`new PubSubHttpClient(...)` does not declare itself as a server.

> [!IMPORTANT]
> Server clients must authenticate with an API key. To use a token instead, see [Server clients and token authentication](https://ably.com/docs/auth/token#server-token-auth).

Your server also issues the tokens that devices authenticate with. Return a token request from
the endpoint your devices use as their `authUrl`:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Ably\PubSub\Server;

$ablyApiKey = getenv('ABLY_API_KEY');
$client = Server::createHttpClient(['key' => $ablyApiKey]);

// Authenticate the caller first. Grant only the operations this user needs.
$tokenRequest = $client->auth->createTokenRequest([
    'clientId' => 'me', // the authenticated user's ID
    // Without a capability, the token gets all of the API key's capabilities.
    'capability' => ['test-channel' => ['publish', 'subscribe', 'history']],
]);

header('Content-Type: application/json');
echo json_encode($tokenRequest->toArray());
```

See [token authentication](https://ably.com/docs/auth/token) for how devices use it.

## Migrating from 1.x

Version 2.0 renames the package and namespace, and creates clients with a factory method.

| | 1.x | 2.0 |
|---|---|---|
| Package | `ably/ably-php` | `ably/pubsub-server` |
| Namespace | `Ably\` | `Ably\PubSub\` |
| HTTP client | `new AblyRest($options)` | `Server::createHttpClient($options)` |

See [UPDATING.md](https://github.com/ably/ably-pubsub-php/blob/main/UPDATING.md#1x-ablyably-php--200-ablypubsub-server) for all other changes.

## Common configuration

`Server::createHttpClient()` takes an options array or an `Ably\PubSub\Models\ClientOptions`
object.

### Endpoint

`endpoint` selects the Ably host for HTTP requests. It replaces the 1.x `environment` and
`restHost` options. 2.0 ignores those options, so a client that still sets them connects to
production.

```php
$ablyApiKey = getenv('ABLY_API_KEY');
$client = Server::createHttpClient([
    'key' => $ablyApiKey,
    'endpoint' => 'nonprod:sandbox',
]);
```

- Never put a port in `endpoint`: `localhost:8080` is not a hostname and does not resolve.
- Set the port with `tlsPort`, or with `port` when TLS is off.
- With TLS off (`'tls' => false`), use token authentication.

See [UPDATING.md](https://github.com/ably/ably-pubsub-php/blob/main/UPDATING.md#endpoint-replaces-environment-and-resthost) for how the old options
map to `endpoint`.

### Proxy

To send every request through a proxy, set `http_proxy` (or `https_proxy`) in `$_SERVER`. From
the PHP command line, an environment variable does this. Put any proxy credentials in the URL:

```bash
export http_proxy=http://username:password@proxy.example.com:8080
```

### Logging

Set `logLevel` to `Log::NONE`, `Log::ERROR`, `Log::WARNING` (the default), `Log::DEBUG` or
`Log::VERBOSE`. Logs go to standard output. To handle them yourself, set `logHandler`:

```php
use Ably\PubSub\Log;
use Ably\PubSub\Server;

$ablyApiKey = getenv('ABLY_API_KEY');
$client = Server::createHttpClient([
    'key' => $ablyApiKey,
    'logLevel' => Log::DEBUG,
    'logHandler' => function ($level, $args) {
        error_log('Ably: ' . json_encode($args));
    },
]);
```

Logging settings are global: the most recently created client sets them for the whole process.

## Platform notes

### Laravel

The current releases of Ably's [Laravel broadcaster](https://github.com/ably/laravel-broadcaster)
and [Laravel SDK](https://github.com/ably/ably-php-laravel) depend on `ably/ably-php` 1.x, not on
`ably/pubsub-server`. See [Getting started with Laravel](https://ably.com/docs/getting-started/laravel).

## Known limitations

- The SDK has no realtime client. It uses the [Ably REST API](https://ably.com/docs/api/rest-api)
  only, so it can't subscribe to channels or enter presence.
- To receive messages in PHP, use Ably's [MQTT adapter](https://ably.com/docs/protocols/mqtt)
  with an MQTT client such as [Mosquitto-PHP](https://github.com/mgdm/Mosquitto-PHP).

## Further reading

- [Getting started with Pub/Sub using PHP](https://ably.com/docs/getting-started/php)
- [Ably Pub/Sub documentation](https://ably.com/docs/basics) and [examples](https://ably.com/examples?product=pubsub)
- [Token authentication](https://ably.com/docs/auth/token)
- [UPDATING.md](https://github.com/ably/ably-pubsub-php/blob/main/UPDATING.md): migrating from 1.x
- [CHANGELOG.md](https://github.com/ably/ably-pubsub-php/blob/main/CHANGELOG.md): release notes; all Ably releases are listed on [changelog.ably.com](https://changelog.ably.com)

## Contributing

Read [CONTRIBUTING.md](https://github.com/ably/ably-pubsub-php/blob/main/CONTRIBUTING.md) before contributing.

## Support, feedback and troubleshooting

For help, visit Ably's [support page](https://ably.com/support). Report bugs and compatibility
problems on [GitHub Issues](https://github.com/ably/ably-pubsub-php/issues).

## License

Licensed under the Apache License, Version 2.0. See [LICENSE](https://github.com/ably/ably-pubsub-php/blob/main/LICENSE).
