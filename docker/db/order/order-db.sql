SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `order` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `order`;

CREATE TABLE `orders`
(
    `id`        int(11)    NOT NULL AUTO_INCREMENT,
    `amount`    int(11)    NOT NULL,
    `currency`  char(3)    NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

