<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageRecordsAction extends waViewAction
{

    private $filter = array();

    private function setPagination($records_count)
    {
        // Постраничная навигация
        if ($records_per_page = waRequest::post("records_per_page", 100)) {
            wa()->getStorage()->set('records_per_page', $records_per_page);
        }
        $session_records_per_page = wa()->getStorage()->get('records_per_page');
        $records_per_page = max(1, $session_records_per_page);
        $this->view->assign('records_per_page', $records_per_page);
        // Текущая страница в постраничном выводе
        $current_page = waRequest::request('page', 1, waRequest::TYPE_INT);
        // Если не задана, то равна 1
        $current_page = max(1, $current_page);
        // Всего страниц
        $max_page = ceil($records_count / $records_per_page);
        // Если текущая страница больше максимальной, то присваиваем текущей максимальной значение 
        if ($current_page > $max_page && $max_page > 0) {
            $current_page = $max_page;
        }
        $this->view->assign('current_page_num', $current_page);
        $pages_num = ceil($records_count / $records_per_page);
        $this->view->assign('total_pages_num', $pages_num);
        $this->filter['limit'] = array("offset" => ($current_page - 1) * $records_per_page, "length" => $records_per_page);
    }

    public function execute()
    {
        $mf = new multiformFormModel();
        $mr = new multiformRecordModel();

        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        $this->filter['form_id'] = $id;

        // Поиск
        if (waRequest::issetPost('query')) {
            $this->filter['query'] = waRequest::post('query');
            $this->filter['query_filter'] = waRequest::post('query-filters');
        }

        // Количество записей
        $records_count = $mr->countRecords($this->filter);
        $this->view->assign('records_count', $records_count);

        // Инициализируем постраничную навигацию
        $this->setPagination($records_count);

        // Сортировка
        $order_by = waRequest::post("field");
        $ext = waRequest::post("ext");
        $direction = waRequest::post("direction");

        // Запоминаем порядок сортировки
        $session_order = wa()->getStorage()->get('order-' . $id);
        if ($session_order && !$order_by) {
            $order_by = isset($session_order['order_by']) ? $session_order['order_by'] : $order_by;
            $ext = isset($session_order['ext']) ? $session_order['ext'] : $ext;
            $direction = isset($session_order['direction']) ? $session_order['direction'] : $direction;
        }
        wa()->getStorage()->set('order-' . $id, array("order_by" => $order_by, "ext" => $ext, "direction" => $direction));

        $this->filter['order_by'] = $order_by;
        $this->filter['ext'] = $ext;
        $this->filter['order_direction'] = (int) $direction ? 'asc' : 'desc';

        $this->view->assign("sort", array("field" => $this->filter['order_by'], "ext" => $this->filter['ext'], "direction" => $this->filter['order_direction']));

        $records = $mr->getRecords($this->filter);
        $form = $mf->getFullForm($id);

        if (!multiformHelper::recordAccess($form)) {
            throw new waRightsException('Access denied');
        }

        if ($records) {
            $this->view->assign("contacts", $mr->getRecordContacts($records));

            // Дополнительные параметры, переданные в форму
            $custom_params = $mr->getCustomParams($id);
            $this->view->assign('custom_params', $custom_params);
        }

        $this->view->assign("domain", wa('multiform')->getConfig()->getDomain());
        $this->view->assign("records", $records);
        $this->view->assign("form", $form);

        // Время последнего посещения 
        $contact_model = new waContactSettingsModel();
        if (wa()->getUser()->getId()) {
            $contact_model->set(wa()->getUser()->getId(), 'multiform', 'multiform_last_datetime_' . $id, date("Y-m-d H:i:s"));
        }
    }

}
