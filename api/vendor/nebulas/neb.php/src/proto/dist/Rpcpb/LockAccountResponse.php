<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: rpc.proto

namespace Rpcpb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>rpcpb.LockAccountResponse</code>
 */
class LockAccountResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>bool result = 1;</code>
     */
    private $result = false;

    public function __construct() {
        \GPBMetadata\Rpc::initOnce();
        parent::__construct();
    }

    /**
     * Generated from protobuf field <code>bool result = 1;</code>
     * @return bool
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Generated from protobuf field <code>bool result = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setResult($var)
    {
        GPBUtil::checkBool($var);
        $this->result = $var;

        return $this;
    }

}
