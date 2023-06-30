<?php
$model = new waModel();
try{
	$model->exec("ALTER TABLE `wa_contact` DROP `in_sendpulse`");
	$model->exec("ALTER TABLE `wa_contact` DROP `sync_with_sendpulse`");
}catch (waDbException $e){
	
}
