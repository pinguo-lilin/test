<?php
/*
 * php模拟post提交[三种方式]
 */
$remote_sever = 'http://www.test.com/data.php'; //curl other
$remote_path = '/data.php';//curl other

$remote_sever = 'www.test.com'; //socket
$remote_path = '/data.php';//socket
$post_arr = array(1,3,4,5,7,9,10=>array('name','age','sex'));
$post_string = dataEncode($post_arr);

//$post_string = "name=stelin&age=16";
//$post_string = "age=34&name%5B%5D=3&name%5B%5D=4&name%5B%5D=5";

$result = request_by_socket($remote_sever,$remote_path,$post_string);//已通过测试
//$result = request_by_curl($remote_sever,$post_string); //已通过测试
//$result = request_by_other($remote_sever,$post_string); //已通过测试

var_dump($result);

/**
 * Socket版本
 * 使用方法：
 * $post_string = "app=socket&version=beta";
 * request_by_socket('facebook.cn','/restServer.php',$post_string);
 */
function request_by_socket($remote_server, $remote_path, $post_string, $port = 80, $timeout = 30)
{
	
//		$poststr	= rtrim($this->dataEncode($heros), '&');
		$fp	= fsockopen($remote_server, 80, $errno, $errstr, 10) or die("$errstr($errno)");
		fwrite($fp, "POST $remote_path HTTP/1.1\r\n");
		fwrite($fp, "Host: $remote_server\r\n");
		fwrite($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fwrite($fp, "Content-Length: ".strlen($post_string)."\r\n");
		fwrite($fp, "Connection: close\r\n\r\n");
		fwrite($fp, $post_string."\r\n\r\n");
		
		$result		= '';
		$isconter	= false;
		$len		= 0;
		while($str=fgets($fp))
		{
			if($isconter==true) $result	.= $str;
			else if($str=="\r\n")
			{
				$isconter	= true;
				if($_SERVER['SERVER_SOFTWARE']!='Microsoft-IIS/6.0') $len		= hexdec(fgets($fp));
			}
		}
		fclose($fp);
		
		if($_SERVER['SERVER_SOFTWARE']!='Microsoft-IIS/6.0') $result	= substr($result, 0, $len);
		return $result;
} 



/**
 * Curl版本
 * 使用方法：
 * $post_string = "app=request&version=beta";
 * request_by_curl('http://facebook.cn/restServer.php',$post_string);
 */
function request_by_curl($remote_server, $post_string)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $remote_server);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'mypost=' . $post_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Jimmy's CURL Example beta");
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
} 


/**
 * 其它版本
 * 使用方法：
 * $post_string = "app=request&version=beta";
 * request_by_other('http://facebook.cn/restServer.php',$post_string);
 */
function request_by_other($remote_server, $post_string)
{
	$context = array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded' .
						'\r\n'.'User-Agent : Jimmy\'s POST Example beta' .
						'\r\n'.'Content-length:' . strlen($post_string) + 1,
			'content' =>   $post_string)
		);
	$stream_context = stream_context_create($context);
	$data = file_get_contents($remote_server, false, $stream_context);
	return $data;
}
 /**
 * POST数据组合，url传递多维数组，格式化
 *
 * @internal
 * @param 数组 $data
 * @param 字符串 $keyprefix
 * @param 字符串 $keypostfix
 * @return 字符串
 */
function dataEncode($data, $keyprefix = '', $keypostfix = '')
{
	assert(is_array($data));
	$vars = '';
	foreach ($data as $key => $value)
	{
		if (TRUE == is_array($value)) $vars .= dataEncode($value, $keyprefix . $key . $keypostfix . urlencode('['), urlencode(']'));
		else $vars .= $keyprefix . $key . $keypostfix . '='.urlencode($value) . '&';
	}
	return $vars;
} 