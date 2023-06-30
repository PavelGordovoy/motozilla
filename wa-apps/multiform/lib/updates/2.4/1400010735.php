<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
// Проверяем, добавлялись ли уже шаблоны регулярных выражений
$app_settings = new waAppSettingsModel();
$regexp = $app_settings->get('multiform', 'regexp_init');
if (!$regexp) {
    try {
        $email_mask = $model->query("insert into `multiform_mask` (`name`, `mask`, `error`) values('Email','".$model->escape('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/')."','" . _w("Print valid email address") . "');")->lastInsertId();
    } catch (waDbException $e) {
        
    }

    try {
        $digits_mask = $model->query("insert into `multiform_mask` (`name`, `mask`, `error`) values('" . _w("Only digits") . "','".$model->escape('/^\d+$/')."','" . _w("Print only digits") . "');")->lastInsertId();
    } catch (waDbException $e) {
        
    }
    $app_settings->set('multiform', 'regexp_init', 1);

    try {
        wa('multiform');
        $mfm = new multiformFormModel();

        $form_created = $mfm->select('id')->where("url = 'htmltestform'")->fetch();
        if (!$form_created) {
            $mfieldm = new multiformFieldModel();

            $sort = $model->query("SELECT MAX(sort) FROM {$mfieldm->getTableName()}")->fetchField();

            $form = array(
                "name" => _w("Form with custom HTML"),
                "url" => 'htmltestform',
                "css" => ''
            );
            if ($form_id = $mfm->save("new", $form)) {
                if ($email_mask && $digits_mask) {
                    $fields = array(
                        'new1' => array(
                            'form_id' => $form_id,
                            'status' => 0, 'name' => '', 'multiple' => 1, 'code' => '',
                            'type' => 'html', 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'helper' => _w("This field is hidden and is designed specifically for You, as a short instruction. Here is an example of creating form. In order to place it on the website, click on the blue icon above, and You will see a code which can be inserted into any page on the website. That's all, good luck!"),
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 1
                        ),
                        'new2' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'name' => '', 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 2, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'type' => 'html',
                            'helper' => sprintf(_w("<h2>Order Information</h2>%s Enter data without errors"), '<img src="{$wa_url}wa-apps/multiform/img/demo/info.jpg" style="vertical-align: middle;">'),
                        ),
                        'new3' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 3, 'class' => '', 'mask' => 0,
                            'name' => _w("Order number"),
                            'max_length' => 10,
                            'type' => 'input',
                            'required' => 1
                        ),
                        'new4' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 4, 'class' => '', 'mask' => 0, 'max_length' => 0,
                            'name' => _w("Product name"),
                            'type' => 'input',
                            'required' => 1
                        ),
                        'new5' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'required' => 1,
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 5, 'class' => '', 'max_length' => 0,
                            'name' => _w("Quantity"),
                            'type' => 'input',
                            'mask' => $digits_mask,
                            'helper' => sprintf("<i style='color: grey'>%s</i>", _w('Print only digits'))
                        ),
                        'new6' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'name' => '', 'multiple' => 1, 'code' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 6, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'type' => 'html',
                            'helper' => sprintf(_w("<h2>Delivery</h2>%s Delivery information"), '<img src="{$wa_url}wa-apps/multiform/img/demo/delivery.jpg" style="vertical-align: middle;">'),
                        ),
                        'new7' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 7, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'name' => _w('Country'),
                            'type' => 'input',
                        ),
                        'new8' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 8, 'class' => '', 'max_length' => 0, 'mask' => 0,
                            'name' => _w('City'),
                            'type' => 'input',
                            'required' => 1
                        ),
                        'new9' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 9, 'class' => '', 'max_length' => 0, 'mask' => 0,
                            'name' => _w('Region'),
                            'type' => 'input',
                            'required' => 1
                        ),
                        'new10' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'name' => '', 'multiple' => 1, 'code' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 10, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'type' => 'html',
                            'helper' => sprintf(_w("<h2>Payment</h2>%s Choose the best method of payment"), '<img src="{$wa_url}wa-apps/multiform/img/demo/payment.jpg" style="vertical-align: middle;">')
                        ),
                        'new11' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(
                                'update1' => '--- ' . _w("Please select") . ' ---',
                                'update2' => _w("Credit card"),
                                'update3' => 'Webmoney',
                                'update4' => 'Yandex money'
                            ),
                            'options_sort' => array(
                                'update1' => 1,
                                'update2' => 2,
                                'update3' => 3,
                                'update4' => 4
                            ),
                            'sort' => $sort + 11, 'class' => '', 'max_length' => 0, 'mask' => 0,
                            'name' => _w('Payment'),
                            'type' => 'select',
                            'required' => 1
                        ),
                        'new12' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'name' => '', 'multiple' => 1, 'code' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 12, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'type' => 'html',
                            'helper' => "<h2>"._w("Contact info")."</h2>",
                        ),
                        'new13' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 13, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'type' => 'input',
                            'name' => _w("First name")
                        ),
                        'new14' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 14, 'class' => '', 'max_length' => 0, 'mask' => 0,
                            'type' => 'input',
                            'name' => _w("Phone"),
                            'required' => 1
                        ),
                        'new15' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 15, 'class' => '', 'max_length' => 0,
                            'type' => 'input',
                            'name' => 'Email',
                            'required' => 1,
                            'mask' => $email_mask
                        ),
                        'new16' => array(
                            'form_id' => $form_id,
                            'status' => 1, 'multiple' => 1, 'code' => '', 'helper' => '',
                            'options' => array(),
                            'extension' => array(),
                            'sort' => $sort + 16, 'class' => '', 'required' => 0, 'max_length' => 0, 'mask' => 0,
                            'type' => 'textarea',
                            'name' => _w("Comment"),
                        ),
                    );
                    if (!$mfieldm->save($form_id, $fields)) {
                        $mfm->delete($form_id);
                    }
                }
            }
        }
    } catch (waDbException $e) {
        
    }
}