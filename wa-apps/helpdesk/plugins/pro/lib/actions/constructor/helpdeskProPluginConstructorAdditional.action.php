<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginConstructorAdditionalAction extends helpdeskViewAction
{

    public function execute()
    {
        $this->view->assign('fields', $this->getFields());
        $this->view->assign('plugin_url', wa('helpdesk')->getPlugin('pro')->getPluginStaticUrl());
    }

    /**
     * Get all available contact fields
     *
     * @return array
     */
    private function getFields()
    {
        $all_fields = (new helpdeskProPluginHelper())->getAdditionalRequestFields(false);
        $fields = array();
        $constructor = new helpdeskProPluginConstructorModel();
        $additional_fields = $constructor->getFields(null, null, 'additional');

        $k = 0;
        foreach ($all_fields as $id => $data) {
            $fields[] = array(
                'id' => $id,
                'name' => $data['name'],
                'params' => isset($additional_fields[$id]) ? $additional_fields[$id]['params'] : array(),
                'enable' => isset($additional_fields[$id]) ? $additional_fields[$id]['enable'] : 0,
            );
            $k++;
        }
        return $fields;
    }

}
