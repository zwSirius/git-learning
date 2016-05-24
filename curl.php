<?php
/**
  * 把从HTML源码中获取的相对路径转换成绝对路径
  * @param string $url HTML中获取的网址
  * @param string $URI 用来参考判断的原始地址
  * @return 返回修改过的网址，如果网址有误则返回FALSE
  */
function filter_relative_url($url, $URI){
 	//STEP1: 先去判断URL中是否包含协议，如果包含说明是绝对地址则可以原样返回
 	if(strpos($url, '://') !== FALSE){
 		return $url;
 	}

 	//STEP2: 解析传入的URI
 	$URI_part = parse_url($URI);
 	if($URI_part == FALSE)
 		return FALSE;
 	$URI_root = $URI_part['scheme'] . '://' . $URI_part['host'] . (isset($URI_part['port']) ? ':' . $URI_part['port'] : '');

 	//STEP3: 如果URL以左斜线开头，表示位于根目录
 	if (strpos($url, '/') === 0){
 		return $URI_root . $url;
 	}

 	//STEP4: 不位于根目录，也不是绝对路径，考虑如果不包含'./'的话，需要把相对地址接在原URL的目录名上
 	$URI_dir = (isset($URI_part['path']) && $URI_part['path']) ? '/' . ltrim(dirname($URI_part['path']), '/') : '';
 	if(strpos($url, './') === FALSE){
 		if($URI_dir != ''){
 			return $URI_root . $URI_dir . '/' . $url;
 		} else {
 			return $URI_root . '/' . $url;
 		}
 	}

 	//STEP5: 如果相对路径中包含'../'或'./'表示的目录，需要对路径进行解析并递归
 		//STEP5.1: 把路径中所有的'./'改为'/'，'//'改为'/'
 	$url = preg_replace('/[^\.]\.\/|\/\//', '/', $url);
 	if(strpos($url, './') === 0)
 		$url = substr($url, 2);

 		//STEP5.2: 使用'/'分割URL字符串以获取目录的每一部分进行判断
 	$URI_full_dir = ltrim($URI_dir . '/' . $url, '/');
 	$URL_arr = explode('/', $URI_full_dir);

 	if($URL_arr[0] == '..')
 		return FALSE;

 	//因为数组的第一个元素不可能为'..'，所以这里从第二个元素可以循环
 	$dst_arr = $URL_arr;  //拷贝一个副本，用于最后组合URL
 	for($i = 1; $i < count($URL_arr); $i ++){
 		if($URL_arr[$i] == '..'){
 			$j = 1;
 			while(TRUE){
 				if(isset($dst_arr[$i - $j]) && $dst_arr[$i - $j] != FALSE){
 					$dst_arr[$i - $j] = FALSE;
 					$dst_arr[$i] = FALSE;
 					break;
 				} else {
 					$j ++;
 				}
 			}
 		}
 	}

 	// 组合最后的URL并返回
 	$dst_str = $URI_root;
 	foreach($dst_arr as $val){
 		if($val != FALSE)
 			$dst_str .= '/' . $val;
 	}
 	return $dst_str;
}

$content = '<link type="text/css" rel="stylesheet" href="css/index.css?time=1463711598743" /><script>';
$host = "http://www.139site.com";
$ret = filter_relative_url($content, $host);
echo $ret;
exit;

// 创建一个新cURL资源
$ch = curl_init();

// 设置URL和相应的选项
curl_setopt($ch, CURLOPT_URL, "http://www.139site.com/");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 抓取URL并把它传递给浏览器
$result = curl_exec($ch);
// 处理浏览器输入内容
$ret = str_replace("<input type=\"text\">", "<input type=\"text\" value='美国队长'>", $result);
echo $ret;
// 关闭cURL资源，并且释放系统资源
curl_close($ch);
?>