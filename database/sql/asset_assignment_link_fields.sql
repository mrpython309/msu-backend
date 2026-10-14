-- Assignment form: Department and Link with Master.
-- Import this whole file in phpMyAdmin. Safe to run more than once.

SET @db := DATABASE();

SET @has_department := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'department_id'
);

SET @sql := IF(
  @has_department > 0,
  'SELECT ''asset_assignments.department_id already exists'' AS message',
  'ALTER TABLE `asset_assignments` ADD COLUMN `department_id` bigint unsigned NULL AFTER `assiged_emoloyee`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_department_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'department_id'
    AND REFERENCED_TABLE_NAME = 'departments'
);

SET @sql := IF(
  @has_department_fk > 0,
  'SELECT ''department_id already linked to departments'' AS message',
  'ALTER TABLE `asset_assignments` ADD CONSTRAINT `asset_assignments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_master := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'employee_master_id'
);

SET @sql := IF(
  @has_master > 0,
  'SELECT ''asset_assignments.employee_master_id already exists'' AS message',
  'ALTER TABLE `asset_assignments` ADD COLUMN `employee_master_id` bigint unsigned NULL AFTER `department_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_master_fk := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'asset_assignments'
    AND COLUMN_NAME = 'employee_master_id'
    AND REFERENCED_TABLE_NAME = 'employee_masters'
);

SET @sql := IF(
  @has_master_fk > 0,
  'SELECT ''employee_master_id already linked to employee_masters'' AS message',
  'ALTER TABLE `asset_assignments` ADD CONSTRAINT `asset_assignments_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
