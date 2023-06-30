<?php

class mylangEventHandler extends waEventHandler
{
    /**
     * Filter out 2-levels level pages only
     * @param $pages
     * @return array
     * @throws waException
     */
    public static function pages(&$pages)
    {
        if (empty($pages)) {
            return [];
        }

        $settings = mylangHelper::getSettings('filterpages');
        if (!$settings) {
            return $pages;
        }
        $locale = mylangLocale::currentLocale();
        //Get event app
        $app = waRequest::param('app');
        if (!$app) {
            $url = reset($pages)['url'];
            $url = ($url === '/') ? '/' : ltrim($url, '/');
            if ($url === null) {
                return [];
            }
            $routing = new mylangRouting();
            $app = $routing->getAppByUrl($url);
        }
        wa($app);

        //Get pages
        $ids = mylangHelper::iterate_recursive($pages);
        $pagesId = implode(', ', array_keys($ids));

        $class = $app . 'PageModel';
        /** @var waModel $model */
        $model = new $class();
        if ($model->fieldExists('mylang_locale')) {
            $result = $model->query(
                'SELECT * FROM ' . $model->getTableName().
                ' WHERE id IN(' . $pagesId . ') AND mylang_locale IS NULL OR mylang_locale = ?',
                $locale
            )->fetchAll('id');
            if (!empty($result)) {
                foreach ($pages as $key => &$page) {
                    if (!array_key_exists($page['id'], $result)) {
                        unset($pages[$key]);
                    } elseif (array_key_exists('childs', $page)) {
                        foreach ($page['childs'] as $ckey=>$child) {
                            if (!array_key_exists($child['id'], $result)) {
                                unset($page[$ckey]);
                            }
                        }
                    }
                }
                return $pages;
            }
        } else {
            try {
                $model->query("SELECT `mylang_locale` FROM `{$model->getTableName()}` WHERE 0");
            } catch (waDbException $e) {
                $model->exec("Alter table `{$model->getTableName()}` add mylang_locale varchar(10) DEFAULT NULL");
            }
        }
        return $pages;
    }
}