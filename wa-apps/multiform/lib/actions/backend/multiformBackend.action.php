<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformBackendAction extends waViewAction
{

    private $filter = array();

    private function setPagination($records_count)
    {
        // Постраничная навигация
        if ($items_per_page = waRequest::post("items_per_page", 100)) {
            wa()->getStorage()->set('items_per_page', $items_per_page);
        }
        $session_items_per_page = wa()->getStorage()->get('items_per_page');
        $items_per_page = max(1, $session_items_per_page);
        $this->view->assign('items_per_page', $items_per_page);
        // Текущая страница в постраничном выводе
        $current_page = waRequest::get('page', 1, waRequest::TYPE_INT);
        // Если не задана, то равна 1
        $current_page = max(1, $current_page);
        // Всего страниц
        $max_page = ceil($records_count / $items_per_page);
        // Если текущая страница больше максимальной, то присваиваем текущей максимальной значение 
        if ($current_page > $max_page) {
            $current_page = $max_page;
        }
        $this->view->assign('current_page_num', $current_page);
        $pages_num = ceil($records_count / $items_per_page);
        $this->view->assign('total_pages_num', $pages_num);
        $this->filter['limit'] = array("offset" => ($current_page - 1) * $items_per_page, "length" => $items_per_page);
    }

    public function execute()
    {
        $this->setLayout(new multiformBackendLayout());

        // Права доступа
        if ($this->getRights('backend') == 1) {
            $form_rights = $this->getRights('forms.%');
            $record_rights = $this->getRights('records.%');
            $this->filter['ids'] = array(0);
            if ($form_rights) {
                if (isset($form_rights['all'])) {
                    unset($form_rights['all']);
                }
                foreach ($form_rights as $fr_id => $fr) {
                    // Если нет полного доступа к форме, то удаляем форму из списка разрешенных
                    if ($fr !== 4) {
                        unset($form_rights[$fr_id]);
                    }
                }
                $this->filter['ids'] = array_merge($this->filter['ids'], array_keys($form_rights));
            }
            if ($record_rights) {
                if (isset($record_rights['all'])) {
                    unset($record_rights['all']);
                }
                $this->filter['ids'] = array_merge($this->filter['ids'], array_keys($record_rights));
            }

            if ($this->getRights('create')) {
                $this->filter['contact_id'] = wa()->getUser()->getId();
            }
        }

        $multiform_form = new multiformFormModel();

        // Количество форм
        $forms_count = $multiform_form->countAllForms($this->filter);


        $this->view->assign('forms_count', $forms_count);

        // Инициализируем постраничную навигацию
        $this->setPagination($forms_count);

        // Сортировка
        $order_by = waRequest::post("field", wa()->getStorage()->get('multiform_form_field'));
        $direction = waRequest::post("direction", wa()->getStorage()->get('multiform_form_direction'));
        if ($order_by && in_array($order_by, array("name", "count", "create_datetime", "create_by", "start", "end", "last_record"))) {
            wa()->getStorage()->set('multiform_form_field', $order_by);
            wa()->getStorage()->set('multiform_form_direction', $direction);
            $this->filter['order_by'] = $order_by;
            $this->filter['order_direction'] = (int) $direction ? 'asc' : 'desc';
            $this->view->assign("sort", array("field" => $this->filter['order_by'], "direction" => $this->filter['order_direction']));
        }
        $this->filter['with_params'] = 1;
        $forms = $multiform_form->getForms($this->filter);

        if ($forms) {
            $contacts = array();
            foreach ($forms as $f_id => $f) {
                if ($f['create_contact_id']) {
                    $contacts[$f['create_contact_id']] = $f['create_contact_id'];
                }
                if (isset($form_rights[$f['id']])) {
                    $forms[$f_id]['has_edit_access'] = 1;
                }
                if (isset($record_rights[$f['id']])) {
                    $forms[$f_id]['has_record_access'] = 1;
                }
            }

            $collection = new waContactsCollection('/id/' . implode(",", $contacts) . '/', array('check_rights' => true));
            $contacts = $collection->getContacts('id,name');
            $this->view->assign("contacts", $contacts);

            $theme_model = new multiformThemeModel();
            $themes = $theme_model->getThemes();
            $this->view->assign("themes", $themes);
        }

        $this->view->assign("settings", (new waAppSettingsModel())->get('multiform'));
        $this->view->assign("forms", $forms);
    }

}
