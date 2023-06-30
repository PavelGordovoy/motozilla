<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

$model = new waModel();
try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_action_form` (                   
                         `id` int(11) NOT NULL AUTO_INCREMENT,                  
                         `form_id` int(11) NOT NULL,                            
                         `action_code` varchar(50) NOT NULL,                    
                         `parent_id` int(11) DEFAULT '0',                       
                         `sort` int(11) DEFAULT '0',                            
                         `group_id` int(11) DEFAULT '0',                        
                         `status` tinyint(1) NOT NULL DEFAULT '0',              
                         PRIMARY KEY (`id`),                                    
                         KEY `form_id` (`form_id`)                              
                       ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS`multiform_action_form_settings` (  
                                  `form_id` int(11) NOT NULL,                    
                                  `action_id` int(11) NOT NULL,                  
                                  `param` varchar(255) NOT NULL,                 
                                  `ext` varchar(255) NOT NULL,                   
                                  `value` text,                                  
                                  KEY `action_id` (`action_id`)                  
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_action_group` (                
                          `id` int(11) NOT NULL AUTO_INCREMENT,                
                          `name` varchar(50) NOT NULL,                         
                          `form_id` int(11) NOT NULL,                          
                          `collapsed` tinyint(1) DEFAULT '0',                  
                          `sort` int(11) DEFAULT '-1',                         
                          PRIMARY KEY (`id`),                                  
                          KEY `form_id` (`form_id`)                            
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}



