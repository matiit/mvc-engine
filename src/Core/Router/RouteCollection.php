<?php

namespace Core\Router;

class RouteCollection{
    protected $route;

    public function add($name, $item){
        $this->route[$name] = $item;
    }
    public function get($name){
        if(array_key_exists($name, $this->route)){
            return $this->route[$name];
        }
        return null;
    }
    public function getAll(){
        return $this->route;
    }
}