DROP DATABASE IF EXISTS mydb1;
CREATE DATABASE mydb1;
USE mydb1;

CREATE TABLE `products`
(
    `id`    INT           NOT NULL AUTO_INCREMENT,
    `name`  VARCHAR(200)  NOT NULL,
    `price` DECIMAL(9, 2) NOT NULL,
    `type`  VARCHAR(10)   NULL,
    PRIMARY KEY (`id`)
) COLLATE = 'utf8mb4_general_ci';

CREATE TABLE `members`
(
    `id`    INT          NOT NULL AUTO_INCREMENT,
    `phone` VARCHAR(11)  NOT NULL,
    `fio`   VARCHAR(100) NULL,
    `email` VARCHAR(50)  NULL,
    PRIMARY KEY (`id`)
) COLLATE = 'utf8mb4_general_ci';

CREATE TABLE `shipments`
(
    `id`          INT           NOT NULL AUTO_INCREMENT,
    `address`     VARCHAR(200)  NOT NULL,
    `total_price` DECIMAL(9, 2) NOT NULL,
    `deliver_at`  DATETIME      NOT NULL,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `member_id`   INT           NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FOREIGN KEY `FK_SHIPMENT_ON_MEMBER` (`member_id`) REFERENCES `members` (`id`)
) COLLATE = 'utf8mb4_general_ci';

CREATE TABLE `shipments_products`
(
    `id`          INT NOT NULL AUTO_INCREMENT,
    `shipment_id` INT NOT NULL,
    `product_id`  INT NOT NULL,
    `cnt`         INT NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT FOREIGN KEY `FK_SHIPMENT_PRODUCT_ON_SHIPMENT` (`shipment_id`) REFERENCES `shipments` (`id`),
    CONSTRAINT FOREIGN KEY `FK_SHIPMENT_PRODUCT_ON_PRODUCT` (`product_id`) REFERENCES `products` (`id`)
) COLLATE = 'utf8mb4_general_ci';

INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Маргарита', '599.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Гавайская', '599.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Мексиканская', '659.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Хот Пепперони', '659.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Вестерн Барбекю', '659.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Маленькая Италия', '659.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Любимая папина пицца', '719.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Итальянская с моцареллой и пепперони', '719.00', 'pizza');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Горчица медовая', '49.00', 'sauce');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Соус Кисло-сладкий', '49.00', 'sauce');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Соус Тысяча островов', '49.00', 'sauce');
INSERT INTO `products` (`name`, `price`, `type`) VALUES ('Соус Особый Чесночный', '49.00', 'sauce');
