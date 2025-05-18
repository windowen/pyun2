<?php

/**
 * Class Config
 *
 * 执行Sample示例所需要的配置，用户在这里配置好Endpoint，AccessId， AccessKey和Sample示例操作的
 * bucket后，便可以直接运行RunAll.php, 运行所有的samples
 */
include (dirname(dirname(dirname(dirname(__FILE__)))).'/data/api/aliyun_oss/oss_data.php');

define('OSS_ACCESS_ID', $oss_data['access_id']);
define('OSS_ACCESS_KEY', $oss_data['access_key']);
define('OSS_ENDPOINT', $oss_data['endpoint']);
define('OSS_TEST_BUCKET', $oss_data['bucket']);
define('OSS_USERDOMAIN', $oss_data['userdomain']);

final class Config
{
    const OSS_ACCESS_ID		=  OSS_ACCESS_ID;
    const OSS_ACCESS_KEY	=  OSS_ACCESS_KEY;
    const OSS_ENDPOINT		=  OSS_ENDPOINT;
    const OSS_TEST_BUCKET	=  OSS_TEST_BUCKET;
    const OSS_USERDOMAIN	=  OSS_USERDOMAIN;
}
