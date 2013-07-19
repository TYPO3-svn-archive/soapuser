#
# INDEX
#
# 'tx_soapuser_groups'



#
# Table structure for table 'tx_soapuser_groups'
#
CREATE TABLE tx_soapuser_groups (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  datgroup varchar(255) DEFAULT '' NOT NULL,
  fe_groups varchar(255) DEFAULT '' NOT NULL,
  note text,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);