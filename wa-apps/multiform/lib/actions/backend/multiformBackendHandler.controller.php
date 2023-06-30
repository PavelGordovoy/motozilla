<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformBackendHandlerController extends waJsonController
{

    public function execute()
    {
        $method_name = waRequest::post("data", "default") . 'Action';

        if (method_exists($this, $method_name)) {
            $this->$method_name();
        } else {
            $this->errors = 1;
        }
    }

    private function defaultAction()
    {

    }

    /**
     * Change order of actions
     */
    private function actionSortAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $ids = waRequest::post("ids");
            if ($ids) {
                $mf = new multiformActionFormModel();
                $mag = new multiformActionGroupModel();
                foreach ($ids as $k => $action_id) {
                    // Если перед нами группа
                    if ($action_id < 0) {
                        $mag->updateById($action_id * (-1), array("sort" => (int) $k));
                    } // Если перед нами действие
                    else {
                        $mf->updateById($action_id, array("sort" => (int) $k));
                    }
                }
            }
        }
    }

    /**
     * Add/edit action group
     */
    private function editActionGroupAction()
    {
        // ID формы
        $id = waRequest::post("form_id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $group_id = waRequest::post("group_id", 0, waRequest::TYPE_INT);
            $group_name = waRequest::post("name");
            $group_name = $group_name ? $group_name : _wp('New group');
            if (mb_strlen($group_name > 50, 'UTF-8')) {
                $group_name = mb_substr($group_name, 0, 50, 'UTF-8');
            }
            $mag = new multiformActionGroupModel();
            if ($group_id == 'new') {
                $id = $mag->insert(array("name" => $group_name, "form_id" => $id));
            } elseif ($group_id) {
                $mag->updateById($group_id, array("name" => $group_name));
                $id = $group_id;
            }
            $this->response = array('id' => $id, 'name' => multiformHelper::secureString($group_name));
        }
    }

    /**
     * Collapse action group
     */
    private function actionGroupCollapseAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $group_id = waRequest::post("group_id", 0, waRequest::TYPE_INT);
            $collapsed = waRequest::post("collapsed", 0);
            wao(new multiformActionGroupModel())->updateById($group_id, array("collapsed" => $collapsed));
        }
    }

    /**
     * Collapse action group
     */
    private function deleteActionGroupAction()
    {
        // ID формы
        $id = waRequest::post("form_id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $group_id = waRequest::post("group_id", 0, waRequest::TYPE_INT);
            wao(new multiformActionGroupModel())->delete($group_id, $id);
        }
    }

    /**
     * Changing of action group ID
     */
    private function changeActionGroupAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $group_id = waRequest::post("group_id", 0, waRequest::TYPE_INT);
            $action_id = waRequest::post("action_id", 0, waRequest::TYPE_INT);
            wao(new multiformActionFormModel())->updateByField(array("form_id" => $id, "id" => $action_id), array("group_id" => $group_id));
        }
    }

    /**
     * Delete action
     */
    private function deleteActionAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $action_id = waRequest::post("action_id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form) && $action_id) {
            $mafm = new multiformActionFormModel();
            $mafm->delete($id, $action_id);
        } else {
            $this->errors = 1;
        }
    }

    /**
     * Save action field
     */
    private function saveActionFieldAction()
    {
        // ID формы
        $id = waRequest::post("form_id", 0, waRequest::TYPE_INT);
        $action_id = waRequest::post("action_id", 0, waRequest::TYPE_INT);
        $name = waRequest::post("name");
        $value = waRequest::post("value");

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form) && $action_id && in_array($name, array('custom_name', 'custom_description', 'custom_color', 'auto_execute'))) {
            $mafsm = new multiformActionFormSettingsModel();
            $result = $mafsm->updateByField(array("form_id" => $id, "action_id" => $action_id, "param" => $name), array('value' => $value));
            if ($result) {
                $this->response = multiformHelper::secureString($value);
            } else {
                $this->errors = 1;
            }
        } else {
            $this->errors = 1;
        }
    }

    /**
     * Change action status
     */
    private function actionStatusAction()
    {
        // ID формы
        $id = waRequest::post("form_id", 0, waRequest::TYPE_INT);
        $action_id = waRequest::post("action_id", 0, waRequest::TYPE_INT);
        $value = waRequest::post("value", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form) && $action_id) {
            $mafm = new multiformActionFormModel();
            $result = $mafm->updateByField(array("id" => $action_id, "form_id" => $id), array('status' => $value));
            if ($result) {
                $this->response = $value;
            } else {
                $this->errors = 1;
            }
        } else {
            $this->errors = 1;
        }
    }

    /**
     * Change form status
     */
    private function formStatusAction()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $mf = new multiformFormModel();
        $form = $mf->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $status = waRequest::post("status", -1);
            if ($status >= 0) {
                $mf->updateById($id, array("status" => $status));
                $this->response = (int) $status;
            }
        } else {
            $this->errors = 1;
        }
    }

    /**
     * Copy form
     */
    private function copyFormAction()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $mf = new multiformFormModel();
        $form = $mf->getFormSettings($id);
        $html = "";
        if (multiformHelper::hasFullFormAccess($form)) {
            $clon_id = $mf->duplicate($id);
            $clon_form = $mf->getFormSettings($clon_id);
            $contact_rights = wa()->getUser()->getRights('contacts');
            $theme_model = new multiformThemeModel();
            $themes = $theme_model->getThemes();

            $html .= "<tr data-id=\"{$clon_form['id']}\">
                <td><a href=\"#/change/status/{$clon_form['id']}\" class='js-action' title='" . _w('Change status') . "' data-type=\"formStatus\"><i class=\"icon-custom lightbulb" . (!$clon_form['status'] ? "-off" : "") . "\"></i></a></td>
                <td>
                    <a href=\"#/copy/form/{$clon_form['id']}\" class='js-action' title='" . _w('Duplicate') . "'><i class=\"icon-custom copy\"></i></a>
                    <a href=\"?module=form&action=edit&id={$clon_form['id']}\" title='" . multiformHelper::secureString($clon_form['name']) . "'>" . (!$clon_form['type'] ? "<i class=\"icon16 lock\" title='" . _w('Private form') . "'></i> " : "") . ($clon_form['start'] && strtotime($clon_form['start']) > time() ? "<i class=\"icon16 clock\" title='" . _w('Has schedule') . "'></i> " : "") . ($clon_form['end'] && strtotime($clon_form['end']) < time() ? "<i class=\"icon16 exclamation-red\" title='" . _w('Form is hidden according to schedule') . "'></i> " : "") . ($clon_form['name'] ? multiformHelper::secureString($clon_form['name']) : _w('No name')) . "</a></td>
                <td>0</td>
                <td>
                    <a href=\"?module=form&action=edit&id={$clon_form['id']}#/page/records/{$clon_form['id']}/\" title='" . _w('records') . "'><i class=\"icon16 view-table\"></i> " . _w('records') . "</a>
                    | <a href=\"?module=form&action=edit&id={$clon_form['id']}\" title='" . _w('edit') . "'><i class=\"icon16 edit\"></i> " . _w('edit') . "</a>
                </td>
                <td>" . ($clon_form['create_datetime'] ? wa_date('date', $clon_form['create_datetime']) . "<br><span class=\"small\">" . wa_date('fulltime', $clon_form['create_datetime']) . "</span>" : "") . "</td>
                <td></td>
                " . (!empty($contact_rights['backend']) ? "<td><a href='" . wa()->getConfig()->getBackendUrl(true) . "contacts/#/contact/{$clon_form['create_contact_id']}'>" . wa()->getUser()->get('name') . "</a></td>" : "") . "
                <td class=\"align-center\">" . ($clon_form['start'] ? wa_date('date', $clon_form['start']) . "<br><span class=\"small\">" . wa_date('fulltime', $clon_form['start']) . "</span>" : "") . "</td>
                <td class=\"align-center\">" . ($clon_form['end'] ? wa_date('date', $clon_form['end']) . "<br><span class=\"small\">" . wa_date('fulltime', $clon_form['end']) . "</span>" : "") . "</td>";
            if (wa()->getUser()->getRights('multiform', 'appearance') || wa()->getUser()->isAdmin('multiform')) {
                $html .= "<td>
                            <select onchange=\"$.backend.changeTheme(this)\">
                                <option value=\"0\">" . _w('Default theme') . "</option>";
                if (!empty($themes)) {
                    foreach ($themes as $theme) {
                        $html .= "<option value=\"{$theme['id']}\"" . (!empty($clon_form['theme_id']) && $clon_form['theme_id'] == $theme['id'] ? " selected" : "") . ">" . multiformHelper::secureString($theme['name']) . "</option>";
                    }
                }
                $html .= "</select>
                        </td>";
            }
            $html .= "<td><a href=\"#/delete/form/{$clon_form['id']}\" class=\"js-action\" title='" . _w('Delete') . "'><i class=\"icon16 delete\"></i></a></td>
            </tr>";
        }
        $this->response = $html;
    }

    private function deleteFormAction()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $mf = new multiformFormModel();
        $form = $mf->getFormSettings($id);
        if (multiformHelper::hasFullFormAccess($form)) {
            $mf->delete($id);
        }
    }

    /**
     * Change field status
     */
    private function fieldStatusAction()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $status = waRequest::post("status", -1);
        if ($status >= 0) {
            $mf = new multiformFieldModel();
            $field = $mf->getById($id);
            if ($field) {
                $form_id = $field['form_id'];
                $mform = new multiformFormModel();
                $form = $mform->getFormSettings($form_id);
                if ($form && multiformHelper::hasFullFormAccess($form)) {
                    $mf->updateById($id, array("status" => $status));
                    $this->response = (int) $status;
                } else {
                    $this->errors = 1;
                }
            } else {
                $this->errors = 1;
            }
        }
    }

    /**
     * Update section for the field
     */
    private function updateSectionAction()
    {
        $field_id = waRequest::post("field_id", 0, waRequest::TYPE_INT);
        $section_id = waRequest::post("section_id", 0, waRequest::TYPE_INT);

        $mfp = new multiformFieldParamsModel();
        $mfp->deleteByField(array("field_id" => $field_id, "param" => "additional", "ext" => "section"));
        if ($section_id) {
            $mfp->insert(array("field_id" => $field_id, "param" => "additional", "ext" => "section", "value" => $section_id));
        }
    }

    /**
     * Change condition status
     */
    private function conditionStatusAction()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $status = waRequest::post("status", -1, waRequest::TYPE_INT);
        if ($id && $status >= 0) {
            $mform = new multiformFormModel();
            $form = $mform->getFormSettings($id);
            if ($form && multiformHelper::hasFullFormAccess($form)) {
                $mfp = new multiformFormParamsModel();

                $mfp->exec("INSERT INTO {$mfp->getTableName()} (`form_id`, `param`, `value`) VALUES ('" . $id . "', 'condition_status', '" . $status . "') ON DUPLICATE KEY UPDATE value='" . $status . "'");
                $this->response = (int) $status;
            } else {
                $this->errors = 1;
            }
        }
    }

    /**
     * Add field
     */
    private function addFieldAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            // Тип поля
            $type = waRequest::post("type");
            $mfield = new multiformFieldModel();
            $field_id = $mfield->createField($type, $id);
            if ($field_id) {
                $this->response['html'] = multiformFormBuilder::getFullControl($field_id);
                $this->response['id'] = $field_id;
            }
        } else {
            $this->errors = 1;
        }
    }

    /**
     * Get field
     */
    private function getFieldAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        // ID поля
        $field_id = waRequest::post("field_id", 0, waRequest::TYPE_INT);

        $mfield = new multiformFieldModel();
        $field = $mfield->getDataById($field_id, 'field', array("form_id" => $id));

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($field['form_id']);
        if ($form && multiformHelper::hasFullFormAccess($form) && $field) {
            $this->response['id'] = $field['unique_id'];
            if ($field['type'] !== 'section') {
                $this->response['html'] = multiformFormBuilder::getControl($field) . multiformFormBuilder::getCss(true) . multiformFormBuilder::getJs(true);
                // Если необходимо получить информацию и о настройках поля
                if (waRequest::post("update_settings")) {
                    $this->response['settings_html'] = multiformFormBuilder::getSettingsControl($field);
                }
            }
        } else {
            $this->errors = 1;
        }
    }

    /**
     * Change field order
     */
    private function fieldSortAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $sort = waRequest::post("sort");
            if ($sort) {
                $mf = new multiformFieldModel();
                $field_ids = $mf->select("id")->where("form_id = i:form", array("form" => $id))->fetchAll('id');
                foreach ($sort as $field_id => $sort_id) {
                    if (isset($field_ids[$field_id])) {
                        $mf->updateById($field_id, array("sort" => (int) $sort_id));
                    }
                }
            }
        }
    }

    /**
     * Delete form field
     */
    private function deleteFieldAction()
    {
        // ID поля
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $mf = new multiformFieldModel();

        $form_id = $mf->select("form_id")->where("id = i:id", array("id" => $id))->fetchField();
        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($form_id);
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            $mf->delete($id);
        }
    }

    /**
     * Copy form field
     */
    private function copyFieldAction()
    {
        // ID поля
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        $mf = new multiformFieldModel();
        $field = $mf->getById($id);
        if ($field) {
            $form_id = $field['form_id'];
            $mform = new multiformFormModel();
            $form = $mform->getFormSettings($form_id);
            if ($form && multiformHelper::hasFullFormAccess($form)) {
                if ($clon_id = $mf->duplicate($id)) {
                    $clon_field = $mf->getDataById($clon_id);
                    if ($clon_field['type'] == 'section') {
                        $fields = $mf->getDataById($form_id, 'form');
                        $clon_field['fields'] = $mf->getTree($fields, $clon_id);
                        $this->response = multiformFormBuilder::getFieldsControlFromTree(array($clon_field));
                    } else {
                        $this->response = multiformFormBuilder::getFullControl($clon_id);
                    }
                }
            }
        }
    }

    /**
     * Get record form fields
     */
    private function editRecordAction()
    {
        // ID формы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        // ID записи
        $record_id = waRequest::post("record_id", 0, waRequest::TYPE_INT);

        if ($id && $record_id) {
            $mr = new multiformRecordModel();
            $record = $mr->getRecord($record_id);
            $form = (new multiformFormModel())->getFullForm($id);

            if ($form && multiformHelper::recordAccess($form) > 1 && $record['form_id'] == $form['id']) {
                $view = new waSmarty3View(wa());
                $view->assign('form', $form);
                $view->assign('record', $record);
                $view->assign('custom_params', $mr->getCustomParams($id));

                $this->response = $view->fetch(wa()->getAppPath('templates/include.edit_record.html', 'multiform'));
            } else {
                $this->errors = 1;
            }
        }
    }

    /**
     * Delete file
     */
    private function deleteFileAction()
    {
        // Хеш файла
        $hash = waRequest::post("hash");
        if ($hash) {
            $mf = new multiformFilesModel();
            $file = $mf->getFileByHash($hash);
            if ($file) {
                $form = (new multiformFormModel())->getFormByFile($file);
                if ($form && multiformHelper::recordAccess($form) == 3) {
                    $mf->deleteByField("hash", $hash);
                    waFiles::delete(multiformFiles::getFilePath($file), true);
                    waFiles::delete(multiformFiles::getFilePath($file, true), true);
                }
            }
        }
    }

    /**
     * Send record to email list
     */
    private function recordSendEmailAction()
    {
        $record_id = waRequest::post("record_id", 0, waRequest::TYPE_INT);
        $emails = waRequest::post("emails", "");
        if ($record_id && $emails) {
            $mr = new multiformRecordModel();
            $record = $mr->getRecord($record_id);

            $mform = new multiformFormModel();
            $form = $mform->getFullForm($record['form_id'], true);

            if ($record && $form && multiformHelper::recordAccess($form) > 1 && multiformHelper::notificationsAccess() >= 3) {
                $email_validator = new waEmailValidator();
                $email_list = array();
                foreach (explode(",", $emails) as $email) {
                    $email = trim($email);
                    if ($email_validator->isValid($email) && count($email_list) < 10) {
                        $email_list[] = $email;
                    }
                }

                if ($email_list) {

                    // Параметры для уведомлений
                    $template_params = array('record' => $record, "form" => $form);

                    // Добавляем вложения к письму
                    $attachments = $mr->getRecordAttachments($record, $form);

                    // Формируем сообщение
                    $collection = new waContactsCollection('/id/' . $record['create_contact_id'] . ',' . $record['update_contact_id'] . '/', array('check_rights' => true));
                    $contacts = $collection->getContacts('id,name');

                    $params = array("send_to" => array_shift($email_list), "email_bcc" => $email_list, "attachments" => $attachments, "confirmation_subject" => multiformTemplate::process(_w("You've got new message: {Request:ID}"), $template_params));

                    // Тема уведомления
                    $params['confirmation_subject'] = !empty($form['email_subject_record']) ? multiformTemplate::process($form['email_subject_record'], $template_params) : multiformTemplate::process(_w("You've got new notification: {Request:ID}"), $template_params);

                    // Email отправителя
                    if (!empty($form['email_sender'])) {
                        $params['confirmation_sender'] = $form['email_sender'];
                    }

                    // Имя отправителя
                    if (!empty($form['email_sender_name_record'])) {
                        $params['confirmation_sender_name'] = multiformTemplate::process($form['email_sender_name_record'], $template_params);
                    }

                    // Reply to
                    if (!empty($form['reply_to']) && !empty($form['fields'][$form['reply_to']]) && !empty($record['fields'][$form['fields'][$form['reply_to']]['id']])) {
                        $params['confirmation_reply_to'] = $params['confirmation_sender'] = $record['fields'][$form['fields'][$form['reply_to']]['id']];
                    }

                    $view = wa()->getView();
                    $custom_params = $mr->getCustomParams($form['id']);

                    $view->assign('custom_params', $custom_params);
                    $view->assign('record', $record);
                    $view->assign('form', $form);
                    $view->assign('is_mail', 1);
                    $view->assign('show_hidden', 1);
                    $view->assign("contacts", $contacts);
                    $view->assign("domain", wa('multiform')->getConfig()->getDomain());

                    $email_message = (new multiformTemplate())->getEmailMessage($view, $form, $template_params, $params['confirmation_subject']);

                    $view->clearAllAssign();

                    multiformNotification::send(multiformTemplate::process($email_message, $template_params), $params, !empty($form['plain_text']) ? 'text/plain' : 'text/html');
                }
            } else {
                $this->errors = _w("Access denied");
            }
        } else {
            $this->errors = _w("Record ID is empty");
        }
    }

    /**
     * Delete record
     */
    private function deleteRecordAction()
    {
        $record_id = waRequest::post("record_id");
        $mr = new multiformRecordModel();

        $mform = new multiformFormModel();
        if (!waRequest::post("all")) {
            $r_id = is_array($record_id) ? reset($record_id) : $record_id;
            $form_id = $mr->select("form_id")->where("id = i:id", array("id" => $r_id))->fetchField();
        } else {
            $form_id = (int) waRequest::post("all");
        }
        $form = $mform->getFormSettings($form_id);

        if ($form && multiformHelper::recordAccess($form) == 3) {
            if (waRequest::post("all")) {
                $record_id = $mr->select("id")->where("form_id = '" . (int) $form['id'] . "'")->fetchAll(null, true);
            }
            $mr->delete($record_id);
        }
    }

    /**
     * Save record comment
     */
    private function saveRecordCommentAction()
    {
        $mr = new multiformRecordModel();
        $mf = new multiformFormModel();

        $record_id = waRequest::post("record_id", 0, waRequest::TYPE_INT);
        $name = waRequest::post("name", '', waRequest::TYPE_STRING_TRIM);
        $comment = waRequest::post("comment", '', waRequest::TYPE_STRING_TRIM);

        if (!$name) {
            $this->errors = _w("Specify name");
            return;
        }
        if (!$comment) {
            $this->errors = _w("Specify comment");
            return;
        }

        $record = $mr->getRecord($record_id);
        $form = $mf->getFormSettings($record['form_id']);

        if ($record && $form && multiformHelper::recordAccess($form) > 1) {
            $mc = new multiformRecordCommentModel();
            $comment = nl2br($comment);
            $date = date("Y-m-d H:i:s");
            $comment_id = $mc->insert(array("record_id" => $record_id, "name" => $name, "comment" => $comment, "create_datetime" => $date, "create_contact_id" => wa()->getUser()->getId()));

            $this->response = "<div class=\"record-comment\">
                                <a href=\"#/record/deleteComment/{$comment_id}\" title='" . _w('Delete') . "' class='js-action delete'><i class=\"icon16 no\"></i></a>
                                <div class=\"small\"><a class=\"bold\" href=\"" . wa()->getConfig()->getBackendUrl(true) . "contacts/#/contact/" . wa()->getUser()->getId() . "\" title=\"" . multiformHelper::secureString($name) . "\">" . multiformHelper::secureString($name) . "</a> " . wa_date('humandate', $date) . " " . wa_date('fulltime', $date) . "</div>
                                <div class=\"margin-block\" style=\"font-size: 12px\">{$comment}</div>
                            </div>";

            // Отправка уведомления
            if (!empty($form['new_comments'])) {
                $email_validator = new waEmailValidator();
                // Email получателей
                $email_list = array();
                if (!empty($form['email'])) {
                    foreach (explode(",", $form['email']) as $email) {
                        $email = trim($email);
                        if ($email_validator->isValid($email)) {
                            $email_list[] = $email;
                        }
                    }
                }

                if ($email_list) {
                    $params = array("send_to" => $email_list);

                    // Email отправителя
                    if (!empty($form['email_sender'])) {
                        $params['confirmation_sender'] = $form['email_sender'];
                    }

                    // Имя отправителя
                    if (!empty($form['email_sender_name_comment'])) {
                        // Параметры для уведомлений
                        $template_params = array('record' => $record);
                        $params['confirmation_sender_name'] = multiformTemplate::process($form['email_sender_name_comment'], $template_params);
                    }

                    // Тема уведомления
                    $params['confirmation_subject'] = !empty($form['email_subject_comment']) ? multiformTemplate::process($form['email_subject_comment'], $template_params) : _w("New comment on record") . ": " . multiformHelper::formatRecordId($record_id, $form);

                    // Email CC
                    if (!empty($form['email_cc'])) {
                        $email_list = array();
                        foreach (explode(",", $form['email_cc']) as $cc) {
                            $cc = trim($cc);
                            if ($email_validator->isValid($cc)) {
                                $email_list[] = $cc;
                            }
                        }
                        $params['email_cc'] = $email_list;
                    }

                    // Email BCC
                    if (!empty($form['email_bcc'])) {
                        $email_list = array();
                        foreach (explode(",", $form['email_bcc']) as $bcc) {
                            $bcc = trim($bcc);
                            if ($email_validator->isValid($bcc)) {
                                $email_list[] = $bcc;
                            }
                        }
                        $params['email_bcc'] = $email_list;
                    }

                    $html = multiformHelper::secureString($name) . ' ' . _w('commented') . ': ' . $comment;

                    multiformNotification::send($html, $params, 'text/plain');
                }
            }

            // SMS уведомления
            if (!empty($form['new_comments_sms']) && !empty($form['sms_phone'])) {
                $text = _w("New comment on record ") . " " . multiformHelper::formatRecordId($record_id, $form) . ". " . $name . ' ' . _w('commented') . ':' . strip_tags($comment);
                if ($text) {
                    $sms_params = array(
                        "to" => $form['sms_phone'],
                        "from" => isset($form['sms_from']) ? $form['sms_from'] : null
                    );
                    multiformNotification::sendSms($text, $sms_params);
                }
            }
        } else {
            $this->errors = _w("Access denied");
        }
    }

    /**
     * Delete record comment
     */
    private function deleteRecordCommentAction()
    {
        $comment_id = waRequest::post("comment_id", 0, waRequest::TYPE_INT);
        if ($comment_id) {
            $mr = new multiformRecordModel();
            $mc = new multiformRecordCommentModel();
            $record_id = $mc->select("record_id")->where("id = i:id", array("id" => $comment_id))->fetchField();
            $form_id = $mr->select("form_id")->where("id = i:id", array("id" => $record_id))->fetchField();
            $mform = new multiformFormModel();
            $form = $mform->getFormSettings($form_id);

            if ($form && multiformHelper::recordAccess($form) > 1) {
                if (multiformHelper::recordAccess($form) == 3) {
                    $filter = array("id" => $comment_id, "record_id" => $record_id);
                } elseif (multiformHelper::recordAccess($form) == 2) {
                    $filter = array("id" => $comment_id, "record_id" => $record_id, "create_contact_id" => wa()->getUser()->getId());
                }
                $mc->deleteByField($filter);
            }
        }
    }

    /**
     * Rename theme
     */
    private function renameThemeAction()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        if ($id) {
            $mt = new multiformThemeModel();
            $theme = $mt->getTheme($id);

            if (!$theme) {
                $this->errors = 1;
                multiformHelper::log(_w("Hey, where is theme ID?"));
                return;
            }

            if (wa()->getUser()->isAdmin('multiform') || (wa()->getUser()->getRights("multiform", "appearance") >= 1 && $theme['create_contact_id'] == wa()->getUser()->getId())) {
                // ID темы
                $name = waRequest::post("name");
                $mt = new multiformThemeModel();
                $mt->updateById($id, array("name" => $name, "update_datetime" => date("Y-m-d H:i:s"), "update_contact_id" => wa()->getUser()->getId()));
                $this->response = multiformHelper::secureString($name);
            } else {
                $this->errors = 1;
                multiformHelper::log(_w("Access denied to theme appearance"));
            }
        } else {
            $this->errors = 1;
            multiformHelper::log(_w("Hey, where is form ID?"));
        }
    }

    /**
     * Duplicate theme
     */
    private function duplicateThemeAction()
    {
        // ID темы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        if ($id) {
            $mt = new multiformThemeModel();
            $theme = $mt->getTheme($id);

            if (!$theme) {
                $this->errors = 1;
                multiformHelper::log(_w("Hey, where is theme ID?"));
                return;
            }

            if (wa()->getUser()->isAdmin('multiform') || wa()->getUser()->getRights('multiform', 'appearance') == 4 || (wa()->getUser()->getRights('multiform', 'appearance') == 1 && $theme['create_contact_id'] == wa()->getUser()->getId())) {
                $theme_id = $mt->insert(array("name" => $theme['name'] . "_copy", "create_datetime" => date("Y-m-d H:i:s"), "create_contact_id" => wa()->getUser()->getId()));
                $settings = array();
                foreach ($theme['settings'] as $property => $fields) {
                    foreach ($fields as $field => $value) {
                        if (is_array($value)) {
                            foreach ($value as $ext => $v) {
                                if (is_array($v)) {
                                    foreach ($v as $ext_k => $v2) {
                                        $settings[] = array("theme_id" => $theme_id, "property" => $property, "field" => $field, "ext" => $ext . ":" . $ext_k, "value" => $v2);
                                    }
                                } else {
                                    $settings[] = array("theme_id" => $theme_id, "property" => $property, "field" => $field, "ext" => $ext, "value" => $v);
                                }
                            }
                        } else {
                            $settings[] = array("theme_id" => $theme_id, "property" => $property, "field" => $field, "ext" => "", "value" => $value);
                        }
                    }
                }
                $mts = new multiformThemeSettingsModel();
                $mts->multipleInsert($settings);
                $this->response = array("name" => multiformHelper::secureString($theme['name'] . "_copy"), "theme_id" => $theme_id);
            } else {
                $this->errors = 1;
                multiformHelper::log(_w("Access denied to theme appearance"));
            }
        } else {
            $this->errors = 1;
            multiformHelper::log(_w("Hey, where is theme ID?"));
        }
    }

    /**
     * Delete theme
     */
    private function deleteThemeAction()
    {
        // ID темы
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        if ($id) {
            $theme_model = new multiformThemeModel();
            $theme = $theme_model->getTheme($id);
            if (!$theme) {
                $this->errors = 1;
                multiformHelper::log(_w("Hey, where is theme ID?"));
                return;
            }
            if (wa()->getUser()->isAdmin('multiform') || (wa()->getUser()->getRights("multiform", "appearance") >= 1 && $theme['create_contact_id'] == wa()->getUser()->getId())) {
                $mt = new multiformThemeModel();
                $mt->delete($id);
            } else {
                $this->errors = 1;
                multiformHelper::log(_w("Access denied to theme appearance"));
            }
        } else {
            $this->errors = 1;
            multiformHelper::log(_w("Hey, where is form ID?"));
        }
    }

    /**
     * Use theme
     */
    private function useThemeAction()
    {

        $id = waRequest::post("id", 0, waRequest::TYPE_INT);
        if ($id) {
            // Настройки формы
            $form_model = new multiformFormModel();
            $form_settings = $form_model->getFormSettings($id);
            if (multiformHelper::hasFullFormAccess($form_settings)) {
                // ID темы
                $theme_id = waRequest::post("theme_id", 0, waRequest::TYPE_INT);

                $theme_model = new multiformThemeModel();
                $theme = $theme_model->getTheme($theme_id);

                if (!$theme) {
                    $this->errors = 1;
                    multiformHelper::log(_w("Hey, where is theme ID?"));
                    return;
                }

                if (!wa()->getUser()->isAdmin() && (!wa()->getUser()->getRights('multiform', 'appearance') || (wa()->getUser()->getRights("multiform", "appearance") == 1 && $theme['create_contact_id'] !== wa()->getUser()->getId()))) {
                    $this->errors = 1;
                    multiformHelper::log(_w("Access denied to theme appearance"));
                    return;
                }

                $mfp = new multiformFormParamsModel();
                $mfp->deleteByField(array("form_id" => $id, "param" => "theme_id"));
                $mfp->insert(array("form_id" => $id, "param" => "theme_id", "ext" => '', "value" => $theme_id));
            } else {
                $this->errors = 1;
                multiformHelper::log(_w("Access denied to theme appearance"));
            }
        } else {
            $this->errors = 1;
            multiformHelper::log(_w("Hey, where is form ID?"));
        }
    }

    /**
     * Get s-helper-block
     */
    private function getTemplateVarsAction()
    {
        $form_id = waRequest::post('form_id', 0, waRequest::TYPE_INT);
        $mform = new multiformFormModel();
        $form = $mform->getFullForm($form_id);
        $html = "";
        if ($form && multiformHelper::hasFullFormAccess($form)) {
            // Дополнительные параметры, переданные в форму
            $view = new waSmarty3View(wa());
            $view->assign('custom_params', (new multiformRecordModel())->getCustomParams($form['id']));
            $view->assign('form', $form);
            $html .= $view->fetch((wa()->getAppPath('templates/include.template_vars.html', 'multiform')));
        }
        $this->response = $html;
    }

}
