<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginSpamModel extends waModel
{

    protected $table = 'helpdesk_pro_spam';

    /**
     * Delete spam request and contact, if neccessary
     *
     * @param int $request_id
     * @param string $email
     * @return bool
     * @throws waException
     */
    public function deleteSpamRequest($request_id, $email)
    {
        $delete_spam_contact = helpdeskProPluginHelper::getSettings('delete_spam_contacts', 0);

        $rm = new helpdeskRequestModel();
        $request = new helpdeskRequest($request_id);
        $client_id = $request->getClient()->getId();

        /* Удаляем запрос */
        $rm->delete($request_id);
        $dir = helpdeskRequest::getAssetsDir($request_id);
        if (is_writable($dir)) {
            waFiles::delete($dir);
        }

        /* Удаляем контакт */
        if ($delete_spam_contact && $client_id) {
            return $this->deleteContact($client_id, $email);
        }
        return true;
    }

    /**
     * Add email to spam list
     * 
     * @param string $email
     * @return bool
     */
    public function addToSpam($email)
    {
        if (!$this->isSpam($email)) {
            return $this->insert(array('email' => $email), 2);
        } else {
            return false;
        }
    }

    /**
     * Is email in spam list?
     * 
     * @param string $email
     * @return array|null
     */
    public function isSpam($email)
    {
        return $this->getByField('email', $email);
    }

    /**
     * Delete spam contact from Helpdesk and Contacts
     *
     * @param int $contact_id
     * @param string $email
     * @return bool
     * @throws waException
     */
    private function deleteContact($contact_id, $email)
    {
        // Удаляем все запросы контакта. У Поддержки нет для этого хендлера, приходится делать самостоятельно
        $c = helpdeskRequestsCollection::create(array(
                    array(
                        'name' => 'c_email',
                        'params' => array($email),
                    ),
        ));
        $requests = helpdeskRequest::prepareRequests($c->getRequests());
        if ($requests) {
            $ids = array();
            foreach ($requests as $request) {
                $ids[] = $request['id'];
                // Удаляем файлы запросов
                $dir = helpdeskRequest::getAssetsDir($request['id']);
                if (is_writable($dir)) {
                    waFiles::delete($dir);
                }
            }
            if ($ids) {
                $rm = new helpdeskRequestModel();
                /* Удаляем запросы */
                $rm->delete($ids);
            }
        }

        // Удаляем контакт
        wa('contacts');

        waUser::revokeUser($contact_id);

        $contact_model = new waContactModel();

        $history_model = new contactsHistoryModel();
        $history_model->deleteByField(array(
            'type' => 'add',
            'hash' => '/contact/' . $contact_id
        ));

        return $contact_model->delete($contact_id);
    }

}
