<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/25
 * Time: 10:57
 */

namespace app\config;

/**
 *
 * http请求帮助类
 *
 * 调用示例：
 *          1.post请求：
 *                  $responseData = HttpHelper::sendRequest($requestUrl, $paramsData, 'POST', $headerArr);
 *          2.get请求：
 *                  $responseData = HttpHelper::sendRequest($requestUrl, $paramsStr, 'GET', $headerArr);
 *          3.网络原因或远程服务器响应异常时获取失败详情：
                    if($responseData === false){
                        $errorMsg = HttpHelper::getErrorMsg();
                    }
 *
 * Class HttpHelper
 * @package common\component
 */
class HttpHelper
{
    private static $requestUrl;
    /**
     * send_request方法请求链接失败返回的失败提示
     * @var string
     */
    private static $errorMsg;

    /**
     * 请求数据
     * @param string $url
     * @param string|array $params 请求参数
     * @param string $httpMethod 请求方式
     * @param array $headerArr header头信息
     * @return mixed
     */
    public static function sendRequest($url, $params = '', $httpMethod = 'GET', $headerArr = [])
    {
		
        self::$requestUrl = $url;
        if (strtoupper($httpMethod) === 'GET'){
            $response = self::handleGetCurl(self::$requestUrl.$params, $headerArr);
        }else{
            $response = self::handlePostCurl(self::$requestUrl, $params, $headerArr);
        }

        return $response;
    }

    /**
     * 返回send_request方法http请求失败的详情
     * @return string
     */
    public static function getErrorMsg()
    {
        return self::$errorMsg;
    }

    /**
     * 发送 get curl 请求
     * @param $url
     * @param array $headerArr
     * @return mixed
     */
    private static function handleGetCurl($url, $headerArr = [])
    {
        $ch = curl_init();
        //需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
        curl_setopt($ch, CURLOPT_URL, $url);
        //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(preg_match('/https:\/\//', $url)) {
            //FALSE 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录。
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //这个是重点。
        }

        //header头信息
        if (!empty($headerArr)){
            //设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        }
        //启用时会将头文件的信息作为数据流输出。
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 这样能够让cURL支持页面链接跳转

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $output = curl_exec($ch);
        if($output === false){
            //记录curl请求失败的详情
            self::$errorMsg = "cURL Error: ".curl_error($ch);
        }

        curl_close($ch);
        return $output;
    }

    /**
     * 发送 post curl 请求
     * @param $url
     * @param string $postData
     * @param array $headerArr
     * @return mixed
     */
    private static function handlePostCurl($url, $postData = '', $headerArr = [])
    {
        $ch = curl_init();
        //需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
        curl_setopt($ch, CURLOPT_URL, $url);
        //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(preg_match('/https:\/\//', $url)) {
            //FALSE 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录。
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //这个是重点。
        }

        //启用时会将头文件的信息作为数据流输出。
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 这样能够让cURL支持页面链接跳转

        //header头信息
        if (!empty($headerArr)){
            //设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        }
        //设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output = curl_exec($ch);
        if($output === false){
            //记录curl请求失败的详情
            self::$errorMsg = "cURL Error: ".curl_error($ch);
        }

        curl_close($ch);
        return $output;
    }
}