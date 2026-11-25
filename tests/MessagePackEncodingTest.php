<?php
namespace tests;

use Ably\PubSub\Models\Message;
use MessagePack\MessagePack;
use MessagePack\PackOptions;

/**
 * Pins the msgpack wire format the SDK sends and accepts.
 *
 * The SDK talks to rybakit/msgpack only through `MessagePack::pack()` and
 * `MessagePack::unpack()` with `PackOptions::FORCE_STR` (see Channel,
 * PubSubHttpClient and Http). The composer constraint admits several msgpack
 * releases, so these offline tests assert exact bytes: a msgpack release that
 * changed the encoding of what the SDK sends, or the decoding of what Ably
 * returns, fails here under every PROTOCOL setting, without the sandbox.
 */
class MessagePackEncodingTest extends \PHPUnit\Framework\TestCase {

    public function testSingleMessageEncoding() {
        $msg = new Message();
        $msg->id = 'id:0';
        $msg->clientId = 'client-1';
        $msg->name = 'greeting';
        $msg->data = 'hello';
        $msg->extras = [ 'headers' => [ 'k' => 'v' ] ];

        $packed = MessagePack::pack( $msg->encodeAsArray(), PackOptions::FORCE_STR );

        $this->assertSame(
            '86a26964a469643a30a8636c69656e744964a8636c69656e742d31a665787472617381a76865616465727381a16b'
            . 'a176a464617461a568656c6c6fa8656e636f64696e67a0a46e616d65a86772656574696e67',
            bin2hex( $packed )
        );
        $this->assertSame( $msg->encodeAsArray(), MessagePack::unpack( $packed, PackOptions::FORCE_STR ) );
    }

    public function testBatchEncodingOfJsonAndBinaryData() {
        $json = new Message();
        $json->name = 'json';
        $json->data = [ 'a' => 1, 'b' => [ true, null ] ];

        $binary = new Message();
        $binary->name = 'binary';
        $binary->data = "\x00\xff\xfe";

        $packed = MessagePack::pack( [ $json->encodeAsArray(), $binary->encodeAsArray() ], PackOptions::FORCE_STR );

        $this->assertSame(
            '9283a464617461b77b2261223a312c2262223a5b747275652c6e756c6c5d7da8656e636f64696e67a46a736f6e'
            . 'a46e616d65a46a736f6e83a464617461a441502f2ba8656e636f64696e67a6626173653634a46e616d65a662696e617279',
            bin2hex( $packed )
        );
    }

    public function testResponseDecoding() {
        // A history-style page: a list of maps with uint64 timestamps.
        $body = hex2bin(
            '9285a26964a56162633a30a46e616d65a16ea464617461a164a974696d657374616d70cf00000193433ea800'
            . 'a665787472617381a76865616465727381a16ba17684a26964a56162633a31a464617461a97b2278223a312e357d'
            . 'a8656e636f64696e67a46a736f6ea974696d657374616d70cf00000193433ea801'
        );

        $this->assertSame(
            [
                [
                    'id' => 'abc:0',
                    'name' => 'n',
                    'data' => 'd',
                    'timestamp' => 1732000000000,
                    'extras' => [ 'headers' => [ 'k' => 'v' ] ],
                ],
                [
                    'id' => 'abc:1',
                    'data' => '{"x":1.5}',
                    'encoding' => 'json',
                    'timestamp' => 1732000000001,
                ],
            ],
            MessagePack::unpack( $body, PackOptions::FORCE_STR )
        );
    }

    public function testIntegerAndFloatDecoding() {
        // Stats-style values: a positive fixint and a float64.
        $body = hex2bin( '81a3616c6c81a86d6573736167657382a5636f756e7402a464617461cb3ff8000000000000' );

        $this->assertSame(
            [ 'all' => [ 'messages' => [ 'count' => 2, 'data' => 1.5 ] ] ],
            MessagePack::unpack( $body, PackOptions::FORCE_STR )
        );
    }
}
