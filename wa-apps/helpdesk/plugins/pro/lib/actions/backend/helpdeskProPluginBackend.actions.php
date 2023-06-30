<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginBackendActions extends waJsonActions
{

    /**
     * Change favourite message
     */
    public function favouriteAction()
    {
        $contact_id = wa()->getUser()->getId();
        $message_id = waRequest::post('message_id');
        $state = waRequest::post('state', 0, waRequest::TYPE_INT);
        $request_id = waRequest::post('request_id', 0, waRequest::TYPE_INT);

        $hpf = new helpdeskProPluginFavouritesModel();
        if ($request_id && $message_id) {
            if ($state) {
                $result = $hpf->insert(array('contact_id' => $contact_id, 'message_id' => $message_id, 'request_id' => $request_id), 2);
            } else {
                $result = $hpf->deleteByField(array('contact_id' => $contact_id, 'message_id' => $message_id, 'request_id' => $request_id));
            }

            if ($result) {
                $this->response = $state;
            }
        } else {
            $this->errors = 1;
        }
    }

    /*
     * Sorting of contact fields and enable changing on constructor page
     */

    public function contactFieldsSortAction()
    {
        if (!wa()->getUser()->getRights('contacts', 'backend')) {
            throw new waRightsException(_w('Access denied'));
        }

        $contact_id = wa()->getUser()->getId();
        $ids = waRequest::post('ids', array());

        // Все поля контакта
        $constructor = new helpdeskProPluginConstructorModel();
        $contact_fields = $constructor->getFields();

        $insert = array();
        foreach ($ids as $k => $field) {
            if (!isset($contact_fields[$field['id']])) {
                $insert[] = array("contact_id" => $contact_id, 'field_id' => $field['id'], 'sort' => $k, 'enable' => $field['enable'], 'params' => null);
            } else {
                $constructor->updateByField(array("contact_id" => $contact_id, "field_id" => $field['id']), array("sort" => $k, "enable" => $field['enable']));
            }
        }
        if ($insert) {
            $constructor->multipleInsert($insert);
        }
    }

    /*
     * Enable changing on additional constructor page
     */

    public function additionalFieldsEnableAction()
    {
        $contact_id = wa()->getUser()->getId();
        $ids = waRequest::post('ids', array());

        // Все поля контакта
        $constructor = new helpdeskProPluginConstructorModel();
        $contact_fields = $constructor->getFields(null, null, 'additional');

        $insert = array();
        foreach ($ids as $k => $field) {
            if (!isset($contact_fields[$field['id']])) {
                $insert[] = array("contact_id" => $contact_id, 'field_id' => $field['id'], 'sort' => -1, 'enable' => $field['enable'], 'params' => null, 'type' => 'additional');
            } else {
                $constructor->updateByField(array("contact_id" => $contact_id, "field_id" => $field['id']), array("sort" => -1, "enable" => $field['enable']));
            }
        }
        if ($insert) {
            $constructor->multipleInsert($insert);
        }
    }

    /*
     * Add email to spam list
     */

    public function addSpamAction()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }
        $email = waRequest::post('email', '');
        $validator = new waEmailValidator();
        $email = $email ? trim($email) : '';
        if ($email && $validator->isValid($email)) {
            $spam_model = new helpdeskProPluginSpamModel();
            if ($spam_model->addToSpam($email)) {
                $this->response = helpdeskProPluginHelper::escape($email);
            } else {
                $this->response = '';
            }
        } else {
            $this->errors = 1;
        }
    }

    /*
     * Add email to spam list, delete request and contact
     */

    public function addEmailToSpamAction()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }
        $email = waRequest::post('email', '');
        $request_id = waRequest::post('request_id', 0, waRequest::TYPE_INT);
        if (!$request_id) {
            $this->errors = _wp('Request ID is empty');
            return;
        }
        $validator = new waEmailValidator();
        $email = $email ? trim($email) : '';
        if ($email && $validator->isValid($email)) {
            $spam_model = new helpdeskProPluginSpamModel();
            $spam_model->addToSpam($email);
            if ($spam_model->isSpam($email)) {
                $this->response = $spam_model->deleteSpamRequest($request_id, $email);
                return;
            }
            $this->errors = _wp('Something goes wrong');
        } else {
            $this->errors = _wp('Email is not valid');
        }
    }

    /*
     * Remove email from spam list
     */

    public function removeSpamAction()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }

        $email = waRequest::post('email', '');
        if ($email) {
            $spam_model = new helpdeskProPluginSpamModel();
            $spam_model->deleteByField('email', $email);
        }
    }

    /*
     * Check if email is in spam list
     */

    public function isSpamAction()
    {
        $email = waRequest::post('email', '');
        if ($email) {
            $spam_model = new helpdeskProPluginSpamModel();
            if ($spam_model->isSpam($email)) {
                $this->response = 1;
            } else {
                $this->errors = 1;
            }
        }
    }

    /*
     * Check if request has email in spam list
     */

    public function isRequestEmailSpamAction()
    {
        $r = waRequest::post('requests', array());
        if ($r && is_array($r)) {
            $c = helpdeskRequestsCollection::create(array(
                array(
                    'name' => 'id',
                    'params' => array($r),
                ),
            ));
            $requests = helpdeskRequest::prepareRequests($c->getRequests());
            if ($requests) {
                $emails = array();
                foreach ($requests as $request) {
                    if (empty($request['client_email'])) {
                        $client_contact = new waContact($request['client_contact_id']);
                        $email = $client_contact->get('email', 'default');
                    } else {
                        $email = $request['client_email'];
                    }
                    if ($email) {
                        $emails[$request['id']] = $email;
                    }
                }
                $spam_model = new helpdeskProPluginSpamModel();
                $spam_emails = $spam_model->select('email')->where("email IN ('" . implode("','", $emails) . "')")->fetchAll(null, true);
                $intersect = array_intersect($emails, $spam_emails);
                $this->response = array_keys($intersect);
            }
        }
    }

    /**
     * Save custom request field
     */
    public function saveCustomRequestFieldAction()
    {
        $request_id = waRequest::post('request_id', 0, waRequest::TYPE_INT);
        $field_id = waRequest::post('field_id');
        $value = waRequest::post('value', '');

        (new helpdeskRequestDataModel())->add(array('request_id' => $request_id, 'field' => $field_id, 'value' => $value), null, 1);
        $this->response = waString::escapeAll($value);
    }

    /**
     * Remove tags
     */
    public function removeTagAction()
    {
        if ($name = waRequest::post('name')) {
            $tag_model = new helpdeskTagModel();
            $request_tag_model = new helpdeskRequestTagsModel();
            $tag_id = $tag_model->getByName($name, true);
            if ($tag_id) {
                $sql = "DELETE t, rt FROM {$tag_model->getTableName()} t 
                        LEFT JOIN {$request_tag_model->getTableName()} rt ON t.id=rt.tag_id
                        WHERE t.id=i:tag_id";
                $tag_model->exec($sql, ['tag_id' => $tag_id]);
            }
        }
    }

    /**
     * Remove attachments
     */
    public function removeAttachmentAction()
    {
        if (!wa()->getUser()->isAdmin()) {
            return;
        }

        $params = waRequest::post('params');

        if ($params && is_array($params)) {
            foreach ($params as $param) {
                $this->removeAttachment($param);
            }
        } else {
            $this->removeAttachment($params);
        }
    }

    /**
     * @param string $url_params
     */
    private function removeAttachment($url_params)
    {
        $parts = parse_url($url_params);
        if (!empty($parts)) {
            parse_str($parts['query'], $params);

            $request_id = $params['r'];
            $log_id = ifset($params, 'l', 0);
            $attach_id = $params['a'];
            $r = new helpdeskRequest($request_id);

            // check access rights
            if ($this->isAllowed($r, $log_id)) {

                // Check if this attachment exists
                if ($log_id) {
                    $file = helpdeskRequest::getAttachmentsDir($request_id, $log_id) . '/' . $attach_id;
                } else {
                    $file = helpdeskRequest::getAttachmentsDir($request_id) . '/' . $attach_id;
                }
                if (file_exists($file)) {

                    waFiles::delete($file, true);

                    $attach = null;
                    if ($log_id) {
                        $rlpm = new helpdeskRequestLogParamsModel();
                        $row = $rlpm->getByField(array(
                            'request_log_id' => $log_id,
                            'name' => 'attachments',
                        ));
                        if ($row) {
                            $attach = @unserialize($row['value']);
                        }
                    } else {
                        $attach = $r->attachments->toArray();
                    }

                    if ($attach) {
                        $a = null;
                        foreach ($attach as $k => $data) {
                            if (basename($data['file']) == $attach_id) {
                                unset($attach[$k]);
                                break;
                            }
                        }
                        if ($log_id) {
                            $model = $rlpm;
                            $field_data = [
                                'request_log_id' => $log_id,
                                'name' => 'attachments',
                            ];
                        } else {
                            $model = (new helpdeskRequestParamsModel());
                            $field_data = [
                                'request_id' => $request_id,
                                'name' => 'attachments',
                            ];
                        }

                        if ($attach) {
                            $model->updateByField($field_data, ['value' => @serialize($attach)]);
                        } else {
                            $model->deleteByField($field_data);
                        }
                    }
                }
            }

            (new helpdeskProPluginAttachmentsModel())->deleteByField(['request_id' => $request_id, 'log_id' => $log_id, 'attach_id' => $attach_id]);
        }
    }

    private function isAllowed($r, $log_id)
    {
        try {
            if ($log_id) {
                $l = new helpdeskRequestLog($log_id);
                if ($l->request_id != $r->id) {
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            // Request or log do not exist
            return false;
        }
    }

}
