<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDialogNewRequestAction extends waViewAction
{

    public function execute()
    {
        if (!helpdeskSourceHelper::isBackendSourceAvailable()) {
            throw new waRightsException('Access denied');
        }

        $message_id = waRequest::get('message_id', 0, waRequest::TYPE_INT);
        $request_id = waRequest::get('request_id', 0, waRequest::TYPE_INT);

        $hrl = new helpdeskRequestLogModel();
        $logs = $hrl->getByRequestWithParams($request_id);

        if (isset($logs[$message_id])) {

            // Данные запроса
            $c = helpdeskRequestsCollection::create(array(
                        array(
                            'name' => 'id',
                            'params' => array($request_id),
                        ),
            ));
            $request = helpdeskRequest::prepareRequests($c->getRequests());
            $request = !empty($request) ? reset($request) : array();
            
            // Сообщение
            $message = $logs[$message_id];
            if (!empty($message['text'])) {
                if (!empty($request['client_contact_id']) && $message['actor_contact_id'] != $request['client_contact_id']) {
                    $message['text'] = preg_replace('~(\r?\n\r?|<div>|<br ?/?>)\s*(<(font|span)[^>]*>\s*)*-{2,}\s*(</(font|span)[^>]*>\s*)*(<br ?/?>|</div>|\r?\n\r?).*$~isum', '', $message['text']);
                    $message['text'] = preg_replace('~(\r?\n\r?|<div>|<br ?/?>)\s*(<(font|span)[^>]*>\s*)*С уважением, .*$~isum', '', $message['text']);
                }
                $message['text'] = helpdeskRequest::formatHTML($message);
                $message['text'] = helpdeskRequest::stripBlockquotes($message['text']);
            }
            
            $assignees = helpdeskHelper::getAssignOptions($message['workflow_id']);
            $this->view->assign(array(
                'message' => $message,
                'user' => new waContact(!empty($request['client_contact_id']) ? $request['client_contact_id'] : $message['actor_contact_id']),
                'request' => $request,
                'assignees' => $assignees,
                'all_states' => helpdeskHelper::getAllWorkflowsWithStates(false),
                'uniqid' => uniqid('f')
            ));
        }
    }

    public function getLogs(helpdeskRequest $request)
    {
        $logs = $request->getLogs();

        foreach ($logs as &$log) {
            $log['fields'] = array();
            if (!empty($log['params'])) {
                $log['fields'] = helpdeskRequestLogParamsModel::formatFields(
                                helpdeskRequestLogParamsModel::filterByType(
                                        $log['params'], helpdeskRequestLogParamsModel::TYPE_REQUEST
                                )
                );
                // extract old_worflow and new_workflow names
                foreach (array('old_workflow', 'new_workflow') as $name) {
                    if (isset($log['params']["{$name}_id"])) {
                        $wf_id = $log['params']["{$name}_id"];
                        $log[$name] = $wf_id;
                        try {
                            $wf = helpdeskWorkflow::getWorkflow($wf_id);
                            $log[$name] = $wf->getName() . " (id = {$wf_id})";
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }
        }
        unset($log);

        return $logs;
    }

}
