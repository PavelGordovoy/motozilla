<?php

class shopOrderchangeCli extends waCliController
{
    public function execute() {
        $file = 'https://motozilla.com.ua/importprice/Artikul.csv';
        $rows = array_map('trim', file($file));
        $ord = new shopOrderModel();
        $model = new waModel();
        $pr_model = new shopSetProductsModel();
        foreach ($rows as $index => $row) {
            $params = array_map('trim', explode(';', $row));
            $sku_mas[$params[1]] = $params[1];
            $order = ltrim(mb_substr($params[0],2, 100),'0');
            $order_mas[$order] = $order;
        }
        foreach ($rows as $index => $row) {
            $params = array_map('trim', explode(';', $row));
            $order = ltrim(mb_substr($params[0],2, 100),'0');
            $sku = $params[1];
            $orda = $ord->getOrder($order);
            $i = "";
            $mark = false;
            if($orda['state_id'] == "oformlen-mz" || $orda['state_id'] == "zaderzhivaetsya" || $orda['state_id'] == "ne-skomplektoval"){
                foreach ($orda['items'] as $index => $item) {
                    if($item['sku_code'] == $sku){
                        $i = $item['id'];
                        $mark = $item['mark'];
                        $name= $item['name'];
                        $sku_c= $item['sku_code'];
                    }
                }
                if ($i){
                    $workflow = new shopWorkflow();
                    $actions = $workflow->getStateById($orda['state_id'])->getActions(NULL, TRUE);
                    if (isset($actions['ne-skomplektovali-1c'])) {
                        $action = $workflow->getActionById('ne-skomplektovali-1c');
                        $action->run($orda['id']);
                    }
                    if ($mark != 1) {
                        $olm = new shopOrderLogModel();
                        $log = "Не скомплектован товар [".$sku_c."] ".$name;
                        $olm->add(array(
                            'order_id'        => $order,
                            'contact_id'      => null,
                            'action_id'       => '',
                            'text'            => $log,
                            'before_state_id' => 'ne-skomplektovali-1c',
                            'after_state_id'  => 'ne-skomplektovali-1c',
                        ));
                        $sql = "UPDATE `shop_order_items` SET `mark`=1 WHERE `id`=" . $i;
                        $model->exec($sql);
                    }
                }
            }

            $sql = "SELECT `product_id` FROM `shop_product_skus` WHERE `sku` = '".$sku."'";
            $prod_id = $model->query($sql)->fetchAll();
            foreach ($prod_id as $index => $rowq) {
                $pr_ids[$rowq['product_id']] = $rowq['product_id'];
                /*waLog::log(var_export($rowq['product_id'], true), '321654.log');*/
            }
        }
        $pr_model->add($pr_ids,'20221201_1');

        $sql = "SELECT * FROM `shop_order_items` WHERE `mark`=1";
        $prod_id = $model->query($sql)->fetchAll();
        foreach ($prod_id as $prod) {
            if (in_array($prod['sku_code'], $sku_mas)) {
                if (in_array($prod['order_id'], $order_mas)) {

                }
                else{
                    $sql = "UPDATE `shop_order_items` SET `mark`=0 WHERE `id`=" . $prod['id'];
                    $model->exec($sql);
                }
            }
            else{
                $sql = "UPDATE `shop_order_items` SET `mark`=0 WHERE `id`=" . $prod['id'];
                $model->exec($sql);
            }
        }
    }
}