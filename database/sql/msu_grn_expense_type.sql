-- MSU GRN: Capex / Opex field
-- Import in phpMyAdmin. Do not run migrate.

SET @db := DATABASE();

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'expense_type') = 0,
    IF(
      (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'vendor_invoice_date') > 0,
      'ALTER TABLE `msu_grns` ADD COLUMN `expense_type` VARCHAR(20) NULL AFTER `vendor_invoice_date`',
      'ALTER TABLE `msu_grns` ADD COLUMN `expense_type` VARCHAR(20) NULL'
    ),
    'SELECT ''expense_type already exists'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- If a GRN was created after selecting Capex but still shows --, set the latest one:
-- UPDATE msu_grns SET expense_type = 'capex' WHERE id = (SELECT id FROM (SELECT MAX(id) AS id FROM msu_grns) t);
