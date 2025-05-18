<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
//禁止抓取远程图片
/*
set_time_limit(0);
include("Uploader.class.php");
include(dirname(dirname(dirname(dirname(__FILE__))))."/data/plus/config.php");
//传入设定 文件类型
if(!empty($config['sy_oss'])){
    $sy_oss = $config['sy_oss'];
    if ($sy_oss == 1){
        $sy_ossurl = $config['sy_ossurl'];
    }else{
        $sy_ossurl = $config['sy_weburl'];
    }
}else{
    $sy_oss = 2;
    $sy_ossurl = $config['sy_weburl'];
}
/* 上传配置 */
$config = array(
    "pathFormat" => $CONFIG['catcherPathFormat'],
    "maxSize"    => $CONFIG['catcherMaxSize'],
    "allowFiles" => $CONFIG['catcherAllowFiles'],
    "oriName"    => "remote.png",
    'sy_oss'     => $sy_oss,
    'sy_ossurl'  => $sy_ossurl
);
$fieldName = $CONFIG['catcherFieldName'];

/* 抓取远程图片 */
$list = array();
if (isset($_POST[$fieldName])) {
    $source = $_POST[$fieldName];
} else {
    $source = $_GET[$fieldName];
}
foreach ($source as $imgUrl) {
    $item = new Uploader($imgUrl, $config, "remote");
    $info = $item->getFileInfo();
    array_push($list, array(
        "state" => $info["state"],
        "url" => $info["url"],
        "size" => $info["size"],
        "title" => htmlspecialchars($info["title"]),
        "original" => htmlspecialchars($info["original"]),
        "source" => htmlspecialchars($imgUrl)
    ));
}

/* 返回抓取数据 */
return json_encode(array(
    'state'=> count($list) ? 'SUCCESS':'ERROR',
    'list'=> $list
));
*/