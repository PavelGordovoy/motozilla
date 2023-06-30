<?php
class shopPashok
{

    public static function getDiscount($contact_id)
    {

        $cm = new shopCustomerModel();
        $customer = $cm->getById($contact_id);
        if ($customer && ($customer['total_spent'] > 0)) {
            $dbsm = new shopDiscountBySumModel();
            $percent = $dbsm->getDiscount('customer_total', $customer['total_spent']);
            return $percent;
        }
        else{
            return null;
        }
    }
    public static function getBonus($contact_id)
    {
        $zc = new shopAffiliateTransactionModel();
        $bonus = $zc->getByContact($contact_id);
        if ($bonus) {
            return $bonus;
        }
        else{
            return null;
        }
    }
    public static function getOrderStatus($order_id)
    {
        $order_model = new shopOrderModel();
        $order = $order_model->getById($order_id);
        if ($order) {
            return $order;
        }
        else{
            return null;
        }
    }
    public static function getReviews($product_id)
    {
        $order_model = new shopProductReviewsModel();
        $reviews = $order_model->getReviews($product_id);
        if ($reviews) {
            return $reviews;
        }
        else{
            return null;
        }
    }
    public static function getShipperiod()
    {
        $model = new waModel();
        $sql = "SELECT * FROM `shop_ship_period`";
        $period = $model->query($sql)->fetchAll();
        foreach ($period as $index => $rowq) {
            $m['settime'] = $rowq['postavshik'];
            $m['workday'] = $rowq['workday'];
            $m['worktime'] = $rowq['worktime'];
            $m['shipingdate'] = $rowq['shipingdate'];
            $p[$rowq['postavshik']] = $m;
        }

        return $p;

    }
    public static function getShipdate($order_id)
    {
        $order_id = preg_replace('/^70\s*/', '', $order_id);
        $model = new waModel();
        $sql = "SELECT * FROM `shop_ship_period`";
        $period = $model->query($sql)->fetchAll();
        foreach ($period as $index => $rowq) {
            $m['settime'] = $rowq['postavshik'];
            $m['workday'] = $rowq['workday'];
            $m['worktime'] = $rowq['worktime'];
            $m['shipingdate'] = $rowq['shipingdate'];
            $p[$rowq['postavshik']] = $m;
        }

        $dn = array(
            "0" => "",
            "1" => "понеділок",
            "2" => "вівторок",
            "3" => "середа",
            "4" => "четвер",
            "5" => "п'ятниця",
            "6" => "субота",
            "7" => "неділя",
        );

        $order_model = new shopOrderItemsModel();
        $items = $order_model->getItems($order_id);

        $product_model = new shopProductFeaturesModel();
        $max_data = 0;

        foreach ($items as $item) {
            $feature = $product_model->getValues($item['product_id']);
            if($item['send_data'] == NULL) {
                if (!empty($feature['kontragent_1C'])) {
                    $kontragent_1C = $feature['kontragent_1C'];
                    $data_p = $p[$kontragent_1C];
                    if ($item['stock_id'] == "5") {
                        $data_p = $p["SUMY"];
                    }
                    $data_p = strtotime($data_p['shipingdate']);
                    if ($max_data < $data_p) {
                        $max_data = $data_p;
                    }
                }
            }
        }
        $d = date('w', strtotime(date('Y-m-d H:i:s', $max_data)));
        $max_data_p = $dn[$d] . ' ' . $date = date('d.m', strtotime(date('Y-m-d H:i:s', $max_data)));

        return $max_data_p;

    }
    public static function getSkuStick($sku_id)
    {
        $prmodel = new shopProductSkusModel();
        $prod = $prmodel->getSku($sku_id);
        $cnt = $prod['stock']['5'];
        return $cnt;
    }
    public static function translateUa($product_id)
    {
        $translate = new mylangTranslate();
        $translated_fields = $translate->productsIds($product_id, 'uk_UA');
        return $translated_fields;
    }
    public static function getVygruzka($order_id)
    {
        $order_id = '70'.$order_id;
        $model = new waModel();
        $sql = "SELECT * FROM `shop_analiz_vygruzka` WHERE `order_id` = ".$order_id;
        $vygruzka = $model->query($sql)->fetchAll();
        foreach ($vygruzka as $index => $rowq) {
            $m[$rowq['sku']]['all_stock'] = $rowq['all_stock'];
            $m[$rowq['sku']]['in_stock'] = $rowq['in_stock'];
            $m[$rowq['sku']]['ordered'] = $rowq['ordered'];
            $m[$rowq['sku']]['have'] = $rowq['have'];
            $m[$rowq['sku']]['data'] = $rowq['data'];
        }
        return $m;

    }

}