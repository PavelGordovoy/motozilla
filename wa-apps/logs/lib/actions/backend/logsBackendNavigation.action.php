<?php

class logsBackendNavigationAction extends logsViewAction
{
    private $backend_url;

    public function __construct()
    {
        parent::__construct();
        $this->backend_url = logsHelper::getLogsBackendUrl(false);
    }

    public function execute()
    {
        $path = waRequest::get('path');
        $action = waRequest::get('action');

        $back_url = $this->getBackUrl();

        if ($action == 'file') {
            $total_size = filesize(logsHelper::getFullPath($path));
        } else {
            if ($action != 'action') {
                $total_size = logsHelper::getTotalLogsSize();
            }
        }

        if (in_array($action, ['file', 'action', 'actions']) || strlen($path)) {
            if (in_array($action, ['file', 'action'])) {
                $this->view->assign('back', strpos($back_url, $this->backend_url) === 0);
            }

            if (strlen($path)) {
                $breadcrumbs = $this->getBreadcrumbs($path);
            } elseif (in_array($action, ['action', 'actions'])) {
                $breadcrumbs = [
                    [
                        'name' => _w('logs'),
                        'url' => '',
                    ],
                    [
                        'name' => _w('User actions'),
                        'url' => http_build_query(array(
                            'action' => 'actions',
                        )),
                    ],
                ];

                if ($action == 'action') {
                    $breadcrumbs[] = [
                        'name' => logsItemAction::getName(waRequest::get('id')),
                        'url' => http_build_query([
                            'action' => 'action',
                            'id' => waRequest::get('id'),
                        ]),
                    ];
                }
            }

            $this->view->assign('breadcrumbs', $breadcrumbs);
        }

        if (isset($total_size)) {
            $total_size_classes = ['total-size'];

            if (waRequest::get('action') == 'file') {
                $total_size_classes[] = 'total-size-file';
            } elseif (logsHelper::isLargeSize($total_size)) {
                $total_size_classes[] = 'total-size-large';
            }

            $total_size_hint = waRequest::get('action') == 'file' ? _w('This file’s size') : _w('All log files‘ total size');
        }

        $this->view->assign('view_modes', $this->getViewModes());
        $this->view->assign('item_actions', $this->getItemActions());
        $this->view->assign('total_size', isset($total_size) ? logsHelper::formatSize($total_size) : null);
        $this->view->assign('total_size_class', isset($total_size_classes) ? implode(' ', $total_size_classes) : '');
        $this->view->assign('total_size_hint', ifset($total_size_hint));
        $this->view->assign('back_url', $back_url);
        $this->view->assign('is_item_list', !in_array(waRequest::get('action'), ['file', 'action']));
    }

    private function sortViewModes($a, $b)
    {
        if ($a['selected'] != $b['selected']) {
            return $b['selected'] ? 1 : -1;
        } else {
            return $a['sort'] < $b['sort'] ? -1 : 1;
        }
    }

    private function getBreadcrumbs($path)
    {
        $path_parts = explode('/', $path);

        if (!strlen($path_parts[0])) {
            return false;
        }

        $result = [];
        $result[] = [
            'name' => 'wa-log',
            'url' => '',
        ];

        $item_path = '';
        foreach ($path_parts as $part) {
            $item_path .= $item_path ? '/'.$part : $part;
            $result[] = [
                'name' => $part,
                'url' => http_build_query([
                    'path' => $item_path,
                ]),
            ];
        }

        return $result;
    }

    private function getBackUrl()
    {
        if (in_array(waRequest::get('action'), ['file', 'action'])) {
            $back_url = waRequest::cookie('back_url', $this->backend_url);
        } else {
            $path_parts = explode('/', waRequest::get('path'));
            array_pop($path_parts);
            $back_url = $path_parts ? '?path='.implode('/', $path_parts) : $this->backend_url;
        }

        if ($back_url != $this->backend_url) {
            $current_url = wa()->getConfig()->getCurrentUrl();
            $back_url_contents = $current_url_contents = null;
            parse_str(str_replace($this->backend_url, '', $back_url), $back_url_contents);
            parse_str(str_replace($this->backend_url, '', $current_url), $current_url_contents);

            if ($back_url_contents && $current_url_contents) {
                if ($back_url_contents == $current_url_contents) {
                    //same keys & values regardless of order
                    $back_url = $this->backend_url;
                }
            }
        }

        return $back_url;
    }

    private function getViewModes()
    {
        $icons = [
            '1.3' => [
                'directory' => 'folders',
                'updatetime' => 'bytime',
                'size' => 'bysize',
            ],
            '2.0' => [
                'directory' => 'folder',
                'updatetime' => 'clock',
                'size' => 'sort-amount-down',
            ],
        ];

        $view_modes = [
            [
                'action' => '',
                'mode'   => '',
                'url'    => '',
                'title'  => _w('By directory'),
                'sort'   => 0,
                'icon'   => logsHelper::getIconClass('directory', $icons),
            ],
            [
                'action' => 'files',
                'mode'   => 'updatetime',
                'url'    => '?action=files&mode=updatetime',
                'title'  => _w('By update time'),
                'sort'   => 1,
                'icon'   => logsHelper::getIconClass('updatetime', $icons),
            ],
            [
                'action' => 'files',
                'mode'   => 'size',
                'url'    => '?action=files&mode=size',
                'title'  => _w('By file size'),
                'sort'   => 2,
                'icon'   => logsHelper::getIconClass('size', $icons),
            ],
        ];

        foreach ($view_modes as &$view_mode) {
            $view_mode['selected'] = $view_mode['action'] == waRequest::get('action') && $view_mode['mode'] == waRequest::get('mode');
        }

        usort($view_modes, [$this, 'sortViewModes']);

        return $view_modes;
    }

    private function getItemActions()
    {
        $result = [];

        $action = waRequest::get('action');
        $path = waRequest::get('path');

        $published_items = $this->getPublishedItems($action, $path);

        $icons = [
            '1.3' => [
                'download' => 'download',
                'published' => 'globe',
                'rename' => 'edit-bw',
                'delete' => 'cross-bw',
                'phpinfo' => 'script-php',
                'settings' => 'settings',
            ],
            '2.0' => [
                'download' => 'download',
                'published' => 'globe',
                'rename' => 'edit',
                'delete' => 'trash',
                'phpinfo' => 'info-circle',
                'settings' => 'cogs',
                'track' => 'flag',
            ],
        ];

        if ($action == 'file') {
            $result['download'] = [
                'title' => _w('Download'),
                'icon_class' => logsHelper::getIconClass('download', $icons),
                'url' => '?action=download&path=' . waString::escape($path),
            ];
        }

        if (strlen($path)) {
            if ($this->getUser()->getRights('logs', 'rename')) {
                $result['rename'] = [
                    'title' => _w('Rename'),
                    'icon_class' => logsHelper::getIconClass('rename', $icons),
                    'data' => [
                        'path' => $path,
                    ],
                ];
            }

            if ($this->getUser()->getRights('logs', 'delete_files')) {
                $result['delete'] = [
                    'title' => _w('Delete'),
                    'icon_class' => logsHelper::getIconClass('delete', $icons),
                    'data' => [
                        'path' => $path,
                        'return-url' => $this->getBackUrl(),
                    ],
                ];
            }
        }

        if ($action == 'file') {
            if (wa()->whichUI() == '2.0') {
                $tracking_enabled = (new logsTrackedModel())->isTracked($path);
                $tracking_title = $tracking_enabled ? _w('Track changes (enabled)') : _w('Track changes');
                $tracking_title_simple = _w('Track changes');
                $tracking_title_enabled = _w('Track changes (enabled)');

                $result['track'] = [
                    'title' => $tracking_title,
                    'title_simple' => $tracking_title_simple,
                    'title_enabled' => $tracking_title_enabled,
                    'icon_class' => logsHelper::getIconClass('track', $icons),
                    'enabled' => $tracking_enabled ,
                    'marker' => true,
                    'data' => [
                        'path' => $path,
                    ],
                ];
            }

            if ($this->getUser()->getRights('logs', 'publish_files')) {
                $file_published = in_array($path, $published_items);
                $file_published_title = $file_published ? _w('Public link (enabled)') : _w('Public link');
                $file_published_title_simple = _w('Public link');
                $file_published_title_enabled = _w('Public link (enabled)');

                $result['published'] = [
                    'title' => $file_published_title,
                    'title_simple' => $file_published_title_simple,
                    'title_enabled' => $file_published_title_enabled,
                    'icon_class' => logsHelper::getIconClass('published', $icons),
                    'enabled' => $file_published,
                    'marker' => true,
                    'data' => [
                        'path' => $path,
                    ],
                ];
            }
        }

        if ($this->getUser()->getRights('logs', 'view_phpinfo')) {
            $phpinfo_published = in_array('//phpinfo//', $published_items);
            $phpinfo_title = $phpinfo_published ? _w('View PHP info (public link enabled)') : _w('View PHP info');
            $phpinfo_title_simple = _w('View PHP info');
            $phpinfo_title_enabled = _w('View PHP info (public link enabled)');

            $result['phpinfo'] = [
                'title' => $phpinfo_title,
                'title_simple' => $phpinfo_title_simple,
                'title_enabled' => $phpinfo_title_enabled,
                'common' => true,
                'icon_class' => logsHelper::getIconClass('phpinfo', $icons),
                'enabled' => $phpinfo_published,
            ];
        }

        if ($this->getUser()->getRights('logs', 'change_settings')) {
            $result['settings'] = [
                'title' => _w('Settings'),
                'common' => true,
                'icon_class' => logsHelper::getIconClass('settings', $icons),
                'data' => [
                    'hide-data' => json_encode(logsHelper::getHideSetting(null, true)),
                ],
            ];
        }

        return $result;
    }

    private function getPublishedItems($action, $path)
    {
        $published_model = new logsPublishedModel();
        $published_paths = [
            '//phpinfo//',
        ];

        if ($action == 'file') {
            $published_paths[] = $path;
        }

        $result = $published_model
            ->select('path')
            ->where('path IN (s:paths)', [
                'paths' => $published_paths,
            ])
            ->fetchAll(null, true);

        return $result;
    }
}

