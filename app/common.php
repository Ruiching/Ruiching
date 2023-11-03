<?php
// 应用公共文件

/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function pt($data, $replace = false, $saveDir = null)
{
    $saveDir = empty($saveDir) ? 'log' : $saveDir;
    $rootPath = runtime_path('pt');
    if (!file_exists($rootPath . $saveDir)) {
        mkdir($rootPath . $saveDir, 0777, true);
    }
    $pathname = $rootPath . $saveDir . '/' . date('Ymd') . '.txt';
    $str = "********************************************************" . "\n";
    if (is_string($data)) {
        $str .= $data . "\n";
    } else {
        if (is_array($data) || is_object($data)) {
            $str .= print_r($data, true) . "\n";
        } else {
            $str .= var_export($data, true) . "\n";
        }
    }
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 返回成功
 * @param array $data
 * @param string $msg
 * @return array
 */
function success($msg, $data = [])
{
    $response = [
        'code' => 0,
        'message' => $msg,
        'data' => $data
    ];
    $jsonResponse = json($response, 200, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'x-requested-with,content-type,Authorization',
        'Access-Control-Allow-Methods' => 'POST,GET,OPTIONS,DELETE'
    ]);
    throw new \think\exception\HttpResponseException($jsonResponse);
}

/**
 * 错误返回
 * @param string $msg
 * @param int $code
 * @return array
 */
function error($msg = '', $code = 110, $data = [])
{
    $response = [
        'code' => $code,
        'message' => $msg,
        'data' => $data
    ];
    $jsonResponse = json($response, 200, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'x-requested-with,content-type,Authorization',
        'Access-Control-Allow-Methods' => 'POST,GET,OPTIONS,DELETE'
    ]);
    throw new \think\exception\HttpResponseException($jsonResponse);
}

/**
 * 图片加域名
 *
 * @param $image
 * @return string
 */
function format_image($image)
{
    if (strpos($image, 'http') !== 0 && strpos($image, '//') !== 0) {
        $domain = Env('app.thumb_domain');
        if (!$domain) {
            $domain = \think\facade\Request::domain();
        }
        return $domain . $image;
    }
    return $image;
}

/**
 * 链接加域名
 *
 * @param $url
 * @return string
 */
function format_url($url)
{
    if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
        $domain = env('app.frontend_domain',  \think\facade\Request::domain());
        if (!$domain) {
            $domain = request()->domain();
        }
        return $domain . $url;
    }
    return $url;
}

/**
 * 检测文件类型
 * @param $fileUrl
 * @return string|bool
 */
function check_media_type($fileUrl)
{
    $imageType = explode(",", "png,jpg,jpeg,gif,bmp");
    $videoType = explode(",", "mp4,mkv");
    //判断文件最后一个参数
    $fileData = explode(".", $fileUrl);
    $fileType = array_pop($fileData);
    if(in_array($fileType, $imageType)) {
        return \app\model\UserMedia::FILE_TYPE_IMAGE;
    }
    if(in_array($fileType, $videoType)) {
        return  \app\model\UserMedia::FILE_TYPE_VIDEO;
    }
    return false;
}

/**
 * 根据键值获取配置
 *
 * @param $key
 * @param $type
 * @return string
 */
function get_config_by_key($key, $type = "")
{
    $config = new \app\model\Configs();
    $condition['key'] = $key;
    if(!empty($type)) {
        $condition['type'] = $type;
    }
    $item = $config->where($condition)->find();
    if ($item) {
        return $item->value;
    }
    return '';
}


/**
 * 简单加密
 *
 * @param $str
 * @param $key
 * @return string
 */
function simple_encrypt($str, $key)
{
    $data = openssl_encrypt($str, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    $data = base64_encode($data);
    return $data;
}

/**
 * 简单解密
 *
 * @param $str
 * @param $key
 * @return string
 */
function simple_decrypt($str, $key)
{
    $decrypted = openssl_decrypt(base64_decode($str), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return $decrypted;
}

/**
 * 生成UUID
 *
 * @return string
 */
function generate_uuid()
{
    $appKey = env('app.token_key',  'token');
    return md5(uniqid(microtime()) . $appKey);
}

/**
 * 随机字符串
 *
 * @param int $length
 * @return string
 */
function str_random($length = 12)
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

/**
 * 订单号生成
 * @return string
 */
function get_order_no()
{
    $date = date('Ymd', time());
    $prefix = "SUO";
    return $prefix . $date . rand(100000, 999999);
}

/**
 * 字符串替换
 *
 * @param $string
 * @param $array
 * @return mixed
 */
function get_str_replace($string, $array)
{
    if (!empty($array)) {
        foreach ($array as $key => $value) {
            $string = str_replace("{" . $key . "}", "$value", $string);
        }
    }
    return $string;
}

/**
 * 去除表情编号
 *
 * @param $str
 * @return mixed
 */
function removeEmoji($str)
{
    $str = preg_replace_callback('/./u', function (array $match) {
        return strlen($match[0]) >= 4 ? '' : $match[0];
    }, $str);
    return $str;
}

/**
 * 在原有链接后面加参数
 *
 * @param $url
 * @param $queryAry
 * @return bool|string
 */
function mergeUrlQuery($url, $queryAry)
{
    if (empty($url) && empty($queryAry)) {
        return false;
    }
    $appendStr = '';
    if (is_array($queryAry)) {
        foreach ($queryAry as $k => $v) {
            $appendStr .= $k . '=' . $v . '&';
        }
    }
    $appendStr = substr($appendStr, 0, -1);
    $check = strpos($url, '?');
    if($check !== false)
    {
        $queryStr = '&' . $appendStr;
    } else {
        $queryStr = '?' . $appendStr;
    }
    return $url . $queryStr;
}


/**
 * 对用户的数据进行安全过滤
 *
 * @param $str
 * @return string
 */
function escapeData($str)
{
    $str = strip_tags(trim($str));
    $str = htmlspecialchars($str); // html标记转换
    //$str = str_replace("_", "\_", $str); // 把 '_'过滤掉
    $str = str_replace("%", "\%", $str); // 把' % '过滤掉
    $str = nl2br($str); // 回车转换
    $str = addslashes($str); // 进行过滤
    $str = preg_replace("/\s(?=\s)/", "\\1", $str);
    return $str;
}

/**
 * redis服务器
 * @return Redis
 */
function redis_server()
{
    try {
        $redisHost = Env('redis.host', '127.0.0.1');
        $redisPort = Env('redis.port', '6379');
        $redisPwd = Env('redis.password', '');

        //redis连接
        $redisServer = new \Redis();
        $redisServer->connect($redisHost, intval($redisPort));
        if(!empty($redisPwd)) {
            $redisServer->auth($redisPwd);
        }
        return $redisServer;

    }catch (Exception $exception) {
        pt($exception->getTraceAsString(), false, "redis_error");
        return false;
    }
}