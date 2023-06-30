<?php

class mylangRules
{
    protected $rules = [];
    protected $domains = [];
    protected $rules_path;

    public function __construct()
    {
        $path = waConfig::get('wa_path_config');
        $this->rules_path = $path.'/apps/mylang/rules.php';
        if (file_exists($this->rules_path) && filesize($this->rules_path)) {
            $this->rules = include $this->rules_path;
        }
    }

    public function getAll()
    {
        return $this->rules;
    }

    public function get($id)
    {
        if (array_key_exists($id, $this->rules)) {
            return $this->rules[$id];
        }
        return [];
    }

    public function save($data)
    {
        $domain = $data['domain'];
        if (strpos($data['url'], '//') === false) {
            if (strpos($data['url'], '/') !== 0) {
                $data['url'] = '/'.$data['url'];
            }
            $data['url'] = rtrim($data['url'], '/');
            $data['url'] .= '/';
            $data['url'] = str_replace('//', '/', $data['url']);
        }
        $this->rules[$domain][$data['locale']] = $data['url'];
        arsort($this->rules[$domain]);
        waFiles::create($this->rules_path);
        return waUtils::varExportToFile($this->rules, $this->rules_path, true);
    }
    
    public function delete($data)
    {
        unset($this->rules[$data['domain']][$data['locale']]);
        return waUtils::varExportToFile($this->rules, $this->rules_path, true);
    }
}
