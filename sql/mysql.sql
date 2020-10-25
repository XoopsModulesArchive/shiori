#
# `xoops_shiori_bookmark`
#

CREATE TABLE `shiori_bookmark` (
    `id`      INT(10)      NOT NULL AUTO_INCREMENT,
    `uid`     MEDIUMINT(8) NOT NULL DEFAULT '0',
    `mid`     SMALLINT(5)  NOT NULL DEFAULT '0',
    `date`    INT(10)      NOT NULL DEFAULT '0',
    `url`     VARCHAR(250) NOT NULL DEFAULT '',
    `sort`    INT(3)       NOT NULL DEFAULT '0',
    `name`    VARCHAR(200) NOT NULL DEFAULT '',
    `icon`    VARCHAR(100) NOT NULL DEFAULT '',
    `counter` INT(10)      NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `id` (`id`),
    KEY `mid` (`mid`),
    KEY `url` (`url`),
    KEY `uid` (`uid`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

