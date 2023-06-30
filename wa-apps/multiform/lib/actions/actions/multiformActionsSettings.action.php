<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsSettingsAction extends waViewAction
{

    public function execute()
    {
        $mf = new multiformFieldModel();
        $fields_html = '';
        // Получаем поля для формы
        $fields = $mf->getDataById($this->params['form_id'], 'form');

        multiformHTMLControlHelper::registerControl('GetFileUpgradedActionsControl');
        multiformHTMLControlHelper::registerControl('GetColorActionsControl');
        multiformHTMLControlHelper::registerControl('GetFormFieldsControl');
        
        foreach ($this->params['action']['settings'] as $field_id => $as) {
            $params = array(
                'form_fields' => $fields,
                'field_id' => $field_id,
                'title_wrapper' => '<div class="name">%s</div>',
                'description_wrapper' => '<div class="multiform-gap-description">%s</div>',
                'control_separator' => '<div class="margin-block bottom semi"></div>',
                'options_wrapper' => array(
                    'control_separator' => '<div class="margin-block bottom semi"></div>',
                ),
                'control_wrapper' => "<div class='field type-" . $as['control_type'] . (!empty($as['required']) ? ' s-required' : '') . " action-field-{$field_id}'>"
                . "%s"
                . "<div class='value'>"
                . "%s"
                . "%s"
                . "</div>"
                . "</div>"
            );
            if (!empty($this->params['action']['fields'][$field_id])) {
                $params['value'] = $this->params['action']['fields'][$field_id];
            }
            if (!empty($this->params['action']['id'])) {
                $params['action_id'] = $this->params['action']['id'];
            }
            waHtmlControl::addNamespace($params, 'data');
            $fields_html .= waHtmlControl::getControl($as['control_type'] == 'file' ? 'GetFileUpgradedActionsControl' : $as['control_type'], $field_id, $params + $as);
        }
        $this->view->assign("content", $fields_html);
    }

}
