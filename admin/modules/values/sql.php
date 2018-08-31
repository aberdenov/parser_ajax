CREATE TABLE module_values (
id INT UNSIGNED NOT NULL AUTO_INCREMENT,
page_id INT UNSIGNED NOT NULL,
lang_id INT UNSIGNED NOT NULL,
group_id INT UNSIGNED NOT NULL,
name VARCHAR( 255 ) NOT NULL ,
description VARCHAR( 255 ) ,
value TEXT,
sortfield int(10) unsigned NOT NULL default '0' ,
PRIMARY KEY ( id ) 
);