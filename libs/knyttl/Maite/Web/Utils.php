<?php
/**
 * Web utils library.
 *
 * This source file is subject to the "New BSD License".
 *
 * @author     Vojtěch Knyttl
 * @copyright  Copyright (c) 2010 Vojtěch Knyttl
 * @license    New BSD License
 * @link       http://knyt.tl/
 */

namespace Maite\Web;

use Nette;

class Utils {

    const PROXY_URL = 'http://skproxy.com/domains/skproxy.com/index.php?hl=1c2&q=';



    /**
     * Curl wrapper.
     * @param  string
     * @param  array
     * @return string
     */
    const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1)';

    const CACHE_DIR = '/tmp/maite';


    public static function get($url, $params = null, &$info = array()) {
        if (is_array($url)) {
            $params = $url;
            $url = $params['url'];
            $info = &$params;
        }

        if (@$params['proxy']) {
            $url = self::PROXY_URL.urlencode($url);
        }

        $cached = !empty($params['cached']) && $params['cached'];
        $file = '';
        if ($cached) {
            if (!is_string($params['cached'])) {
                throw new \Exception('Parameter cached must be cache path.');
            }
            if (!isset($params['expiry'])) {
                throw new \Exception('Parameter expiry must be set.');
            }
            @mkdir($params['cached']);
            $file = $params['cached'].'/'.md5($url);
            $info['cached_file'] = $file;
            if (file_exists($file) && ($params['expiry'] === false || filemtime($file) > time() - $params['expiry'])) {
                $info['from_cache'] = true;
                return file_get_contents($file);
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, @$params['user_agent'] ?: self::USER_AGENT);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        $params['header'] = !isset($params['header']) ? true : $params['header'];
        curl_setopt($ch, CURLOPT_HEADER, $params['header']);

        if (!empty($params['cookie_jar'])) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $params['cookie_jar']);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $params['cookie_jar']);
        }

        if (!empty($params['http_header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $params['http_header']);
        }

        if (!empty($params['timeout_connect'])) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params['timeout_connect']);
        }

        if (!empty($params['timeout_read'])) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout_read']);
        }

        if (!empty($params['gzip'])) {
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        }

        if (!empty($params['head'])) {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        if (!empty($params['put'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['put']);
        }

        if (!empty($params['delete'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        if (!empty($params['post'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($params['post'])) {
                $params['post'] = http_build_query($params['post']);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post']);
        }

        if (!empty($params['auth'])) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, $params['auth']);
        }

        if (!empty($params['verbose'])) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        $response = curl_exec($ch);

        if (!is_array($info)) {
            $info = array();
        }

        $info = curl_getinfo($ch) + $info;
        if ($params['header']) {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            if ($headerSize !== false) {
                $headers = explode("\n", substr($response, 0, $headerSize));
                $response = trim(substr($response, $headerSize));
            }
        }

        curl_close($ch);

        $info['headers'] = array();
        if (!empty($headers)) {
            foreach ($headers as $value) {
                if (strpos($value, ':') === false) {
                    continue;
                }
                $value = explode(':', str_replace("\r", '', $value), 2);
                $info['headers'][strtolower($value[0])] = \Maite\Utils\Strings::normalize($value[1]);
            }
        }

        if ($info['http_code'] > 400) {
            return $response;
            /*
            $params['retries'] = (empty($params['retries']) ? 3 : $params['retries']) - 1;
            if ($params['retries'] <= 0) {
                return $response;
            }
            sleep(1);
            return self::get($url, $params, $info);
            */
        }

        if ($info['http_code'] > 300 && $info['http_code'] < 304) {
            unset($params['post']);
            if ($urln = @$info['headers']['location']) {
                if (!preg_match('#^[fthps]{3,5}://#', $urln)) {
                    preg_match('#^.*?//[^/]*#', $url, $regs);
                    $urln = $regs[0].$urln;
                }
                return self::get($urln, $params, $info);
            }
        }

        if (!empty($params['enc'])) {
            $response = @iconv($params['enc'], 'utf-8//TRANSLIT//IGNORE', $response);
        }

        if (@$params['proxy']) {
            $response = preg_replace_callback('#href="http://skproxy.*?[?&]q=(.*?)"#', function($url) {
                return 'href="'.urldecode(str_replace(Utils::PROXY_URL, '', $url[1])).'"';
            }, $response);
        }

        if ($cached && !empty($response)) {
            file_put_contents($file, $response);
        }

        return $response;
    }



    /**
     * Google search helper
     *
     * @param   string   needle
     * @param   string   LocalSearch|WebSearch|VideoSearch|BlogSearch|NewsSearch|ImageSearch|PatentSearch|BookSearch
     * @param   string   results limit
     * @return  string
     */
    public static function search($string, $type, $limit) {
        return json_decode(self::get(
            'http://www.google.com/uds/G' . $type .
            'Search?hl=cs&key=ABQIAAAAuzUN-vePi9PKecJElPqD6BRJSvctF1oSnOeykeMje__m2k47nBQtfuYFXNSzN15TMuSrFaxTN0faxQ&v=1.0&rsz=' .
            $limit . '&userip=' . $_SERVER['REMOTE_ADDR'] . '&q=' . urlencode($string)
        ))->responseData->results;
    }



    public static function pingSearchEngines($name, $url) {
        self::internalPing('http://rpc.weblogs.com/pingSiteForm', $name, $url);
        return 'Thanks for the ping.' ==
            self::internalPing('http://blogsearch.google.com/ping?', $name, $url);
    }



    private static function internalPing($target, $name, $url) {
        return self::get(
            $target.
            '?name='.
            \Maite\Utils\Strings::escapeUrl($name).
            '&url='.
            \Maite\Utils\Strings::escapeUrl($url));
    }



    /** Položení XML-RPC požadavku
     * @param string URL webové služby
     * @param string funkce, která se má zavolat
     * @param array parametry, které se mají funkci předat
     * @param array zvláštní typy "base64" nebo "datetime"
     * @param string kódování odpovědi, výchozí je utf-8
     * @return mixed data vrácená funkcí
     * @copyright Jakub Vrána, http://php.vrana.cz/
     */
    public static function xmlrpc($url, $method, $params, $types = array(), $encoding = 'utf-8') {
        foreach ($types as $key => $val) {
            xmlrpc_set_type($params[$key], $val);
        }
        $encoded = xmlrpc_encode_request($method, $params, array('escaping' => 'markup', 'encoding' => $encoding));
        $context = stream_context_create(array('http' => array(
            'method' => "POST",
            'header' => "Content-Type: text/xml; charset=utf-8",
            'content' => $encoded
        )));
        return xmlrpc_decode(file_get_contents($url, false, $context), $encoding);
    }
}
