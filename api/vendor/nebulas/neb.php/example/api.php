<?php
/**
 * Created by PhpStorm.
 * User: yupna
 * Date: 2018/5/24
 * Time: 14:48
 */


require ('../vendor/autoload.php');

use Nebulas\Rpc\HttpProvider;
use Nebulas\Rpc\Neb;

//$neb = new Neb();
//$neb = new Neb(new HttpProvider("http://172.0.0.1:8685"));    //local node
//$neb->setRequest(new HttpProvider("https://testnet.nebulas.io")); //mainnet
$neb = new Neb(new HttpProvider("https://testnet.nebulas.io")); //testnet

$api = $neb->api;

echo $api->getNebState(), PHP_EOL;  // {"result":{"chain_id":1001,"tail":"aaec....
echo $neb->api->getNebState(), PHP_EOL;

//echo $api->latestIrreversibleBlock(), PHP_EOL;
//
//echo $api->getAccountState("n1GDCCpQ2Z97o9vei2ajq6frrTPyLNCbnt7"), PHP_EOL;
//echo $api->getBlockByHash("5cce7b5e719b5af679dbc0f4166e9c8665eb03704eb33b97ccb59d4e4ba14352"), PHP_EOL;
//echo $api->getBlockByHeight(1000), PHP_EOL;
//echo $api->getBlockByHeight("1000"), PHP_EOL;

//echo $api->getTransactionReceipt("8b98a5e4a27d2744a6295fe71e4f138d3e423ced11c81e201c12ac8379226ad1"), PHP_EOL;
//echo $api->gasPrice(), PHP_EOL;

//echo $api->getTransactionByContract("n1zRenwNRXVwY6akcF4rUNoKhmNWP9bhSq8");

//echo $api->getEventsByHash("8b98a5e4a27d2744a6295fe71e4f138d3e423ced11c81e201c12ac8379226ad1");

/**
 * Get account state
 */
$resp = $api->getAccountState("n1H2Yb5Q6ZfKvs61htVSV4b1U2gr2GA9vo6");
$respObj = json_decode($resp);
$nonce = $respObj->result->nonce;
echo "account state: ", $resp, PHP_EOL; //account state: {"result":{"balance":"100999717112000000","nonce":"14","type":87}}
echo "account nonce: ", $nonce, PHP_EOL;

/**
 * simulate an binary type transaction
 */
$resp = $api->call("n1JmhE82GNjdZPNZr6dgUuSfzy2WRwmD9zy",
    "n1JmhE82GNjdZPNZr6dgUuSfzy2WRwmD9zy",
    "100000",
    $respObj->result->nonce + 1,
    "200000",
    "200000");

echo $resp, PHP_EOL;
//{"result":{"result":"","execute_err":"","estimate_gas":"20000"}}

/**
 * simulate an call type transaction
 */
$resp = $neb->api->call("n1H2Yb5Q6ZfKvs61htVSV4b1U2gr2GA9vo6",
    "n1oXdmwuo5jJRExnZR5rbceMEyzRsPeALgm",
    "0",
    $nonce + 1,
    "200000",
    "200000",
    array("function" => 'get', 'args' => '["nebulas"]'));

echo $resp, PHP_EOL;
//{"result":{"result":"{\"key\":\"nebulas\",\"value\":\"forever nebulas!\",\"author\":\"n1JmhE82GNjdZPNZr6dgUuSfzy2WRwmD9zy\"}","execute_err":"","estimate_gas":"20221"}}


/**
 * estimate gas consumption
 */
$resp = $api->estimateGas("n1JmhE82GNjdZPNZr6dgUuSfzy2WRwmD9zy",
    "n1JmhE82GNjdZPNZr6dgUuSfzy2WRwmD9zy",
    "100000",
    0,
    "200000",
    "200000");
echo $resp, PHP_EOL;




