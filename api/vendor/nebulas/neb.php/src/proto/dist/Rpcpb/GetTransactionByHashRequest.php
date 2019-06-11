<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: rpc.proto

namespace Rpcpb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message of GetTransactionByHash rpc.
 *
 * Generated from protobuf message <code>rpcpb.GetTransactionByHashRequest</code>
 */
class GetTransactionByHashRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Hex string of transaction hash.
     *
     * Generated from protobuf field <code>string hash = 1;</code>
     */
    private $hash = '';

    public function __construct() {
        \GPBMetadata\Rpc::initOnce();
        parent::__construct();
    }

    /**
     * Hex string of transaction hash.
     *
     * Generated from protobuf field <code>string hash = 1;</code>
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Hex string of transaction hash.
     *
     * Generated from protobuf field <code>string hash = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setHash($var)
    {
        GPBUtil::checkString($var, True);
        $this->hash = $var;

        return $this;
    }

}

