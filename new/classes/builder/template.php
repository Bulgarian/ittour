<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

class template {

    private $template;
    private $dir = '';
    private $params = array();
    private $content = '';
    private $assetsPath = '';

    public function extend($template) {
        if (null !== $this->template) {
            throw new Exception(sprintf('This template is already extends "%s"', $this->template));
        }

        $this->template = $template;
    }

    public function setDir($dir) {
        if (!file_exists($dir)) {
            throw new Exception(sprintf('Dir "%s" does not exist', $dir));
        }

        $this->dir = $dir;
    }

    public function block($name, $default = '') {
        echo array_key_exists($name, $this->params) ? $this->params[$name] : $default;
    }

    public function startBlock($name) {
        if (array_key_exists($name, $this->params)) {
            throw new Exception(sprintf('The block "%s" was previously initialized', $name));
        }

        $this->params[$name] = '';
        ob_start();
    }

    public function endBlock($name) {
        if (!array_key_exists($name, $this->params)) {
            throw new Exception(sprintf('The block "%s" was not previously initialized', $name));
        }

        $this->params[$name] = ob_get_clean();
    }
    
    public function includeBlock($name, $template, array $params = array()) {
        if (array_key_exists($name, $this->params)) {
            throw new Exception(sprintf('The block "%s" was previously initialized', $name));
        }
        
        $this->params[$name] = $this->renderTemplate($template, array_merge($this->params, $params));
    }

    private function renderTemplate($template, array $params = array()) {
        $filename = $this->dir . $template;

        if (!file_exists($filename)) {
            throw new Exception(sprintf('Template "%s" not found in "%s"', $template, $this->dir));
        }

        extract($params);
        ob_start();
        include $filename;

        return ob_get_clean();
    }

    public function render($template, array $params = array()) {
        $this->template = $template;
        $this->params = $params;
        while (null !== $this->template) {
            $this->template = null;
            $this->content .= $this->renderTemplate($template, $this->params);
            $template = $this->template;
        }

        return $this->content;
    }

    public function asset($path) {
        $asset = $this->assetsPath . $path;
        
        return $asset;
    }

    public function setAssetsPath($path) {
        if (!is_string($path)) {
            throw new Exception(sprintf("Assets path must be a string, '%s' given", $path));
        }

        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        $this->assetsPath = $path;
    }
    
    public function includeTemplate($template) {
        echo $this->renderTemplate($template, $this->params);
    }
    
}
