<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginAttachmentsAction extends helpdeskViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new waRightsException(_w('Access denied'));
        }

        $plugin = wa('helpdesk')->getPlugin('pro');

        $attachments_model = (new helpdeskProPluginAttachmentsModel());
        $offset = $this->getOffset($attachments_model->countAll(), $this->view);
        $order_by = $this->getOrder();
        $attachments = $attachments_model->getAttachments(['pagination' => $offset, 'order' => $order_by]);
        $requests = (new helpdeskRequestModel())->getById(array_column($attachments, 'request_id'));
        foreach ($attachments as &$attachment) {
            $request = isset($requests[$attachment['request_id']]) ? $requests[$attachment['request_id']] : [];
            $attachment['human_filesize'] = $this->humanFilesize($attachment['size']);
            $attachment['last_update'] = ifset($requests, $attachment['request_id'], 'updated', '-');
            if ($request) {
                $s = helpdeskWorkflow::get($request['workflow_id'])->getStateById($request['state_id']);
                if ($s) {
                    $attachment['state'] = '<span style="' . $s->getOption('list_row_css', '') . '">' . $s->getName() . '</span>';
                }
                $attachment['link'] = helpdeskRequest::getAttachmentUrl($request['id'], $attachment['log_id'], $attachment['file']);
                if (isset($attachment['attach_datetime'])) {
                    $attachment['link'] .= '&datetime=' . $attachment['attach_datetime'];
                }
            }
        }
        $this->view->assign('pagination', $offset);
        $this->view->assign('order', $order_by);
        $this->view->assign('attachments', $attachments);
        $this->view->assign('pagination_template', wa()->getAppPath('plugins/pro/templates/pagination.html'));
        $this->view->assign('total_size', $this->humanFilesize($attachments_model->getTotalSize()));
        $this->view->assign('cron_command', 'php '.wa()->getConfig()->getRootPath().'/cli.php helpdesk proPluginIndexFiles');
        $this->view->assign('plugin_url', $plugin->getPluginStaticUrl());
    }

    public function getOrder()
    {
        $order_by = ['field' => 'size', 'direction' => 'DESC'];

        $valid_fields = ['name', 'request_id', 'size', 'updated'];

        $post_field = waRequest::post('sort', '');
        if (in_array($post_field, $valid_fields)) {
            $order_by['field'] = $post_field;
        }

        $post_direction = waRequest::post('direction', '');
        if (in_array($post_direction, ['ASC', 'DESC'])) {
            $order_by['direction'] = $post_direction;
        }

        return $order_by;
    }

    public function getOffset($count, $view)
    {
        $wa = wa();

        if ($per_page = waRequest::post("per_page")) {
            $wa->getStorage()->set('per_page', $per_page);
        }
        $session_per_page = $wa->getStorage()->get('per_page');
        $session_per_page = $session_per_page ? $session_per_page : 50;
        $items_per_page = max(1, $session_per_page);
        $view->assign('items_per_page', $items_per_page);

        $current_page = waRequest::request('page', 1, waRequest::TYPE_INT);
        $current_page = max(1, $current_page);
        $max_page = ceil($count / $items_per_page);
        if ($current_page > $max_page && $max_page > 0) {
            $current_page = $max_page;
        }
        $view->assign('current_page_num', $current_page);
        $view->assign('total_count', $count);
        $pages_num = ceil($count / $items_per_page);
        $view->assign('total_pages_num', $pages_num);
        return ["offset" => ($current_page - 1) * $items_per_page, "length" => $items_per_page];
    }

    private function humanFilesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}
