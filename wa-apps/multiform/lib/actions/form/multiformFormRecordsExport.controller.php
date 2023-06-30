<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormRecordsExportController extends waLongActionController
{

    private $csv;
    private $record_model;

    public function execute()
    {
        try {
            parent::execute();
        } catch (waException $ex) {
            if ($ex->getCode() == '302') {
                echo json_encode(array('warning' => $ex->getMessage()));
            } else {
                echo json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    protected function finish($filename)
    {
        $this->info();
        if ($this->getRequest()->post('cleanup')) {
            return true;
        }
        return false;
    }

    protected function init()
    {
        if ($form_id = waRequest::post('form_id', 0, waRequest::TYPE_INT)) {
            $mf = new multiformFormModel();
            $mr = new multiformRecordModel();

            $form = $mf->getFormSettings($form_id);
            if (multiformHelper::recordAccess($form) !== 3) {
                throw new waRightsException('Access denied');
            }

            $encoding = 'utf-8';

            // Общее количество записей
            $this->data['total_count'] = $mr->countByField(array("form_id" => $form_id));

            // Заголовки таблицы
            $map = multiformHelper::getMapFields($form_id);
            // Название файла
            $name = sprintf('records_%s_%s.csv', date('Y-m-d-H-i-s'), strtolower($encoding));
            $file = wa()->getTempPath('csv/export/' . $form_id . '/' . $name);
            $this->csv = new multiformCsv($file, ';', $encoding);
            // Устанавливаем заголовки
            $this->csv->setMap($map);

            $this->data['file'] = serialize($this->csv);
            $this->data['offset'] = 0;
            $this->data['form_id'] = $form_id;
            $this->data['filename'] = $this->csv->file();
            $this->data['timestamp'] = time();
        } else {
            $this->data['error_message'] = _w("Specify form ID");
        }
    }

    protected function isDone()
    {
        return $this->data['offset'] >= $this->data['total_count'];
    }

    protected function restore()
    {
        $this->csv = unserialize($this->data['file']);
        $this->record_model = new multiformRecordModel();
    }

    protected function step()
    {
        try {
            $records = $this->record_model->getRecords(array(
                "form_id" => $this->data['form_id'],
                "order_by" => "id",
                "order_direction" => "asc",
                "limit" => array("offset" => $this->data['offset'], "length" => 50)
            ));

            $contacts = $this->record_model->getRecordContacts($records);

            $mf = new multiformFormModel();
            $form = $mf->getFullForm($this->data['form_id']);
            if (!empty($form['fields']) && !empty($records)) {
                foreach ($records as $r) {
                    $record = $this->getRecordValues($r, $form, $form['fields'], $contacts, !empty($r['fields']) ? $r['fields'] : []);
                    $this->csv->write($record);

                    // Получаем дублированные поля
                    if (!empty($r['has_cloned_fields'])) {
                        $record_data = $this->record_model->getRecord($r['id']);
                        if (!empty($record_data['cloned_fields'])) {
                            foreach ($record_data['cloned_fields'] as $section_id => $cloned_fields) {
                                if (!empty($form['fields'][$section_id])) {
                                    $this->getRecordClonedValues($r, $form, $form['fields'][$section_id], $contacts, $cloned_fields);
                                }
                            }
                        }
                    }

                    $this->data['offset']++;
                }
            }

            $this->data['file'] = serialize($this->csv);
        } catch (Exception $ex) {
            sleep(5);
            $this->error($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    private function getRecordClonedValues($record_data, $form, $section, $contacts, $cloned_fields)
    {
        foreach ($cloned_fields as $fields) {
            $record_data['is_clone'] = 1;
            $record = $this->getRecordValues($record_data, $form, array_intersect_key($form['fields'], $section['all_childs']), $contacts, $fields);
            $this->csv->write($record);

            if (!empty($fields['_'])) {
                foreach ($fields['_'] as $section_id => $cloned_fields2) {
                    if (!empty($form['fields'][$section_id])) {
                        $this->getRecordClonedValues($record_data, $form, $form['fields'][$section_id], $contacts, $cloned_fields2);
                    }
                }
            }
        }
    }

    private function getRecordValues($record_data, $form, $form_fields, $contacts, $record_fields = [])
    {
        $record = array();
        $record['m_record_id'] = multiformHelper::formatRecordId($record_data['id'], $form) . (!empty($record_data['is_clone']) ? ' (•••)' : '');
        foreach ($form_fields as $f) {
            if (in_array($f['type'], ['html', 'section', 'button']) || !$f['status']) {
                continue;
            }
            if (($f['type'] == 'checkbox' || $f['type'] == 'table_grid') && !empty($f['option_fields'])) {
                $options = $f['type'] == 'checkbox' ? $f['option_fields'] : $f['option_fields']['statement'];
                foreach ($options as $o) {
                    $record[$f['type'] . '_' . $f['id'] . '_' . $o['id']] = !empty($record_fields[$f['id']][$o['id']]) ? $record_fields[$f['id']][$o['id']] : "";
                }
            }
            if ($f['type'] == 'file') {
                $files = array();
                if (!empty($record_fields[$f['id']])) {
                    foreach ($record_fields[$f['id']] as $file) {
                        $files[] = wa()->getRouteUrl('multiform/frontend/downloadFile', array("hash" => $file['hash'], "domain" => wa('multiform')->getConfig()->getDomain()), true);
                    }
                }
                $record[$f['type'] . '_' . $f['id']] = $files ? implode(" ; ", $files) : "";
            } else {
                $record[$f['type'] . '_' . $f['id']] = (!empty($record_fields[$f['id']]) ? (is_array($record_fields[$f['id']]) ? reset($record_fields[$f['id']]) : $record_fields[$f['id']]) : "");
            }
        }
        $record['m_created'] = $record_data['create_datetime'];
        $record['m_created_by'] = !empty($contacts[$record_data['create_contact_id']]['name']) ? $contacts[$record_data['create_contact_id']]['name'] : "";
        $record['m_updated'] = $record_data['update_datetime'];
        $record['m_updated_by'] = !empty($contacts[$record_data['update_contact_id']]['name']) ? $contacts[$record_data['create_contact_id']]['name'] : "";
        $record['m_ip'] = $record_data['ip'];
        $record['m_status'] = $record_data['status'];

        return $record;
    }

    protected function info()
    {
        $interval = 0;
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
        }
        $response = array(
            'time' => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId' => $this->processId,
            'progress' => 0.0,
            'ready' => $this->isDone(),
            'offset' => $this->data['offset'],
            'total_count' => $this->data['total_count'],
            'error_message' => isset($this->data['error_message']) ? $this->data['error_message'] : "",
        );
        $response['progress'] = ($this->data['offset'] / $this->data['total_count']) * 100;
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);

        if ($this->getRequest()->post('cleanup')) {
            $response['report'] = $this->report();
        }

        echo json_encode($response);
    }

    protected function report()
    {
        $file = $this->data['filename'];
        $report = '<div style="margin-top: 50px;" class="align-center export-report">' . _w('Download:') . ' <a href="?module=form&action=exportDownload&file=' . basename($file) . '&form_id=' . $this->data['form_id'] . '"><i class="icon-custom excel"></i> ' . basename($file) . '</a></div>';
        $report .= '<br><br><div class="align-center"><a href="javascript:void(0)" class="button yellow close" onclick=\'$("#records-export-dialog").trigger("close")\'>' . _w('Close') . '</a></div>';
        return $report;
    }

}
