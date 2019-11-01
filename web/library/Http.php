<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 16/10/8
 * Time: 11:09
 */

namespace app\library;


class Http
{
    private static $instance = null;
    private function __construct(){}
    public static function ins()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * get请求
     * @param $url
     * @param array $options
     * @param int $force_curl
     * @return mixed
     */
    public function get($url, $options=array(), $force_curl=0)
    {
        if (!is_array($url)) {
            if (!$options && !$force_curl) {
                return file_get_contents($url);
            } else {
                $ch = curl_init();
                $this->initCurl($ch);
                $options = $this->optionsHandle(
                    $options,
                    array(
                        CURLOPT_POST,
                        CURLOPT_POSTFIELDS,
                        CURLOPT_NOBODY,
                        CURLOPT_CUSTOMREQUEST
                    ),
                    true
                );
                $options[CURLOPT_URL] = $url;
                # Force to HTTP GET 默认是true
                # $options[CURLOPT_HTTPGET] = true;

                curl_setopt_array($ch, $options);

                $ret = curl_exec($ch);
                curl_close($ch);
                return $ret;
            }
        } else {
            $mh = curl_multi_init();
            $chs = array();
            $options = $this->optionsHandle(
                $options,
                array(
                    CURLOPT_POST,
                    CURLOPT_POSTFIELDS,
                    CURLOPT_NOBODY,
                    CURLOPT_CUSTOMREQUEST
                ),
                true
            );

            foreach ($url as $k => $v) {
                $chs[$k] = curl_init();
                $this->initCurl($chs[$k]);
                $options[CURLOPT_URL] = $v;
                curl_setopt_array($chs[$k], $options);
                curl_multi_add_handle($mh, $chs[$k]);
            }

            $active = null;
            # 执行批处理句柄
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            /**
             * doc: http://php.net/manual/zh/function.curl-multi-select.php
             */
            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) == -1) {
                    usleep(10);
                }
                if (curl_multi_select($mh) != -1) {
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
            }

            $ret = array();
            foreach ($chs as $k => $ch) {
                $ret[$k] = curl_multi_getcontent($chs[$k]);
                curl_multi_remove_handle($mh, $chs[$k]);
            }
            curl_multi_close($mh);
            unset($chs);
            return $ret;
        }
    }

    /**
     * post请求
     * @param $url
     * @param $data
     * @param array $options
     * @param bool $emulate_browser 是否模拟浏览器请求
     * @param null $info 记录信息
     * @return mixed
     */
    public function post($url, $data, $options=array(), $emulate_browser=false, &$info=null)
    {
        $ch = curl_init();
        $this->initCurl($ch);
        $options = $this->optionsHandle(
            $options,
            array(
                CURLOPT_NOBODY,
                CURLOPT_HTTPGET,
                CURLOPT_CUSTOMREQUEST
            ),
            true
        );

        if ($emulate_browser) {
            $this->setBrowserOption($options);
        }

        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $data;
        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $options);

        $ret = curl_exec($ch);
        if (isset($info) && $info !== null) {
            $info = curl_getinfo($ch);
        }

        curl_close($ch);
        return $ret;
    }

    /**
     * 初始化，处理共用options
     * @param $ch
     */
    protected function initCurl(&$ch)
    {
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        # 开启递归的header
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        // http://www.laruence.com/2014/01/21/2939.html
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        # 在发起连接前等待的时间
        # curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    }

    /**
     * 处理options
     * @param array $options 选项，可以使用 timeout, referer, cookie 等，也可以使用 CURLOPT_*
     * @param array $optionlist
     * @param bool $blacklist 选项类型，$optionlist 为黑名单或白名单
     * @return mixed
     */
    protected function optionsHandle($options, $optionlist = array(), $blacklist = true)
    {
        $option_map = array(
            'timeout'        => CURLOPT_TIMEOUT,
            'connecttimeout' => CURLOPT_CONNECTTIMEOUT,
            'referer'        => CURLOPT_REFERER,
            'cookie'         => CURLOPT_COOKIE,
            'cookiejar'      => CURLOPT_COOKIEJAR,
            'cookiefile'     => CURLOPT_COOKIEFILE,
            'version'        => CURLOPT_HTTP_VERSION,
            'data'           => CURLOPT_POSTFIELDS,
            'post'           => CURLOPT_POST,
            'get'            => CURLOPT_HTTPGET,
            'authtype'       => CURLOPT_HTTPAUTH,
            'userpwd'        => CURLOPT_USERPWD,
            'useragent'      => CURLOPT_USERAGENT
        );

        if (defined('CURLOPT_TIMEOUT_MS')) {
            // curl >= 7.16.2
            // 某些版本中未定义
            if (!defined('CURLOPT_CONNECTTIMEOUT_MS')) {
                //see http://stackoverflow.com/questions/9062798/php-curl-timeout-is-not-working/9063006#9063006
                define('CURLOPT_CONNECTTIMEOUT_MS', 156);
            }
            $option_map['timeout_ms'] = CURLOPT_TIMEOUT_MS;
            $option_map['connecttimeout_ms'] = CURLOPT_CONNECTTIMEOUT_MS;
        }

        foreach ($option_map as $k => $v) {
            if (isset($options[$k])) {
                $options[$v] = $options[$k];
                unset($options[$k]);
            }
        }
        if (!empty($optionlist)) {
            if ($blacklist) {
                foreach ($optionlist as $v) {
                    if (isset($options[$v])) {
                        unset($options[$v]);
                    }
                }
            } else {
                foreach ($options as $k => $v) {
                    if (! in_array($k, $optionlist)) {
                        unset($options[$k]);
                    }
                }
            }
        }
        if (isset($options[CURLOPT_HTTP_VERSION])) {
            if (!in_array($options[CURLOPT_HTTP_VERSION], array(
                CURL_HTTP_VERSION_1_0,
                CURL_HTTP_VERSION_1_1,
                CURL_HTTP_VERSION_NONE
            ))) {
                if ($options[CURLOPT_HTTP_VERSION] == '1.0') {
                    $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
                } else if ($options[CURLOPT_HTTP_VERSION] == '1.1') {
                    $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
                } else {
                    $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_NONE;
                }
            }
        }
        return $options;
    }

    /**
     * 模拟浏览器option
     * @param $options
     */
    private function setBrowserOption(&$options)
    {
        $options[CURLOPT_HTTPHEADER] = array(
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'CLIENT-IP: '.$this->_randIp(),
            'X-FORWARDED-FOR: '.$this->_randIp()
        );
        $options[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
        $options[CURLOPT_COOKIEFILE] = $this->_getCookieFile();
        $options[CURLOPT_COOKIEJAR] = $this->_getCookieFile();
    }

    /**
     * 随机ip
     * @return string
     */
    private function _randIp()
    {
        $ip_long = array(
            array('607649792', '608174079'), //36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
            array('-569376768', '-564133889') //222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
        $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
        return $ip;
    }

    private function _getCookieFile()
    {
        return '/tmp/curl.cookie';
    }
}