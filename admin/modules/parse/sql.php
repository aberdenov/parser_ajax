CREATE TABLE module_parse (
id INT UNSIGNED NOT NULL AUTO_INCREMENT,
page_id INT UNSIGNED NOT NULL,
lang_id INT UNSIGNED NOT NULL,
name VARCHAR( 255 ) NOT NULL ,
param_parse int(10) unsigned NOT NULL default '0' ,
price_cut_type1 int(10) unsigned NOT NULL default '0' ,
price_cut_type2 int(10) unsigned NOT NULL default '0' ,
title_clean int(10) unsigned NOT NULL default '0' ,
sortfield int(10) unsigned NOT NULL default '0' ,
PRIMARY KEY ( id ) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8