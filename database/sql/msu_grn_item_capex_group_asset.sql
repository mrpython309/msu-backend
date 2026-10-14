-- MSU GRN items: CAPEX and Group Asset flags
-- Import in phpMyAdmin. Existing Capex GRN lines are marked CAPEX,
-- and lines with received qty greater than 1 are marked Group Asset
-- so previously saved receipts keep the old one-asset behavior.

SET @db := DATABASE();

SET @had_capex := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grn_items' AND COLUMN_NAME = 'is_capex'
);

SET @has_expense := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grns' AND COLUMN_NAME = 'expense_type'
);

SET @sql := (
  SELECT IF(
    @had_capex = 0,
    'ALTER TABLE `msu_grn_items` ADD COLUMN `is_capex` TINYINT(1) NOT NULL DEFAULT 0 AFTER `received_quantity`',
    'SELECT ''is_capex already exists'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'msu_grn_items' AND COLUMN_NAME = 'is_group_asset') = 0,
    'ALTER TABLE `msu_grn_items` ADD COLUMN `is_group_asset` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_capex`',
    'SELECT ''is_group_asset already exists'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    @had_capex = 0 AND @has_expense > 0,
    'UPDATE `msu_grn_items` i INNER JOIN `msu_grns` g ON g.id = i.msu_grn_id SET i.is_capex = 1, i.is_group_asset = IF(i.received_quantity > 1, 1, 0) WHERE LOWER(TRIM(g.expense_type)) = ''capex''',
    'SELECT ''capex item backfill skipped'' AS message'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
