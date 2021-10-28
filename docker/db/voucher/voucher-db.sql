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
    `orderId`   int(11)    NOT NULL,
    `processed` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `orderId` (`orderId`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
