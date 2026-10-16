-- MSU GRN: Approve / Cancel before adding to Asset
-- Import this file in phpMyAdmin (or any MySQL client). Do not run migrate.

-- -------------------------------------------------
-- 1) Add columns on msu_grns
-- -------------------------------------------------
SET @db := DATABASE();

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'asset_status') = 0,
    IF(
      (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'is_added_to_asset') > 0,
      'ALTER TABLE `msu_grns` ADD COLUMN `asset_status` VARCHAR(30) NOT NULL DEFAULT ''pending'' AFTER `is_added_to_asset`',
      'ALTER TABLE `msu_grns` ADD COLUMN `asset_status` VARCHAR(30) NOT NULL DEFAULT ''pending'''
    ),
    'SELECT ''asset_status already exists'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'asset_cancel_reason') = 0,
    IF(
      (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'asset_status') > 0,
      'ALTER TABLE `msu_grns` ADD COLUMN `asset_cancel_reason` TEXT NULL AFTER `asset_status`',
      'ALTER TABLE `msu_grns` ADD COLUMN `asset_cancel_reason` TEXT NULL'
    ),
    'SELECT ''asset_cancel_reason already exists'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Existing GRNs already converted to assets
UPDATE `msu_grns`
SET `asset_status` = 'approved'
WHERE `is_added_to_asset` = 1
  AND (`asset_status` IS NULL OR `asset_status` = '' OR `asset_status` = 'pending');

-- Remaining GRNs wait for Approve / Cancel
UPDATE `msu_grns`
SET `asset_status` = 'pending'
WHERE (`asset_status` IS NULL OR `asset_status` = '')
  AND (`is_added_to_asset` = 0 OR `is_added_to_asset` IS NULL);

-- -------------------------------------------------
-- 2) Permissions (so Approve / Cancel buttons work)
-- -------------------------------------------------
INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'msu-grn:approve', 'web', NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `permissions` WHERE `name` = 'msu-grn:approve' AND `guard_name` = 'web'
);

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'msu-grn:cancel', 'web', NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `permissions` WHERE `name` = 'msu-grn:cancel' AND `guard_name` = 'web'
);

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
INNER JOIN `roles` r
  ON r.guard_name = 'web'
 AND r.name IN ('Super Admin', 'superadmin', 'Admin', 'admin')
WHERE p.guard_name = 'web'
  AND p.name IN ('msu-grn:approve', 'msu-grn:cancel')
  AND NOT EXISTS (
    SELECT 1
    FROM `role_has_permissions` rhp
    WHERE rhp.permission_id = p.id
      AND rhp.role_id = r.id
  );

-- If permission cache is stored in the cache table, clear it
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cache') > 0,
    'DELETE FROM `cache` WHERE `key` LIKE ''%spatie.permission.cache%''',
    'SELECT ''cache table not found, skip'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
