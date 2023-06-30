<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformAction extends multiformActions implements ArrayAccess
{

    protected $data = array();
    protected $action_code = '';
    protected $is_dirty = array();

    public function __construct($action_code = '')
    {
        parent::__construct();

        if ($action_code) {
            $this->data = $this->getAction($action_code);
            if (!empty($this->data['id'])) {
                $this->action_code = $this->data['id'];
                $this->data['action_code'] = $this->action_code;
            }
        }
    }

    public function getPath()
    {
        return $this->_getPath($this->action_code);
    }

    public function getUrl()
    {
        return $this->_getUrl($this->action_code);
    }

    public function getConfigPath()
    {
        return $this->_getConfigPath($this->action_code);
    }

    public function getConfig()
    {
        return $this->_getConfig($this->action_code);
    }

    public function getInfo()
    {
        return $this->_getInfo($this->action_code);
    }

    public function getSettings()
    {
        return $this->_getSettings($this->action_code);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->__set($offset, null);
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $method = "get" . preg_replace_callback('@(^|_)([a-z])@', array(__CLASS__, 'camelCase'), $name);
        if (method_exists($this, $method)) {
            $this->data[$name] = $this->$method();
            return $this->data[$name];
        }
        return null;
    }

    private static function camelCase($m)
    {
        return strtoupper($m[2]);
    }

    public function getData($name = null)
    {
        if ($name) {
            return isset($this->data[$name]) ? $this->data[$name] : null;
        } else {
            return $this->data;
        }
    }

    public function __set($name, $value)
    {
        return $this->setData($name, $value);
    }

    public function setData($name, $value)
    {
        if ($this->getData($name) !== $value) {
            $this->data[$name] = $value;
            $this->is_dirty[$name] = true;
        }
        return $value;
    }

}
