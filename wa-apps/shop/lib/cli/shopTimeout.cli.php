<?php

class shopTimeoutCli extends waCliController
{
    public function execute() {
        $model = new waModel();
        $sql = "SELECT * FROM `shop_order` WHERE `state_id`='oformlen-mz'";
        $orders = $model->query($sql)->fetchAll();
        $data_now = time();

        foreach ($orders as $order) {
            if (!empty($order['send_data'])){

                $order_data = strtotime($order['send_data']." 23:59:59");

                if ($order_data < $data_now){

                    waLog::dump('Замовлення '.$order['id']. ' Переведено в статус zaderzhivaetsya', 'Timeout.log');
                    waLog::dump('дата замовлення < Дата зараз', 'Timeout.log');
                    waLog::dump($order_data . ' < ' . $data_now, 'Timeout.log');
                    waLog::dump('************************', 'Timeout.log');

                    $workflow = new shopWorkflow();
                    $action = $workflow->getActionById('zaderzhivaetsya');
                    $action->run($order['id']);
                }
            }
        }

    }
}
