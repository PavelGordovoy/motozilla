<?php

class multiformRightConfig extends waRightConfig
{

    const RIGHT_NO = 0;
    const RIGHT_FORM_PRIVATE_FIELDS = 1;
    const RIGHT_FORM_PRIVATE_LIMIT = 2;
    const RIGHT_FORM_PRIVATE_FULL = 3;
    const RIGHT_FORM_FULL = 4;

    public function init()
    {
        $this->addItem('create', _w('Can create new forms'));
        $this->addItem('notifications', _w('Notifications'), 'select', array(
            'options' => array(
                self::RIGHT_NO => _w('No access (Hide tab)'),
                self::RIGHT_FORM_PRIVATE_FIELDS => _w('No access (Readonly)'),
                self::RIGHT_FORM_FULL => _w('Full access'),
            ),
        ));

        $mfm = new multiformFormModel();
        $all_forms = $mfm->getForms();
        $forms = array();
        if ($all_forms) {
            foreach ($all_forms as $f) {
                $forms[$f['id']] = strip_tags($f['name']);
            }
        }
        $this->addItem('appearance', _w('Appearance'), 'select', array(
            'options' => array(
                self::RIGHT_NO => _w('No access'),
                self::RIGHT_FORM_PRIVATE_FIELDS => _w('Can create and use own theme appearances'),
                self::RIGHT_FORM_FULL => _w('Full access'),
            ),
        ));
        $this->addItem('forms', _w('Forms'), 'selectlist', array(
            'items' => $forms,
            'options' => array(
                self::RIGHT_NO => _w('No access'),
                self::RIGHT_FORM_PRIVATE_FIELDS => _w('Access to private fields'),
                self::RIGHT_FORM_PRIVATE_LIMIT => _w('Private access to form without private fields'),
                self::RIGHT_FORM_PRIVATE_FULL => _w('Private access to form with private fields'),
                self::RIGHT_FORM_FULL => _w('Full access'),
            ),
            'hint1' => 'all_select'
        ));
        $this->addItem('records', _w('Records'), 'selectlist', array(
            'items' => $forms,
            'options' => array(
                self::RIGHT_NO => _w('No access'),
                self::RIGHT_FORM_PRIVATE_FIELDS => _w('Only read'),
                self::RIGHT_FORM_PRIVATE_LIMIT => _w('Edit records and add comments'),
                self::RIGHT_FORM_PRIVATE_FULL => _w('Full access'),
            ),
            'hint1' => 'all_select'
        ));

        /**
         * @event rights.config
         * @param waRightConfig $this Rights setup object
         * @return void
         */
        wa()->event('rights.config', $this);
    }

}
