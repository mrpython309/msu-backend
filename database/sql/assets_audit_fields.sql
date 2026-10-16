-- Latest asset audit, written by the mobile audit submission.
-- Null columns mean the asset has never been audited.
-- The export and asset view show "-" / "Pending Audit" for those rows.

ALTER TABLE `assets`
  ADD COLUMN `audited_by` BIGINT UNSIGNED NULL AFTER `custodian_id`,
  ADD COLUMN `audited_at` DATETIME NULL AFTER `audited_by`,
  ADD COLUMN `audit_status` VARCHAR(30) NULL AFTER `audited_at`;

ALTER TABLE `assets`
  ADD CONSTRAINT `assets_audited_by_foreign`
  FOREIGN KEY (`audited_by`) REFERENCES `users` (`id`)
  ON DELETE SET NULL;
