<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

try {
    $model = new waModel();
    $sql = <<<SQL
  CREATE TABLE IF NOT EXISTS `helpdesk_pro_attachments` (          
        `request_id` int(11) NOT NULL,                   
        `log_id` int(11) NOT NULL,                       
        `attach_id` int(11) NOT NULL,                    
        `name` varchar(255) DEFAULT NULL,                
        `size` int(11) DEFAULT NULL,                     
        `not_found` tinyint(1) DEFAULT '0',              
        `file` varchar(255) DEFAULT NULL,                
        `attach_datetime` datetime DEFAULT NULL,         
        PRIMARY KEY (`request_id`,`log_id`,`attach_id`)  
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;
    $model->exec($sql);
} catch (waDbException $e) {

}