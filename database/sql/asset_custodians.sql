-- Asset Custodian master, same shape as Employee Master.
-- Import this whole file in phpMyAdmin. Safe to run more than once.
-- Existing employee master rows are copied so current asset links stay valid,
-- then assets and assignments point at asset_custodians.

SET @db := DATABASE();

CREATE TABLE IF NOT EXISTS `asset_custodians` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_name` varchar(255) NOT NULL,
  `department_id` bigint unsigned NULL,
  `mobile_number` varchar(20) NULL,
  `email` varchar(255) NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_custodians_department_id_foreign` (`department_id`),
  CONSTRAINT `asset_custodians_department_id_foreign`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `asset_custodians`
  (`id`, `employee_name`, `department_id`, `mobile_number`, `email`, `status`, `created_at`, `updated_at`)
SELECT `id`, `employee_name`, `department_id`, `mobile_number`, `email`, `status`, `created_at`, `updated_at`
FROM `employee_masters`
WHERE NOT EXISTS (
  SELECT 1 FROM `asset_custodians` ac WHERE ac.`id` = `employee_masters`.`id`
);

-- -------------------------------------------------
-- Assets.custodian_id -> asset_custodians
-- -------------------------------------------------
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'assets'
    AND COLUMN_NAME = 'custodian_id'
    AND REFERENCED_TABLE_NAME IS NOT NULL
  LIMIT 1
);

SET @sql := IF(
  @fk IS NULL,
  'SELECT ''no foreign key on assets.custodian_id'' AS message',
  CONCAT('ALTER TABLE `assets` DROP FOREIGN KEY `', @fk, '`')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `assets`
SET `custodian_id` = NULL
WHERE `custodian_id` IS NOT NULL
  AND `custodian_id` NOT IN (SELECT `id` FROM `asset_custodians`);

SET @has_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'assets'
    AND COLUMN_NAME = 'custodian_id'
    AND REFERENCED_TABLE_NAME = 'asset_custodians'
);

SET @sql := IF(
  @has_fk > 0,
  'SELECT ''assets.custodian_id already linked to asset_custodians'' AS message',
  'ALTER TABLE `assets` ADD CONSTRAINT `assets_custodian_id_foreign` FOREIGN KEY (`custodian_id`) REFERENCES `asset_custodians` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------
-- Assignments.assiged_emoloyee -> asset_custodians
-- -------------------------------------------------
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'assiged_emoloyee'
    AND REFERENCED_TABLE_NAME IS NOT NULL
  LIMIT 1
);

SET @sql := IF(
  @fk IS NULL,
  'SELECT ''no foreign key on asset_assignments.assiged_emoloyee'' AS message',
  CONCAT('ALTER TABLE `asset_assignments` DROP FOREIGN KEY `', @fk, '`')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `asset_assignments`
SET `assiged_emoloyee` = NULL
WHERE `assiged_emoloyee` IS NOT NULL
  AND `assiged_emoloyee` NOT IN (SELECT `id` FROM `asset_custodians`);

SET @has_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'assiged_emoloyee'
    AND REFERENCED_TABLE_NAME = 'asset_custodians'
);

SET @sql := IF(
  @has_fk > 0,
  'SELECT ''assiged_emoloyee already linked to asset_custodians'' AS message',
  'ALTER TABLE `asset_assignments` ADD CONSTRAINT `asset_assignments_assiged_emoloyee_foreign` FOREIGN KEY (`assiged_emoloyee`) REFERENCES `asset_custodians` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'asset-custodian:view', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'asset-custodian:view' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'asset-custodian:create', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'asset-custodian:create' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'asset-custodian:edit', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'asset-custodian:edit' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'asset-custodian:delete', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'asset-custodian:delete' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'asset-custodian:status', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'asset-custodian:status' AND `guard_name` = 'web');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
JOIN `roles` r ON r.guard_name = 'web' AND r.name IN ('Super Admin', 'superadmin', 'Admin', 'admin')
WHERE p.name IN (
    'asset-custodian:view',
    'asset-custodian:create',
    'asset-custodian:edit',
    'asset-custodian:delete',
    'asset-custodian:status'
  )
  AND p.guard_name = 'web'
  AND NOT EXISTS (
    SELECT 1 FROM `role_has_permissions` rp
    WHERE rp.permission_id = p.id AND rp.role_id = r.id
  );
