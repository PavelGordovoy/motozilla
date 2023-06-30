<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFiles
{

    /**
     * Let user download attachment file
     *
     * @param string $hash
     * @return bool
     */
    public static function getAttachment($hash)
    {
        $mfpm = new multiformFieldParamsModel();
        $field_id = (int) substr($hash, 16, -16);
        if ($field_id) {
            $field = $mfpm->getByField(array("field_id" => $field_id, 'ext' => 'attach_file'));

            $mfield = new multiformFieldModel();
            $field_full_info = $mfield->getById($field_id);

            $mf = new multiformFormModel();
            $form = $mf->getFormSettings($field_full_info['form_id']);
            // Если форма не общедоступна
            if (!$form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$form['id']}") <= 1) {
                return false;
            }

            $file_info = (!empty($field['value']) ? unserialize($field['value']) : array());
            if (isset($file_info['hash']) && $file_info['hash'] == $hash) {
                $file = self::getAttachmentPath($field_id, $file_info['name']);
                if (file_exists($file)) {
                    waFiles::readFile($file, $file_info['name']);
                }
            }
        }
    }

    /**
     * Download field file
     *
     * @param string $hash
     * @return bool
     */
    public static function getFile($hash)
    {
        $file = (new multiformFilesModel())->getFileByHash($hash);
        if ($file) {
            $form = (new multiformFormModel)->getFormByFile($file);
            // Если форма не общедоступна
            if (!$form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$form['id']}") <= 1) {
                return false;
            }

            $filepath = self::getFilePath($file);
            if (file_exists($filepath)) {
                waFiles::readFile($filepath, $file['filename']);
            }
        }
    }

    /**
     * Get path to attachment dir or file
     *
     * @param int $field_id
     * @param string $name
     * @return string
     */
    public static function getAttachmentPath($field_id, $name = "")
    {
        return wa()->getDataPath('fields/' . $field_id . '/attachment/' . $name, false, 'multiform');
    }

    /**
     * Get path to field file
     *
     * @param array $file
     * @param bool $public
     * @return string
     */
    public static function getFilePath($file, $public = false)
    {
        $path = 'files/' . (int) $file['record_id'] . '/' . (int) $file['field_id'] . '/' . ($file['cloned_index'] ? $file['cloned_index'] . '/' : '') . $file['filename'];
        return wa()->getDataPath($path, $public, 'multiform');
    }

    public static function getActionFilePath($action_id, $param, $filename, $private = false)
    {
        return wa()->getDataPath('files/actions/' . $action_id . '/' . $param . '/' . $filename, $private, 'multiform');
    }

    public static function getActionFileUrl($action_id, $param, $filename, $private = false)
    {
        return wa()->getDataUrl('files/actions/' . $action_id . '/' . $param . '/' . $filename, $private, 'multiform');
    }

    /**
     * Download field file
     *
     * @param string $hash
     */
    public static function getActionFile($hash)
    {
        $mfiles = new multiformActionFormSettingsModel();
        $file = $mfiles->getByField("ext", $hash);
        if ($file) {
            $maf = new multiformActionFormModel();
            $action = $maf->getFormActions($file['form_id'], $file['action_id']);
            $filepath = self::getActionFilePath($file['action_id'], $file['param'], $file['value'], empty($action['settings'][$file['param']]['private']));
            if (file_exists($filepath)) {
                waFiles::readFile($filepath, $file['value']);
            }
        }
    }

    public static function listdir($dir, $recursive = false, $limit = 50, $offset = 0, &$i = -1, &$c = 1)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return array();
        }

        if (!($dh = opendir($dir))) {
            return array();
        }

        $result = array();
        while (false !== ($file = readdir($dh))) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $i++;
            if ($i < $offset) {
                continue;
            }
            if ($c > $limit) {
                break;
            }

            if ($recursive && is_dir($dir . '/' . $file)) {
                $files = self::listdir($dir . '/' . $file, $recursive, $limit, $offset, $i, $c);
                if ($files) {
                    foreach ($files as $sub_file) {
                        $result[] = $file . '/' . $sub_file;
                    }
                }
            } else {
                $result[] = $file;
                $c++;
            }
        }
        closedir($dh);
        return $result;
    }

}
