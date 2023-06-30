<?php
use Gettext\Translation;

require_once wa()->getAppPath('lib/vendors/pofile.php', 'mylang');
require_once wa()->getAppPath('lib/vendors/autoload.php', 'mylang');

/**
 * Class mylangEditorjsActions
 */
class mylangEditorjsActions extends waJsonActions
{
    /** @var poFileCore $pofile */
    private $pofile;
    private $filename;
    private $filename_backup;
    /** @var array $entries */
    private $entries;
    private $keys;

    public function defaultAction()
    {
    }

    public function syncAction()
    {
        $slug = waRequest::get('slug', null, 'string_trim');
        $data = $this->scanPO($slug);
        $this->addKeys($slug);
        $this->response = $data;
    }
    
    public function repairAction($keys = false)
    {
        $locale = waRequest::get('locale', null, 'string_trim');
        $slug = waRequest::get('slug', null, 'string_trim');
        $original = $this->getPofile($slug, $locale);
        $pofile = new PoFileCore();
        $pofile->read($original);
        $this->entries = &$pofile->getEntries();
        if ($keys) {
            foreach ($this->entries as &$entry) {
                if (!isset($entry['msgid'])) {
                    continue;
                }
                $entry['msgid'] = preg_replace("/\\\*(')/", "'", $entry['msgid']);
            }
            unset($entry);
        } else {
            foreach ($this->entries as &$entry) {
                if (!isset($entry['msgstr'])) {
                    if (isset($entry['msgid_plural'])) {
                        if (isset($entry['msgstr[0]'])) {
                            $entry['msgstr[0]'] = preg_replace('/\\\\n[a-f0-9]{32}$/i', '', $entry['msgstr[0]']);
                        }
                        if (isset($entry['msgstr[1]'])) {
                            $entry['msgstr[1]'] = preg_replace('/\\\\n[a-f0-9]{32}$/i', '', $entry['msgstr[1]']);
                        }
                        if (isset($entry['msgstr[2]'])) {
                            $entry['msgstr[2]'] = preg_replace('/\\\\n[a-f0-9]{32}$/i', '', $entry['msgstr[2]']);
                        }
                    }
                    continue;
                }
                $entry['msgstr'] = preg_replace('/\\\\n[a-f0-9]{32}$/i', '', $entry['msgstr']);
            }
            unset($entry);
        }
        $pofile->write($original);
        $translations = mylangGettext::readPO($original);
        return mylangGettext::writeMO($translations, $original);
    }

    /**
     *
     */
    public function repairkeysAction()
    {
        $this->repairAction(true); //Fix keys;
    }
    
    private function addKeys($slug)
    {
        $original = $this->getPofile($slug, 'en_US');
        $locales = waLocale::getAll();
        $key = array_search('en_US', $locales, true);
        unset($locales[$key]);
        if (file_exists($original)) {
            $translationsOriginal = mylangGettext::readPO($original);
            foreach ($translationsOriginal as $text) {
                $text->translate('');
            }
            foreach ($locales as $locale) {
                $path = str_ireplace('/locale/en_US/LC_MESSAGES/', '/locale/'.$locale.'/LC_MESSAGES/', $original);
                $translationsTarget = mylangGettext::readPO($path);
                $translationsTarget->mergeWith($translationsOriginal); // TODO check
                mylangGettext::writePOMO($translationsTarget, $path);
            }
        }
    }

    /**
     * @return mixed|string
     *
     */
    public function compileAction()
    {
        $locale = waRequest::get('locale', null, 'string_trim');
        $slug = waRequest::get('slug', null, 'string_trim');
        $locale = trim($locale, '/');
        $slug = trim($slug, '/');
        $strings = $this->getStrings($slug, $locale);
        if (!$strings) {
            return $this->errors = _w('Input data error');
        }
        //first time
        $output = str_ireplace('.po', '.mo', $this->filename);
        if (file_exists($output)) {
            waFiles::move($output, $output.'.bak');
        }
        $translations = mylangGettext::readPO($this->filename);
        mylangGettext::writeMO($translations, $this->filename);
    }

    /**
     * @return mixed|string
     */
    public function uploadAction()
    {
        $pofile = waRequest::file('pofile');
        $locale = waRequest::post('locale');
        $slug = waRequest::post('slug');
        if (empty($locale) || empty($slug) || !$pofile->uploaded()) {
            return $this->errors = _w('Input file error');
        }
        try { //check valid po
            $translationsNew = mylangGettext::readPO($pofile->tmp_name);
        } catch (waException $e) {
            waLog::log($slug.'/'.$pofile->name.' - '.$e->getMessage(), 'mylang_import.txt');
            return $this->errors = _w('Input file content error');
        }
        $oldfilename = $this->getPofile($slug, $locale);
        try {
            $translations = mylangGettext::readPO($oldfilename);
            $translations->mergeWith($translationsNew); // TODO
        } catch (Exception $e) {
            waLog::log($slug.'/'.$pofile->name.' - '.$e->getMessage(), 'mylang_import.txt');
            return $this->errors = _w('Previous file content error');
        }
        return $this->response = mylangGettext::writePOMO($translations, $oldfilename);
    }

    public function saveAction()
    {
        $data = waRequest::post();
        $slug = ifempty($data['slug']);
        unset($data['slug']);
        $locale = ifempty($data['locale']);
        unset($data['locale']);
        if (empty($slug)||empty($locale)||empty($data)) {
            return $this->errors = _w('Input data error');
        }
        $this->getStrings($slug, $locale);
        $this->hashkeys();
        foreach ($data as $key => $entry) {
            if (array_key_exists($key, $this->keys)) {
                $realkey = $this->keys[$key];
                if (is_array($entry)) {
                    if (isset($entry[0])) {
                        $this->entries[$realkey]['msgstr[0]'] = $entry[0];
                    }
                    if (isset($entry[1])) {
                        $this->entries[$realkey]['msgstr[1]'] = $entry[1];
                    }
                    if (isset($entry[2])) {
                        $this->entries[$realkey]['msgstr[2]'] = $entry[2];
                    }
                } else {
                    $this->entries[$realkey]['msgstr'] = $entry;
                }
                unset($this->entries[$realkey]['id']);
            }
        }
        $this->unhashkeys($this->entries);
        $this->pofile->write($this->filename);
        $translations = mylangGettext::readPO($this->filename);
        $headers = $translations->getHeaders();
        $headers_arr = $headers->toArray();
        $header = ifset($headers_arr, 'Plural-Forms', false);
        if (!$header) {
            $plurals = include wa()->getAppPath('lib/config/data/plurals.php', 'mylang');
            if (array_key_exists($locale, $plurals)) {
                $headers->set('Plural-Forms', $plurals[$locale]['nplural']);
                $headers->set('Language', $locale);
                mylangGettext::writePO($translations, $this->filename);
            }
        }
        mylangGettext::writeMO($translations, $this->filename); //TODO
        clearstatcache($this->filename);
    }

    private function getPofile($slug, $locale, $check = true)
    {
        $appspath = waConfig::get('wa_path_apps');
        $filename_backup = wa()->getDataPath('backup', null, 'mylang');
        $slug_path = str_replace(['/plugins/', '/widgets/'], '_', $slug);
        if (strpos($slug, 'wa-plugins/') === 0) {
            $appspath = waConfig::get('wa_path_root');
            $slug_path = str_replace(['wa-plugins/', '/'], ['', '_'], $slug);
        }
        if (strpos($slug, 'wa-widgets/') === 0) {
            $appspath = waConfig::get('wa_path_root');
            $slug_path = str_replace(['wa-widgets/', '/'], ['widget_', '_'], $slug);
        }
        if (strpos($slug, 'webasyst') === 0) {
            $appspath = waConfig::get('wa_path_system');
            $slug_path = $slug;
        }
        if ($slug === 'mylang_custom') {
            $appspath = wa()->getDataPath(null, null, 'mylang');
            $slug_path = 'mylang_custom';
        }
        $filename = $appspath.'/'.$slug.'/locale/'.$locale.'/LC_MESSAGES/'.$slug_path.'.po';
        $filename_backup .= '/'.$slug.'/locale/'.$locale.'/LC_MESSAGES/'.$slug_path.'.po';
        if ($check) {
            if (!file_exists($filename)) {
                //create new
                $this->scanPO($slug);
                if (!file_exists($filename)) {
                    $this->errors = _w('File not found.');
                    return false;
                }
            }
            if (!file_exists($filename_backup)) {
                //create new
                waFiles::write($filename_backup, '');
            }
        }
        $this->filename = $filename;
        $this->filename_backup = $filename_backup;
        return $filename;
    }

    private function search($query = null): void
    {
        if (empty($query)) {
            return;
        }
        $entries = ifset($entries, $this->entries);
        foreach ($entries as $key => $str) {
            $str['comments'] = null;
            $in = implode('', $str);
            if (stripos($in, $query) === false) {
                unset($entries[$key]);
            }
        }
        $this->entries = $entries;
    }

    private function getStrings($slug, $locale): bool
    {
        $this->getUser()->setSettings('mylang', 'editor_locale', $locale);
        $filename = $this->getPofile($slug, $locale);
        if (empty($filename)) {
            waLog::log(_w('Empty filename.'));
            waLog::dump($filename, $slug, $locale);
            return false;
        }
        $pofile = new PoFileCore();
        $this->pofile = $pofile;
        try {
            $pofile->read($filename);
        } catch (Exception $e) {
            //try to fix file
            $translations = mylangGettext::readPO($filename);
            mylangGettext::writePO($translations, $filename);
            try {
                $pofile->read($filename);
            } catch (Exception $e) {
                waLog::log($filename.':'.$e->getMessage());
                return false;
            }
        }
        $this->entries = &$pofile->getEntries();
        return true;
    }

    private function hashkeys(): void
    {
        $md5keys = [];
        foreach ($this->entries as $key => &$str) {
            $str['id'] = md5($key);
            $md5keys[$str['id']] = $key;
        }
        $this->keys = $md5keys;
    }

    private function unhashkeys(&$entries = null): void
    {
        $entries = ifset($entries, $this->entries);
        foreach ($entries as $key => &$str) {
            unset($str['id']);
        }
        $this->entries = $entries;
    }

    /**
     * @return mixed|string
     */
    public function getStringsAction()
    {
        $slug = waRequest::get('slug', null, 'string_trim');
        trim($slug, '/');
        $locale = waRequest::get('locale', null, 'string_trim');
        if (empty($slug)||empty($locale)) {
            return $this->errors = _w('Input data error');
        }
        $strings = $this->getStrings($slug, $locale);
        if (!$strings) {
            return $this->errors = _w('Request strings error');
        }
        $query = waRequest::get('query', null, 'string_trim');
        if (!empty($query)) {
            $this->search($query);
        }
        $this->hashkeys();
        unset($this->entries['']);
        $limit = (int)waRequest::cookie('mylangentries_per_page');
        if (!$limit || $limit < 0 || $limit > 250) {
            $limit = 150;
        }
        $page = waRequest::get('page', 1, 'int');
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;
        $data = array_slice($this->entries, $offset, $limit, true);
        $count = count($this->entries);
        $data['mylang_pagecount'] = ceil((float)$count / $limit);
        $data['mylang_entries'] = $count;
        $this->response = $data;
    }

    /**
     * @param $app_id string
     * @return array|bool|void
     * @throws waException
     */
    private function scanPO($app_id)
    {
        if ($app_id === 'mylang_custom') {
            $path = waConfig::get('wa_path_root');
            $path .= '/wa-data/protected/mylang/mylang_custom';
            $this->initCustom($path);
            return true;
        }
        $params = [$app_id];
        $parser = new mylangGettextParser($params);
        try {
            $parser->exec();
        } catch (Exception $e) {
            waLog::log($e->getMessage(), 'mylang_sync.txt');
        }
    }

    /**
     * @param $path
     * @throws waException
     */
    public function initCustom($path): void
    {
        $locales = waLocale::getAll();
        foreach ($locales as $l) {
            $localePath = $path.'/locale/'.$l.'/LC_MESSAGES/mylang_custom.po';
            if (!file_exists($localePath)) {
                waFiles::write($localePath, '');
            }
        }
    }

    /**
     * @return mixed|string
     */
    public function addStringsAction()
    {
        $data = waRequest::post();
        if (empty($data['slug']) || empty($data['custom_id'])) {
            return $this->errors = _w('Input data error');
        }
        foreach ($data['custom_str'] as $locale => $value) {
            $this->getPofile($data['slug'], $locale); //create new files if needed.
            $translations = mylangGettext::readPO($this->filename);
            $translation = Translation::create('', $data['custom_id']);
            $translation->translate($value);
            if (!empty($data['custom_id_plural'])) {
                $translation->setPlural($data['custom_id_plural']);
                if (isset($data['custom_str_plural'][$locale])) {
                    $translation->translatePlural($data['custom_str_plural'][$locale]);
                }
            }
            $translations->add($translation);
            mylangGettext::writePO($translations, $this->filename);
            mylangGettext::writePO($translations, $this->filename_backup);
            //compile at once after add
            if (!empty($value)) {
                mylangGettext::writeMO($translations, $this->filename);
                clearstatcache($this->filename);
            }
        }
    }

    /**
     * @return mixed|string
     */
    public function deleteStringsAction()
    {
        $data = waRequest::post();
        if (empty($data['slug']) || empty($data['locale']) || empty($data['strings'])) {
            return $this->errors = _w('Input data error');
        }
        $this->getStrings($data['slug'], $data['locale']);
        $this->hashkeys();
        if (array_key_exists($data['strings'], $this->keys)) {
            $realkey = $this->keys[$data['strings']];
            unset($this->entries[$realkey]);
        }
        $this->unhashkeys();
        $this->pofile->write($this->filename);
        $this->pofile->write($this->filename_backup);
        $translations = mylangGettext::readPO($this->filename);
        mylangGettext::writeMO($translations, $this->filename);
        clearstatcache($this->filename);
    }
}
