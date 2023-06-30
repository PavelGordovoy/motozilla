<?php

class shopShipperiodCli extends waCliController
{
    public function execute() {
        $csv_url = 'http://motooem.com/motozilla-1c-sync/PlanData.csv';
        $rows = array_map('trim', file($csv_url));
        $model = new waModel();

        $csv_file = fopen($csv_url, "r");

        $sql = "TRUNCATE TABLE `shop_ship_period`";
        $model->exec($sql);

        foreach ($rows as $index => $row) {
            $data = array_map('trim', explode(';', $row));
            $sql = "INSERT INTO `shop_ship_period`(postavshik, settime, workday, worktime, shipingdate) VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]')";
            $model->exec($sql);
        }

        fclose($csv_file);

        $csv_url = 'http://motooem.com/motozilla-1c-sync/analiz_vygruzka.csv';
        $rows = array_map('trim', file($csv_url));
        $model = new waModel();

        $csv_file = fopen($csv_url, "r");

        $sql = "TRUNCATE TABLE `shop_analiz_vygruzka`";
        $model->exec($sql);

        foreach ($rows as $index => $row) {
            $data = array_map('trim', explode(';', $row));
            $sql = "INSERT INTO `shop_analiz_vygruzka`(sku, order_id, all_stock, in_stock, ordered, have, data, else_value) VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]', '$data[7]')";
            $model->exec($sql);
        }

        fclose($csv_file);

    }
}