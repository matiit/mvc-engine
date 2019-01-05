<?php

namespace Core\Router;

class Route{
    protected $path;
    protected $file;
    protected $class;
    protected $method;
    protected $defaults;
    protected $params;

    public function __construct(string $path, array $config, array $params = [], array $defaults = []){
        $this->path = $path;
        $this->file = 'src/Controller/'.$config['file'];
        $this->class = 'Controller\\'.$config['class'];
        $this->method = $config['method'];
        $this->setParams($params);
        $this->setDefaults($defaults);
    }

    public function setPath(string $path){
        $this->path = HTTP_SERVER.$path;
    }

    public function getPath(): string{
        return $this->path;
    }

    public function setFile(string $controller){
        $this->file = $controller;
    }

    public function getFile(): string{
        return $this->file;
    }

    public function setClass(string $class){
        $this->class = $class;
    }

    public function getClass(): string{
        return $this->class;
    }

    public function setMethod(string $method){
        $this->method = $method;
    }

    public function getMethod(): string{
        return $this->method;
    }

    public function setParams(array $params){
        $this->params = $params;
    }

    public function getParams(): array{
        return $this->params;
    }

    public function setDefaults(array $defaults){
        $this->defaults = $defaults;
    }

    public function getDefaults(): array{
        return $this->defaults;
    }
}