SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `voucher`
--
CREATE DATABASE IF NOT EXISTS `voucher` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `voucher`;

CREATE TABLE `vouchers`
(
    `id`        int(11)    NOT NULL AUTO_INCREMENT,
    `order_id`   int(11)    NOT NULL,
    `amount`       int(11)  NOT NULL,
    `currency`     char(3)  NOT NULL,
    `inserted_at`  datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_id` (`order_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
