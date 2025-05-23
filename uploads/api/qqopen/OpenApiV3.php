<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
/**
 * PHP SDK for  OpenAPI V3
 *
 * @version 3.0.2
 * @author open.qq.com
 * @copyright ? 2011, Tencent Corporation. All rights reserved.
 * @ History:
 *               3.0.2 | sparkeli | 2012-03-06 17:58:20 | add statistic fuction which can report API's access time and number to background server
 *               3.0.1 | nemozhang | 2012-02-14 17:58:20 | resolve a bug: at line 108, change  'post' to  $method
 *               3.0.0 | nemozhang | 2011-12-12 11:11:11 | initialization
 */

require_once 'lib/SnsNetwork.php';
require_once 'lib/SnsSigCheck.php';
require_once 'lib/SnsStat.php';

/**
 * 如果您的 PHP 没有安装 cURL 扩展，请先安装
 */
if (!function_exists('curl_init'))
{
	throw new Exception('OpenAPI needs the cURL PHP extension.');
}

/**
 * 如果您的 PHP 不支持JSON，请升级到 PHP 5.2.x 以上版本
 */
if (!function_exists('json_decode'))
{
	throw new Exception('OpenAPI needs the JSON PHP extension.');
}

/**
 * 错误码定义
 */
define('OPENAPI_ERROR_REQUIRED_PARAMETER_EMPTY', 2001); // 参数为空
define('OPENAPI_ERROR_REQUIRED_PARAMETER_INVALID', 2002); // 参数格式错误
define('OPENAPI_ERROR_RESPONSE_DATA_INVALID', 2003); // 返回包格式错误
define('OPENAPI_ERROR_CURL', 3000); // 网络错误, 偏移量3000, 详见 http://curl.haxx.se/libcurl/c/libcurl-errors.html

/**
 * 提供访问腾讯开放平台 OpenApiV3 的接口
 */
class OpenApiV3
{
	private $appid  = 0;
	private $appkey = '';
	private $server_name = '';
	private $format = 'json';
	private $stat_url = "apistat.tencentyun.com";
	private $is_stat = true;

	/**
	 * 构造函数
	 *
	 * @param int $appid 应用的ID
	 * @param string $appkey 应用的密钥
	 */
	function __construct($appid, $appkey)
	{
		$this->appid = $appid;
		$this->appkey = $appkey;
	}

	public function setServerName($server_name)
	{
		$this->server_name = $server_name;
	}

	public function setStatUrl($stat_url)
	{
		$this->stat_url = $stat_url;
	}

	public function setIsStat($is_stat)
	{
		$this->is_stat = $is_stat;
	}

	/**
	 * 执行API调用，返回结果数组
	 *
	 * @param array $script_name 调用的API方法 参考 http://wiki.open.qq.com/wiki/API_V3.0%E6%96%87%E6%A1%A3
	 * @param array $params 调用API时带的参数
	 * @param string $method 请求方法 post / get
	 * @param string $protocol 协议类型 http / https
	 * @return array 结果数组
	 */
	public function api($script_name, $params, $method='post', $protocol='http')
	{
		// 检查 openid 是否为空
		if (!isset($params['openid']) || empty($params['openid']))
		{
			return array(
				'ret' => OPENAPI_ERROR_REQUIRED_PARAMETER_EMPTY,
				'msg' => 'openid is empty');
		}
		// 检查 openkey 是否为空
		if (!isset($params['openkey']) || empty($params['openkey']))
		{
			return array(
				'ret' => OPENAPI_ERROR_REQUIRED_PARAMETER_EMPTY,
				'msg' => 'openkey is empty');
		}
		// 检查 openid 是否合法
		if (!self::isOpenId($params['openid']))
		{
			return array(
				'ret' => OPENAPI_ERROR_REQUIRED_PARAMETER_INVALID,
				'msg' => 'openid is invalid');
		}

		// 无需传sig, 会自动生成
		unset($params['sig']);

		// 添加一些参数
		$params['appid'] = $this->appid;
		$params['format'] = $this->format;

		// 生成签名
		$secret = $this->appkey . '&';
		$sig = SnsSigCheck::makeSig( $method, $script_name, $params, $secret);
		$params['sig'] = $sig;

		$url = $protocol . '://' . $this->server_name . $script_name;
		$cookie = array();

		//记录接口调用开始时间
		$start_time = SnsStat::getTime();

		// 发起请求
		$ret = SnsNetwork::makeRequest($url, $params, $cookie, $method, $protocol);

		if (false === $ret['result'])
		{
			$result_array = array(
				'ret' => OPENAPI_ERROR_CURL + $ret['errno'],
				'msg' => $ret['msg'],
			);
		}

		$result_array = json_decode($ret['msg'], true);

		// 远程返回的不是 json 格式, 说明返回包有问题
		if (is_null($result_array)) {
			$result_array = array(
				'ret' => OPENAPI_ERROR_RESPONSE_DATA_INVALID,
				'msg' => $ret['msg']
			);
		}

		// 统计上报
		if ($this->is_stat)
		{
			$stat_params = array(
					'appid' => $this->appid,
					'pf' => $params['pf'],
					'rc' => $result_array['ret'],
					'svr_name' => $this->server_name,
					'interface' => $script_name,
					'protocol' => $protocol,
					'method' => $method,
			);
			SnsStat::statReport($this->stat_url, $start_time, $stat_params);
		}

		return $result_array;
	}

	/**
	 * 检查 openid 的格式
	 *
	 * @param string $openid openid
	 * @return bool (true|false)
	 */
	private static function isOpenId($openid)
	{
		return (0 == preg_match('/^[0-9a-fA-F]{32}$/', $openid)) ? false : true;
	}
}

// end of script
