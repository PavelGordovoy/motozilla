<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPlugin extends waPlugin
{
    public function viewWorkflowAction($params)
    {
        if (waRequest::isMobile()) {
            // Фиксируем тулбар в мобильной версии
            if ($params['action'] instanceof helpdeskWorkflowBasicAction) {
                return <<<HTML
            <script>
                $(function() {
                    $.Redactor.settings = { toolbarFixed: false };
                })
            </script>
HTML;
            }
        }
    }

    public function viewAction($params)
    {
        if (!$params['action'] instanceof helpdeskRequestsInfoAction) {
            return false;
        }
        $hpf = new helpdeskProPluginFavouritesModel();
        $helper = new helpdeskProPluginHelper();
        $spam_model = new helpdeskProPluginSpamModel();

        $is_admin = wa()->getUser()->isAdmin();

        $settings = helpdeskProPluginHelper::getSettings();
        $client_contact_id = $params['action']->client ? $params['action']->client->getId() : 0;

        $source_name_block = $output_menu = $html = "";

        /* CRM tags */
        $crm_rights = wa()->getUser()->getRights('crm', 'backend');
        if (wa()->appExists('crm') && $settings['crm_tags'] && !empty($crm_rights)) {
            $crm_tags_name = (!empty($settings['crm_tags_name']) ? helpdeskProPluginHelper::escape($settings['crm_tags_name']) : _wp('CRM tags'));
            $source_name_block .= $helper->getCRMTagsHtml($client_contact_id, $crm_tags_name);
        }

        /* Spam prepare */
        $client = $params['action']->client;
        // Получаем email отправителя
        $email = $params['action']->request->getContactEmailFromData();
        if (empty($email) && $client) {
            $email = $client->get('email', 'default');
        }
        // Проверяем, находится ли email в спам листе
        $is_spam = 0;
        if ($email && $spam_model->isSpam($email)) {
            $is_spam = 1;
        }

        $html .= '<script src="' . $this->getPluginStaticUrl() . 'js/pro.requests.js?v=' . $this->getVersion() . '"></script>';
        $html .= '<link rel="stylesheet" type="text/css" href="' . $this->getPluginStaticUrl() . 'js/jquery-colorbox/colorbox.css">';
        $html .= '<script src="' . $this->getPluginStaticUrl() . 'js/jquery-colorbox/jquery.colorbox-min.js"></script>';

        $html .= "<script>";
        $html .= "new helpdeskProRequests({ 
                    favourite: " . json_encode($hpf->getFavourites($params['action']->request_id)) . ",
                    crmUrl: '" . wa()->getAppUrl('crm') . "',
                    crmTagsName: '" . (isset($crm_tags_name) ? $crm_tags_name : '') . "',
                    clientContactId: '" . $client_contact_id . "',
                    clientContactEmail: '" . ($is_admin ? $email : '') . "',
                    deleteSpamContacts: '" . (!empty($settings['delete_spam_contacts']) ? 1 : 0) . "',
                    spam: '" . $is_spam . "',
                    customRequestFields: " . json_encode($helper->getAdditionalRequestFields()) . "
                 });";

        if ($source_name_block) {
            $html .= '$(".source-name").after("' . $source_name_block . '");';
        }

        /* Contact constructor */
        if ($params['action']->client) {
            $html .= $helper->getContactConstructorFieldsHtml($params['action']->client);
        }

        /* License check */
        if ($settings['developer_key']) {
            $output_menu .= $helper->getDeveloperMenuHtml();
        }
        if (waRequest::isMobile()) {
            $html .= '$(".hu-tags-list").after("' . $output_menu . '")';
        } else {
            $html .= '$(".request-basic-info-block").append("' . $output_menu . '")';
        }
        $html .= '</script>';
        return $html;
    }

    public function jsonController(&$params)
    {
        if (!$params['controller'] instanceof helpdeskRequestsListController || empty($params['response']['request_ids'])) {
            return false;
        }

        $helper = new helpdeskProPluginHelper();
        // Отображение тегов в списке запросов
        if ($helper::getSettings('show_tags')) {
            $request_tags = $helper->getRequestTags($params['response']['request_ids']);
            $tags = (new helpdeskRequestTagsModel())->getTags($params['response']['request_ids']);
            foreach ($request_tags as &$request_tag) {
                if (isset($request_tag['tag_id']) && isset($tags[$request_tag['tag_id']])) {
                    $request_tag['name'] = $tags[$request_tag['tag_id']];
                }
            }

            $params['response']['request_tags'] = $request_tags;
            $params['response']['tags'] = $tags;
        }


        // Дополнительные поля из конструктора полей
        if ($additional_fields = $helper->getAdditionalConstructorFields($params['response']['request_ids'])) {
            $params['response']['additional_fields'] = $additional_fields;
        }
    }

    public function backendLayout($params)
    {
        $this->addJs('js/pro.js');
        $this->addJs('js/translate.js');
        $this->addCss('css/pro.css');

        $settings = helpdeskProPluginHelper::getSettings();

        $html = "<script>";
        if (!empty($settings['ignore_ajax_errors'])) {
            $html .= "$.wa.helpdesk_controller.ignore_ajax_errors = true;";
        }
        $isBackendSourceAvailable = helpdeskSourceHelper::isBackendSourceAvailable();

        $html .= "$.helpdesk_pro.initLocale(".(new helpdeskProPluginHelper())->getJsLocaleStrings().");";
        if (waRequest::isMobile()) {
            $html .= "$.helpdesk_pro.initMobile();";
        }

        $html .= "
            $(function () {
                $.helpdesk_pro.init({ 
                    ignoreAjaxErrors: " . (!empty($settings['ignore_ajax_errors']) ? 1 : 0) . ",
                    createNewRequest: " . (!$isBackendSourceAvailable ? 0 : $isBackendSourceAvailable) . ",
                    localeStrings: " . (new helpdeskProPluginHelper())->getJsLocaleStrings() . "
                });
            });"
            . "</script>";

        /* CSS settings */
        $helper = new helpdeskProPluginHelper();
        $html .= "<style>#ticket.hp-spam #h-request-top-block:after, .requests-table tr.hp-spam td.description-col:after, .requests-table tr.hp-spam .summary-col:after { content: '" . _wp('SPAM') . "'; }";
        $html .= $helper->getCSSFromSettings($settings);
        $html .= '</style>';

        return $html;
    }

    public function sidebar()
    {
        $contacts_rights = wa()->getUser()->getRights('contacts', 'backend');

        $html = "<div class='block'><h5 class='heading'>" . _wp('Helpdesk PRO') . "</h5><ul class='menu-v'>";
        if (wa()->getUser()->isAdmin()) {
            $html .= "<li><a href='#/proPlugin/spam/' title='" . _wp('Spam') . "'><i class='icon16 spam'></i> " . _wp('Spam') . "</a></li>";
        }
        if ($contacts_rights) {
            $html .= "<li><a href='#/proPlugin/constructor/' title='" . _wp('Contact fields constructor') . "'><i class='icon16 application-form'></i> " . _wp('Contact fields constructor') . "</a></li>";
        }
        $html .= "<li><a href='#/proPlugin/additionalConstructor/' title='" . _wp('Additional fields constructor') . "'><i class='icon16 application-form'></i> " . _wp('Additional fields constructor') . "</a></li>";
        $html .= "<li><a href='#/proPlugin/attachments/' title='" . _wp('Attachments') . "'><i class='icon16 archive-text'></i> " . _wp('Attachments') . "</a></li>";
        $html .= "<li><a href='#/proPlugin/settings/' title='" . _wp('Settings') . "'><i class='icon16 settings'></i> " . _wp('Settings') . "</a></li>";
        $html .= "</ul></div>";
        return $html;
    }

    public function requestsDelete($request_id)
    {
        $hpf = new helpdeskProPluginFavouritesModel();
        $hpf->deleteByField('request_id', $request_id);
    }

    public function requestCreated($message)
    {
        $this->createNewRequest($message);
        // Запросы, созданные через бекенд не трогаем, потому что у нас нет возможности сделать перенаправление на другую страницу
        // Если удалить запрос, тогда скрипт попытается открыть несуществующий запрос
        $source_type = $message['source']->getSourceType();
        $type = $source_type->getType();
        if ($type == 'email' || ($type == 'form' && wa()->getEnv() == 'frontend')) {
            $this->spamFilter($message);
        }
    }

    public function frontendError($params)
    {
        // Если пользователь, email которого находится в спаме, пытался сделать запрос, предупреждаем его, что
        // он временно не может осуществить это действие
        if (waRequest::param('action') == 'myRequest') {
            $request_id = waRequest::param('id');
            $is_spam = wa()->getStorage()->get('helpdesk_spam_' . wa()->getUser()->getId() . '_' . $request_id);
            if ($is_spam) {
                $params['message'] = _wp("You are temporarily not allowed to send requests");
            }
        }
    }

    private function createNewRequest($message)
    {
        $message_id = waRequest::get('message_id', 0, waRequest::TYPE_INT);
        $request_id = waRequest::get('request_id', 0, waRequest::TYPE_INT);
        $create_new_request = waRequest::post('create_new_request', 0, waRequest::TYPE_INT);
        $add_current_attach = waRequest::post("add_current_attach");
        $current_request_state_id = waRequest::post("current_request_state_id");

        // Если создается новый запрос из сообщения, получаем необходимые данные запроса и сообщения
        if ($create_new_request) {
            $hrl = new helpdeskRequestLogModel();
            $logs = $hrl->getByRequestWithParams($request_id);
            if (isset($logs[$message_id])) {

                // Данные запроса
                $c = helpdeskRequestsCollection::create(array(
                    array(
                        'name' => 'id',
                        'params' => array($request_id),
                    )
                ));
                $request = helpdeskRequest::prepareRequests($c->getRequests());
                $request = !empty($request) ? reset($request) : array();

                // Сообщение
                $old_message = $logs[$message_id];
                if (!empty($old_message['text'])) {
                    if (!empty($request['client_contact_id']) && $old_message['actor_contact_id'] != $request['client_contact_id']) {
                        $old_message['text'] = preg_replace('~(\r?\n\r?|<div>|<br ?/?>)\s*(<(font|span)[^>]*>\s*)*-{2,}\s*(</(font|span)[^>]*>\s*)*(<br ?/?>|</div>|\r?\n\r?).*$~isum', '', $old_message['text']);
                        $old_message['text'] = preg_replace('~(\r?\n\r?|<div>|<br ?/?>)\s*(<(font|span)[^>]*>\s*)*С уважением, .*$~isum', '', $old_message['text']);
                    }
                    $old_message['text'] = helpdeskRequest::formatHTML($old_message);
                    $old_message['text'] = helpdeskRequest::stripBlockquotes($old_message['text']);
                }
            }
        }

        // Проверяем необходимость прикрепления файлов сообщения к новому запросу
        if (!empty($add_current_attach) && !empty($old_message['params']['attachments'])) {
            $attachments = @unserialize($old_message['params']['attachments']);
            // Если удалось успешно получить информацию о прикрепленных файлах
            if ($attachments) {
                foreach ($attachments as $k => $attach) {
                    $file = helpdeskRequest::getAttachmentsDir($request_id, $message_id) . '/' . $attach['file'];
                    if (!file_exists($file)) {
                        unset($attachments[$k]);
                        continue;
                    }
                    // Создаем каталог для изображений
                    $request_attach_dir = helpdeskRequest::getAttachmentsDir($message['request']->id) . '/';
                    try {
                        // Копируем файлы
                        waFiles::copy($file, $request_attach_dir . $attach['file']);
                    } catch (Exception $e) {
                        unset($attachments[$k]);
                    }
                }
                // Если удалось успешно скопировать файлы для нового запроса, запишем информацию в БД
                if ($attachments) {
                    $hrp = new helpdeskRequestParamsModel();
                    // Проверяем есть ли у запроса вложения
                    $request_has_attachments = $hrp->getByField(array('request_id' => $message['request']->id, 'name' => 'attachments'));
                    $request_attachments = $request_has_attachments ? @unserialize($request_has_attachments['value']) : array();
                    $attachments = array_merge($attachments, is_array($request_attachments) ? $request_attachments : array());
                    // Сохраняем вложения
                    if ($request_has_attachments) {
                        $hrp->updateByField(array('request_id' => $message['request']->id, 'name' => 'attachments'), array('value' => @serialize($attachments)));
                    } else {
                        $hrp->insert(array('request_id' => $message['request']->id, 'name' => 'attachments', 'value' => @serialize($attachments)));
                    }

                    foreach ($attachments as $data) {
                        $attach_id = $data['file'];
                        $data['name'] = ifset($data['name'], $attach_id);
                        $link = helpdeskRequest::getAttachmentUrl($message['request']->id, $attach_id);
                        if (isset($data['datetime'])) {
                            $link .= '&datetime=' . $data['datetime'];
                        }

                        if (isset($data['cid'])) {
                            $old_message['text'] = str_replace('cid:' . $data['cid'], $link, $old_message['text']);
                        }
                    }
                }
            }
        }

        if ($create_new_request) {
            $request_model = new helpdeskRequestModel();
            $request_model->updateById($message['request']->id, array('text' => $old_message['text']));
        }

        // Изменяем статус старого запроса
        if (!empty($current_request_state_id) && $request) {

            // Проверяем доступ пользователя к состоянию запроса
            $user_ws = helpdeskHelper::getAllWorkflowsWithStates(false);
            if (strpos($current_request_state_id, '@') !== false) {
                list($new_workflow_id, $new_state_id) = explode('@', $current_request_state_id);
                if (!isset($user_ws[$new_workflow_id]['states'][$new_state_id]) || ((int) $request['workflow_id'] === (int) $new_workflow_id && $request['state_id'] == $new_state_id)) {
                    return;
                }
            } else {
                return;
            }

            $field = 'state_id';
            $value = $new_state_id;
            $action_id_prefix = '!bulk_change_';

            $wf = null;
            if ($new_workflow_id && !wa_is_int($new_workflow_id)) {
                $new_workflow_id = null;
            }
            if ($new_workflow_id) {
                try {
                    $wf = helpdeskWorkflow::get($new_workflow_id);
                } catch (Exception $e) {
                    $new_workflow_id = null;
                }
            }

            // Create request log entries
            $fake_state_id = uniqid('fake');
            if (!$request_id || !wa_is_int($request_id)) {
                return;
            }
            $log = array(
                'request_id' => $request_id,
                'datetime' => date('Y-m-d H:i:s'),
                'action_id' => $action_id_prefix . $field,
                'actor_contact_id' => wa()->getUser()->getId(),
                'text' => '',
                'to' => '',
                'assigned_contact_id' => null,
                'before_state_id' => $fake_state_id,
                'after_state_id' => $value,
                'workflow_id' => $new_workflow_id
            );

            $rlm = new helpdeskRequestLogModel();
            $rlm->insert($log);
            $rlm->exec(
                "UPDATE helpdesk_request_log AS rl
                    JOIN helpdesk_request AS r
                        ON r.id=rl.request_id
                SET r.last_log_id=rl.id,
                    rl.before_state_id=r.state_id
                WHERE rl.request_id IN (i:ids)
                    AND rl.before_state_id=:fake_state_id", array(
                    'ids' => $request_id,
                    'fake_state_id' => $fake_state_id,
                )
            );

            // Update helpdesk_request
            $rm = new helpdeskRequestModel();

            if ($new_workflow_id) {
                $request = $rm->getById($request_id);
                if ($request['workflow_id'] != $new_workflow_id) {
                    $rlm->exec(
                        "INSERT IGNORE INTO helpdesk_request_log_params (request_log_id, name, value)
                             SELECT r.last_log_id, 'old_workflow_id', r.workflow_id
                             FROM helpdesk_request AS r
                             WHERE r.id IN (:ids)", array(
                            'ids' => $request_id
                        )
                    );
                    $rlm->exec(
                        "INSERT IGNORE INTO helpdesk_request_log_params (request_log_id, name, value)
                             SELECT r.last_log_id, 'new_workflow_id', :wid
                             FROM helpdesk_request AS r
                             WHERE r.id IN (:ids)", array(
                            'ids' => $request_id,
                            'wid' => $new_workflow_id,
                        )
                    );
                }
            }

            $upd = array(
                $field => $value,
            );
            if ($new_workflow_id) {
                $upd['workflow_id'] = $new_workflow_id;
            }
            $upd['updated'] = date('Y-m-d H:i:s');
            $rm->updateById($request_id, $upd);

            wao(new helpdeskUnreadModel())->markUnreadBulk(array($request_id), '');
        }
    }

    private function spamFilter($message)
    {
        // Получаем email отправителя
        $client = $message['client_contact'];
        $email = $message['request']->getContactEmailFromData();
        if (empty($email) && $client) {
            $email = $client->get('email', 'default');
        }
        if (!$email) {
            return;
        }

        $spam_model = new helpdeskProPluginSpamModel();
        if ($spam_model->isSpam($email)) {
            $spam_model->deleteSpamRequest($message['request']->id, $email);
            // Если запрос был с витрины, сохраняем данные в сессию, чтобы вывести сообщение об ошибке
            if (wa()->getEnv() == 'frontend') {
                wa()->getStorage()->write('helpdesk_spam_' . $client->getId() . '_' . $message['request']->id, 1);
            }
        }
    }

}
