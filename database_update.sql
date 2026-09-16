-- Run this once in the `project` database after the existing schema has been imported.

ALTER TABLE `user`
  ADD COLUMN `status` ENUM('pending','active') NOT NULL DEFAULT 'pending' AFTER `role`;

-- Keep the existing administrator and current sample accounts usable.
UPDATE `user` SET `status` = 'active' WHERE `role` = 'admin';
UPDATE `user` SET `status` = 'active' WHERE `id` IN (10,15,17,18,19);
