<?php

namespace Wheregroup\MapExport\CoreBundle\Component;

class HTTPClient
{
    protected $container = "";
    protected $ch = null;
    protected $method = "GET";
    public $headers = array();
    protected $host = "";
    protected $port = "";
    protected $path = "";
    protected $username = null;
    protected $password = null;
    protected $proxyHost = null;
    protected $proxyPort = null;
    protected $noProxyHosts = array();
    protected $usrpwd = null;
    protected $timeout;
    protected $connecttimeout;

    public function __construct($proxyConf = null)
    {
        $this->ch = curl_init();
        if ($proxyConf && isset($proxyConf['host']) && $proxyConf['host'] != "") {
            $this->setProxyHost($proxyConf['host']);
            if (isset($proxyConf['port']) && $proxyConf['port'] != "") {
                $this->setProxyPort($proxyConf['port']);
            }
            if (isset($proxyConf['user']) && $proxyConf['user'] != "") {
                $this->setUsrPwd($proxyConf['user'] . ":" . (isset($proxyConf['password'])
                        ? $proxyConf['password'] : null));
            }
            if (isset($proxyConf['noproxy']) && is_array($proxyConf['noproxy']) && count($proxyConf['noproxy']) > 0) {
                $this->setNoProxyHosts($proxyConf['noproxy']);
            } else {
                $this->setNoProxyHosts(array());
            }
        }

        if ($proxyConf !== null && isset($proxyConf['timeout']) && $proxyConf['timeout'] !== null) {
            $this->timeout = $proxyConf['timeout'];
        }

        if ($proxyConf !== null && isset($proxyConf['connecttimeout']) && $proxyConf['connecttimeout'] !== null) {
            $this->connecttimeout = $proxyConf['connecttimeout'];
        }
    }

    public function __destruct()
    {
        $this->ch = curl_close($this->ch);
    }

    public function setProxyHost($host)
    {
        $this->proxyHost = $host;
    }

    public function getProxyHost()
    {
        return $this->proxyHost;
    }

    public function setProxyPort($port)
    {
        $this->proxyPort = $port;
    }

    public function getProxyPort()
    {
        return $this->proxyPort;
    }

    public function setNoProxyHosts($noProxyHosts)
    {
        $this->noProxyHosts = $noProxyHosts;
    }

    public function getNoProxyHosts()
    {
        return $this->noProxyHosts;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setUsrPwd($usrpwd)
    {
        $this->usrpwd = $usrpwd;
    }

    public function getUsrPwd()
    {
        return $this->usrpwd;
    }

    /**
     * Shortcut Method
     */
    public function open($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($this->timeout) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        }

        if ($this->connecttimeout) {
            curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        }

        $purl = parse_url($url);
        if ($this->getUsrPwd() !== null && !in_array($purl['host'], $this->getNoProxyHosts())) {
            curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($this->ch, CURLOPT_USERPWD, $this->getUsrPwd());
        }
        if ($this->getProxyHost() !== null && !in_array($purl['host'], $this->getNoProxyHosts())) {
            curl_setopt($this->ch, CURLOPT_PROXY, $this->getProxyHost());
        }
        if ($this->getProxyPort() !== null && !in_array($purl['host'], $this->getNoProxyHosts())) {
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $this->getProxyport());
        }
        $data = curl_exec($this->ch);
        $info = curl_getinfo($this->ch);
        $headers = $info;

        if (($error = curl_error($this->ch)) != "") {
            throw new \Exception("Curl says: '$error'");
        }
        $statusCode = curl_getInfo($this->ch, CURLINFO_HTTP_CODE);

        $result = new HTTPResult();
        $result->setData($data);
        $result->setStatusCode($statusCode);

        $result->setHeaders($headers);
        $this->headers = $headers;
        return $result;
    }

    public static function parseQueryString($str)
    {
        $op = array();
        $pairs = explode("&", $str);
        foreach ($pairs as $pair) {
            $arr = explode("=", $pair);
            $k = isset($arr[0]) ? $arr[0] : null;
            $v = isset($arr[1]) ? $arr[1] : null;
            if ($k !== null) {
                $op[$k] = $v;
            }
        }
        return $op;
    }

    public static function buildQueryString($parsedQuery)
    {
        $result = array();
        foreach ($parsedQuery as $key => $value) {
            if ($key || $value) {
                $result[] = "$key=$value";
            }
        }
        return implode("&", $result);
    }

    public static function parseUrl($url)
    {
        $defaults = array(
            "scheme" => "http",
            "host" => null,
            "port" => null,
            "user" => null,
            "pass" => null,
            "path" => "/",
            "query" => null,
            "fragment" => null
        );

        $parsedUrl = parse_url($url);

        return array_merge($defaults, $parsedUrl);
    }

    public static function buildUrl(array $parsedUrl)
    {
        $defaults = array(
            "scheme" => "http",
            "host" => null,
            "port" => null,
            "user" => null,
            "pass" => null,
            "path" => "/",
            "query" => null,
            "fragment" => null
        );

        $mergedUrl = array_merge($defaults, $parsedUrl);

        $result = $mergedUrl['scheme'] . "://";

        $authString = $mergedUrl['user'];
        $authString .= $mergedUrl['pass'] ? ":" . $mergedUrl['pass'] : "";
        $authString .= $authString ? "@" : "";
        $result .= $authString;

        $result .= $mergedUrl['host'];
        $result .= $mergedUrl['port'] ? ':' . $mergedUrl['port'] : "";
        $result .= $mergedUrl['path'];
        return $result . $mergedUrl['query'] ? '?' . $mergedUrl['query'] : "";
    }
}