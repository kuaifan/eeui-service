<?php

/**
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function input($key, $default = null)
{
    if (isset($_GET[$key])) {
        return $_GET[$key];
    }
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    return $default;
}

/**
 * @param $array
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function value($array, $key, $default = null)
{
    if (!is_array($array)) {
        return $default;
    }
    if (isset($array[$key])) {
        return $array[$key];
    }
    return $default;
}

/**
 * @param $string
 * @param $find
 * @return bool
 */
function strExists($string, $find)
{
    if (!is_string($string) || !is_string($find)) {
        return false;
    }
    return !(strpos($string, $find) === FALSE);
}

/**
 * @param null $path
 * @return string
 */
function url($path = null)
{
    $protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    if (substr($path, 0, 1) === '/') {
        return $protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $path;
    }
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $req_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    $curUrl = $protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $req_url;
    if ($path === null) {
        return $curUrl;
    }
    return dirname($curUrl) . '/' . $path;
}

/**
 * @param $srcurl
 * @param string $baseurl
 * @return mixed|string
 */
function formatUrl($srcurl, $baseurl = '')
{
    if (empty($srcurl)) {
        return "";
    }
    if (empty($baseurl)) {
        $baseurl = url('');
    }
    $srcinfo = parse_url($srcurl);
    if (isset($srcinfo['scheme'])) {
        return $srcurl;
    }
    $baseinfo = parse_url($baseurl);
    $url = $baseinfo['scheme'] . '://' . $baseinfo['host'];
    if (substr($srcinfo['path'], 0, 1) == '/') {
        $path = $srcinfo['path'];
    } else {
        $path = dirname($baseinfo['path']) . '/' . $srcinfo['path'];
    }
    $rst = [];
    $path_array = explode('/', $path);
    if (!$path_array[0]) {
        $rst[] = '';
    }
    foreach ($path_array AS $key => $dir) {
        if ($dir == '..') {
            if (end($rst) == '..') {
                $rst[] = '..';
            } elseif (!array_pop($rst)) {
                $rst[] = '..';
            }
        } elseif ($dir && $dir != '.') {
            $rst[] = $dir;
        }
    }
    if (!end($path_array)) {
        $rst[] = '';
    }
    $url .= implode('/', $rst);
    return str_replace('\\', '/', $url);
}

/**
 * @return array|mixed
 */
function getConfig()
{
    $array = [];
    $file = __DIR__ . '/config.json';
    if (is_file($file)) {
        $array = json_decode(file_get_contents($file), true);
    }
    $array['appkey'] = value($array, 'appkey', 'test');
    $array['welcome_image'] = formatUrl(value($array, 'welcome_image', ''));
    $array['welcome_wait'] = intval(value($array, 'welcome_wait', 2000));
    $array['welcome_skip'] = intval(value($array, 'welcome_skip', 1));
    $array['welcome_jump'] = formatUrl(value($array, 'welcome_jump', ''));
    $array['welcome_limit_s'] = intval(value($array, 'welcome_limit_s', 0));
    $array['welcome_limit_e'] = intval(value($array, 'welcome_limit_e', 0));
    return $array;
}

/**
 * @param $file
 * @return array
 */
function getJson($file)
{
    $array = [
        'platform' => '',
        'debug' => 0,
        'valid' => 1,
        'reboot' => 2,
        'reboot_info' => [],
        'clear_cache' => 0,
    ];
    $jsonFile = substr($file, 0, strlen($file) - 4) . '.json';
    if (is_file($jsonFile)) {
        $jsonArray = json_decode(file_get_contents($jsonFile), true);
        $array = array_merge($array, $jsonArray);
    }
    if (empty($array['platform'])) {
        $array['platform'] = 'android,ios';
    }
    $array['platform'] = ',' . strtolower($array['platform']) . ',';
    $array['debug'] = intval($array['debug']);
    $array['valid'] = intval($array['valid']);
    $array['reboot'] = intval($array['reboot']);
    $array['clear_cache'] = intval($array['clear_cache']);
    if (!is_array($array['reboot_info'])) {
        $array['reboot_info'] = [];
    }
    $array['reboot_info']['title'] = value($array['reboot_info'], 'title', '温馨提示');
    $array['reboot_info']['message'] = value($array['reboot_info'], 'message', '已为您更新至最新版本');
    $array['reboot_info']['confirm_reboot'] = 1;
    return $array;
}

/**
 * @param $str
 * @return mixed
 */
function idEncode($str) {
    $str = str_replace('/', '--2-2f-f--', $str);
    return $str;
}

/**
 * @param $str
 * @return mixed
 */
function idDecode($str) {
    $str = str_replace('--2-2f-f--', '/', $str);
    return $str;
}

/**
 * @param $version
 * @param $platform
 * @param $debug
 * @return array
 */
function getUplists($version, $platform, $debug)
{
    $dir = 'zip/' . $version . '/';
    $lists = glob(__DIR__ . '/' . $dir . '*.zip', GLOB_BRACE);
    sort($lists);

    $uplists = [];
    foreach ($lists AS $key => $file) {
        if (is_dir($file)) {
            continue;
        }
        $jsonArray = getJson($file);
        if (!strExists($jsonArray['platform'], ',' . $platform . ',')) {
            continue;
        }
        if ($debug && $jsonArray['debug'] !== 1) {
            continue;
        }
        if ($jsonArray['valid'] === 0) {
            continue;
        }
        $fileName = basename($file);
        $filePath = $dir . $fileName;
        $uplists[] = [
            'id' => idEncode(substr($filePath, 0, strlen($filePath) - 4)),
            'size' => sprintf("%.2f", filesize($file) / 1024),
            'path' => url($filePath),
            'valid' => $jsonArray['valid'],
            'clear_cache' => $jsonArray['clear_cache'],
            'reboot' => $jsonArray['reboot'],
            'reboot_info' => $jsonArray['reboot_info'],
        ];
    }

    return $uplists;
}

/**
 * @param $msg
 * @param array $data
 * @param int $ret
 */
function error($msg, $data = [], $ret = 0)
{
    $param = [
        'ret' => $ret,
        'msg' => $msg,
        'data' => $data
    ];
    header('Content-Type: application/json');
    echo json_encode($param);
    die();
}

/**
 * @param $msg
 * @param array $data
 * @param int $ret
 */
function success($msg, $data = [], $ret = 1)
{
    $param = [
        'ret' => $ret,
        'msg' => $msg,
        'data' => $data
    ];
    header('Content-Type: application/json');
    echo json_encode($param);
    die();
}

/** ************************************************************************************************/
/** ************************************************************************************************/
/** ************************************************************************************************/

$act = input('act', 'app');

switch ($act) {
    case "update-success":
        {
            $id = idDecode(input('id'));
            $path = $file = __DIR__ . '/' . $id . '.success.log';
            file_put_contents($path, date("Y-m-d H:i:s") . "\n", FILE_APPEND);
            success('success');
            break;
        }

    case "update-delete":
        {
            $id = idDecode(input('id'));
            $path = $file = __DIR__ . '/' . $id . '.delete.log';
            file_put_contents($path, date("Y-m-d H:i:s") . "\n", FILE_APPEND);
            success('success');
            break;
        }

    case "app":
        {
            $appkey = input('appkey');
            $package = input('package');
            $version = input('version');
            $platform = strtolower(input('platform'));
            $debug = intval(input('debug'));
            if (empty($appkey)) {
                error("appkey is empty!");
            }
            if (empty($version)) {
                error("version is empty!");
            }
            if (empty($platform)) {
                error("version is platform!");
            }
            $config = getConfig();
            if ($config['appkey'] !== $appkey) {
                error("appkey is error!");
            }
            $config['uplists'] = getUplists($version, $platform, $debug);
            success('success', $config);
            break;
        }
}











