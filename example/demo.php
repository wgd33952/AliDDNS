<?php
require(dirname(__DIR__) . '/vendor/autoload.php');

use wgd33952\AliyunCloud\AliDDNS;

$config = [
    // 开启调试模式
    "debug" => true,
    // 阿里云 AccessKey ID
    "accessKeyId" => "accessKeyId",
    // 阿里云 AccessKey Secret
    "accessKeySecret" => "accessKeySecret",
    // 域名
    "domain" => "domain.com",
    // 主机记录
    "rr" => "www",
    // TTL
    "ttl" => 600
];
AliDDNS::init($config)->run();