<?php

 namespace Core\Router;

 class Router{
     protected $url;
     protected static $collection;
     protected $file;
     protected $class;
     protected $method;

     public function __construct($url, $collection = null){
         if($collection != null){
             Router::$collection = $collection;
         }
         $url = explode('?', $url);
         $this->url = $url[0];
     }

     public function setUrl(string $url){
         $this->url = $url;
     }

     public function getUrl(): string{
         return $this->url;
     }

     public function setCollection(string $collection){
         Router::$collection = $collection;
     }

     public function getCollection(){
         return Router::$collection;
     }

     public function setFile(string $controller){
         $this->file = $controller;
     }

     public function getFile():? string{
         return $this->file;
     }

     public function setClass(string $class){
         $this->class = $class;
     }

     public function getClass():? string{
         return $this->class;
     }

     public function setMethod(string $method){
         $this->method = $method;
     }

     public function getMethod():? string{
         return $this->method;
     }

     protected function matchRoute(Route $route): bool{
         $params = array();
         $key_params = array_keys($route->getParams());
         $value_params = $route->getParams();

         foreach($key_params as $key){
             $params['{'.$key.'}'] = $value_params[$key];
         }

         $url = $route->getPath();
         $url = str_replace(array_keys($params), $params, $url);
         $url = preg_replace('/<\w+>/', '.*', $url);
         preg_match("#^$url$#", $this->url,$results);

         if($results){
             $this->url=str_replace(array($this->strlcs($url, $this->url)), array(''), $this->url);
             $this->file = $route->getFile();
             $this->class = $route->getClass();
             $this->method = $route->getMethod();
             return true;
         }
         return false;
     }

     public function run(): bool{
         foreach(Router::$collection->getAll() as $route){
             if($this->matchRoute($route)){
                 $this->setGetData($route);
                 return true;
             }
         }
         return false;
     }

     protected function setGetData(Route $route){
         $route_path = $route->getPath();
         $trim = explode('{', $route_path);
         $parsed_url = str_replace(array(HTTP_SERVER), array(''), $this->url);
         $parsed_url = preg_replace("#$trim[0]#", '', $parsed_url, 1);

         foreach($route->getParams() as $key => $param){
             if($parsed_url[0] == '/'){
                 $parsed_url = substr($parsed_url, 1);
             }
             preg_match("#$param#", $parsed_url,$results);
             if(!empty($results[0])){
                 $_GET[$key] = $results[0];
                 $temp_url = explode($results[0], $parsed_url, 2);
                 $parsed_url = $temp_url[1];
             }
         }

         foreach($route->getDefaults() as $key => $default){
             if(!isset($_GET[$key])){
                 $_GET[$key] = $default;
             }
         }
     }

     protected function strlcs($str1, $str2){
         $str1Len = strlen($str1);
         $str2Len = strlen($str2);
         $ret = array();

         if($str1Len == 0 || $str2Len == 0)
             return $ret;

         $CSL = array();
         $intLargestSize = 0;

         for($i=0; $i<$str1Len; $i++){
             $CSL[$i] = array();
             for($j=0; $j<$str2Len; $j++){
                 $CSL[$i][$j] = 0;
             }
         }

         for($i=0; $i<$str1Len; $i++){
             for($j=0; $j<$str2Len; $j++){
                 if( $str1[$i] == $str2[$j] ){
                     if($i == 0 || $j == 0)
                         $CSL[$i][$j] = 1;
                     else
                         $CSL[$i][$j] = $CSL[$i-1][$j-1] + 1;

                     if( $CSL[$i][$j] > $intLargestSize ){
                         $intLargestSize = $CSL[$i][$j];
                         $ret = array();
                     }
                     if( $CSL[$i][$j] == $intLargestSize )
                         $ret[] = substr($str1, $i-$intLargestSize+1, $intLargestSize);
                 }
             }
         }
         if(isset($ret[0])) {
             return $ret[0];
         } else {
             return '';
         }
     }
 }