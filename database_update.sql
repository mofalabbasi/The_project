-- Run this once in the `project` database after the existing schema has been imported.

ALTER TABLE `user`
  ADD COLUMN `status` ENUM('pending','active') NOT NULL DEFAULT 'pending' AFTER `role`;

-- Keep the existing administrator and current sample accounts usable.
UPDATE `user` SET `status` = 'active' WHERE `role` = 'admin';
UPDATE `user` SET `status` = 'active' WHERE `id` IN (10,15,17,18,19);

-- Contact messages submitted from the Contact page.
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
