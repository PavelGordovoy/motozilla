<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginAttachmentsModel extends waModel
{

    protected $table = 'helpdesk_pro_attachments';

    public function getAttachments($filter)
    {
        $order_field = ifempty($filter, 'order', 'field', 'size');
        $order_direction = ifempty($filter, 'order', 'direction', 'DESC');

        $sql = "SELECT hpa.* FROM {$this->table} hpa ";

        if ($order_field === 'updated') {
            $request_model = new helpdeskRequestModel();
            $sql .= " LEFT JOIN {$request_model->getTableName()} hr ON hr.id = hpa.request_id";
        }

        $sql .= " WHERE 1=1";
        $sql .= " ORDER BY ";

        if ($order_field === 'updated') {
            $sql .= "hr.updated";
        } else {
            $sql .= "hpa." . $order_field;
        }

        $sql .= " " . $order_direction;

        if (!empty($filter['pagination'])) {
            $sql .= " LIMIT " . $filter['pagination']['offset'] . "," . $filter['pagination']['length'];
        }

        return $this->query($sql)->fetchAll();
    }

    public function getTotalSize()
    {
        return $this->select('SUM(size)')->fetchField();
    }
}
