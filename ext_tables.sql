#
# Table structure for table 'tx_saveformtodb_domain_model_formdata'
#
CREATE TABLE tx_saveformtodb_domain_model_formdata
(
    uid                int(11) NOT NULL auto_increment,
    pid                int(11) DEFAULT '0' NOT NULL,
    tstamp             int(11) DEFAULT '0' NOT NULL,
    crdate             int(11) DEFAULT '0' NOT NULL,
    cruser_id          int(11) DEFAULT '0' NOT NULL,
    deleted            tinyint(4) DEFAULT '0' NOT NULL,
    pluginUid          int(11) DEFAULT '0' NOT NULL,
    formIdentifier     varchar(255),
    formIdentifierPath varchar(1024) DEFAULT '' NOT NULL,
    senderEmail        varchar(255),
    values             text,

    PRIMARY KEY (uid),
    KEY                parent (pid)
);
#
# Table structure for table 'tx_saveformtodb_configuration'
#
CREATE TABLE tx_saveformtodb_configuration
(
    uid            int(11) NOT NULL auto_increment,
    pid            int(11) DEFAULT '0' NOT NULL,
    tstamp         int(11) DEFAULT '0' NOT NULL,
    crdate         int(11) DEFAULT '0' NOT NULL,
    cruser_id      int(11) DEFAULT '0' NOT NULL,
    deleted        tinyint(4) DEFAULT '0' NOT NULL,
    userId         int(11) NOT NULL,
    formIdentifier varchar(255),
    configuration  text,

    PRIMARY KEY (uid),
    KEY            parent (pid)
);
