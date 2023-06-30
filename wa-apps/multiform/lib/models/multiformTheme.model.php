<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformThemeModel extends waModel
{

    protected $table = 'multiform_theme';

    /**
     * Get all themes or theme by ID
     * 
     * @param int $id
     * @return array
     */
    public function getThemes($id = 0)
    {
        $mts = new multiformThemeSettingsModel();
        // Получаем все настройки тем дизайна
        $settings = array();
        $sql = "SELECT * FROM {$mts->getTableName()}";
        if ($id) {
            $sql .= " WHERE theme_id = '" . (int) $id . "'";
        }
        foreach ($mts->query($sql) as $r) {
            if ($r['ext']) {
                if (strpos($r['ext'], ":") !== false) {
                    $parts = explode(":", $r['ext']);
                    $settings[$r['theme_id']][$r['property']][$r['field']][$parts[0]][$parts[1]] = $r['value'];
                } else {
                    $settings[$r['theme_id']][$r['property']][$r['field']][$r['ext']] = $r['value'];
                }
            } else {
                $settings[$r['theme_id']][$r['property']][$r['field']] = $r['value'];
            }
        }

        $themes = array();
        $sql2 = "SELECT * FROM {$this->getTableName()}";
        if ($id) {
            $sql2 .= " WHERE id = '" . (int) $id . "'";
        }
        foreach ($this->query($sql2) as $r2) {
            if (isset($settings[$r2['id']])) {
                $themes[$r2['id']] = $r2;
                $themes[$r2['id']]['settings'] = $settings[$r2['id']];
            }
        }

        return $id ? reset($themes) : $themes;
    }

    /**
     * Get theme settings
     * 
     * @param int $id
     * @return array
     */
    public function getTheme($id)
    {
        return $this->getThemes($id);
    }

    /**
     * Delete theme
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $mts = new multiformThemeSettingsModel();
        $mfp = new multiformFormParamsModel();
        $sql = "DELETE t, mts, mfp FROM {$this->table} t
                LEFT JOIN {$mts->getTableName()} mts ON t.id = mts.theme_id
                LEFT JOIN {$mfp->getTableName()} mfp ON (mfp.param = 'theme_id' AND t.id = mfp.value)
                WHERE t.id = '" . (int) $id . "'";
        return $this->exec($sql);
    }

    /**
     * Generate css styles for theme
     * 
     * @param array $theme
     * @param int $is_popup
     * @return string
     */
    public function generateThemeCss($theme, $is_popup = 0)
    {
        $view = wa()->getView();
        $view->assign('multiform_theme_id', $theme['id']);
        $view->assign('multiform_important', $theme['important']);
        $view->assign('multiform_theme_settings', $theme['settings']);
        $view->assign('is_popup', $is_popup);

        $css = $view->fetch(wa('multiform')->getAppPath("templates/actions/form/include.form.appearance.html"));

        $view->clearAssign('multiform_theme_id');
        $view->clearAssign('multiform_theme_settings');
        $view->clearAssign('multiform_important');
        $view->clearAssign('is_popup');
        return $css;
    }

}
