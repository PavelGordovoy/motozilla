<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

$model = new waModel();
try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_condition` (                     
                    `id` int(11) NOT NULL AUTO_INCREMENT,                  
                    `form_id` int(11) NOT NULL,                            
                    `action` varchar(10) NOT NULL,                         
                    `target` text NOT NULL,                                
                    `andor` tinyint(1) NOT NULL,                           
                    `sort` int(11) NOT NULL,                               
                    `tab` varchar(20) DEFAULT NULL,                        
                    PRIMARY KEY (`id`),                                    
                    KEY `form_id` (`form_id`)                              
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_condition_params` (                               
                    `condition_id` int(11) NOT NULL,                                        
                    `source` int(11) NOT NULL,                                              
                    `source_ext` int(11) NOT NULL DEFAULT '0',                              
                    `operator` varchar(20) NOT NULL,                                        
                    `value` varchar(255) NOT NULL,                                          
                    PRIMARY KEY (`condition_id`,`source`,`source_ext`,`operator`,`value`),  
                    KEY `condition_id` (`condition_id`)                                     
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_field_params` (    
                    `field_id` int(11) NOT NULL,             
                    `param` varchar(255) NOT NULL,           
                    `ext` varchar(255) NOT NULL DEFAULT '',  
                    `value` text,                            
                    KEY `field_id` (`field_id`)              
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_form_params` (     
                    `form_id` int(11) NOT NULL,              
                    `param` varchar(255) NOT NULL,           
                    `ext` varchar(255) NOT NULL DEFAULT '',  
                    `value` text NOT NULL,                   
                    PRIMARY KEY (`form_id`,`param`),         
                    KEY `ext` (`ext`)                        
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_record` (                        
                    `id` int(11) NOT NULL AUTO_INCREMENT,                  
                    `form_id` int(11) NOT NULL,                            
                    `create_datetime` datetime NOT NULL,                   
                    `create_contact_id` int(11) NOT NULL DEFAULT '0',      
                    `ip` varchar(15) NOT NULL DEFAULT '',                  
                    `status` tinyint(1) NOT NULL DEFAULT '0',              
                    `update_datetime` datetime DEFAULT NULL,               
                    `update_contact_id` int(11) NOT NULL DEFAULT '0',      
                    PRIMARY KEY (`id`),                                    
                    KEY `form_id` (`form_id`)                              
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_record_comment` (              
                    `id` int(11) NOT NULL AUTO_INCREMENT,                
                    `record_id` int(11) NOT NULL,                        
                    `name` varchar(50) DEFAULT '',                       
                    `comment` text,                                      
                    `create_datetime` datetime DEFAULT NULL,             
                    `create_contact_id` int(11) DEFAULT NULL,            
                    PRIMARY KEY (`id`),                                  
                    KEY `record_id` (`record_id`)                        
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_record_values` (       
                    `record_id` int(11) NOT NULL,                
                    `field_id` int(11) NOT NULL,                 
                    `ext` varchar(255) NOT NULL,                  
                    `value` text NOT NULL,                       
                    PRIMARY KEY (`record_id`,`field_id`,`ext`),  
                    KEY `record` (`record_id`)                   
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_theme` (                       
                   `id` int(11) NOT NULL AUTO_INCREMENT,                
                   `name` varchar(255) NOT NULL,                        
                   `create_datetime` datetime DEFAULT NULL,             
                   `update_datetime` datetime DEFAULT NULL,             
                   `create_contact_id` int(11) DEFAULT NULL,            
                   `update_contact_id` int(11) DEFAULT '0',            
                   `important` tinyint(1) DEFAULT '0',                  
                   PRIMARY KEY (`id`)                                   
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}

try {
    $model->exec("CREATE TABLE IF NOT EXISTS `multiform_theme_settings` (            
                    `theme_id` int(11) NOT NULL,                       
                    `property` varchar(50) NOT NULL,                   
                    `field` varchar(50) NOT NULL,                      
                    `ext` varchar(50) NOT NULL,                        
                    `value` varchar(255) DEFAULT NULL,                 
                    PRIMARY KEY (`theme_id`,`property`,`field`,`ext`)  
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
} catch (waDbException $e) {
    
}





