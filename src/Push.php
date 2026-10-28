<?php
namespace Ably\PubSub;

class Push {

    private $ably;
    public $admin;

    /**
     * Constructor
     * @param PubSubHttpClient $ably Ably API instance
     */
    public function __construct( PubSubHttpClient $ably ) {
        $this->ably = $ably;
        $this->admin = new PushAdmin( $ably );
    }

}
