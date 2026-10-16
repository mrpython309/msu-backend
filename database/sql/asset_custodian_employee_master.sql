-- Create / Edit Asset columns for msulivedb2
-- Import this whole file in phpMyAdmin. Safe to run more than once.
-- Adds missing form columns, then links custodian_id to employee_masters.

SET @db := DATABASE();

-- -------------------------------------------------
-- 1) Columns used by Create / Edit Asset
-- -------------------------------------------------
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'hsn_code') = 0,
    'ALTER TABLE `assets` ADD COLUMN `hsn_code` VARCHAR(255) NULL',
    'SELECT ''hsn_code already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'uom') = 0,
    'ALTER TABLE `assets` ADD COLUMN `uom` VARCHAR(50) NULL',
    'SELECT ''uom already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'po_number') = 0,
    'ALTER TABLE `assets` ADD COLUMN `po_number` VARCHAR(255) NULL',
    'SELECT ''po_number already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'po_date') = 0,
    'ALTER TABLE `assets` ADD COLUMN `po_date` DATE NULL',
    'SELECT ''po_date already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'invoice_date') = 0,
    'ALTER TABLE `assets` ADD COLUMN `invoice_date` DATE NULL',
    'SELECT ''invoice_date already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'purchase_type') = 0,
    'ALTER TABLE `assets` ADD COLUMN `purchase_type` VARCHAR(30) NULL',
    'SELECT ''purchase_type already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'ownership_type') = 0,
    'ALTER TABLE `assets` ADD COLUMN `ownership_type` VARCHAR(30) NULL',
    'SELECT ''ownership_type already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'cost_centre') = 0,
    'ALTER TABLE `assets` ADD COLUMN `cost_centre` VARCHAR(255) NULL',
    'SELECT ''cost_centre already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'product_category_id') = 0,
    'ALTER TABLE `assets` ADD COLUMN `product_category_id` BIGINT UNSIGNED NULL',
    'SELECT ''product_category_id already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'product_sub_category_id') = 0,
    'ALTER TABLE `assets` ADD COLUMN `product_sub_category_id` BIGINT UNSIGNED NULL',
    'SELECT ''product_sub_category_id already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'custodian_id') = 0,
    'ALTER TABLE `assets` ADD COLUMN `custodian_id` BIGINT UNSIGNED NULL',
    'SELECT ''custodian_id already exists'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Asset Class, Category, and Brand are no longer required on the form
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'asset_type_id' AND IS_NULLABLE = 'NO') > 0,
    'ALTER TABLE `assets` MODIFY `asset_type_id` BIGINT UNSIGNED NULL',
    'SELECT ''asset_type_id already nullable or missing'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'category_id' AND IS_NULLABLE = 'NO') > 0,
    'ALTER TABLE `assets` MODIFY `category_id` BIGINT UNSIGNED NULL',
    'SELECT ''category_id already nullable or missing'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'sub_category_id' AND IS_NULLABLE = 'NO') > 0,
    'ALTER TABLE `assets` MODIFY `sub_category_id` BIGINT UNSIGNED NULL',
    'SELECT ''sub_category_id already nullable or missing'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'brand_id' AND IS_NULLABLE = 'NO') > 0,
    'ALTER TABLE `assets` MODIFY `brand_id` BIGINT UNSIGNED NULL',
    'SELECT ''brand_id already nullable or missing'' AS message'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------
-- 2) Custodian must point at employee_masters, not users
-- -------------------------------------------------
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'assets'
    AND COLUMN_NAME = 'custodian_id'
    AND REFERENCED_TABLE_NAME = 'users'
  LIMIT 1
);

SET @sql := IF(
  @fk IS NULL,
  'SELECT ''no users foreign key on custodian_id'' AS message',
  CONCAT('ALTER TABLE `assets` DROP FOREIGN KEY `', @fk, '`')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Only clear values that used to be user ids
SET @sql := IF(
  @fk IS NULL,
  'SELECT ''custodian values left as-is'' AS message',
  'UPDATE `assets` SET `custodian_id` = NULL WHERE `custodian_id` IS NOT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_emp_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'assets'
    AND COLUMN_NAME = 'custodian_id'
    AND REFERENCED_TABLE_NAME IN ('employee_masters', 'asset_custodians')
);

SET @sql := IF(
  @has_emp_fk > 0,
  'SELECT ''custodian already linked'' AS message',
  'ALTER TABLE `assets` ADD CONSTRAINT `assets_custodian_id_foreign` FOREIGN KEY (`custodian_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------
-- 3) Product category links
-- -------------------------------------------------
SET @has_pc_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'assets'
    AND COLUMN_NAME = 'product_category_id'
    AND REFERENCED_TABLE_NAME = 'product_categories'
);

SET @sql := IF(
  @has_pc_fk > 0,
  'SELECT ''product_category_id already linked'' AS message',
  'ALTER TABLE `assets` ADD CONSTRAINT `assets_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_psc_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'assets'
    AND COLUMN_NAME = 'product_sub_category_id'
    AND REFERENCED_TABLE_NAME = 'product_sub_categories'
);

SET @sql := IF(
  @has_psc_fk > 0,
  'SELECT ''product_sub_category_id already linked'' AS message',
  'ALTER TABLE `assets` ADD CONSTRAINT `assets_product_sub_category_id_foreign` FOREIGN KEY (`product_sub_category_id`) REFERENCES `product_sub_categories` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------
-- 4) Assignment custodian must point at employee_masters, not users
-- -------------------------------------------------
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'assiged_emoloyee'
    AND REFERENCED_TABLE_NAME = 'users'
  LIMIT 1
);

SET @sql := IF(
  @fk IS NULL,
  'SELECT ''no users foreign key on assiged_emoloyee'' AS message',
  CONCAT('ALTER TABLE `asset_assignments` DROP FOREIGN KEY `', @fk, '`')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `asset_assignments`
SET `assiged_emoloyee` = NULL
WHERE `assiged_emoloyee` IS NOT NULL
  AND `assiged_emoloyee` NOT IN (SELECT `id` FROM `employee_masters`);

SET @has_emp_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'assiged_emoloyee'
    AND REFERENCED_TABLE_NAME IN ('employee_masters', 'asset_custodians')
);

SET @sql := IF(
  @has_emp_fk > 0,
  'SELECT ''assiged_emoloyee already linked'' AS message',
  'ALTER TABLE `asset_assignments` ADD CONSTRAINT `asset_assignments_assiged_emoloyee_foreign` FOREIGN KEY (`assiged_emoloyee`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
