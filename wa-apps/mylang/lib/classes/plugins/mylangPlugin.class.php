<?php

class mylangPlugin extends waPlugin
{

    /**
     * Used for langpacks
     * @return mixed|null
     */
    public function getLanguage()
    {
        return ifset($this->info['language'], '');
    }

    public function getTranslate($params)
    {
        $class = 'mylang' . ucfirst($this->id) . 'PluginTranslate';
        if (class_exists($class)) {
            return new $class($params);
        }
        return false;
    }
}