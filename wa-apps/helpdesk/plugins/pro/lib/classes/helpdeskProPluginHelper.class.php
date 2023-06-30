<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginHelper
{

    private static $settings = null;

    public static function escape($str, $mode = ENT_QUOTES, $charset = 'UTF-8')
    {
        return htmlentities($str, $mode, $charset);
    }

    /**
     * Get settings.php contents
     *
     * @return array
     */
    public static function getSettingsConfig()
    {
        $path = wa()->getAppPath('plugins/pro', 'helpdesk') . '/lib/config/settings.php';
        $settings_config = array();
        if (file_exists($path)) {
            $settings_config = include($path);
        }
        return $settings_config;
    }

    /**
     * Get setting controls using waHtmlControl
     *
     * @return array
     */
    public static function getControls()
    {
        $params = array(
            'title_wrapper' => '<div class="name">%s</div>',
            'description_wrapper' => '<div class="margin-block top semi">%s</div>',
            'control_separator' => '<div class="margin-block bottom semi"></div>',
            'options_wrapper' => array(
                'control_separator' => '<div class="margin-block bottom semi"></div>',
            ),
            'control_wrapper' => "<div class='field'>"
                . "%s"
                . "<div class='value'>"
                . "%s"
                . "%s"
                . "</div>"
                . "</div>"
        );
        $controls = array();
        $settings_config = self::getSettingsConfig();
        foreach ($settings_config as $name => $row) {
            if (!is_array($row)) {
                continue;
            }
            $row = array_merge($row, $params);
            $row['value'] = self::getSettings($name);
            if (!empty($row['control_type'])) {
                $controls[$name] = waHtmlControl::getControl($row['control_type'], $name, $row);
            }
        }
        return $controls;
    }

    /**
     * Get developer products
     *
     * @return array
     */
    public function getProducts()
    {
        $path = self::path('products.php');
        return include $path;
    }

    /**
     * Get path to file
     * @param string $file - filename or path
     * @param $original - if true - return original path to file
     * @return string - protected path to file
     */
    public static function path($file, $original = false)
    {
        $path = wa()->getDataPath('plugins/pro/' . $file, false, 'helpdesk', true);
        if ($original) {
            return dirname(__FILE__) . '/../config/' . $file;
        }
        if (!file_exists($path)) {
            waFiles::copy(dirname(__FILE__) . '/../config/' . $file, $path);
        }
        return $path;
    }

    /**
     * Get plugin settings
     *
     * @param null|string $name
     * @param null|int $contact_id
     * @return string|array
     */
    public static function getSettings($name = null, $contact_id = null)
    {
        if ($contact_id === null) {
            $contact_id = wa()->getUser()->getId();
        }

        if (!isset(self::$settings[$contact_id])) {
            $model = new helpdeskProPluginSettingsModel();
            self::$settings[$contact_id] = $model->getSettings($contact_id);
        }

        if ($name === null) {
            return self::$settings[$contact_id];
        } else {
            return isset(self::$settings[$contact_id][$name]) ? self::$settings[$contact_id][$name] : '';
        }
    }

    /**
     * Clear static variable of plugin settings
     *
     * @param null|int $contact_id
     */
    public static function clearSettings($contact_id = null)
    {
        if ($contact_id && isset(self::$settings[$contact_id])) {
            unset(self::$settings[$contact_id]);
        }
        if ($contact_id === null) {
            self::$settings = null;
        }
    }

    /**
     * Generate CSS code from settings. Get mobile CSS styles
     *
     * @param array $settings
     * @return string
     */
    public function getCSSFromSettings($settings)
    {
        $css = "";

        if (!empty($settings['highlight_unread'])) {
            $css .= ".requests-table .unread { background: #" . (self::escape($settings['highlight_unread'])) . "; }";
        }
        if (empty($settings['favourite_use_image'])) {
            $css .= ".hp-favourite:after { background: none; }";
        }
        if (!empty($settings['bg_favourite'])) {
            $css .= "#ticket .s-comments > li.hp-favourite { background: #" . (self::escape($settings['bg_favourite'])) . " !important; }";
        }
        if (!empty($settings['bg_selected'])) {
            $css .= "table.requests-table tr.selected2 td { background: #" . (self::escape($settings['bg_selected'])) . "; }";
        }
        if (waRequest::isMobile()) {
            $css .= '#wa, #wa-app, table.requests-table { min-width: initial !important;}
                    #wa-app, .request-basic-info-block { margin-top: 0 !important; }
                    .request-status { width: 100%; float: none;  }
                    #c-core-content ul.menu-h li { box-sizing: border-box; }
                    .request-header { margin-right: initial;}
                    #h-request-tags-input_tagsinput-wrapper, div.contact-tabs,
                    .h-request-item .details .float-right, .h-log-item .details .float-right, .h-request-left-fields, 
                    .header-above-requests-table .list-views, td.avatar-col, td.info-col, #h-add-filter-menu, #wa-announcement,
                     .request-basic-info-block > *, .request-basic-info-block .h-request-right-field { display:none;}
                    .header-above-requests-table, #h-request-status-block, #h-request-status-block-wrapper { display: block; }
                    .s-comments .profile.image50px .details { margin-left: initial;}
                    .top-padded { margin-top: initial;}
                    .block.double-padded, ul.menu-v a, ul.menu-v.compact li a { padding:10px;}
                    #mobile-sidebar { background: #22284f; top: 0; position: fixed; left: -100%; z-index: 2001; bottom: 0; padding: 10px; word-wrap: break-word; float: left; width: 80%; max-width: 300px; }
                    #mobile-sidebar-content { position: absolute; left: 0; top: 48px; overflow-y: auto; bottom: 0; padding: 10px; }
                    #top-fixed { position: fixed; left: 0; z-index: 7777; top: 0; height: 48px; width: 100%; background: #22284f; }
                    #top-fixed li { margin: 0; padding: 0; }
                    #s-core { margin-top: 60px } 
                    #top-fixed > ul.menu-h a { height: 48px; color: #fff; font-size: 80px; padding: 0 10px; margin: 0; line-height: 50%; }
                    #top-fixed > ul > li { width: 30% } 
                    #top-fixed > ul > li.first { width: 60% } 
                    #mobile-sidebar-content a, #mobile-sidebar > .sidebar .count { color: #fff !important; }
                    #helpdesk #mobile-sidebar-content .highlighted { color: #fff; background: none; }
                    .mobile-content #hd-reply-form .wa-field .wa-value, .field .value { margin-left: 0 !important; }
                    .crm-dialog-wrapper { z-index: 999 }
                    .h-request-right-field { display: block; text-align: left; }
                    .w-dialog-block, .dialog-window { width: 95% !important; min-width: 95% !important; max-height: 80%; overflow-y: auto;  }
                    .w-dialog-content select { font-size: 1em !important; }
                    .hd-page-num { margin: 10px 0; width: 100%; }
                    #mobile-sidebar-content .s-search > .shadowed{ background: #fff; } 
                    #mobile-sidebar-content .s-search > .shadowed a { color: #03c !important; } 
                    ul.menu-h li.hp-apps { float: right; text-align: right; }
                    ul.menu-h li.hp-apps i { top: 17px; position: relative; margin: 0; right: 10px; }
                    .requests-table td:active { background: #ccc; }
                    #ticket.hp-spam #h-request-top-block:after { font-size: 7em; }
                    .h-new-request-forms { z-index: 1 }
                    #mobile-sidebar > .sidebar ul.menu-v > li > a b { width: 200px; }
                    #mobile-sidebar > .sidebar ul.menu-v > li > a b.nowrap > i.fader { -webkit-box-shadow: -10px 0 10px #22284f inset; -moz-box-shadow: -10px 0 10px #22284f inset; box-shadow: -10px 0 10px #22284f inset;  }
                    #mobile-sidebar > .sidebar ul.menu-v > li > a b.nowrap { font-weight: normal; }
                    #hd-main-filters .count { top: .4em; }
                    #hd-common-filters .count, #hd-personal-filters .count { top: 1em; }
                    .field .name, .sidebar.right150px, .sidebar.right200px, .sidebar.left200px { float: none; width: 100% !important; }
                    .content.right200px, .content.right150px, .content.right300px { margin-right: 0 !important; width: 100%; }
                    .content.left200px { margin-left: 0 !important; width: 100%; }
                    .h-form-top-wrapper .field .value input { min-width: 100% !important; }
                    .field .value input[type="text"], .field .value input[type="password"], .field .value textarea,
                    .backend-new-request-form .field .value input[type="text"], .backend-new-request-form .field .value select,
                    .field.template-subfield .value input, .field.template-subfield .value textarea, .field.template-subfield .value select,
                    .email-template-editor .field .value input[type="text"], .email-template-editor .field .value select,
                    .fields, .editor-wrapper, form.advanced-search-form .field .value input[type="text"], 
                    form.advanced-search-form .field .value input[type="password"], form.advanced-search-form .field .value input[type="search"], 
                    form.advanced-search-form .field .value textarea, form.advanced-search-form .field .value select,
                    #c-core-content ul.menu-h li, .h-faq-search { width: 100% !important; min-width: 100% !important; }
                    #s-core .support-background { margin: 10px; }
                    .wa-editor-wysiwyg-html-toggle { margin-right: 0; }
                    #wa-page-container, #wa-design-container, #wa.hp-nomobile, .hp-nomobile #wa-app { min-width: 760px !important; }
                    #c-core-content #wa-page-container ul.menu-h li, #c-core-content #wa-design-container ul.menu-h li,
                    #c-core-content ul.menu-h.h-category-settings-icons li,
                    #c-core-content .header-above-requests-table ul.menu-h.menu-above-list li,
                    #c-core-content .header-above-requests-table ul.menu-h.h-sorting-menu li { width: initial !important; min-width: initial !important; }
                    code, .h-faq-question-settings .h-frontend-root-url { word-wrap: break-word; }
                    .dialog-window { top: 11% !important; }
                    #wa .h-sidebar ul.menu-v li.selected a { background: #34395d; }
                    #mobile-sidebar > .sidebar ul.menu-v > li.selected > a b.nowrap > i.fader { -webkit-box-shadow: -10px 0 10px #34395d inset; -moz-box-shadow: -10px 0 10px #34395d inset; box-shadow: -10px 0 10px #34395d inset; }
                    table.zebra td { padding: 10px 5px; }
                    #request-and-log .text img { max-width: 100%; }
                    .w-dialog-wrapper .w-dialog-block .w-dialog-content, .w-dynamic-content { overflow: auto; }';
        }
        return $css;
    }

    /**
     * Get HTML code of CRM tags setting
     *
     * @param array $tags
     * @param string $crm_tags_name
     * @return string
     */
    private function getTagsHtml($tags, $crm_tags_name)
    {
        $html = "<ul class='hu-tags-list'>";
        if ($tags) {
            foreach ($tags as $t) {
                $html .= "<li data-id='" . $t['id'] . "'><span class='hu-tag'>" . htmlentities($t['name'], ENT_COMPAT, 'UTF-8') . "</span></li>";
            }
        }
        $html .= "<li>" .
            "<a class='inline-link crm-contact-assign-tags' href='javascript:void(0);'>" .
            "<i class='icon16 tags'></i>" .
            "<b><i>" . $crm_tags_name . "</i></b>" .
            "</a>" .
            "</li>" .
            "</ul>";
        return $html;
    }

    /**
     * Get CRM tags HTML
     *
     * @param int $client_contact_id
     * @param string $crm_tags_name
     * @return string
     */
    public function getCRMTagsHtml($client_contact_id, $crm_tags_name)
    {
        $html = "";
        $html .= '<link rel=\"stylesheet\" type=\"text/css\" href=\"' . wa()->getAppStaticUrl('crm') . 'js/crmDialog/crmDialog.css?v=' . wa('crm')->getVersion() . '\">';
        $html .= '<script src=\"' . wa()->getAppStaticUrl('crm') . 'js/crmDialog/crmDialog.js?v=' . wa('crm')->getVersion() . '\"><\/script>';
        $html .= '<script src=\"' . wa()->getAppStaticUrl('crm') . 'js/contacts.operation.assignTags.js?v=' . wa('crm')->getVersion() . '\"><\/script>';

        // Теги, принадлежащие контакту
        $tags = wao(new crmTagModel())->getByContact($client_contact_id);
        $html .= $this->getTagsHtml($tags, $crm_tags_name);
        return $html;
    }

    /**
     * Get HTML code of developer menu (check domain and check order)
     *
     * @return string
     */
    public function getDeveloperMenuHtml()
    {
        $html = '<div class=\"h-request-right-field align-right\"><div class=\"margin-block semi\">' .
            '<a href=\"#\" class=\"js-check-domain\" title=\"' . _wp('Check domain') . '\"><i class=\"icon16 globe\"></i> ' . _wp('Check domain') . '</a>' .
            '</div></div>' .
            '<div class=\"h-request-right-field align-right\"><div class=\"margin-block semi\">' .
            '<a href=\"#\" class=\"js-check-order\" title=\"' . _wp('Check order') . '\"><i class=\"icon16 search-bw\"></i> ' . _wp('Check order') . '</a>' .
            '</div></div>';
        return $html;
    }

    /**
     * Generate CSS for contact field
     *
     * @param array $params
     * @return string
     */
    private function generateFieldCss($params)
    {
        $css = "";
        if (!empty($params['font']['color'])) {
            $css .= "color:#" . $params['font']['color'] . ";";
        }
        if (!empty($params['font']['family'])) {
            $css .= "font-family:" . str_replace("'", '\"', $params['font']['family']) . ";";
        }
        if (!empty($params['font']['size'])) {
            $css .= "font-size:" . $params['font']['size'] . ";";
        }
        if (!empty($params['font']['style'])) {
            if ($params['font']['style'] == 'bold') {
                $css .= "font-style:normal;font-weight:bold;";
            } else if ($params['font']['style'] == 'bolditalic') {
                $css .= "font-style:italic;font-weight:bold;";
            } else if ($params['font']['style'] == 'italic') {
                $css .= "font-style:italic;font-weight:normal;";
            } else {
                $css .= "font-style:normal;font-weight:normal;";
            }
        }
        if (!empty($params['background'])) {
            $css .= "background:#" . $params['background'] . ";padding:2px 5px;";
        }
        if (!empty($params['border']['size'])) {
            $css .= "border:" . $params['border']['size'] . " " . $params['border']['style'] . " #" . $params['border']['color'] . ";";
        }
        if (!empty($params['border']['radius'])) {
            $css .= "-webkit-border-radius:" . $params['border']['radius'] . ";-moz-border-radius:" . $params['border']['radius'] . ";border-radius:" . $params['border']['radius'] . ";";
        }

        return $css;
    }

    /**
     * Prepare contact values before display
     *
     * @param string $value
     * @param array $field
     * @param null|array $contact_field
     * @return string
     */
    private function prepareContactValue($value, $field, $contact_field = null)
    {
        $value = strip_tags(str_replace('"', '\"', trim($value)));
        $value = self::escape($value);
        $value = preg_replace('/\v+|\\\r\\\n/', '<br/>', $value);

        if ($field['id'] == 'email') {
            if (!empty($contact_field['data'])) {
                $value = '<a href=\"mailto:' . self::escape($contact_field['data']) . '\">' . self::escape($contact_field['data']) . '</a>';
            }
            $value = '<i class=\"icon16 email\"></i>' . $value;
        } elseif ($field['id'] == 'phone') {
            $value = '<i class=\"icon16 phone\"></i>' . $value;
        } elseif ($field['id'] == 'im') {
            $value = '<i class=\"icon16 ' . (!empty($contact_field['ext']) && !empty($field['ext'][$contact_field['ext']]) ? $contact_field['ext'] : 'im') . '\"></i>' . $value;
        } elseif ($field['id'] == 'socialnetwork') {
            if (!empty($contact_field['data'])) {
                $value = '<a href=\"' . self::escape($contact_field['data']) . '\" target=\"_blank\">' . self::escape($contact_field['data']) . '</a>';
            }
            if (!empty($contact_field['ext']) && !empty($field['ext'][$contact_field['ext']])) {
                $value = '<i class=\"icon16 ' . self::escape($contact_field['ext']) . '\"></i>' . $value;
            }
        } elseif ($field['id'] == 'address' && !empty($contact_field['data']['country'])) {
            $value = '<img src=\"' . wa_url() . 'wa-content/img/country/' . $contact_field['data']['country'] . '.gif\" class=\"overhanging\">' . $value;
            if (!empty($contact_field['for_map'])) {
                $addr = !empty($contact_field['for_map']['with_street']) ? $contact_field['for_map']['with_street'] : (!empty($contact_field['for_map']['without_street']) ? $contact_field['for_map']['without_street'] : $contact_field['for_map']);
                $value = $value . ' <a target=\"_blank\" href=\"//maps.google.com/maps?q=' . urlencode($addr) . '&z=15\" class=\"small underline map-link\">' . _wp('map') . '</a>';
            }
        } elseif ($field['id'] == 'url' && !empty($contact_field['data'])) {
            $value = '<a target=\"_blank\" href=\"' . self::escape($contact_field['data']) . '\">' . self::escape($contact_field['data']) . '<i class=\"icon16 new-window\"></i></a>';
        } elseif ($field['id'] == 'categories' && !empty($field['options'][$contact_field])) {
            $value = '<a target=\"_blank\" href=\"' . wa()->getConfig()->getBackendUrl(true) . 'contacts/' . $field['hrefPrefix'] . $contact_field . '\">' . self::escape($field['options'][$contact_field]) . '<i class=\"icon16 new-window\"></i></a>';
        }

        return $value;
    }

    /**
     * Get HTML code of contact fields, builded in constructor
     *
     * @param waContact $client_contact
     * @return string
     */
    public function getContactConstructorFieldsHtml($client_contact)
    {
        $html = "";
        $constructor = new helpdeskProPluginConstructorModel();
        // Получаем только активные поля
        $contact_fields = $constructor->getFields(null, 1);
        if ($contact_fields) {
            wa('contacts');
            $all_fields = waContactFields::getInfo('all', true);
            $contact_field_values = $client_contact->load('js', true);
            contactsHelper::normalzieContactFieldValues($contact_field_values, $all_fields);

            $output = array(
                'before_name' => '',
                'after_name' => '',
                'before_tabs' => '',
                'after_tabs' => '',
                'bottom' => '',
                'after_request_id' => '',
            );
            foreach ($contact_fields as $k => $cf) {
                if (isset($all_fields[$k]) && !empty($contact_field_values[$k])) {
                    $output_place = !empty($cf['params']['output']) && isset($output[$cf['params']['output']]) ? $cf['params']['output'] : 'after_name';

                    // Получаем значение поля
                    $cfv = $contact_field_values[$k];
                    if (isset($all_fields[$k]['options']) && !is_array($cfv) && isset($all_fields[$k]['options'][$cfv])) {
                        $value = $all_fields[$k]['options'][$cfv];
                    } elseif (is_array($cfv)) {
                        $value = array();
                        foreach ($cfv as $kk => $v) {
                            $value[$kk]['value'] = isset($v['value']) ? $v['value'] : (!is_array($v) ? $v : '');
                            if (!empty($v['ext'])) {
                                $value[$kk]['ext'] = !empty($all_fields[$k]['ext'][$v['ext']]) ? $all_fields[$k]['ext'][$v['ext']] : $v['ext'];
                            }
                        }
                    } else {
                        $value = $cfv;
                    }

                    if ($value) {
                        // HTML для вывода
                        $field_html = "<div class='hp-contact-row margin-block semi'>";
                        // Название поля
                        if (!empty($cf['params']['name']['show']) || !isset($cf['params']['name']['show'])) {
                            $field_html .= "<div class='hp-contact-name'" . (!empty($cf['params']['name']['color']) ? " style='color:#" . self::escape($cf['params']['name']['color']) . "'" : "") . ">" .
                                (!empty($cf['params']['name']['value']) ? self::escape($cf['params']['name']['value']) : self::escape($all_fields[$k]['name'])) .
                                ":</div>";
                        }
                        $field_html .= "<div class='hp-contact-value'>";
                        // Значение поля
                        if (is_array($value)) {
                            $break = 1;
                            $len = count($value);
                            foreach ($value as $value_k => $vv) {
                                if (!isset($vv['value']) || !trim($vv['value'])) {
                                    continue;
                                }
                                $break = 0;
                                $vv['value'] = $this->prepareContactValue($vv['value'], $all_fields[$k], $contact_field_values[$k][$value_k]);
                                if (!empty($vv['ext'])) {
                                    $vv['ext'] = $this->prepareContactValue($vv['ext'], array('id' => 'ext'));
                                }

                                $field_html .= "<div class='" . (((int) $value_k + 1 !== $len ? 'margin-block semi bottom' : '')) . "'><div class='inline-block vtop' style='" . $this->generateFieldCss($cf['params']) . "'>" . $vv['value'] . "</div>" . (!empty($vv['ext']) ? "<em class='hint inline-block'>" . $vv['ext'] . "</em>" : "") . "</div>";
                            }
                            // Если все-таки не удалось получить данные поля, переходим к другому 
                            if ($break) {
                                continue;
                            }
                        } else {
                            $value = $this->prepareContactValue($value, $all_fields[$k], $contact_field_values[$k]);

                            $field_html .= "<div style='" . $this->generateFieldCss($cf['params']) . "'>" . $value . "</div>";
                        }

                        $field_html .= "</div></div>";

                        $output[$output_place] .= $field_html;
                    }
                }
            }
            foreach ($output as $k => $v) {
                if ($k == 'before_name') {
                    $html .= '$("#ticket .request-header .profile > .details > h3").before("' . $v . '");';
                } elseif ($k == 'after_name') {
                    $html .= '$(".source-name").after("' . $v . '");';
                } elseif ($k == 'before_tabs') {
                    $html .= '$("#ticket .request-header .profile > .details > .contact-tabs").before("' . $v . '");';
                } elseif ($k == 'after_tabs') {
                    $html .= '$("#ticket .request-header .profile > .details > .contact-tabs").after("' . $v . '");';
                } elseif ($k == 'bottom') {
                    $html .= '$("#ticket .h-request-left-fields").append("' . $v . '");';
                } elseif ($k == 'after_request_id') {
                    $html .= '$("#h-request-status-block").after("' . $v . '");';
                }
            }
        }

        return $html;
    }

    /**
     * Get additional fields, builded in constructor
     *
     * @param array $request_ids
     * @return array
     */
    public function getAdditionalConstructorFields($request_ids)
    {
        $result = [];
        // Получаем только активные поля
        $fields = (new helpdeskProPluginConstructorModel())->getFields(null, 1, 'additional');
        $additional_field_values = $this->getAdditionalRequestValues($request_ids, array_keys($fields));
        if ($additional_field_values) {
            $all_fields = $this->getAdditionalRequestFields(false);
            foreach ($additional_field_values as $request_id => $request_fields) {
                foreach ($request_fields as $k => $field) {
                    $key = 'a_' . $field['field'];
                    if (isset($all_fields[$key]) && $fields[$key] && trim($field['value'])) {
                        $af = $fields[$key];

                        // HTML для вывода
                        $field_html = "<div class='hp-contact-row margin-block semi' data-id='" . $field['field'] . "'>";
                        // Название поля
                        if (!empty($af['params']['name']['show']) || !isset($af['params']['name']['show'])) {
                            $field_html .= "<div class='hp-contact-name'" . (!empty($af['params']['name']['color']) ? " style='color:#" . self::escape($af['params']['name']['color']) . "'" : "") . ">" .
                                (!empty($af['params']['name']['value']) ? self::escape($af['params']['name']['value']) : self::escape($all_fields[$key]['name'])) .
                                ":</div>";
                        }
                        $field_html .= "<div class='hp-contact-value'>";
                        // Значение поля
                        $field_html .= "<div style='" . $this->generateFieldCss($af['params']) . "'>" . $field['value'] . "</div>";
                        $field_html .= "</div></div>";
                        $result[$request_id][$k] = $field_html;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $request_ids
     * @param array $additional_keys
     * @return array|null
     */
    private function getAdditionalRequestValues($request_ids, $additional_keys)
    {
        $additional_ids = array_map(function ($key) {
            return ltrim($key, 'a_');
        }, $additional_keys);
        return $additional_ids ? (new helpdeskRequestDataModel())->select('*')->where('request_id IN (?) AND field IN (?)', [$request_ids, $additional_ids])->fetchAll('request_id', 2) : [];
    }

    /**
     * Get fields from Admin - Fields constructor
     *
     * @param bool $name_as_id
     * @return array
     */
    public function getAdditionalRequestFields($name_as_id = true)
    {
        $fields = array();
        foreach (helpdeskRequestFields::getFields() as $fld_id => $fld) {
            /**
             * @var helpdeskRequestField $fld
             */
            $field = $fld->getInfo();
            $fields[$name_as_id ? $field['name'] : 'a_' . $field['id']] = $field;
        }
        return $fields;
    }

    public function getRequestTags($request_ids)
    {
        $request_tags_model = new helpdeskRequestTagsModel();
        $sql = "
            SELECT rt.*
            FROM " . $request_tags_model->getTableName() . " rt
            WHERE rt.request_id IN (i:id)
        ";
        return $request_tags_model->query($sql, array('id' => $request_ids))->fetchAll('request_id', 2);
    }

    /**
     * Get locale string for JS backend or frontend
     *
     * @param bool $backend
     * @return array|false|string
     */
    public function getJsLocaleStrings($backend = true)
    {
        // JS Локализция
        $name = 'pro';
        $js_locale_path = wa()->getAppPath("plugins/{$name}/locale/" . wa()->getLocale() . "/LC_MESSAGES/helpdesk_{$name}_js_" . ($backend ? 'backend' : 'frontend') . ".json", 'helpdesk');
        $js_locale_strings = [];
        if (file_exists($js_locale_path)) {
            $js_locale_strings = file_get_contents($js_locale_path);
        }
        return $js_locale_strings;
    }
}
