<?php
/**fix locale field English to en_US and etc.**/
wa('mylang');
$model = new mylangModel();
$list = waLocale::getAll('name');
try {
    foreach ($list as $key => $value) {
        $model->updateByField('locale', $value, ['locale' => $key]);
    }
} catch (waDbException $e) {
        waLog::log($e->getMessage(), 'mylang_update.log');
}
