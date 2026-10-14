-- Cost Centre master, used by the asset create and edit Cost Centre list.
-- Import in phpMyAdmin. Permissions are added by the Laravel migration.

CREATE TABLE IF NOT EXISTS `cost_centres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cost_centre_name` varchar(255) NOT NULL,
  `cost_centre_description` text NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cost_centres_cost_centre_name_unique` (`cost_centre_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'cost-centres:view', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'cost-centres:view' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'cost-centres:create', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'cost-centres:create' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'cost-centres:edit', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'cost-centres:edit' AND `guard_name` = 'web');

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'cost-centres:delete', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'cost-centres:delete' AND `guard_name` = 'web');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
JOIN `roles` r ON r.guard_name = 'web' AND r.name IN ('Super Admin', 'superadmin', 'Admin', 'admin')
WHERE p.name IN ('cost-centres:view', 'cost-centres:create', 'cost-centres:edit', 'cost-centres:delete')
  AND p.guard_name = 'web'
  AND NOT EXISTS (
    SELECT 1 FROM `role_has_permissions` rp
    WHERE rp.permission_id = p.id AND rp.role_id = r.id
  );
