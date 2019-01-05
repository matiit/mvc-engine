<?php

namespace Core\Router;

/**
 * That's main class of routing system, taking part in
 * true URL reading.
 *
 * Class Router
 *
 * @category Routing
 *
 * @package Core\Router
 *
 * @author Original Author <kamil.ubermade@gmail.com>
 *
 * @license The MIT License (MIT)
 *
 * @link https://github.com/Ubermade/mvc-engine
 */
class Router
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var mixed
     */
    protected static $collection;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var \stdClass
     */
    protected $class;

    /**
     * @var string
     */
    protected $method;

    /**
     * Router constructor.
     *
     * @param string $url
     * @param object $collection
     */
    public function __construct(string $url, ?object $collection = null)
    {
        if ($collection != null) {
            self::$collection = $collection;
        }

        $url = explode('?', $url);
        $this->url = $url[0];
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param object $collection
     */
    public function setCollection(object $collection)
    {
        self::$collection = $collection;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return self::$collection;
    }

    /**
     * @param string $controller
     */
    public function setFile(string $controller)
    {
        $this->file = $controller;
    }

    /**
     * @return string|null
     */
    public function getFile():?string
    {
        return $this->file;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class)
    {
        $this->class = $class;
    }

    /**
     * @return string|null
     */
    public function getClass():?string
    {
        return $this->class;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return string|null
     */
    public function getMethod():?string
    {
        return $this->method;
    }

    /**
     * @param  Route $route
     * @return bool
     */
    protected function matchRoute(Route $route): bool
    {
        $params = [];
        $key_params = array_keys($route->getParams());
        $value_params = $route->getParams();

        foreach ($key_params as $key) {
            $params['{'.$key.'}'] = $value_params[$key];
        }

        $url = $route->getPath();
        $url = str_replace(array_keys($params), $params, $url);
        $url = preg_replace('/<\w+>/', '.*', $url);
        preg_match("#^$url$#", $this->url, $results);

        if ($results) {
            $this->url = str_replace([$this->strlcs($url, $this->url)], [''], $this->url);
            $this->file = $route->getFile();
            $this->class = $route->getClass();
            $this->method = $route->getMethod();
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function run(): bool
    {
        foreach (self::$collection->getAll() as $route) {
            if ($this->matchRoute($route)) {
                $this->setGetData($route);
                return true;
            }
        }

        return false;
    }

    /**
     * @param Route $route
     */
    protected function setGetData(Route $route)
    {
        $route_path = $route->getPath();
        $trim = explode('{', $route_path);
        $parsed_url = str_replace([HTTP_SERVER], [''], $this->url);
        $parsed_url = preg_replace("#$trim[0]#", '', $parsed_url, 1);

        foreach ($route->getParams() as $key => $param) {
            if ($parsed_url[0] == '/') {
                $parsed_url = substr($parsed_url, 1);
            }

            preg_match("#$param#", $parsed_url, $results);
            if (!empty($results[0])) {
                $_GET[$key] = $results[0];
                $temp_url   = explode($results[0], $parsed_url, 2);
                $parsed_url = $temp_url[1];
            }
        }

        foreach ($route->getDefaults() as $key => $default) {
            if (!isset($_GET[$key])) {
                $_GET[$key] = $default;
            }
        }
    }

    /**
     * @param string$str1
     * @param string $str2
     * @return array|mixed|string
     */
    protected function strlcs($str1, $str2)
    {
        $str1Len = strlen($str1);
        $str2Len = strlen($str2);
        $ret     = [];

        if ($str1Len == 0 || $str2Len == 0) {
            return $ret;
        }

        $CSL            = [];
        $intLargestSize = 0;

        for ($i = 0; $i < $str1Len; $i++) {
            $CSL[$i] = [];
            for ($j = 0; $j < $str2Len; $j++) {
                $CSL[$i][$j] = 0;
            }
        }

        for ($i = 0; $i < $str1Len; $i++) {
            for ($j = 0; $j < $str2Len; $j++) {
                if ($str1[$i] == $str2[$j]) {
                    if ($i == 0 || $j == 0) {
                        $CSL[$i][$j] = 1;
                    } else {
                        $CSL[$i][$j] = ($CSL[($i - 1)][($j - 1)] + 1);
                    }

                    if ($CSL[$i][$j] > $intLargestSize) {
                        $intLargestSize = $CSL[$i][$j];
                        $ret            = [];
                    }

                    if ($CSL[$i][$j] == $intLargestSize) {
                        $ret[] = substr($str1, ($i - $intLargestSize + 1), $intLargestSize);
                    }
                }
            }
        }

        if (isset($ret[0])) {
            return $ret[0];
        } else {
            return '';
        }
    }
}