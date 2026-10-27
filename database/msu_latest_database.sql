-- MySQL dump 10.13  Distrib 8.4.11, for Linux (x86_64)
--
-- Host: localhost    Database: msu_test_db
-- ------------------------------------------------------
-- Server version	8.4.11-0ubuntu0.26.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `approval_history`
--

DROP TABLE IF EXISTS `approval_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indent_id` bigint unsigned NOT NULL,
  `workflow_id` bigint unsigned DEFAULT NULL,
  `sequence` int unsigned DEFAULT NULL,
  `approver_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `action_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_history_workflow_id_foreign` (`workflow_id`),
  KEY `approval_history_approver_id_foreign` (`approver_id`),
  KEY `approval_history_indent_id_action_index` (`indent_id`,`action`),
  CONSTRAINT `approval_history_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_history_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_history_workflow_id_foreign` FOREIGN KEY (`workflow_id`) REFERENCES `approval_workflows` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_history`
--

LOCK TABLES `approval_history` WRITE;
/*!40000 ALTER TABLE `approval_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_template_steps`
--

DROP TABLE IF EXISTS `approval_template_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_template_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `approval_template_id` bigint unsigned NOT NULL,
  `sequence` int unsigned NOT NULL,
  `assignee_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `approver_user_id` bigint unsigned DEFAULT NULL,
  `assignee_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_template_steps_seq_unique` (`approval_template_id`,`sequence`),
  KEY `approval_template_steps_approver_user_id_index` (`approver_user_id`),
  CONSTRAINT `approval_template_steps_approval_template_id_foreign` FOREIGN KEY (`approval_template_id`) REFERENCES `approval_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_template_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_template_steps`
--

LOCK TABLES `approval_template_steps` WRITE;
/*!40000 ALTER TABLE `approval_template_steps` DISABLE KEYS */;
INSERT INTO `approval_template_steps` VALUES (1,1,1,'user',3,NULL,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13'),(13,2,1,'user',3,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(14,3,1,'user',3,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(15,3,2,'user',4,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(16,4,1,'user',3,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(17,4,2,'user',4,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(18,4,3,'user',5,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(19,5,1,'user',3,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(20,6,1,'user',3,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(21,6,2,'user',4,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(22,6,3,'user',5,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48'),(23,6,4,'user',6,NULL,NULL,1,'2026-10-13 07:11:48','2026-10-13 07:11:48');
/*!40000 ALTER TABLE `approval_template_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_templates`
--

DROP TABLE IF EXISTS `approval_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_templates_module_category_unique` (`module`,`category_id`),
  KEY `approval_templates_is_active_index` (`is_active`),
  KEY `approval_templates_category_id_foreign` (`category_id`),
  CONSTRAINT `approval_templates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_templates`
--

LOCK TABLES `approval_templates` WRITE;
/*!40000 ALTER TABLE `approval_templates` DISABLE KEYS */;
INSERT INTO `approval_templates` VALUES (1,'Default Approval',NULL,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13'),(2,'Laptop Approval',1,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13'),(3,'Mobile Phone Approval',2,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13'),(4,'Server Approval',3,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13'),(5,'CCTV Approval',4,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13'),(6,'Networking Equipment Approval',5,NULL,1,'2026-10-13 07:11:13','2026-10-13 07:11:13');
/*!40000 ALTER TABLE `approval_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_workflows`
--

DROP TABLE IF EXISTS `approval_workflows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_workflows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workflow_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Indent',
  `category_id` bigint unsigned DEFAULT NULL,
  `sequence` int unsigned NOT NULL,
  `permission_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_workflows_category_id_foreign` (`category_id`),
  KEY `approval_workflows_lookup_index` (`module`,`category_id`,`sequence`),
  KEY `approval_workflows_module_index` (`module`),
  CONSTRAINT `approval_workflows_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_workflows`
--

LOCK TABLES `approval_workflows` WRITE;
/*!40000 ALTER TABLE `approval_workflows` DISABLE KEYS */;
INSERT INTO `approval_workflows` VALUES (8,'Manager Approval','Indent',4,1,'indent.approve.manager',1,1,'2026-10-13 07:11:46','2026-10-13 07:11:46'),(9,'Manager Approval','Indent',1,1,'indent.approve.manager',1,1,'2026-10-13 07:11:46','2026-10-13 07:11:46'),(10,'IT Head Approval','Indent',1,2,'indent.approve.it_head',1,1,'2026-10-13 07:11:46','2026-10-13 07:11:46'),(11,'Manager Approval','Indent',3,1,'indent.approve.manager',1,1,'2026-10-13 07:11:46','2026-10-13 07:11:46'),(12,'IT Head Approval','Indent',3,2,'indent.approve.it_head',1,1,'2026-10-13 07:11:46','2026-10-13 07:11:46'),(13,'Finance Approval','Indent',3,3,'indent.approve.finance',1,1,'2026-10-13 07:11:46','2026-10-13 07:11:46'),(14,'L1 Manager Approval','Indent',NULL,1,'indent.approve.l1',1,1,'2026-10-13 07:11:47','2026-10-13 07:11:47'),(15,'L2 IT Head Approval','Indent',NULL,2,'indent.approve.l2',1,1,'2026-10-13 07:11:47','2026-10-13 07:11:47'),(16,'L3 Finance Approval','Indent',NULL,3,'indent.approve.l3',1,1,'2026-10-13 07:11:47','2026-10-13 07:11:47');
/*!40000 ALTER TABLE `approval_workflows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_assignments`
--

DROP TABLE IF EXISTS `asset_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `from_branch` bigint unsigned DEFAULT NULL,
  `from_location` bigint unsigned DEFAULT NULL,
  `to_branch` bigint unsigned NOT NULL,
  `to_location` bigint unsigned DEFAULT NULL,
  `transfer_status` enum('in_transit','accepted','rejected','return_accepted','return') COLLATE utf8mb4_unicode_ci DEFAULT 'in_transit',
  `accepted_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `accepted_by` bigint unsigned DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `returned_by` bigint unsigned DEFAULT NULL,
  `return_accepted_by` bigint unsigned DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `return_accepted_at` datetime DEFAULT NULL,
  `return_accepted_notes` text COLLATE utf8mb4_unicode_ci,
  `return_notes` text COLLATE utf8mb4_unicode_ci,
  `accepted_notes` text COLLATE utf8mb4_unicode_ci,
  `rejected_notes` text COLLATE utf8mb4_unicode_ci,
  `assign_date` date DEFAULT NULL,
  `status` enum('assigned','returned','transferred') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transferred',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `assigned_notes` text COLLATE utf8mb4_unicode_ci,
  `assiged_emoloyee` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `from_company_id` bigint unsigned DEFAULT NULL,
  `to_company_id` bigint unsigned DEFAULT NULL,
  `assigned_to` enum('employee','branch') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'branch',
  PRIMARY KEY (`id`),
  KEY `asset_assignments_asset_id_foreign` (`asset_id`),
  KEY `asset_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `asset_assignments_from_company_id_foreign` (`from_company_id`),
  KEY `asset_assignments_to_company_id_foreign` (`to_company_id`),
  KEY `asset_assignments_assiged_emoloyee_foreign` (`assiged_emoloyee`),
  KEY `asset_assignments_department_id_foreign` (`department_id`),
  CONSTRAINT `asset_assignments_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_assignments_assiged_emoloyee_foreign` FOREIGN KEY (`assiged_emoloyee`) REFERENCES `asset_custodians` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_assignments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_assignments_from_company_id_foreign` FOREIGN KEY (`from_company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_assignments_to_company_id_foreign` FOREIGN KEY (`to_company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_assignments`
--

LOCK TABLES `asset_assignments` WRITE;
/*!40000 ALTER TABLE `asset_assignments` DISABLE KEYS */;
INSERT INTO `asset_assignments` VALUES (1,1,2,4,3,8,'rejected',NULL,'2026-10-12 12:41:54',NULL,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Rejected due to issue','2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-05 12:41:54','Initial assignment',NULL,NULL,1,2,'branch'),(2,2,3,9,4,11,'accepted','2026-10-12 12:41:54',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Accepted successfully',NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-12 12:41:54','Initial assignment',NULL,NULL,2,2,'branch'),(3,3,1,3,3,8,'return',NULL,NULL,NULL,NULL,4,NULL,'2026-10-10 12:41:54',NULL,NULL,'Asset returned',NULL,NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-12 12:41:54','Initial assignment',NULL,NULL,1,2,'branch'),(4,4,3,8,1,2,'accepted','2026-10-10 12:41:54',NULL,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Accepted successfully',NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-11 12:41:54','Initial assignment',NULL,NULL,2,1,'branch'),(5,5,3,8,4,10,'return',NULL,NULL,NULL,NULL,1,NULL,'2026-10-11 12:41:54',NULL,NULL,'Asset returned',NULL,NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-05 12:41:54','Initial assignment',NULL,NULL,2,2,'branch'),(6,6,4,10,2,4,'rejected',NULL,'2026-10-11 12:41:54',NULL,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Rejected due to issue','2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-11 12:41:54','Initial assignment',NULL,NULL,2,1,'branch'),(7,7,2,6,4,10,'in_transit',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-04 12:41:54','Initial assignment',NULL,NULL,1,2,'branch'),(8,8,2,6,4,11,'rejected',NULL,'2026-10-08 12:41:54',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Rejected due to issue','2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-12 12:41:54','Initial assignment',NULL,NULL,1,2,'branch'),(9,9,2,6,1,1,'return_accepted',NULL,NULL,NULL,NULL,1,1,'2026-10-10 12:41:54','2026-10-12 12:41:54','Return accepted','Asset returned',NULL,NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-08 12:41:54','Initial assignment',NULL,NULL,1,1,'branch'),(10,10,4,10,1,2,'return',NULL,NULL,NULL,NULL,5,NULL,'2026-10-09 12:41:54',NULL,NULL,'Asset returned',NULL,NULL,'2026-10-13','assigned',NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54',1,'2026-10-07 12:41:54','Initial assignment',NULL,NULL,2,1,'branch');
/*!40000 ALTER TABLE `asset_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_audits`
--

DROP TABLE IF EXISTS `asset_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `auditor_id` bigint unsigned DEFAULT NULL,
  `audit_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending Audit',
  `previous_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `company_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `audited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_audits_auditor_id_foreign` (`auditor_id`),
  KEY `asset_audits_asset_id_created_at_index` (`asset_id`,`created_at`),
  KEY `asset_audits_audit_status_index` (`audit_status`),
  KEY `asset_audits_company_id_foreign` (`company_id`),
  KEY `asset_audits_branch_id_foreign` (`branch_id`),
  KEY `asset_audits_location_id_foreign` (`location_id`),
  CONSTRAINT `asset_audits_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_audits_auditor_id_foreign` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_audits_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_audits_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_audits_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `branch_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_audits`
--

LOCK TABLES `asset_audits` WRITE;
/*!40000 ALTER TABLE `asset_audits` DISABLE KEYS */;
INSERT INTO `asset_audits` VALUES (1,1,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(2,2,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(3,3,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(4,4,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(5,5,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(6,6,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(7,7,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(8,8,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(9,9,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(10,10,NULL,'Pending Audit',NULL,NULL,'web',NULL,NULL,NULL,NULL,NULL,'Asset created','2026-10-13 07:11:54','2026-10-13 07:11:54','2026-10-13 07:11:54'),(12,1,1,'Audited','Pending Audit','matches','web',NULL,NULL,NULL,NULL,NULL,'Physical verification completed successfully','2026-10-13 08:46:36','2026-10-13 08:46:36','2026-10-13 08:46:36'),(13,1,1,'Audited','Audited','matches','web',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-13 10:03:33','2026-10-13 10:03:33','2026-10-13 10:03:33'),(14,2,1,'Audited','Pending Audit','Verified (Warehouse)','web',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-13 10:06:39','2026-10-13 10:06:39','2026-10-13 10:06:39'),(15,3,1,'Audited','Pending Audit','Verified (Warehouse)','web',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-13 10:18:00','2026-10-13 10:18:00','2026-10-13 10:18:00'),(16,5,1,'Audited','Pending Audit','Verified (HR Cabin)','web',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-13 10:23:50','2026-10-13 10:23:50','2026-10-13 10:23:50'),(17,4,1,'Audited','Pending Audit','matches','web',NULL,NULL,NULL,NULL,NULL,'Physical verification complete. Asset in perfect condition.','2026-10-13 10:39:30','2026-10-13 10:39:30','2026-10-13 10:39:30'),(18,4,1,'Audited','Audited','matches','web',NULL,NULL,NULL,NULL,NULL,'Physical verification complete. Asset in perfect condition.','2026-10-13 10:50:35','2026-10-13 10:50:35','2026-10-13 10:50:35'),(19,7,1,'Audited','Pending Audit','matches','web',NULL,NULL,NULL,NULL,NULL,'Verified in server room rack 2','2026-10-13 11:13:59','2026-10-13 11:13:59','2026-10-13 11:13:59'),(20,1,21,'Audited','Not Found','Location Matches','mobile_app',1,2,5,NULL,NULL,'found on location','2026-10-23 12:52:05','2026-10-23 12:52:05','2026-10-23 12:52:05');
/*!40000 ALTER TABLE `asset_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_categories`
--

DROP TABLE IF EXISTS `asset_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'asset',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_categories_parent_id_foreign` (`parent_id`),
  KEY `asset_categories_item_type_index` (`item_type`),
  CONSTRAINT `asset_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_categories`
--

LOCK TABLES `asset_categories` WRITE;
/*!40000 ALTER TABLE `asset_categories` DISABLE KEYS */;
INSERT INTO `asset_categories` VALUES (1,'Laptop',NULL,'active','asset','2026-10-13 07:11:13','2026-10-13 07:11:13'),(2,'Mobile Phone',NULL,'active','asset','2026-10-13 07:11:13','2026-10-13 07:11:13'),(3,'Server',NULL,'active','asset','2026-10-13 07:11:13','2026-10-13 07:11:13'),(4,'CCTV',NULL,'active','asset','2026-10-13 07:11:13','2026-10-13 07:11:13'),(5,'Networking Equipment',NULL,'active','asset','2026-10-13 07:11:13','2026-10-13 07:11:13'),(6,'Tangible Assets (Physical)',NULL,'active','asset',NULL,NULL),(7,'Intangible Assets (Non-Physical)',NULL,'active','asset',NULL,NULL),(8,'Machinery & Equipment',6,'active','asset',NULL,NULL),(9,'Vehicles',6,'active','asset',NULL,NULL),(10,'Computers / Laptops',6,'active','asset',NULL,NULL),(11,'Furniture & Fixtures',6,'active','asset',NULL,NULL),(12,'Tools & Instruments',6,'active','asset',NULL,NULL),(13,'Electrical & Mechanical Assets',6,'active','asset',NULL,NULL),(14,'Software Licenses',7,'active','asset',NULL,NULL),(15,'Patents & Trademarks',7,'active','asset',NULL,NULL),(16,'Brand Value',7,'active','asset',NULL,NULL),(17,'Domain Names',7,'active','asset',NULL,NULL),(18,'Digital Certificates',7,'active','asset',NULL,NULL),(19,'Intellectual Property',7,'active','asset',NULL,NULL);
/*!40000 ALTER TABLE `asset_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_custodians`
--

DROP TABLE IF EXISTS `asset_custodians`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_custodians` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `mobile_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_custodians_department_id_foreign` (`department_id`),
  CONSTRAINT `asset_custodians_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_custodians`
--

LOCK TABLES `asset_custodians` WRITE;
/*!40000 ALTER TABLE `asset_custodians` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_custodians` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_documents`
--

DROP TABLE IF EXISTS `asset_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `document_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_documents_asset_id_foreign` (`asset_id`),
  KEY `asset_documents_created_by_foreign` (`created_by`),
  KEY `asset_documents_updated_by_foreign` (`updated_by`),
  CONSTRAINT `asset_documents_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_documents`
--

LOCK TABLES `asset_documents` WRITE;
/*!40000 ALTER TABLE `asset_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_maintenance`
--

DROP TABLE IF EXISTS `asset_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_maintenance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `maintenance_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` enum('manual','support_ticket') COLLATE utf8mb4_unicode_ci NOT NULL,
  `maintenance_date` date DEFAULT NULL,
  `maintenance_notes` text COLLATE utf8mb4_unicode_ci,
  `maintenance_cost` decimal(10,2) DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `status` enum('pending','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `reported_by` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `support_ticket_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_maintenance_asset_id_foreign` (`asset_id`),
  KEY `asset_maintenance_vendor_id_foreign` (`vendor_id`),
  KEY `asset_maintenance_reported_by_foreign` (`reported_by`),
  KEY `asset_maintenance_created_by_foreign` (`created_by`),
  KEY `asset_maintenance_updated_by_foreign` (`updated_by`),
  KEY `asset_maintenance_support_ticket_id_foreign` (`support_ticket_id`),
  CONSTRAINT `asset_maintenance_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_maintenance_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_maintenance_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_maintenance_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_maintenance_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_maintenance_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_maintenance`
--

LOCK TABLES `asset_maintenance` WRITE;
/*!40000 ALTER TABLE `asset_maintenance` DISABLE KEYS */;
INSERT INTO `asset_maintenance` VALUES (1,1,'MTN-00001','support_ticket','2026-10-17','Automated ticket for system maintenance: Printer Paper Jam on 3rd Floor',NULL,NULL,'medium','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:10','2026-10-17 06:19:10',6),(2,1,'MTN-00002','support_ticket','2026-10-17','Automated ticket for system maintenance: VPN Authentication Failure from Remote Branch',NULL,NULL,'high','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',7),(3,1,'MTN-00003','support_ticket','2026-10-17','Automated ticket for system maintenance: Conference Room Audio Echoing',NULL,NULL,'low','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',8),(4,1,'MTN-00004','support_ticket','2026-10-17','Automated ticket for system maintenance: Core Switch Port 14 Flapping',NULL,NULL,'critical','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',9),(5,1,'MTN-00005','support_ticket','2026-10-17','Automated ticket for system maintenance: Monitor Display Discoloration',NULL,NULL,'medium','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',10),(6,1,'MTN-00006','support_ticket','2026-10-17','Automated ticket for system maintenance: Replace Faulty Laptop Charger',NULL,NULL,'low','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',11),(7,1,'MTN-00007','support_ticket','2026-10-17','Automated ticket for system maintenance: Outlook Crashing on Windows 11',NULL,NULL,'medium','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',12),(8,1,'MTN-00008','support_ticket','2026-10-17','Automated ticket for system maintenance: Request Access to Production Database',NULL,NULL,'high','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',13),(9,1,'MTN-00009','support_ticket','2026-10-17','Automated ticket for system maintenance: UPS Battery Replacement Required',NULL,NULL,'critical','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',14),(10,1,'MTN-00010','support_ticket','2026-10-17','Automated ticket for system maintenance: Install Visual Studio Code & Git',NULL,NULL,'low','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',15),(11,1,'MTN-00011','support_ticket','2026-10-17','Automated ticket for system maintenance: New Joiner Laptop Provisioning',NULL,NULL,'high','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',16),(12,1,'MTN-00012','support_ticket','2026-10-17','Automated ticket for system maintenance: Biometric Scanner Not Syncing',NULL,NULL,'critical','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',17),(13,1,'MTN-00013','support_ticket','2026-10-17','Automated ticket for system maintenance: Wi-Fi Signal Drop in Cafeteria',NULL,NULL,'medium','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',18),(14,1,'MTN-00014','support_ticket','2026-10-17','Automated ticket for system maintenance: Slow Performance on CAD Workstation',NULL,NULL,'high','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-17 06:19:11','2026-10-17 06:19:11',19),(15,1,'MTN-00015','support_ticket','2026-10-17','Automated ticket for system maintenance: Annual Antivirus License Renewal',0.00,NULL,'medium','completed','2026-10-17','2026-10-22',NULL,1,1,1,'2026-10-17 06:19:11','2026-10-22 11:30:29',20),(16,4,'MTN-00016','support_ticket','2026-10-19',NULL,NULL,NULL,'medium','pending',NULL,NULL,NULL,1,1,NULL,'2026-10-19 13:36:58','2026-10-19 13:36:58',21),(17,1,'MTN-00017','support_ticket','2026-10-22',NULL,NULL,NULL,'medium','pending',NULL,NULL,NULL,20,20,NULL,'2026-10-22 14:55:30','2026-10-22 14:55:30',22),(18,12,'MTN-00018','support_ticket','2026-10-23',NULL,0.00,1,'high','completed','2026-10-23','2026-10-23',NULL,21,21,1,'2026-10-23 10:42:00','2026-10-23 11:27:18',23);
/*!40000 ALTER TABLE `asset_maintenance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_names`
--

DROP TABLE IF EXISTS `asset_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_names` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_normalized` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_names_name_normalized_unique` (`name_normalized`),
  KEY `asset_names_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_names`
--

LOCK TABLES `asset_names` WRITE;
/*!40000 ALTER TABLE `asset_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_replacements`
--

DROP TABLE IF EXISTS `asset_replacements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_replacements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `old_serial_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_serial_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_condition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_condition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_operational_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_operational_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replacement_date` date DEFAULT NULL,
  `replacement_reason` text COLLATE utf8mb4_unicode_ci,
  `company_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_replacements_asset_id_foreign` (`asset_id`),
  KEY `asset_replacements_company_id_foreign` (`company_id`),
  KEY `asset_replacements_branch_id_foreign` (`branch_id`),
  KEY `asset_replacements_location_id_foreign` (`location_id`),
  CONSTRAINT `asset_replacements_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_replacements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_replacements_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_replacements_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `branch_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_replacements`
--

LOCK TABLES `asset_replacements` WRITE;
/*!40000 ALTER TABLE `asset_replacements` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_replacements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_types`
--

DROP TABLE IF EXISTS `asset_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_type_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_type_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `sub_category_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_types_category_id_foreign` (`category_id`),
  KEY `asset_types_sub_category_id_foreign` (`sub_category_id`),
  CONSTRAINT `asset_types_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_types_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_types`
--

LOCK TABLES `asset_types` WRITE;
/*!40000 ALTER TABLE `asset_types` DISABLE KEYS */;
INSERT INTO `asset_types` VALUES (1,'Laptop','Company issued laptops for employees','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,10),(2,'Desktop','Office desktop computers','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,10),(3,'Printer','Office printers and scanners','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,13),(4,'Router','Network routers and gateways','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,13),(5,'Switch','Network switches','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,13),(6,'Monitor','External monitors and displays','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,13),(7,'Projector','Projectors for meeting rooms','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,8),(8,'Biometric Device','Attendance & access control systems','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,8),(9,'Server','Physical and virtual servers','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,8),(10,'CCTV Camera','Surveillance and security cameras','active','2026-10-13 07:11:53','2026-10-13 07:11:53',6,8);
/*!40000 ALTER TABLE `asset_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_warranties`
--

DROP TABLE IF EXISTS `asset_warranties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_warranties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `covered_parts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warranty_type` enum('manufacturer','vendor','extended','amc') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manufacturer',
  `warranty_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warranty_expire_date` date DEFAULT NULL,
  `usage_metric` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_usage` int DEFAULT NULL,
  `status` enum('active','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_warranties_asset_id_foreign` (`asset_id`),
  KEY `asset_warranties_created_by_foreign` (`created_by`),
  KEY `asset_warranties_updated_by_foreign` (`updated_by`),
  CONSTRAINT `asset_warranties_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_warranties_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_warranties_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_warranties`
--

LOCK TABLES `asset_warranties` WRITE;
/*!40000 ALTER TABLE `asset_warranties` DISABLE KEYS */;
INSERT INTO `asset_warranties` VALUES (1,4,'Motherboard, Processor, RAM, SSD','manufacturer','WAR-M6IOBMLD','2028-02-13','hours',1618,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(2,6,'Motherboard, Processor, RAM, SSD','amc','WAR-Q7BTTROC','2027-11-13','hours',1480,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(3,9,'Motherboard, Processor, RAM, SSD','extended','WAR-6EDAGYZQ','2028-06-13','hours',7915,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(4,1,'Motherboard, Processor, RAM, SSD','manufacturer','WAR-NMMP41RQ','2028-06-13','hours',2611,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(5,10,'Motherboard, Processor, RAM, SSD','amc','WAR-J7CWL23Q','2029-03-13','hours',4101,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(6,5,'Motherboard, Processor, RAM, SSD','vendor','WAR-PCVTF9G9','2029-01-13','hours',7189,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(7,7,'Motherboard, Processor, RAM, SSD','manufacturer','WAR-DYQ33N8V','2027-10-13','hours',6534,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(8,2,'Motherboard, Processor, RAM, SSD','vendor','WAR-PMZ5R53O','2028-02-13','hours',7624,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(9,3,'Motherboard, Processor, RAM, SSD','manufacturer','WAR-SWOWLMIO','2028-09-13','hours',4516,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54'),(10,8,'Motherboard, Processor, RAM, SSD','extended','WAR-ZYTQVHSL','2027-09-13','hours',1065,'active','Auto seeded warranty record',1,NULL,'2026-10-13 07:11:54','2026-10-13 07:11:54');
/*!40000 ALTER TABLE `asset_warranties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_structure` enum('single','group') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `category_id` bigint unsigned DEFAULT NULL,
  `sub_category_id` bigint unsigned DEFAULT NULL,
  `product_category_id` bigint unsigned DEFAULT NULL,
  `product_sub_category_id` bigint unsigned DEFAULT NULL,
  `items_count` int DEFAULT NULL,
  `uom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_type_id` bigint unsigned DEFAULT NULL,
  `serial_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(18,3) DEFAULT NULL,
  `status` enum('available','assigned','in_transit','transferred','under_maintenance','disposed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition` enum('new','good','fair','poor') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `branch_id` bigint unsigned DEFAULT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `supplier` bigint unsigned DEFAULT NULL,
  `grn_item_id` bigint unsigned DEFAULT NULL,
  `msu_grn_item_id` bigint unsigned DEFAULT NULL,
  `source_unit_index` int unsigned DEFAULT NULL,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `indent_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `custodian_id` bigint unsigned DEFAULT NULL,
  `audited_by` bigint unsigned DEFAULT NULL,
  `audited_at` datetime DEFAULT NULL,
  `audit_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `invoice_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `purchase_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ownership_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_centre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warrenty_information` text COLLATE utf8mb4_unicode_ci,
  `warrenty_expires` date DEFAULT NULL,
  `depreciation_method` enum('no_depreciation','straight_line','reducing_balance') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userful_life` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salvage_value` decimal(10,2) DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_nos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operational_status` enum('working','under_maintenance','damaged','disposed') COLLATE utf8mb4_unicode_ci DEFAULT 'working',
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_grn_item_unit_unique` (`grn_item_id`,`source_unit_index`),
  UNIQUE KEY `assets_msu_grn_item_unit_unique` (`msu_grn_item_id`,`source_unit_index`),
  KEY `assets_location_id_foreign` (`location_id`),
  KEY `assets_created_by_foreign` (`created_by`),
  KEY `assets_updated_by_foreign` (`updated_by`),
  KEY `assets_branch_id_foreign` (`branch_id`),
  KEY `assets_brand_id_foreign` (`brand_id`),
  KEY `assets_company_id_foreign` (`company_id`),
  KEY `assets_asset_code_idx` (`asset_code`),
  KEY `assets_serial_no_idx` (`serial_no`),
  KEY `assets_asset_name_idx` (`asset_name`),
  KEY `assets_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `assets_indent_id_foreign` (`indent_id`),
  KEY `assets_department_id_foreign` (`department_id`),
  KEY `assets_product_category_id_foreign` (`product_category_id`),
  KEY `assets_product_sub_category_id_foreign` (`product_sub_category_id`),
  KEY `assets_asset_type_id_foreign` (`asset_type_id`),
  KEY `assets_category_id_foreign` (`category_id`),
  KEY `assets_sub_category_id_foreign` (`sub_category_id`),
  KEY `assets_custodian_id_foreign` (`custodian_id`),
  KEY `assets_audited_by_foreign` (`audited_by`),
  CONSTRAINT `assets_asset_type_id_foreign` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_audited_by_foreign` FOREIGN KEY (`audited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_custodian_id_foreign` FOREIGN KEY (`custodian_id`) REFERENCES `asset_custodians` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_grn_item_id_foreign` FOREIGN KEY (`grn_item_id`) REFERENCES `grn_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `branch_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_msu_grn_item_id_foreign` FOREIGN KEY (`msu_grn_item_id`) REFERENCES `msu_grn_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_product_sub_category_id_foreign` FOREIGN KEY (`product_sub_category_id`) REFERENCES `product_sub_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (1,'Dell Latitude 7420','single',6,10,NULL,NULL,1,NULL,1,'IBXOVLRZXU',NULL,'MSU000001','2026-08-15',159385.000,'available','new',1,'Auto generated asset for ERP testing',2,5,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,21,'2026-10-23 12:52:05','audited',2,'INV-SUGXST',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','4',3180.00,1,1,'2026-10-13 07:11:54','2026-10-23 12:52:05',NULL,'MSU','1','under_maintenance'),(2,'HP EliteBook 850 G8','single',6,8,NULL,NULL,1,NULL,8,'LVFHLG0P3J',NULL,'MSU000002','2026-01-08',130481.000,'assigned','new',2,'Auto generated asset for ERP testing',4,11,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-13 15:36:39','Audited',2,'INV-3V3VVA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','5',1025.00,1,1,'2026-10-13 07:11:54','2026-10-13 10:06:39',NULL,'MSU','2','working'),(3,'Lenovo ThinkPad T14','single',6,10,NULL,NULL,1,NULL,1,'JVTCEEKFIE',NULL,'MSU000003','2025-02-07',160617.000,'available','new',2,'Auto generated asset for ERP testing',4,11,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-13 15:48:00','Audited',4,'INV-LIJBTW',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','5',3960.00,1,10,'2026-10-13 07:11:54','2026-10-13 10:18:00',NULL,'MSU','3','working'),(4,'Canon ImageRunner 2525','single',6,8,NULL,NULL,1,NULL,10,'UJHAXSEJKZ',NULL,'MSU000004','2025-10-06',92458.000,'assigned','new',1,'Auto generated asset for ERP testing',1,2,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-13 16:20:35','Audited',1,'INV-CYCGUI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','3',5436.00,1,1,'2026-10-13 07:11:54','2026-10-19 13:36:58',NULL,'MSU','4','under_maintenance'),(5,'Epson L3150 Printer','single',6,10,NULL,NULL,1,NULL,2,'SIWPZXRPGU',NULL,'MSU000005','2024-06-12',241751.000,'available','new',2,'Auto generated asset for ERP testing',3,9,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-13 15:53:50','Audited',1,'INV-6E5TPI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','5',2849.00,1,1,'2026-10-13 07:11:54','2026-10-13 10:23:50',NULL,'MSU','5','working'),(6,'Cisco ISR 4331 Router','single',6,13,NULL,NULL,1,NULL,6,'QRJKXRVFBC',NULL,'MSU000006','2026-05-29',134245.000,'available','new',1,'Auto generated asset for ERP testing',2,4,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',5,'INV-ENKQQK',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','5',8398.00,1,2,'2026-10-13 07:11:54','2026-10-13 07:11:54',NULL,'MSU','6','working'),(7,'HP ProLiant DL380 Server','single',6,10,NULL,NULL,1,NULL,1,'FQRIHOC2GR',NULL,'MSU000007','2024-02-01',142667.000,'transferred','new',2,'Auto generated asset for ERP testing',4,10,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-13 16:43:59','Audited',4,'INV-FNYCQ7',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','3',8534.00,1,1,'2026-10-13 07:11:54','2026-10-13 11:13:59',NULL,'MSU','7','working'),(8,'Samsung 27 Monitor','single',6,13,NULL,NULL,1,NULL,5,'AGMJTTIC88',NULL,'MSU000008','2026-07-08',219825.000,'available','new',2,'Auto generated asset for ERP testing',4,11,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',1,'INV-QPF29D',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','5',9643.00,1,11,'2026-10-13 07:11:54','2026-10-13 07:11:54',NULL,'MSU','8','working'),(9,'BenQ Projector','single',6,8,NULL,NULL,1,NULL,9,'I8OCDKZRQU',NULL,'MSU000009','2025-03-06',44911.000,'available','new',1,'Auto generated asset for ERP testing',2,4,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',4,'INV-7RX6MZ',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','3',4106.00,1,6,'2026-10-13 07:11:54','2026-10-13 07:11:54',NULL,'MSU','9','working'),(10,'Hikvision CCTV Camera','single',6,8,NULL,NULL,1,NULL,9,'V8ZSTQZPEJ',NULL,'MSU000010','2024-07-12',60934.000,'available','new',1,'Auto generated asset for ERP testing',2,6,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-14 10:28:10','audited',2,'INV-UHCZLN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'straight_line','4',9151.00,1,NULL,'2026-10-13 07:11:54','2026-10-14 04:58:10',NULL,'MSU','10','working'),(12,'MacBook Pro M2 14-inch','single',6,10,NULL,NULL,NULL,NULL,1,'SN3E759AD8EE',NULL,'MSU000011','2025-02-17',111411.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-23 11:27:17',NULL,'MSU','11','working'),(13,'Dell UltraSharp 27 4K Monitor','single',6,10,NULL,NULL,NULL,NULL,1,'SNA6B56F8325',NULL,'MSU000012','2025-01-17',120374.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','12','working'),(14,'HP LaserJet Enterprise MFP','single',6,10,NULL,NULL,NULL,NULL,1,'SNCAC50462F8',NULL,'MSU000013','2025-06-17',133121.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Audited',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-17 06:19:10','2026-10-19 15:38:17',NULL,'MSU','13','working'),(15,'Cisco Catalyst 2960 Switch','single',6,10,NULL,NULL,NULL,NULL,1,'SN43B8ECA2FC',NULL,'MSU000014','2025-12-17',136661.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','14','working'),(16,'Lenovo ThinkPad X1 Carbon','single',6,10,NULL,NULL,NULL,NULL,1,'SN901CAD570A',NULL,'MSU000015','2025-06-17',104061.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','15','working'),(17,'Apple iPad Pro 12.9 Cellular','single',6,10,NULL,NULL,NULL,NULL,1,'SNDE1CBC8A53',NULL,'MSU000016','2026-01-17',79660.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','16','working'),(18,'Sony WH-1000XM5 Headset','single',6,10,NULL,NULL,NULL,NULL,1,'SND7B4BEE677',NULL,'MSU000017','2026-05-17',54096.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','17','working'),(19,'APC Smart-UPS 3000VA','single',6,10,NULL,NULL,NULL,NULL,1,'SN133AE05778',NULL,'MSU000018','2026-01-17',26875.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','18','working'),(20,'Logitech Rally Bar Conference System','single',6,10,NULL,NULL,NULL,NULL,1,'SN33FC0AC89C',NULL,'MSU000019','2025-09-17',72846.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','19','working'),(21,'Samsung 34 Odyssey Curved Monitor','single',6,10,NULL,NULL,NULL,NULL,1,'SN00BAED53A3',NULL,'MSU000020','2026-04-17',107471.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','20','working'),(22,'Polycom Studio Video Bar','single',6,10,NULL,NULL,NULL,NULL,1,'SNABA8118FCD',NULL,'MSU000021','2025-05-17',61223.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','21','working'),(23,'Epson WorkForce Pro Scanner','single',6,10,NULL,NULL,NULL,NULL,1,'SNAFC40962CD',NULL,'MSU000022','2025-07-17',120328.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','22','working'),(24,'Fortinet FortiGate 60F Firewall','single',6,10,NULL,NULL,NULL,NULL,1,'SNE85ABD6D18',NULL,'MSU000023','2025-04-17',72565.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','23','working'),(25,'Ubiquiti UniFi AP 6 Pro','single',6,10,NULL,NULL,NULL,NULL,1,'SN64198F0E80',NULL,'MSU000024','2024-11-17',111856.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending Audit',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-17 06:19:10','2026-10-17 07:50:08',NULL,'MSU','24','working'),(26,'Dell PowerEdge R740 Server','single',6,10,NULL,NULL,NULL,NULL,1,'SN1CA01B23AB',NULL,'MSU000025','2024-11-17',144476.000,'available',NULL,1,NULL,2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Audited',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-17 06:19:10','2026-10-19 13:34:01',NULL,'MSU','25','working');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_histories`
--

DROP TABLE IF EXISTS `audit_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `auditor_id` bigint unsigned DEFAULT NULL,
  `auditor_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Auditor',
  `audit_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Audited',
  `location_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `audited_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_histories_auditor_id_foreign` (`auditor_id`),
  KEY `audit_histories_asset_id_audited_at_index` (`asset_id`,`audited_at`),
  CONSTRAINT `audit_histories_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_histories_auditor_id_foreign` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_histories`
--

LOCK TABLES `audit_histories` WRITE;
/*!40000 ALTER TABLE `audit_histories` DISABLE KEYS */;
INSERT INTO `audit_histories` VALUES (1,10,1,'Super Admin','Audited','Location Matches','Physical verification completed from MSU Mobile App.','2026-10-14 10:28:10','2026-10-14 04:58:10','2026-10-14 04:58:10'),(2,1,1,'Super Admin','Not Found','Not Found','Not found in place.','2026-10-14 10:49:27','2026-10-14 05:19:27','2026-10-14 05:19:27'),(3,1,1,'Super Admin','Audited','Matches','Physical verification completed.','2026-10-14 10:59:54','2026-10-14 05:29:54','2026-10-14 05:29:54'),(4,1,1,'Super Admin','Not Found','Not Found','It is not there at the location.','2026-10-14 11:08:28','2026-10-14 05:38:28','2026-10-14 05:38:28'),(5,1,1,'Super Admin','Audited','Matches','Verified on-site during mobile QA audit','2026-10-16 16:11:51','2026-10-16 10:41:51','2026-10-16 10:41:51'),(6,1,1,'Super Admin','Not Found','Not Found','Asset marked as Not Found.','2026-10-16 16:21:51','2026-10-16 10:51:51','2026-10-16 10:51:51'),(7,26,1,'Super Admin','Audited','Location Matches','Physical verification completed.','2026-10-19 00:19:20','2026-10-19 00:19:20','2026-10-19 00:19:20'),(8,26,1,'Super Admin','Not Found','Location Does Not Match','Asset marked as Not Found.','2026-10-19 00:19:34','2026-10-19 00:19:34','2026-10-19 00:19:34'),(9,26,1,'Super Admin','Audited','Location Matches','Physical verification completed.','2026-10-19 00:26:06','2026-10-19 00:26:06','2026-10-19 00:26:06'),(10,26,1,'Super Admin','Audited','Location Does Not Match','Physical verification completed.','2026-10-19 09:47:42','2026-10-19 09:47:42','2026-10-19 09:47:42'),(11,26,1,'Super Admin','Not Found','Location Does Not Match','Asset marked as Not Found.','2026-10-19 09:48:53','2026-10-19 09:48:53','2026-10-19 09:48:53'),(12,26,1,'Super Admin','Audited','Location Matches','found on the location','2026-10-19 13:34:01','2026-10-19 13:34:01','2026-10-19 13:34:01'),(13,14,1,'Super Admin','Audited','Location Does Not Match','Aserg fthhc v','2026-10-19 15:38:17','2026-10-19 15:38:17','2026-10-19 15:38:17');
/*!40000 ALTER TABLE `audit_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch_locations`
--

DROP TABLE IF EXISTS `branch_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branch_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_locations_branch_id_foreign` (`branch_id`),
  CONSTRAINT `branch_locations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch_locations`
--

LOCK TABLES `branch_locations` WRITE;
/*!40000 ALTER TABLE `branch_locations` DISABLE KEYS */;
INSERT INTO `branch_locations` VALUES (1,'Admin Block','active','2026-10-13 07:11:53','2026-10-13 07:11:53',1),(2,'Accounts Dept','active','2026-10-13 07:11:53','2026-10-13 07:11:53',1),(3,'Server Room','active','2026-10-13 07:11:53','2026-10-13 07:11:53',1),(4,'Front Office','active','2026-10-13 07:11:53','2026-10-13 07:11:53',2),(5,'Sales Dept','active','2026-10-13 07:11:53','2026-10-13 07:11:53',2),(6,'Service Center','active','2026-10-13 07:11:53','2026-10-13 07:11:53',2),(7,'Reception','active','2026-10-13 07:11:53','2026-10-13 07:11:53',3),(8,'Conference Hall','active','2026-10-13 07:11:53','2026-10-13 07:11:53',3),(9,'HR Cabin','active','2026-10-13 07:11:53','2026-10-13 07:11:53',3),(10,'Admin Office','active','2026-10-13 07:11:53','2026-10-13 07:11:53',4),(11,'Warehouse','active','2026-10-13 07:11:53','2026-10-13 07:11:53',4),(12,'Support Team','active','2026-10-13 07:11:53','2026-10-13 07:11:53',4);
/*!40000 ALTER TABLE `branch_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch_user`
--

DROP TABLE IF EXISTS `branch_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branch_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_user_branch_id_user_id_unique` (`branch_id`,`user_id`),
  KEY `branch_user_user_id_foreign` (`user_id`),
  CONSTRAINT `branch_user_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `branch_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch_user`
--

LOCK TABLES `branch_user` WRITE;
/*!40000 ALTER TABLE `branch_user` DISABLE KEYS */;
INSERT INTO `branch_user` VALUES (1,'2026-10-13 07:11:55','2026-10-13 07:11:55',1,13),(2,'2026-10-13 07:11:55','2026-10-13 07:11:55',2,13),(3,'2026-10-13 07:11:56','2026-10-13 07:11:56',1,14),(4,'2026-10-13 07:11:56','2026-10-13 07:11:56',2,14),(5,'2026-10-13 07:11:56','2026-10-13 07:11:56',4,15),(6,'2026-10-13 07:11:56','2026-10-13 07:11:56',3,16),(7,'2026-10-13 07:11:56','2026-10-13 07:11:56',4,16),(8,'2026-10-22 11:53:24','2026-10-22 11:53:24',2,20),(9,'2026-10-22 15:52:04','2026-10-22 15:52:04',2,21);
/*!40000 ALTER TABLE `branch_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branches_code_unique` (`code`),
  KEY `branches_company_id_foreign` (`company_id`),
  KEY `branches_code_company_id_status_index` (`code`,`company_id`,`status`),
  CONSTRAINT `branches_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Empire - Head Office','EMP-HO',1,NULL,'Erode','Tamil Nadu','India',NULL,NULL,NULL,NULL,'active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(2,'Empire - Coimbatore Branch','EMP-CBE',1,NULL,'Coimbatore','Tamil Nadu','India',NULL,NULL,NULL,NULL,'active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(3,'Smart - Main Branch','SSPL-HO',2,NULL,'Coimbatore','Tamil Nadu','India',NULL,NULL,NULL,NULL,'active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(4,'Smart - Erode Branch','SSPL-ERD',2,NULL,'Erode','Tamil Nadu','India',NULL,NULL,NULL,NULL,'active','2026-10-13 07:11:53','2026-10-13 07:11:53');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Dell','Enterprise laptops, desktops and servers','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(2,'HP','Business and personal computing solutions','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(3,'Lenovo','ThinkPad laptops, desktops and workstations','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(4,'Apple','Premium laptops, mobiles and accessories','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(5,'Acer','Affordable laptops and desktops','active','2026-10-13 07:11:53','2026-10-13 07:11:53');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('msu_asset_management_cache_356a192b7913b04c54574d18c28d46e6395428ab','i:7;',1793080044),('msu_asset_management_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1793080044;',1793080044),('msu_asset_management_cache_472b07b9fcf2c2451e8781e944bf5f77cd8457c8','i:20;',1793080691),('msu_asset_management_cache_472b07b9fcf2c2451e8781e944bf5f77cd8457c8:timer','i:1793080691;',1793080691),('msu_asset_management_cache_setting.warehouse_company_id','s:1:\"2\";',2108439508),('msu_asset_management_cache_setting.warehouse_id','s:1:\"3\";',2108439508),('msu_asset_management_cache_setting.warehouse_location','s:1:\"8\";',2108439508),('msu_asset_management_cache_spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:300:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:15:\"msu-grn:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"msu-grn:cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:17:\"cost-centres:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:19:\"cost-centres:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:17:\"cost-centres:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:19:\"cost-centres:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:20:\"asset-custodian:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:22:\"asset-custodian:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:20:\"asset-custodian:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:22:\"asset-custodian:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:22:\"asset-custodian:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"roles:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"roles:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:10:\"roles:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:12:\"roles:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:16:\"permissions:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:18:\"permissions:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:16:\"permissions:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:18:\"permissions:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:10:\"users:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:12:\"users:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:10:\"users:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:12:\"users:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:14:\"dashboard:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:9:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:10;i:5;i:11;i:6;i:15;i:7;i:16;i:8;i:17;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:14:\"companies:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:16:\"companies:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:14:\"companies:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:16:\"companies:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:11:\"branch:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:13:\"branch:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:11:\"branch:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:13:\"branch:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:13:\"location:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:15:\"location:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:13:\"location:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:15:\"location:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:11:\"brands:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:13:\"brands:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:11:\"brands:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:13:\"brands:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:15:\"department:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:17:\"department:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:15:\"department:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:17:\"department:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:16:\"designation:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:18:\"designation:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:16:\"designation:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:18:\"designation:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:11:\"vendor:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:13:\"vendor:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:11:\"vendor:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:13:\"vendor:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:17:\"asset-master.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:19:\"asset-master.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:17:\"asset-master.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:19:\"asset-master.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:16:\"item-master:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"item-master:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:16:\"item-master:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:18:\"item-master:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:21:\"product-category:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:23:\"product-category:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:21:\"product-category:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:23:\"product-category:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:23:\"product-category:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:25:\"product-sub-category:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:27:\"product-sub-category:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:25:\"product-sub-category:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:27:\"product-sub-category:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:27:\"product-sub-category:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:9:\"unit:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:11:\"unit:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:9:\"unit:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:11:\"unit:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:11:\"unit:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:17:\"payment-term:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:19:\"payment-term:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:17:\"payment-term:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:19:\"payment-term:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:19:\"payment-term:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:25:\"miscellaneous-charge:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:27:\"miscellaneous-charge:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:25:\"miscellaneous-charge:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:27:\"miscellaneous-charge:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:27:\"miscellaneous-charge:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:8:\"hsn:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:10:\"hsn:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:8:\"hsn:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:10:\"hsn:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:10:\"hsn:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:20:\"employee-master:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:22:\"employee-master:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:20:\"employee-master:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:22:\"employee-master:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:22:\"employee-master:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:20:\"shipping-detail:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:22:\"shipping-detail:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:20:\"shipping-detail:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:22:\"shipping-detail:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:22:\"shipping-detail:status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:23:\"msu-purchase-order:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:25:\"msu-purchase-order:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:23:\"msu-purchase-order:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:25:\"msu-purchase-order:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:26:\"msu-purchase-order:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:25:\"msu-purchase-order:cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:19:\"msu-work-order:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:21:\"msu-work-order:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:19:\"msu-work-order:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:21:\"msu-work-order:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:22:\"msu-work-order:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:21:\"msu-work-order:cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:12:\"msu-grn:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:14:\"msu-grn:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:14:\"msu-grn:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:16:\"asset-types:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:18:\"asset-types:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:16:\"asset-types:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:18:\"asset-types:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:19:\"asset-category:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:21:\"asset-category:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:19:\"asset-category:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:21:\"asset-category:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:10:\"asset:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:12:\"asset:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:10:\"asset:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:12:\"asset:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:127;a:4:{s:1:\"a\";i:128;s:1:\"b\";s:22:\"asset.assign.bulk:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:22:\"asset.maintenance:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:129;a:4:{s:1:\"a\";i:130;s:1:\"b\";s:24:\"asset.maintenance:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:22:\"asset.maintenance:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:131;a:4:{s:1:\"a\";i:132;s:1:\"b\";s:24:\"asset.maintenance:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:132;a:4:{s:1:\"a\";i:133;s:1:\"b\";s:19:\"asset.document:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:133;a:4:{s:1:\"a\";i:134;s:1:\"b\";s:21:\"asset.document:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:134;a:4:{s:1:\"a\";i:135;s:1:\"b\";s:19:\"asset.document:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:135;a:4:{s:1:\"a\";i:136;s:1:\"b\";s:21:\"asset.document:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:136;a:4:{s:1:\"a\";i:137;s:1:\"b\";s:21:\"asset.assignment:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:137;a:4:{s:1:\"a\";i:138;s:1:\"b\";s:23:\"asset.assignment:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:138;a:4:{s:1:\"a\";i:139;s:1:\"b\";s:21:\"asset.assignment:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:139;a:4:{s:1:\"a\";i:140;s:1:\"b\";s:23:\"asset.assignment:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:140;a:4:{s:1:\"a\";i:141;s:1:\"b\";s:19:\"asset.warranty:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:141;a:4:{s:1:\"a\";i:142;s:1:\"b\";s:21:\"asset.warranty:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:142;a:4:{s:1:\"a\";i:143;s:1:\"b\";s:19:\"asset.warranty:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:143;a:4:{s:1:\"a\";i:144;s:1:\"b\";s:21:\"asset.warranty:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:144;a:4:{s:1:\"a\";i:145;s:1:\"b\";s:22:\"asset.replacement:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:145;a:4:{s:1:\"a\";i:146;s:1:\"b\";s:24:\"asset.replacement:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:146;a:4:{s:1:\"a\";i:147;s:1:\"b\";s:22:\"asset.replacement:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:147;a:4:{s:1:\"a\";i:148;s:1:\"b\";s:24:\"asset.replacement:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:148;a:4:{s:1:\"a\";i:149;s:1:\"b\";s:23:\"asset.depreciation:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:149;a:4:{s:1:\"a\";i:150;s:1:\"b\";s:20:\"dashboard.asset:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:150;a:4:{s:1:\"a\";i:151;s:1:\"b\";s:12:\"indents:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:151;a:4:{s:1:\"a\";i:152;s:1:\"b\";s:14:\"indents:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:152;a:4:{s:1:\"a\";i:153;s:1:\"b\";s:12:\"indents:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:153;a:4:{s:1:\"a\";i:154;s:1:\"b\";s:14:\"indents:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:154;a:4:{s:1:\"a\";i:155;s:1:\"b\";s:15:\"indents:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:155;a:4:{s:1:\"a\";i:156;s:1:\"b\";s:14:\"indents:reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:156;a:4:{s:1:\"a\";i:157;s:1:\"b\";s:22:\"indent.approve.manager\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:157;a:4:{s:1:\"a\";i:158;s:1:\"b\";s:22:\"indent.approve.it_head\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:158;a:4:{s:1:\"a\";i:159;s:1:\"b\";s:22:\"indent.approve.finance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:159;a:4:{s:1:\"a\";i:160;s:1:\"b\";s:25:\"approval-workflows:manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:26:\"purchase-requisitions:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;i:4;i:16;i:5;i:17;}}i:161;a:4:{s:1:\"a\";i:162;s:1:\"b\";s:25:\"purchase-requisition.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;i:5;i:16;i:6;i:17;}}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:28:\"purchase-requisition.process\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:163;a:4:{s:1:\"a\";i:164;s:1:\"b\";s:9:\"rfqs:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:15;i:5;i:16;i:6;i:17;}}i:164;a:4:{s:1:\"a\";i:165;s:1:\"b\";s:11:\"rfqs:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;}}i:165;a:4:{s:1:\"a\";i:166;s:1:\"b\";s:9:\"rfqs:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;}}i:166;a:4:{s:1:\"a\";i:167;s:1:\"b\";s:20:\"purchase-orders:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:10;i:5;i:12;i:6;i:14;}}i:167;a:4:{s:1:\"a\";i:168;s:1:\"b\";s:22:\"purchase-orders:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:168;a:4:{s:1:\"a\";i:169;s:1:\"b\";s:20:\"purchase-orders:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:169;a:4:{s:1:\"a\";i:170;s:1:\"b\";s:9:\"grns:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:10;i:5;i:11;}}i:170;a:4:{s:1:\"a\";i:171;s:1:\"b\";s:11:\"grns:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:11;}}i:171;a:4:{s:1:\"a\";i:172;s:1:\"b\";s:9:\"grns:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:10;i:5;i:11;}}i:172;a:4:{s:1:\"a\";i:173;s:1:\"b\";s:12:\"grns:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:10;i:5;i:11;}}i:173;a:4:{s:1:\"a\";i:174;s:1:\"b\";s:8:\"grn.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:11;}}i:174;a:4:{s:1:\"a\";i:175;s:1:\"b\";s:10:\"grn.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:11;}}i:175;a:4:{s:1:\"a\";i:176;s:1:\"b\";s:8:\"grn.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:11;}}i:176;a:4:{s:1:\"a\";i:177;s:1:\"b\";s:11:\"grn.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:177;a:4:{s:1:\"a\";i:178;s:1:\"b\";s:26:\"procurement.dashboard:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:12;i:5;i:14;}}i:178;a:4:{s:1:\"a\";i:179;s:1:\"b\";s:24:\"procurement.reports:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;i:4;i:12;i:5;i:14;}}i:179;a:4:{s:1:\"a\";i:180;s:1:\"b\";s:13:\"settings:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:180;a:4:{s:1:\"a\";i:181;s:1:\"b\";s:30:\"reports.companywiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:181;a:4:{s:1:\"a\";i:182;s:1:\"b\";s:29:\"reports.branchwiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:182;a:4:{s:1:\"a\";i:183;s:1:\"b\";s:28:\"reports.brandwiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:183;a:4:{s:1:\"a\";i:184;s:1:\"b\";s:29:\"reports.vendorwiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:31:\"reports.locationwiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:185;a:4:{s:1:\"a\";i:186;s:1:\"b\";s:31:\"reports.categorywiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:186;a:4:{s:1:\"a\";i:187;s:1:\"b\";s:27:\"reports.typewiseassets:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:187;a:4:{s:1:\"a\";i:188;s:1:\"b\";s:14:\"employees:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:188;a:4:{s:1:\"a\";i:189;s:1:\"b\";s:16:\"employees:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:189;a:4:{s:1:\"a\";i:190;s:1:\"b\";s:14:\"employees:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:190;a:4:{s:1:\"a\";i:191;s:1:\"b\";s:16:\"employees:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:191;a:4:{s:1:\"a\";i:192;s:1:\"b\";s:17:\"indent.approve.l1\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:192;a:4:{s:1:\"a\";i:193;s:1:\"b\";s:17:\"indent.approve.l2\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:193;a:4:{s:1:\"a\";i:194;s:1:\"b\";s:17:\"indent.approve.l3\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:194;a:4:{s:1:\"a\";i:195;s:1:\"b\";s:17:\"indent.approve.l4\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:195;a:4:{s:1:\"a\";i:196;s:1:\"b\";s:8:\"rfq.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;i:5;i:16;i:6;i:17;}}i:196;a:4:{s:1:\"a\";i:197;s:1:\"b\";s:10:\"rfq.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;}}i:197;a:4:{s:1:\"a\";i:198;s:1:\"b\";s:8:\"rfq.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;}}i:198;a:4:{s:1:\"a\";i:199;s:1:\"b\";s:8:\"rfq.send\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;}}i:199;a:4:{s:1:\"a\";i:200;s:1:\"b\";s:10:\"rfq.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;}}i:200;a:4:{s:1:\"a\";i:201;s:1:\"b\";s:14:\"quotation.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:15;i:5;i:16;i:6;i:17;}}i:201;a:4:{s:1:\"a\";i:202;s:1:\"b\";s:13:\"quotation.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:16;}}i:202;a:4:{s:1:\"a\";i:203;s:1:\"b\";s:17:\"quotation.compare\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:17;}}i:203;a:4:{s:1:\"a\";i:204;s:1:\"b\";s:23:\"quotation.select-vendor\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:17;}}i:204;a:4:{s:1:\"a\";i:205;s:1:\"b\";s:19:\"quotation.negotiate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:16;}}i:205;a:4:{s:1:\"a\";i:206;s:1:\"b\";s:19:\"purchase-order.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:12;i:5;i:14;}}i:206;a:4:{s:1:\"a\";i:207;s:1:\"b\";s:21:\"purchase-order.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:207;a:4:{s:1:\"a\";i:208;s:1:\"b\";s:19:\"purchase-order.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:208;a:4:{s:1:\"a\";i:209;s:1:\"b\";s:21:\"purchase-order.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:209;a:4:{s:1:\"a\";i:210;s:1:\"b\";s:20:\"purchase-order.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:210;a:4:{s:1:\"a\";i:211;s:1:\"b\";s:21:\"purchase-order.cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:211;a:4:{s:1:\"a\";i:212;s:1:\"b\";s:20:\"purchase-report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:12;i:5;i:14;}}i:212;a:4:{s:1:\"a\";i:213;s:1:\"b\";s:11:\"grn.inspect\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:11;}}i:213;a:4:{s:1:\"a\";i:214;s:1:\"b\";s:29:\"purchase-management.dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;i:4;i:12;i:5;i:14;}}i:214;a:4:{s:1:\"a\";i:215;s:1:\"b\";s:14:\"indents:submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:215;a:4:{s:1:\"a\";i:216;s:1:\"b\";s:17:\"indents:send-back\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:216;a:4:{s:1:\"a\";i:217;s:1:\"b\";s:14:\"indents:cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:217;a:4:{s:1:\"a\";i:218;s:1:\"b\";s:13:\"indents:print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:218;a:4:{s:1:\"a\";i:219;s:1:\"b\";s:28:\"purchase-requisitions:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:219;a:4:{s:1:\"a\";i:220;s:1:\"b\";s:26:\"purchase-requisitions:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:220;a:4:{s:1:\"a\";i:221;s:1:\"b\";s:29:\"purchase-requisitions:process\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:221;a:4:{s:1:\"a\";i:222;s:1:\"b\";s:36:\"purchase-requisitions:proceed-to-rfq\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:222;a:4:{s:1:\"a\";i:223;s:1:\"b\";s:41:\"purchase-requisitions:transfer-from-store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:223;a:4:{s:1:\"a\";i:224;s:1:\"b\";s:34:\"purchase-requisitions:manual-close\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:224;a:4:{s:1:\"a\";i:225;s:1:\"b\";s:27:\"purchase-requisitions:print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:225;a:4:{s:1:\"a\";i:226;s:1:\"b\";s:35:\"purchase-requisition.proceed-to-rfq\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:226;a:4:{s:1:\"a\";i:227;s:1:\"b\";s:40:\"purchase-requisition.transfer-from-store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:227;a:4:{s:1:\"a\";i:228;s:1:\"b\";s:33:\"purchase-requisition.manual-close\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:228;a:4:{s:1:\"a\";i:229;s:1:\"b\";s:26:\"purchase-requisition.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:229;a:4:{s:1:\"a\";i:230;s:1:\"b\";s:26:\"purchase-requisition.close\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:230;a:4:{s:1:\"a\";i:231;s:1:\"b\";s:9:\"rfqs:send\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;}}i:231;a:4:{s:1:\"a\";i:232;s:1:\"b\";s:11:\"rfqs:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;}}i:232;a:4:{s:1:\"a\";i:233;s:1:\"b\";s:9:\"rfq.close\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;}}i:233;a:4:{s:1:\"a\";i:234;s:1:\"b\";s:15:\"quotations:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:15;i:4;i:16;i:5;i:17;}}i:234;a:4:{s:1:\"a\";i:235;s:1:\"b\";s:17:\"quotations:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:16;}}i:235;a:4:{s:1:\"a\";i:236;s:1:\"b\";s:18:\"quotations:compare\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:17;}}i:236;a:4:{s:1:\"a\";i:237;s:1:\"b\";s:24:\"quotations:select-vendor\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:17;}}i:237;a:4:{s:1:\"a\";i:238;s:1:\"b\";s:22:\"purchase-orders:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:10;}}i:238;a:4:{s:1:\"a\";i:239;s:1:\"b\";s:21:\"purchase-orders:print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:239;a:4:{s:1:\"a\";i:240;s:1:\"b\";s:22:\"purchase-orders:cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:240;a:4:{s:1:\"a\";i:241;s:1:\"b\";s:23:\"purchase-orders:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:241;a:4:{s:1:\"a\";i:242;s:1:\"b\";s:21:\"purchase-order.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:242;a:4:{s:1:\"a\";i:243;s:1:\"b\";s:22:\"purchase-order.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:243;a:4:{s:1:\"a\";i:244;s:1:\"b\";s:21:\"purchase-order.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:244;a:4:{s:1:\"a\";i:245;s:1:\"b\";s:24:\"purchase-order.send-back\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:245;a:4:{s:1:\"a\";i:246;s:1:\"b\";s:22:\"purchase-order.payment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:12;i:4;i:14;}}i:246;a:4:{s:1:\"a\";i:247;s:1:\"b\";s:18:\"purchase-order.pay\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:12;i:4;i:14;}}i:247;a:4:{s:1:\"a\";i:248;s:1:\"b\";s:23:\"purchase-orders:payment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:9;i:3;i:12;i:4;i:14;}}i:248;a:4:{s:1:\"a\";i:249;s:1:\"b\";s:12:\"grns:inspect\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:249;a:4:{s:1:\"a\";i:250;s:1:\"b\";s:10:\"grn.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:250;a:4:{s:1:\"a\";i:251;s:1:\"b\";s:23:\"approval-templates:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:251;a:4:{s:1:\"a\";i:252;s:1:\"b\";s:25:\"approval-templates:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:252;a:4:{s:1:\"a\";i:253;s:1:\"b\";s:23:\"approval-templates:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:253;a:4:{s:1:\"a\";i:254;s:1:\"b\";s:25:\"approval-templates:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:254;a:4:{s:1:\"a\";i:255;s:1:\"b\";s:25:\"approval-templates:manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:255;a:4:{s:1:\"a\";i:256;s:1:\"b\";s:12:\"payment.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:256;a:4:{s:1:\"a\";i:257;s:1:\"b\";s:13:\"payments:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:257;a:4:{s:1:\"a\";i:258;s:1:\"b\";s:14:\"payment.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:258;a:4:{s:1:\"a\";i:259;s:1:\"b\";s:15:\"payments:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:259;a:4:{s:1:\"a\";i:260;s:1:\"b\";s:15:\"payment.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:260;a:4:{s:1:\"a\";i:261;s:1:\"b\";s:16:\"payments:approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:261;a:4:{s:1:\"a\";i:262;s:1:\"b\";s:11:\"payment.pay\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:262;a:4:{s:1:\"a\";i:263;s:1:\"b\";s:12:\"payments:pay\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:263;a:4:{s:1:\"a\";i:264;s:1:\"b\";s:23:\"quality-inspection.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:264;a:4:{s:1:\"a\";i:265;s:1:\"b\";s:24:\"quality-inspections:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:265;a:4:{s:1:\"a\";i:266;s:1:\"b\";s:26:\"quality-inspection.perform\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:266;a:4:{s:1:\"a\";i:267;s:1:\"b\";s:27:\"quality-inspections:perform\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:267;a:3:{s:1:\"a\";i:268;s:1:\"b\";s:19:\"support-ticket:view\";s:1:\"c\";s:3:\"api\";}i:268;a:3:{s:1:\"a\";i:269;s:1:\"b\";s:21:\"support-ticket:create\";s:1:\"c\";s:3:\"api\";}i:269;a:3:{s:1:\"a\";i:270;s:1:\"b\";s:19:\"support-ticket:edit\";s:1:\"c\";s:3:\"api\";}i:270;a:3:{s:1:\"a\";i:271;s:1:\"b\";s:21:\"support-ticket:delete\";s:1:\"c\";s:3:\"api\";}i:271;a:3:{s:1:\"a\";i:272;s:1:\"b\";s:21:\"support-ticket:accept\";s:1:\"c\";s:3:\"api\";}i:272;a:3:{s:1:\"a\";i:273;s:1:\"b\";s:19:\"support-ticket:exit\";s:1:\"c\";s:3:\"api\";}i:273;a:3:{s:1:\"a\";i:274;s:1:\"b\";s:23:\"support-ticket:complete\";s:1:\"c\";s:3:\"api\";}i:274;a:3:{s:1:\"a\";i:275;s:1:\"b\";s:28:\"dashboard.supportticket:view\";s:1:\"c\";s:3:\"api\";}i:275;a:4:{s:1:\"a\";i:276;s:1:\"b\";s:19:\"support-ticket:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:276;a:4:{s:1:\"a\";i:277;s:1:\"b\";s:21:\"support-ticket:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:277;a:4:{s:1:\"a\";i:278;s:1:\"b\";s:19:\"support-ticket:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:278;a:4:{s:1:\"a\";i:279;s:1:\"b\";s:21:\"support-ticket:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:279;a:4:{s:1:\"a\";i:280;s:1:\"b\";s:21:\"support-ticket:accept\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:280;a:4:{s:1:\"a\";i:281;s:1:\"b\";s:19:\"support-ticket:exit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:281;a:4:{s:1:\"a\";i:282;s:1:\"b\";s:23:\"support-ticket:complete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:282;a:4:{s:1:\"a\";i:283;s:1:\"b\";s:28:\"dashboard.supportticket:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:283;a:4:{s:1:\"a\";i:284;s:1:\"b\";s:10:\"audit:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:284;a:4:{s:1:\"a\";i:285;s:1:\"b\";s:12:\"audit:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:285;a:4:{s:1:\"a\";i:286;s:1:\"b\";s:10:\"audit:edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:286;a:4:{s:1:\"a\";i:287;s:1:\"b\";s:12:\"audit:delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:287;a:3:{s:1:\"a\";i:288;s:1:\"b\";s:14:\"dashboard:view\";s:1:\"c\";s:3:\"api\";}i:288;a:3:{s:1:\"a\";i:289;s:1:\"b\";s:10:\"asset:view\";s:1:\"c\";s:3:\"api\";}i:289;a:3:{s:1:\"a\";i:290;s:1:\"b\";s:10:\"audit:view\";s:1:\"c\";s:3:\"api\";}i:290;a:4:{s:1:\"a\";i:291;s:1:\"b\";s:21:\"mobile.dashboard:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:291;a:3:{s:1:\"a\";i:292;s:1:\"b\";s:21:\"mobile.dashboard:view\";s:1:\"c\";s:3:\"api\";}i:292;a:4:{s:1:\"a\";i:293;s:1:\"b\";s:17:\"mobile.asset:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:293;a:3:{s:1:\"a\";i:294;s:1:\"b\";s:17:\"mobile.asset:view\";s:1:\"c\";s:3:\"api\";}i:294;a:4:{s:1:\"a\";i:295;s:1:\"b\";s:17:\"mobile.audit:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:295;a:3:{s:1:\"a\";i:296;s:1:\"b\";s:17:\"mobile.audit:view\";s:1:\"c\";s:3:\"api\";}i:296;a:4:{s:1:\"a\";i:297;s:1:\"b\";s:26:\"mobile.support-ticket:view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:297;a:3:{s:1:\"a\";i:298;s:1:\"b\";s:26:\"mobile.support-ticket:view\";s:1:\"c\";s:3:\"api\";}i:298;a:4:{s:1:\"a\";i:299;s:1:\"b\";s:28:\"mobile.support-ticket:create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:299;a:3:{s:1:\"a\";i:300;s:1:\"b\";s:28:\"mobile.support-ticket:create\";s:1:\"c\";s:3:\"api\";}}s:5:\"roles\";a:11:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"superadmin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"employee\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"Super Admin\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:10;s:1:\"b\";s:16:\"Purchase Officer\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"Store Keeper\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:15;s:1:\"b\";s:11:\"RFQ Creator\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:16;s:1:\"b\";s:20:\"Quotation Negotiator\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:17;s:1:\"b\";s:15:\"Vendor Selector\";s:1:\"c\";s:3:\"web\";}i:9;a:3:{s:1:\"a\";i:12;s:1:\"b\";s:19:\"Accounts Department\";s:1:\"c\";s:3:\"web\";}i:10;a:3:{s:1:\"a\";i:14;s:1:\"b\";s:18:\"Finance & Accounts\";s:1:\"c\";s:3:\"web\";}}}',1793167031);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_code_unique` (`code`),
  KEY `companies_code_status_index` (`code`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Empire Technologies','EMP001',NULL,NULL,'Erode','Tamil Nadu','India',NULL,NULL,NULL,NULL,NULL,'active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(2,'Smart Solutions Pvt Ltd','SSPL002',NULL,NULL,'Coimbatore','Tamil Nadu','India',NULL,NULL,NULL,NULL,NULL,'active','2026-10-13 07:11:53','2026-10-13 07:11:53');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_user`
--

DROP TABLE IF EXISTS `company_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_user_company_id_foreign` (`company_id`),
  KEY `company_user_user_id_foreign` (`user_id`),
  CONSTRAINT `company_user_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_user`
--

LOCK TABLES `company_user` WRITE;
/*!40000 ALTER TABLE `company_user` DISABLE KEYS */;
INSERT INTO `company_user` VALUES (1,1,20,'2026-10-22 11:53:24','2026-10-22 11:53:24'),(2,1,21,'2026-10-22 15:52:04','2026-10-22 15:52:04');
/*!40000 ALTER TABLE `company_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cost_centres`
--

DROP TABLE IF EXISTS `cost_centres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cost_centres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cost_centre_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost_centre_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cost_centres_cost_centre_name_unique` (`cost_centre_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cost_centres`
--

LOCK TABLES `cost_centres` WRITE;
/*!40000 ALTER TABLE `cost_centres` DISABLE KEYS */;
/*!40000 ALTER TABLE `cost_centres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dealer_locations`
--

DROP TABLE IF EXISTS `dealer_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dealer_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dealer_locations_code_unique` (`code`),
  KEY `dealer_locations_company_id_foreign` (`company_id`),
  KEY `dealer_locations_code_company_id_status_index` (`code`,`company_id`,`status`),
  CONSTRAINT `dealer_locations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dealer_locations`
--

LOCK TABLES `dealer_locations` WRITE;
/*!40000 ALTER TABLE `dealer_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `dealer_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_user`
--

DROP TABLE IF EXISTS `department_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_user_user_id_department_id_unique` (`user_id`,`department_id`),
  KEY `department_user_department_id_foreign` (`department_id`),
  CONSTRAINT `department_user_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `department_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_user`
--

LOCK TABLES `department_user` WRITE;
/*!40000 ALTER TABLE `department_user` DISABLE KEYS */;
INSERT INTO `department_user` VALUES (1,9,1,'2026-10-13 07:11:53','2026-10-13 07:11:53'),(2,5,1,'2026-10-13 07:11:53','2026-10-13 07:11:53'),(3,13,15,'2026-10-13 07:11:55','2026-10-13 07:11:55'),(4,14,5,'2026-10-13 07:11:56','2026-10-13 07:11:56'),(5,14,6,'2026-10-13 07:11:56','2026-10-13 07:11:56'),(6,15,10,'2026-10-13 07:11:56','2026-10-13 07:11:56'),(7,16,10,'2026-10-13 07:11:56','2026-10-13 07:11:56'),(8,20,2,'2026-10-22 11:53:24','2026-10-22 11:53:24'),(10,21,2,'2026-10-22 15:52:04','2026-10-22 15:52:04');
/*!40000 ALTER TABLE `department_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_department_name_unique` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Finance & Accounts','active','2026-10-13 07:11:52','2026-10-13 07:11:52'),(2,'Information Technology','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(3,'Human Resources','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(4,'Sales & Marketing','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(5,'Customer Support','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(6,'Research & Development','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(7,'Operations','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(8,'Quality Assurance','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(9,'Administration','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(10,'Procurement','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(11,'Legal','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(12,'Training & Development','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(13,'Network Operations','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(14,'Data Center','active','2026-10-13 07:11:54','2026-10-13 07:11:54'),(15,'Security','active','2026-10-13 07:11:54','2026-10-13 07:11:54');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `designation_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `designations_designation_name_unique` (`designation_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
INSERT INTO `designations` VALUES (1,'Managing Director','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(2,'Chief Executive Officer','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(3,'Operations Manager','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(4,'HR Manager','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(5,'Accounts Manager','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(6,'Senior Software Engineer','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(7,'Software Engineer','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(8,'System Administrator','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(9,'IT Support Engineer','active','2026-10-13 07:11:53','2026-10-13 07:11:53'),(10,'Sales Executive','active','2026-10-13 07:11:53','2026-10-13 07:11:53');
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `device_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('android','ios') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'android',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_tokens`
--

LOCK TABLES `device_tokens` WRITE;
/*!40000 ALTER TABLE `device_tokens` DISABLE KEYS */;
INSERT INTO `device_tokens` VALUES (1,1,'dFfVO-alSmCLm5hwn8jOYh:APA91bGeUdL2hkCCtrpciiFTotvZmiA_Wx9Y-e-Xp-GXuzr5zTO91Yf50x6PxvGH-51gbCBm9mrnknF30LwleOzJ9TrfqMqsJI3FNVfLF0MTz4mAxLWpwM4','android','2026-10-16 07:22:18','2026-10-27 11:24:36'),(2,19,'dFfVO-alSmCLm5hwn8jOYh:APA91bGeUdL2hkCCtrpciiFTotvZmiA_Wx9Y-e-Xp-GXuzr5zTO91Yf50x6PxvGH-51gbCBm9mrnknF30LwleOzJ9TrfqMqsJI3FNVfLF0MTz4mAxLWpwM4','android','2026-10-19 17:22:50','2026-10-19 17:22:50'),(3,20,'dFfVO-alSmCLm5hwn8jOYh:APA91bGeUdL2hkCCtrpciiFTotvZmiA_Wx9Y-e-Xp-GXuzr5zTO91Yf50x6PxvGH-51gbCBm9mrnknF30LwleOzJ9TrfqMqsJI3FNVfLF0MTz4mAxLWpwM4','android','2026-10-22 12:24:37','2026-10-22 14:52:09'),(4,21,'dFfVO-alSmCLm5hwn8jOYh:APA91bGeUdL2hkCCtrpciiFTotvZmiA_Wx9Y-e-Xp-GXuzr5zTO91Yf50x6PxvGH-51gbCBm9mrnknF30LwleOzJ9TrfqMqsJI3FNVfLF0MTz4mAxLWpwM4','android','2026-10-22 15:55:03','2026-10-22 15:55:03');
/*!40000 ALTER TABLE `device_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_masters`
--

DROP TABLE IF EXISTS `employee_masters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_masters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `mobile_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_masters_department_id_foreign` (`department_id`),
  CONSTRAINT `employee_masters_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_masters`
--

LOCK TABLES `employee_masters` WRITE;
/*!40000 ALTER TABLE `employee_masters` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_masters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mile_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `is_left` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `is_user` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `designation_id` bigint unsigned NOT NULL,
  `segment_id` bigint unsigned NOT NULL,
  `department_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `dealer_location_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `subsegment_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `reporting_to` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `mobile_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_joining` date NOT NULL,
  `employee_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `left_date` date DEFAULT NULL,
  `role_id` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_emp_code_unique` (`emp_code`),
  UNIQUE KEY `employees_email_unique` (`email`),
  KEY `employees_designation_id_foreign` (`designation_id`),
  KEY `employees_segment_id_foreign` (`segment_id`),
  KEY `employees_user_id_foreign` (`user_id`),
  CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_segment_id_foreign` FOREIGN KEY (`segment_id`) REFERENCES `segments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_chk_1` CHECK (json_valid(`department_id`)),
  CONSTRAINT `employees_chk_2` CHECK (json_valid(`dealer_location_id`)),
  CONSTRAINT `employees_chk_3` CHECK (json_valid(`subsegment_id`)),
  CONSTRAINT `employees_chk_4` CHECK (json_valid(`reporting_to`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_storage`
--

DROP TABLE IF EXISTS `file_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `file_storage` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int NOT NULL,
  `storage_location` enum('local','aws_s3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_storage`
--

LOCK TABLES `file_storage` WRITE;
/*!40000 ALTER TABLE `file_storage` DISABLE KEYS */;
INSERT INTO `file_storage` VALUES (1,'support-docs','804e6f2d024e579d85580f935dfcbec6.png',NULL,91,'local','2026-10-23 11:05:19','2026-10-23 11:05:19'),(2,'support-docs','c0bd8264e179b8c0ecbe98c521663b6f.png',NULL,91,'local','2026-10-23 11:05:19','2026-10-23 11:05:19'),(3,'support-docs','8758760727182731bd172ba8c0a9261f.png',NULL,91,'local','2026-10-23 11:06:29','2026-10-23 11:06:29'),(4,'support-docs','8ac33ff52dca0a0c89a58586bb498958.png',NULL,91,'local','2026-10-23 11:06:29','2026-10-23 11:06:29'),(5,'support-docs','bd0bc984911b71d863522d74b2f5e09e.png',NULL,35261,'local','2026-10-23 11:26:38','2026-10-23 11:26:38'),(6,'support-docs','f2343b118e8fd84484b12e2914205409.png',NULL,113503,'local','2026-10-23 11:37:39','2026-10-23 11:37:39');
/*!40000 ALTER TABLE `file_storage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grn_documents`
--

DROP TABLE IF EXISTS `grn_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grn_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` bigint unsigned NOT NULL,
  `document_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grn_documents_grn_id_document_type_index` (`grn_id`,`document_type`),
  KEY `grn_documents_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `grn_documents_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `grns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grn_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grn_documents`
--

LOCK TABLES `grn_documents` WRITE;
/*!40000 ALTER TABLE `grn_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `grn_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grn_histories`
--

DROP TABLE IF EXISTS `grn_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grn_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grn_histories_grn_id_created_at_index` (`grn_id`,`created_at`),
  KEY `grn_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `grn_histories_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `grns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grn_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `grn_histories_chk_1` CHECK (json_valid(`meta`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grn_histories`
--

LOCK TABLES `grn_histories` WRITE;
/*!40000 ALTER TABLE `grn_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `grn_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grn_items`
--

DROP TABLE IF EXISTS `grn_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grn_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` bigint unsigned NOT NULL,
  `po_item_id` bigint unsigned NOT NULL,
  `ordered_quantity` int NOT NULL,
  `unit_rate` decimal(15,3) DEFAULT NULL,
  `received_quantity` int NOT NULL,
  `accepted_quantity` int NOT NULL DEFAULT '0',
  `rejected_quantity` int NOT NULL DEFAULT '0',
  `damage_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `condition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessories` text COLLATE utf8mb4_unicode_ci,
  `inspected_by` bigint unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  `assets_generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hsn_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marketer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `free_qty` decimal(15,2) DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_in_hand` decimal(15,2) DEFAULT NULL,
  `stock_after` decimal(15,3) DEFAULT NULL,
  `inspection_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `inspection_remarks` text COLLATE utf8mb4_unicode_ci,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conversion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `challan_quantity` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grn_items_grn_id_foreign` (`grn_id`),
  KEY `grn_items_po_item_id_foreign` (`po_item_id`),
  KEY `grn_items_inspected_by_foreign` (`inspected_by`),
  CONSTRAINT `grn_items_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `grns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grn_items_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `grn_items_po_item_id_foreign` FOREIGN KEY (`po_item_id`) REFERENCES `po_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grn_items_chk_1` CHECK (json_valid(`serial_numbers`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grn_items`
--

LOCK TABLES `grn_items` WRITE;
/*!40000 ALTER TABLE `grn_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `grn_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grns`
--

DROP TABLE IF EXISTS `grns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `po_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `received_date` date NOT NULL,
  `received_by` bigint unsigned NOT NULL,
  `received_by_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivered_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivered_by_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `challan_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `challan_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending Inspection',
  `invoice_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_challan_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `inspected_by` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `delivery_memo_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_center_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_entry_date` datetime DEFAULT NULL,
  `characteristic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspection_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `inspection_remarks` text COLLATE utf8mb4_unicode_ci,
  `bill_amount` decimal(15,2) DEFAULT NULL,
  `gate_entry_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_memo_date` date DEFAULT NULL,
  `po_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grns_grn_no_unique` (`grn_no`),
  KEY `grns_po_id_foreign` (`po_id`),
  KEY `grns_vendor_id_foreign` (`vendor_id`),
  KEY `grns_received_by_foreign` (`received_by`),
  KEY `grns_inspected_by_foreign` (`inspected_by`),
  KEY `grns_approved_by_foreign` (`approved_by`),
  CONSTRAINT `grns_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `grns_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `grns_po_id_foreign` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grns_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  CONSTRAINT `grns_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grns`
--

LOCK TABLES `grns` WRITE;
/*!40000 ALTER TABLE `grns` DISABLE KEYS */;
/*!40000 ALTER TABLE `grns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hsns`
--

DROP TABLE IF EXISTS `hsns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hsns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hsn_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hsns_hsn_code_unique` (`hsn_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hsns`
--

LOCK TABLES `hsns` WRITE;
/*!40000 ALTER TABLE `hsns` DISABLE KEYS */;
/*!40000 ALTER TABLE `hsns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indent_approval_levels`
--

DROP TABLE IF EXISTS `indent_approval_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indent_approval_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` tinyint unsigned NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `approver_role_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indent_approval_levels_level_unique` (`level`),
  CONSTRAINT `indent_approval_levels_chk_1` CHECK (json_valid(`approver_role_names`))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indent_approval_levels`
--

LOCK TABLES `indent_approval_levels` WRITE;
/*!40000 ALTER TABLE `indent_approval_levels` DISABLE KEYS */;
INSERT INTO `indent_approval_levels` VALUES (1,1,'L1','Level 1 Approval',1,1,'[\"department_head\"]','2026-10-13 07:09:10','2026-10-13 07:09:10'),(2,2,'L2','Level 2 Approval',1,2,'[\"purchase_manager\"]','2026-10-13 07:09:10','2026-10-13 07:09:10'),(3,3,'L3','Level 3 Approval',1,3,'[\"superadmin\"]','2026-10-13 07:09:10','2026-10-13 07:09:10');
/*!40000 ALTER TABLE `indent_approval_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indent_approval_steps`
--

DROP TABLE IF EXISTS `indent_approval_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indent_approval_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indent_id` bigint unsigned NOT NULL,
  `template_step_id` bigint unsigned DEFAULT NULL,
  `sequence` int unsigned NOT NULL,
  `assignee_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `approver_user_id` bigint unsigned DEFAULT NULL,
  `assignee_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `acted_by` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `acted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indent_approval_steps_seq_unique` (`indent_id`,`sequence`),
  KEY `indent_approval_steps_template_step_id_foreign` (`template_step_id`),
  KEY `indent_approval_steps_acted_by_foreign` (`acted_by`),
  KEY `indent_approval_steps_approver_user_id_status_index` (`approver_user_id`,`status`),
  CONSTRAINT `indent_approval_steps_acted_by_foreign` FOREIGN KEY (`acted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indent_approval_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indent_approval_steps_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indent_approval_steps_template_step_id_foreign` FOREIGN KEY (`template_step_id`) REFERENCES `approval_template_steps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indent_approval_steps`
--

LOCK TABLES `indent_approval_steps` WRITE;
/*!40000 ALTER TABLE `indent_approval_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `indent_approval_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indent_approvals`
--

DROP TABLE IF EXISTS `indent_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indent_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indent_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned NOT NULL,
  `level` enum('L1','L2','L3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `acted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indent_approvals_indent_id_foreign` (`indent_id`),
  KEY `indent_approvals_approver_id_foreign` (`approver_id`),
  CONSTRAINT `indent_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indent_approvals_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indent_approvals_chk_1` CHECK (json_valid(`meta`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indent_approvals`
--

LOCK TABLES `indent_approvals` WRITE;
/*!40000 ALTER TABLE `indent_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `indent_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indent_items`
--

DROP TABLE IF EXISTS `indent_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indent_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indent_id` bigint unsigned NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` int NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nos',
  `estimated_price` decimal(12,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_kit` tinyint(1) NOT NULL DEFAULT '0',
  `composition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_in_hand` decimal(15,2) DEFAULT NULL,
  `conversion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facility_stock_in_hand` decimal(15,2) DEFAULT NULL,
  `to_stock_in_hand` decimal(15,2) DEFAULT NULL,
  `indent_min_qty` decimal(15,2) DEFAULT NULL,
  `indent_max_qty` decimal(15,2) DEFAULT NULL,
  `item_remarks` text COLLATE utf8mb4_unicode_ci,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indent_items_indent_id_foreign` (`indent_id`),
  KEY `indent_items_category_id_foreign` (`category_id`),
  CONSTRAINT `indent_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indent_items_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indent_items`
--

LOCK TABLES `indent_items` WRITE;
/*!40000 ALTER TABLE `indent_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `indent_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indents`
--

DROP TABLE IF EXISTS `indents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indent_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_date` date NOT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint unsigned NOT NULL,
  `from_store_id` bigint unsigned DEFAULT NULL,
  `item_category_id` bigint unsigned DEFAULT NULL,
  `approval_template_id` bigint unsigned DEFAULT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Medium',
  `required_date` date DEFAULT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `approval_levels_required` tinyint unsigned NOT NULL DEFAULT '3',
  `current_level` tinyint unsigned DEFAULT NULL,
  `current_sequence` int unsigned DEFAULT NULL,
  `approval_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_approver_id` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `to_store_id` bigint unsigned DEFAULT NULL,
  `request_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_urgent` tinyint(1) NOT NULL DEFAULT '0',
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indents_indent_no_unique` (`indent_no`),
  KEY `indents_department_id_foreign` (`department_id`),
  KEY `indents_requested_by_foreign` (`requested_by`),
  KEY `indents_to_store_id_foreign` (`to_store_id`),
  KEY `indents_approval_status_index` (`approval_status`),
  KEY `indents_assigned_permission_index` (`assigned_permission`),
  KEY `indents_approval_template_id_foreign` (`approval_template_id`),
  KEY `indents_assigned_approver_id_foreign` (`assigned_approver_id`),
  KEY `indents_from_store_id_foreign` (`from_store_id`),
  KEY `indents_item_category_id_foreign` (`item_category_id`),
  CONSTRAINT `indents_approval_template_id_foreign` FOREIGN KEY (`approval_template_id`) REFERENCES `approval_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indents_assigned_approver_id_foreign` FOREIGN KEY (`assigned_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indents_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `indents_from_store_id_foreign` FOREIGN KEY (`from_store_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indents_item_category_id_foreign` FOREIGN KEY (`item_category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indents_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `indents_to_store_id_foreign` FOREIGN KEY (`to_store_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indents`
--

LOCK TABLES `indents` WRITE;
/*!40000 ALTER TABLE `indents` DISABLE KEYS */;
/*!40000 ALTER TABLE `indents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_masters`
--

DROP TABLE IF EXISTS `item_masters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_masters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `asset_type_id` bigint unsigned NOT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `uom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_masters_category_id_asset_type_id_item_name_unique` (`category_id`,`asset_type_id`,`item_name`),
  UNIQUE KEY `item_masters_item_code_unique` (`item_code`),
  KEY `item_masters_asset_type_id_foreign` (`asset_type_id`),
  CONSTRAINT `item_masters_asset_type_id_foreign` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_masters_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_masters`
--

LOCK TABLES `item_masters` WRITE;
/*!40000 ALTER TABLE `item_masters` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_masters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_issue_items`
--

DROP TABLE IF EXISTS `material_issue_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_issue_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `material_issue_id` bigint unsigned NOT NULL,
  `material_request_item_id` bigint unsigned NOT NULL,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `issue_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `material_issue_items_material_issue_id_foreign` (`material_issue_id`),
  KEY `material_issue_items_material_request_item_id_foreign` (`material_request_item_id`),
  KEY `material_issue_items_msu_item_master_id_foreign` (`msu_item_master_id`),
  CONSTRAINT `material_issue_items_material_issue_id_foreign` FOREIGN KEY (`material_issue_id`) REFERENCES `material_issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `material_issue_items_material_request_item_id_foreign` FOREIGN KEY (`material_request_item_id`) REFERENCES `material_request_items` (`id`),
  CONSTRAINT `material_issue_items_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_issue_items`
--

LOCK TABLES `material_issue_items` WRITE;
/*!40000 ALTER TABLE `material_issue_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `material_issue_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_issues`
--

DROP TABLE IF EXISTS `material_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_issues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mi_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `employee_master_id` bigint unsigned DEFAULT NULL,
  `material_request_id` bigint unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `material_issues_mi_number_unique` (`mi_number`),
  KEY `material_issues_department_id_foreign` (`department_id`),
  KEY `material_issues_employee_master_id_foreign` (`employee_master_id`),
  KEY `material_issues_material_request_id_foreign` (`material_request_id`),
  KEY `material_issues_created_by_foreign` (`created_by`),
  CONSTRAINT `material_issues_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_issues_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_issues_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_issues_material_request_id_foreign` FOREIGN KEY (`material_request_id`) REFERENCES `material_requests` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_issues`
--

LOCK TABLES `material_issues` WRITE;
/*!40000 ALTER TABLE `material_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `material_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_request_items`
--

DROP TABLE IF EXISTS `material_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_request_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `material_request_id` bigint unsigned NOT NULL,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `requested_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `issued_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `unit_id` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `material_request_items_material_request_id_foreign` (`material_request_id`),
  KEY `material_request_items_msu_item_master_id_foreign` (`msu_item_master_id`),
  KEY `material_request_items_unit_id_foreign` (`unit_id`),
  CONSTRAINT `material_request_items_material_request_id_foreign` FOREIGN KEY (`material_request_id`) REFERENCES `material_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `material_request_items_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`),
  CONSTRAINT `material_request_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_request_items`
--

LOCK TABLES `material_request_items` WRITE;
/*!40000 ALTER TABLE `material_request_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `material_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_requests`
--

DROP TABLE IF EXISTS `material_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mr_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_date` date NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `employee_master_id` bigint unsigned DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `cancel_reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `material_requests_mr_number_unique` (`mr_number`),
  KEY `material_requests_department_id_foreign` (`department_id`),
  KEY `material_requests_employee_master_id_foreign` (`employee_master_id`),
  KEY `material_requests_company_id_foreign` (`company_id`),
  KEY `material_requests_created_by_foreign` (`created_by`),
  CONSTRAINT `material_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_requests_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_requests`
--

LOCK TABLES `material_requests` WRITE;
/*!40000 ALTER TABLE `material_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `material_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_02_175549_create_companies_table',1),(5,'2026_01_02_175912_create_dealer_locations_table',1),(6,'2026_01_02_180126_create_segments_table',1),(7,'2026_01_05_100722_create_permission_tables',1),(8,'2026_01_06_121052_create_designations',1),(9,'2026_01_07_042257_create_departments_table',1),(10,'2026_01_07_044902_create_sub_segments_table',1),(11,'2026_01_07_085954_create_employees_table',1),(12,'2026_01_08_122733_add_employee_fields_to_users_table',1),(13,'2026_01_11_165932_create_brands_table',1),(14,'2026_01_11_170012_create_asset_types_table',1),(15,'2026_01_12_033706_create_branch_locations_table',1),(16,'2026_01_12_074737_create_personal_access_tokens_table',1),(17,'2026_01_12_164903_create_branches_table',1),(18,'2026_01_12_172658_add_branch_id_to_branch_locations_table',1),(19,'2026_01_13_022832_create_assets_table',1),(20,'2026_01_13_102652_create_vendors_table',1),(21,'2026_01_13_123414_create_personal_access_tokens_table',1),(22,'2026_01_20_023557_create_asset_assigning_table',1),(23,'2026_01_21_134908_create_asset_warranties_table',1),(24,'2026_01_22_104446_create_branch_user_table',1),(25,'2026_01_22_111539_add_branch_id_to_users_table',1),(26,'2026_01_22_122630_change_migration_to_assets_table',1),(27,'2026_01_24_013155_add_branch_id_to_assets_table',1),(28,'2026_01_24_022311_alter_assets_status_enum_add_in_transit',1),(29,'2026_01_24_032504_add_aditional_fields_to_asset_assignments_table',1),(30,'2026_01_26_133930_create_support_tickets_table',1),(31,'2026_01_26_170523_add_additional_fields_to_branch_user_table',1),(32,'2026_01_26_172045_create_department_user_table',1),(33,'2026_01_27_164715_create_support_ticket_assignments_table',1),(34,'2026_01_28_093421_create_support_ticket_commands_table',1),(35,'2026_01_29_023703_create_support_ticket_status_logs_table',1),(36,'2026_01_30_025148_add_is_department_head_to_users_table',1),(37,'2026_01_31_031159_change_enum_values_to_asset_assignments_table',1),(38,'2026_02_02_035922_add_is_active_to_support_ticket_assignments_table',1),(39,'2026_02_03_064224_create_file_storage_table',1),(40,'2026_02_03_072626_add_ticket_file_to_support_tickets_table',1),(41,'2026_02_03_103335_create_asset_categories_table',1),(42,'2026_02_03_111156_add_category_to_asset_types_table',1),(43,'2026_02_04_031520_add_asset_structure_fields_to_assets_table',1),(44,'2026_02_04_105457_create_asset_documents_table',1),(45,'2026_02_04_172147_add_filename_to_support_tickets_table',1),(46,'2026_02_05_171447_add_filename_to_assets_table',1),(47,'2026_02_05_172935_add_filename_to_support_ticket_commands_table',1),(48,'2026_02_06_025141_create_support_ticket_activities_table',1),(49,'2026_02_06_105747_add_voice_note_file_to_support_ticket_commands_table',1),(50,'2026_02_06_114021_create_company_user_table',1),(51,'2026_02_06_170321_add_brand_id_to_assets_table',1),(52,'2026_02_07_020952_add_asset_code_to_assets_table',1),(53,'2026_02_08_085950_create_settings_table',1),(54,'2026_02_08_145712_change_in_transit_to_asset_assignments_table',1),(55,'2026_02_09_023246_add_assigned_by_to_asset_assignments_table',1),(56,'2026_02_09_170442_add_company_id_to_assets_table',1),(57,'2026_02_09_174009_add_company_id_to_asset_assignments_table',1),(58,'2026_02_11_163532_add_vendor_id_to_support_tickets_table',1),(59,'2026_02_12_031709_add_close_ticket_commands_to_support_tickets_table',1),(60,'2026_02_12_090254_create_asset_maintenance_table',1),(61,'2026_02_14_052136_add_operational_status_to_assets_table',1),(62,'2026_02_15_171409_create_asset_replacements_table',1),(63,'2026_03_04_094511_create_device_tokens_table',1),(64,'2026_03_11_191226_update_status_enum_in_support_tickets_table',1),(65,'2026_03_14_084828_add_completed_file_to_support_tickets_table',1),(66,'2026_03_20_000001_create_task_categories_table',1),(67,'2026_03_20_000002_create_recurring_tasks_table',1),(68,'2026_03_20_000003_create_recurring_task_tickets_table',1),(69,'2026_03_20_100000_add_fields_to_recurring_tasks_table',1),(70,'2026_03_21_000001_update_recurring_task_priority_medium_to_normal',1),(71,'2026_03_21_100000_add_task_code_to_recurring_tasks_table',1),(72,'2026_03_21_120000_make_recurring_tasks_frequency_nullable',1),(73,'2026_03_21_140000_add_employee_completion_fields_to_recurring_tasks_table',1),(74,'2026_03_22_000001_add_branch_and_assignee_to_recurring_tasks_table',1),(75,'2026_03_23_000001_add_assignment_type_to_recurring_tasks_table',1),(76,'2026_03_24_000001_drop_task_code_from_recurring_tasks_table',1),(77,'2026_03_24_000002_backfill_recurring_task_assignment_type_none',1),(78,'2026_03_24_224730_add_completed_at_to_support_tickets_table',1),(79,'2026_03_25_000001_change_recurring_task_dates_to_datetime',1),(80,'2026_03_26_000001_recurring_tasks_assignment_type_none_to_branch',1),(81,'2026_03_27_000001_add_schedule_config_to_recurring_tasks_table',1),(82,'2026_03_28_000001_add_task_no_to_recurring_tasks_table',1),(83,'2026_03_29_000001_add_attachment_path_to_recurring_tasks_table',1),(84,'2026_03_30_000001_add_ticket_status_to_recurring_tasks_table',1),(85,'2026_03_30_000002_add_created_by_to_recurring_tasks_table',1),(86,'2026_03_31_000001_add_ticket_completed_at_to_recurring_tasks_table',1),(87,'2026_04_13_000001_create_tasks_and_task_user_tables',1),(88,'2026_04_13_000002_add_recurring_type_weekdays_months_to_recurring_tasks_table',1),(89,'2026_04_14_000002_add_due_date_to_tasks_table',1),(90,'2026_04_14_000003_add_status_to_tasks_table',1),(91,'2026_04_15_000001_add_branch_id_to_recurring_task_tickets_table',1),(92,'2026_04_15_000002_update_recurring_task_tickets_unique_key_for_branch',1),(93,'2026_04_15_000003_cleanup_null_branch_id_in_recurring_task_tickets',1),(94,'2026_04_15_000004_create_task_reports_table',1),(95,'2026_04_15_000005_add_unique_task_reports_task_branch_date',1),(96,'2026_04_15_000006_make_task_reports_occurrence_date_not_null',1),(97,'2026_04_15_000007_create_task_report_collaborators_table',1),(98,'2026_04_16_144528_alter_employee_completion_image_path_column',1),(99,'2026_05_06_183500_add_missing_recurring_task_performance_indexes',1),(100,'2026_05_07_091800_create_recurring_task_read_model_tables',1),(101,'2026_05_07_103000_create_asset_names_table',1),(102,'2026_05_07_105000_add_asset_search_indexes',1),(103,'2026_05_07_163443_add_progress_counter_columns_to_recurring_tasks_table',1),(104,'2026_05_07_164653_add_acceptance_and_access_columns_to_task_reports_table',1),(105,'2026_05_13_120000_add_is_lifetime_to_recurring_tasks_table',1),(106,'2026_05_13_140000_add_last_generated_date_to_recurring_tasks_table',1),(107,'2026_05_14_120000_consolidate_recurring_task_tickets_one_per_occurrence',1),(108,'2026_05_15_100000_ensure_recurring_task_tickets_nullable_branch_id_and_occurrence_unique',1),(109,'2026_05_20_120000_add_transferred_to_assets_status_enum',1),(110,'2026_05_21_100000_prune_assets_operational_status_values',1),(111,'2026_05_21_120000_recreate_support_tickets_table_if_missing',1),(112,'2026_06_05_120000_add_completed_files_to_support_tickets_table',1),(113,'2026_06_05_130000_add_filenames_to_support_ticket_commands_table',1),(114,'2026_07_16_124635_add_rejected_to_support_tickets_status_enum',1),(115,'2026_07_17_150000_add_notification_sent_columns_to_recurring_task_tickets_table',1),(116,'2026_07_18_000000_remove_support_ticket_link_from_asset_maintenance',1),(117,'2026_07_18_151819_create_indents_table',1),(118,'2026_07_18_151820_create_indent_items_table',1),(119,'2026_07_18_151821_create_indent_approvals_table',1),(120,'2026_07_18_151822_create_purchase_requisitions_table',1),(121,'2026_07_18_151823_create_rfqs_table',1),(122,'2026_07_18_151824_create_vendor_quotations_table',1),(123,'2026_07_18_151825_create_purchase_orders_table',1),(124,'2026_07_18_151826_create_po_items_table',1),(125,'2026_07_18_151827_create_grns_table',1),(126,'2026_07_18_151828_create_grn_items_table',1),(127,'2026_07_18_160000_add_procurement_domain_foundation',1),(128,'2026_07_18_160001_create_notifications_table',1),(129,'2026_07_18_171018_add_karexpert_fields_to_procurement_tables',1),(130,'2026_07_18_180000_create_dynamic_approval_workflows',1),(131,'2026_07_20_100000_enterprise_procurement_item_type_and_inventory',1),(132,'2026_07_20_110000_create_approval_templates_system',1),(133,'2026_07_20_120000_extend_indent_approval_hierarchy_fields',1),(134,'2026_07_20_140000_drop_inventory_tables',1),(135,'2026_07_20_150000_extend_purchase_order_fields',1),(136,'2026_07_20_160000_complete_grn_module_fields',1),(137,'2026_07_20_170000_extend_purchase_requisition_module',1),(138,'2026_07_20_180000_extend_purchase_order_module',1),(139,'2026_07_20_190000_renumber_procurement_document_ids',1),(140,'2026_07_20_200000_extend_rfq_module_fields',1),(141,'2026_07_21_100000_create_po_approval_steps_table',1),(142,'2026_07_21_100001_add_module_to_approval_templates',1),(143,'2026_07_21_110000_approval_templates_unique_module_category',1),(144,'2026_07_21_170000_create_purchase_order_payments_table',1),(145,'2026_07_21_180000_add_vendor_quotation_negotiations',1),(146,'2026_07_24_171340_create_item_masters_table',1),(147,'2026_07_25_220000_create_payment_requests_table',1),(148,'2026_07_25_230000_create_quality_inspections_and_add_item_status',1),(149,'2026_07_26_180000_add_delivery_details_to_grns_table',1),(150,'2026_07_27_140000_add_negotiated_unit_price_to_vendor_quotation_items_table',1),(151,'2026_07_29_000001_add_tax_and_summary_fields_to_po_tables',1),(152,'2026_07_29_104151_add_has_free_qty_to_po_items_table',1),(153,'2026_08_19_172823_restore_support_ticket_link_to_asset_maintenance',1),(154,'2026_08_20_000001_create_product_categories_table',1),(155,'2026_08_20_000002_create_product_sub_categories_table',1),(156,'2026_08_20_100001_create_msu_item_masters_table',1),(157,'2026_08_20_110001_add_company_code_to_vendors_table',1),(158,'2026_08_20_120001_create_units_table',1),(159,'2026_08_20_120002_add_unit_id_to_msu_item_masters_table',1),(160,'2026_08_20_130001_create_payment_terms_table',1),(161,'2026_08_20_130002_create_msu_purchase_orders_tables',1),(162,'2026_08_20_140001_create_miscellaneous_charges_table',1),(163,'2026_08_20_160001_add_code_to_payment_terms_and_misc_charges',1),(164,'2026_08_21_100001_add_branch_gst_and_msu_po_branch_fields',1),(165,'2026_08_21_110001_add_category_code_to_product_categories_table',1),(166,'2026_08_21_111000_uppercase_category_and_item_codes',1),(167,'2026_08_21_133000_uppercase_unit_names',1),(168,'2026_08_21_140001_create_hsns_table',1),(169,'2026_08_21_150001_create_employee_masters_table',1),(170,'2026_08_21_150002_enhance_msu_item_masters_with_hsn_image_rates',1),(171,'2026_08_21_160001_add_vendor_pan_and_payment_mode',1),(172,'2026_08_21_170001_create_shipping_details_table',1),(173,'2026_08_21_180001_add_department_and_remarks_to_msu_purchase_orders',1),(174,'2026_08_21_190001_add_miscellaneous_tax_to_msu_purchase_orders',1),(175,'2026_08_21_200001_update_msu_po_status_and_received_qty',1),(176,'2026_08_21_210001_create_msu_grns_tables',1),(177,'2026_08_22_102639_create_material_requests_and_issues_tables',1),(178,'2026_08_24_125029_add_stock_to_msu_item_masters',1),(179,'2026_08_24_125037_create_msu_stock_movements_table',1),(180,'2026_08_24_154646_add_branch_id_to_material_requests_table',1),(181,'2026_08_24_160010_add_igst_to_msu_purchase_orders_table',1),(182,'2026_08_24_172635_update_mr_mi_statuses',1),(183,'2026_08_24_181541_create_msu_purchase_order_charges_table',1),(184,'2026_08_24_184409_create_msu_stock_adjustments_table',1),(185,'2026_08_25_130137_add_cancel_reason_to_msu_purchase_orders_table',1),(186,'2026_08_25_130932_add_cancel_reason_to_material_requests_table',1),(187,'2026_08_26_100344_create_msu_daily_item_stocks_table',1),(188,'2026_08_28_100526_add_miscellaneous_charges_to_msu_grns_table',1),(189,'2026_08_28_120000_add_logo_to_companies_table',1),(190,'2026_08_28_122945_create_msu_work_orders_tables',1),(191,'2026_08_28_150716_add_is_added_to_stock_to_msu_grns_table',1),(192,'2026_08_28_180000_add_unit_id_to_msu_work_order_items_table',1),(193,'2026_08_29_120000_add_msu_grn_asset_link',1),(194,'2026_08_29_123000_add_product_categories_to_assets_table',1),(195,'2026_08_31_102217_add_scope_of_work_to_msu_work_order_items_table',1),(196,'2026_08_31_102218_add_document_path_to_msu_work_orders_table',1),(197,'2026_08_31_113849_add_misc_charges_to_msu_work_invoices',1),(198,'2026_09_07_150000_add_contact_person_to_vendors_table',1),(199,'2026_09_07_151500_add_branch_id_to_msu_grns_table',1),(200,'2026_09_09_135707_widen_msu_item_name_columns',1),(201,'2026_09_10_111338_widen_payment_terms_payment_mode',1),(202,'2026_09_11_122700_widen_msu_item_name_to_1500',2),(203,'2026_09_11_133700_ensure_branch_id_on_msu_grns_table',2),(204,'2026_09_21_160000_add_asset_status_to_msu_grns_table',2),(205,'2026_09_24_160000_add_frequency_to_msu_work_orders_table',2),(206,'2026_09_24_170000_add_threshold_quantity_to_msu_item_masters_table',2),(207,'2026_09_25_120000_add_client_fields_to_assets_table',2),(208,'2026_09_29_120000_add_pricing_to_msu_stock_adjustments_table',2),(209,'2026_09_29_160000_point_asset_custodian_to_employee_masters',2),(210,'2026_09_29_170000_point_asset_assignment_employee_to_employee_masters',2),(211,'2026_09_30_150000_add_capex_and_group_asset_to_msu_grn_items',2),(212,'2026_10_01_100000_create_cost_centres_table',2),(213,'2026_10_01_100000_widen_assets_purchase_cost',2),(214,'2026_10_05_120000_create_asset_custodians_and_link_assets',2),(215,'2026_10_05_150000_add_department_and_employee_master_to_asset_assignments',2),(216,'2026_10_08_113000_drop_employee_master_id_from_asset_assignments',2),(217,'2026_10_13_120000_add_audit_fields_to_assets_table',2),(218,'2026_10_13_130000_create_asset_audits_table',3),(219,'2026_10_14_100000_create_audit_histories_table',4),(220,'2026_10_13_120100_create_audit_histories_table',5),(221,'2026_10_21_104500_set_operational_status_disposed_for_disposed_assets',6),(222,'2026_10_13_112245_create_personal_access_tokens_table',7),(223,'2026_10_13_120000_create_assets_table',7),(224,'2026_10_21_150500_add_metadata_to_asset_audits_table',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `miscellaneous_charges`
--

DROP TABLE IF EXISTS `miscellaneous_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `miscellaneous_charges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `charge_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `miscellaneous_charges_charge_name_unique` (`charge_name`),
  UNIQUE KEY `miscellaneous_charges_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `miscellaneous_charges`
--

LOCK TABLES `miscellaneous_charges` WRITE;
/*!40000 ALTER TABLE `miscellaneous_charges` DISABLE KEYS */;
/*!40000 ALTER TABLE `miscellaneous_charges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
INSERT INTO `model_has_permissions` VALUES (268,'App\\Models\\User',1),(269,'App\\Models\\User',1),(270,'App\\Models\\User',1),(271,'App\\Models\\User',1),(272,'App\\Models\\User',1),(273,'App\\Models\\User',1),(274,'App\\Models\\User',1),(275,'App\\Models\\User',1),(276,'App\\Models\\User',1),(277,'App\\Models\\User',1),(278,'App\\Models\\User',1),(279,'App\\Models\\User',1),(280,'App\\Models\\User',1),(281,'App\\Models\\User',1),(282,'App\\Models\\User',1),(283,'App\\Models\\User',1),(24,'App\\Models\\User',7);
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(12,'App\\Models\\User',5),(10,'App\\Models\\User',7),(11,'App\\Models\\User',8),(12,'App\\Models\\User',9),(15,'App\\Models\\User',10),(16,'App\\Models\\User',11),(17,'App\\Models\\User',12),(3,'App\\Models\\User',13),(3,'App\\Models\\User',14),(3,'App\\Models\\User',15),(3,'App\\Models\\User',16),(9,'App\\Models\\User',17),(2,'App\\Models\\User',20),(3,'App\\Models\\User',21);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_daily_item_stocks`
--

DROP TABLE IF EXISTS `msu_daily_item_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_daily_item_stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `opening_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_in` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_out` decimal(15,2) NOT NULL DEFAULT '0.00',
  `closing_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_daily_item_stocks_msu_item_master_id_date_unique` (`msu_item_master_id`,`date`),
  CONSTRAINT `msu_daily_item_stocks_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_daily_item_stocks`
--

LOCK TABLES `msu_daily_item_stocks` WRITE;
/*!40000 ALTER TABLE `msu_daily_item_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_daily_item_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_grn_items`
--

DROP TABLE IF EXISTS `msu_grn_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_grn_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_grn_id` bigint unsigned NOT NULL,
  `msu_purchase_order_item_id` bigint unsigned NOT NULL,
  `msu_item_master_id` bigint unsigned DEFAULT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(1500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordered_quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `previously_received_quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `received_quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `is_capex` tinyint(1) NOT NULL DEFAULT '0',
  `is_group_asset` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_grn_items_msu_grn_id_foreign` (`msu_grn_id`),
  KEY `msu_grn_items_msu_purchase_order_item_id_foreign` (`msu_purchase_order_item_id`),
  KEY `msu_grn_items_msu_item_master_id_foreign` (`msu_item_master_id`),
  CONSTRAINT `msu_grn_items_msu_grn_id_foreign` FOREIGN KEY (`msu_grn_id`) REFERENCES `msu_grns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `msu_grn_items_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_grn_items_msu_purchase_order_item_id_foreign` FOREIGN KEY (`msu_purchase_order_item_id`) REFERENCES `msu_purchase_order_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_grn_items`
--

LOCK TABLES `msu_grn_items` WRITE;
/*!40000 ALTER TABLE `msu_grn_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_grn_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_grns`
--

DROP TABLE IF EXISTS `msu_grns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_grns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msu_purchase_order_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `employee_master_id` bigint unsigned DEFAULT NULL,
  `vendor_invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_invoice_date` date DEFAULT NULL,
  `grn_date` date NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `is_added_to_stock` tinyint(1) NOT NULL DEFAULT '0',
  `is_added_to_asset` tinyint(1) NOT NULL DEFAULT '0',
  `asset_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `asset_cancel_reason` text COLLATE utf8mb4_unicode_ci,
  `miscellaneous_charges` decimal(15,2) NOT NULL DEFAULT '0.00',
  `miscellaneous_tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_grns_grn_number_unique` (`grn_number`),
  KEY `msu_grns_msu_purchase_order_id_foreign` (`msu_purchase_order_id`),
  KEY `msu_grns_vendor_id_foreign` (`vendor_id`),
  KEY `msu_grns_department_id_foreign` (`department_id`),
  KEY `msu_grns_employee_master_id_foreign` (`employee_master_id`),
  KEY `msu_grns_created_by_foreign` (`created_by`),
  KEY `msu_grns_grn_date_vendor_id_index` (`grn_date`,`vendor_id`),
  KEY `msu_grns_branch_id_foreign` (`branch_id`),
  CONSTRAINT `msu_grns_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_grns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_grns_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_grns_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_grns_msu_purchase_order_id_foreign` FOREIGN KEY (`msu_purchase_order_id`) REFERENCES `msu_purchase_orders` (`id`),
  CONSTRAINT `msu_grns_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_grns`
--

LOCK TABLES `msu_grns` WRITE;
/*!40000 ALTER TABLE `msu_grns` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_grns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_item_masters`
--

DROP TABLE IF EXISTS `msu_item_masters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_item_masters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_name` varchar(1500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_category_id` bigint unsigned NOT NULL,
  `product_sub_category_id` bigint unsigned NOT NULL,
  `expense_type` enum('opex','capex') COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `hsn_id` bigint unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_stock` decimal(15,3) NOT NULL DEFAULT '0.000',
  `current_stock` decimal(15,3) NOT NULL DEFAULT '0.000',
  `threshold_quantity` decimal(15,3) DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_item_masters_item_code_unique` (`item_code`),
  KEY `msu_item_masters_product_sub_category_id_foreign` (`product_sub_category_id`),
  KEY `msu_item_masters_unit_id_foreign` (`unit_id`),
  KEY `msu_item_masters_hsn_id_foreign` (`hsn_id`),
  KEY `msu_item_masters_product_category_id_index` (`product_category_id`),
  CONSTRAINT `msu_item_masters_hsn_id_foreign` FOREIGN KEY (`hsn_id`) REFERENCES `hsns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_item_masters_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`),
  CONSTRAINT `msu_item_masters_product_sub_category_id_foreign` FOREIGN KEY (`product_sub_category_id`) REFERENCES `product_sub_categories` (`id`),
  CONSTRAINT `msu_item_masters_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_item_masters`
--

LOCK TABLES `msu_item_masters` WRITE;
/*!40000 ALTER TABLE `msu_item_masters` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_item_masters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_item_rates`
--

DROP TABLE IF EXISTS `msu_item_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_item_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `rate` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  `effective_from` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_item_rates_msu_item_master_id_effective_from_index` (`msu_item_master_id`,`effective_from`),
  CONSTRAINT `msu_item_rates_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_item_rates`
--

LOCK TABLES `msu_item_rates` WRITE;
/*!40000 ALTER TABLE `msu_item_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_item_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_purchase_order_charges`
--

DROP TABLE IF EXISTS `msu_purchase_order_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_purchase_order_charges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_purchase_order_id` bigint unsigned NOT NULL,
  `charge_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_percentage` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_purchase_order_charges_msu_purchase_order_id_foreign` (`msu_purchase_order_id`),
  CONSTRAINT `msu_purchase_order_charges_msu_purchase_order_id_foreign` FOREIGN KEY (`msu_purchase_order_id`) REFERENCES `msu_purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_purchase_order_charges`
--

LOCK TABLES `msu_purchase_order_charges` WRITE;
/*!40000 ALTER TABLE `msu_purchase_order_charges` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_purchase_order_charges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_purchase_order_items`
--

DROP TABLE IF EXISTS `msu_purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_purchase_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_purchase_order_id` bigint unsigned NOT NULL,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(1500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `received_quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Tax percent',
  `net_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_purchase_order_items_msu_purchase_order_id_foreign` (`msu_purchase_order_id`),
  KEY `msu_purchase_order_items_msu_item_master_id_foreign` (`msu_item_master_id`),
  CONSTRAINT `msu_purchase_order_items_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`),
  CONSTRAINT `msu_purchase_order_items_msu_purchase_order_id_foreign` FOREIGN KEY (`msu_purchase_order_id`) REFERENCES `msu_purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_purchase_order_items`
--

LOCK TABLES `msu_purchase_order_items` WRITE;
/*!40000 ALTER TABLE `msu_purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_purchase_orders`
--

DROP TABLE IF EXISTS `msu_purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `po_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `date_of_commencement` date DEFAULT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `employee_master_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `shipping_detail_id` bigint unsigned DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `shipping_gst` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `cancel_reason` text COLLATE utf8mb4_unicode_ci,
  `payment_term_id` bigint unsigned DEFAULT NULL,
  `miscellaneous_charge_id` bigint unsigned DEFAULT NULL,
  `total_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `miscellaneous_charges` decimal(15,2) NOT NULL DEFAULT '0.00',
  `miscellaneous_tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  `sgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `igst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'po_created',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_purchase_orders_po_number_unique` (`po_number`),
  KEY `msu_purchase_orders_vendor_id_foreign` (`vendor_id`),
  KEY `msu_purchase_orders_payment_term_id_foreign` (`payment_term_id`),
  KEY `msu_purchase_orders_created_by_foreign` (`created_by`),
  KEY `msu_purchase_orders_miscellaneous_charge_id_foreign` (`miscellaneous_charge_id`),
  KEY `msu_purchase_orders_branch_id_foreign` (`branch_id`),
  KEY `msu_purchase_orders_employee_master_id_foreign` (`employee_master_id`),
  KEY `msu_purchase_orders_shipping_detail_id_foreign` (`shipping_detail_id`),
  KEY `msu_purchase_orders_department_id_foreign` (`department_id`),
  CONSTRAINT `msu_purchase_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_miscellaneous_charge_id_foreign` FOREIGN KEY (`miscellaneous_charge_id`) REFERENCES `miscellaneous_charges` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_payment_term_id_foreign` FOREIGN KEY (`payment_term_id`) REFERENCES `payment_terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_shipping_detail_id_foreign` FOREIGN KEY (`shipping_detail_id`) REFERENCES `shipping_details` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_purchase_orders`
--

LOCK TABLES `msu_purchase_orders` WRITE;
/*!40000 ALTER TABLE `msu_purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_stock_adjustments`
--

DROP TABLE IF EXISTS `msu_stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_stock_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adjustment_date` date NOT NULL,
  `adjustment_type` enum('increment','decrement') COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_master_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gst_percentage` decimal(8,2) NOT NULL DEFAULT '0.00',
  `gst_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_stock_adjustments_adjustment_number_unique` (`adjustment_number`),
  KEY `msu_stock_adjustments_employee_master_id_foreign` (`employee_master_id`),
  KEY `msu_stock_adjustments_department_id_foreign` (`department_id`),
  KEY `msu_stock_adjustments_msu_item_master_id_foreign` (`msu_item_master_id`),
  KEY `msu_stock_adjustments_created_by_foreign` (`created_by`),
  CONSTRAINT `msu_stock_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `msu_stock_adjustments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_stock_adjustments_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_stock_adjustments_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_stock_adjustments`
--

LOCK TABLES `msu_stock_adjustments` WRITE;
/*!40000 ALTER TABLE `msu_stock_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_stock_movements`
--

DROP TABLE IF EXISTS `msu_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_item_master_id` bigint unsigned NOT NULL,
  `transaction_type` enum('in','out','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `stock_before` decimal(15,3) NOT NULL,
  `stock_after` decimal(15,3) NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_stock_movements_msu_item_master_id_foreign` (`msu_item_master_id`),
  KEY `msu_stock_movements_created_by_foreign` (`created_by`),
  CONSTRAINT `msu_stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_stock_movements_msu_item_master_id_foreign` FOREIGN KEY (`msu_item_master_id`) REFERENCES `msu_item_masters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_stock_movements`
--

LOCK TABLES `msu_stock_movements` WRITE;
/*!40000 ALTER TABLE `msu_stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_work_invoice_items`
--

DROP TABLE IF EXISTS `msu_work_invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_work_invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_work_invoice_id` bigint unsigned NOT NULL,
  `msu_work_order_item_id` bigint unsigned NOT NULL,
  `work_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Tax percent',
  `net_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_work_invoice_items_msu_work_invoice_id_foreign` (`msu_work_invoice_id`),
  KEY `msu_work_invoice_items_msu_work_order_item_id_foreign` (`msu_work_order_item_id`),
  CONSTRAINT `msu_work_invoice_items_msu_work_invoice_id_foreign` FOREIGN KEY (`msu_work_invoice_id`) REFERENCES `msu_work_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `msu_work_invoice_items_msu_work_order_item_id_foreign` FOREIGN KEY (`msu_work_order_item_id`) REFERENCES `msu_work_order_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_work_invoice_items`
--

LOCK TABLES `msu_work_invoice_items` WRITE;
/*!40000 ALTER TABLE `msu_work_invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_work_invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_work_invoices`
--

DROP TABLE IF EXISTS `msu_work_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_work_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wi_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msu_work_order_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `igst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `miscellaneous_charges` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'passed',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_work_invoices_wi_number_unique` (`wi_number`),
  KEY `msu_work_invoices_msu_work_order_id_foreign` (`msu_work_order_id`),
  KEY `msu_work_invoices_vendor_id_foreign` (`vendor_id`),
  KEY `msu_work_invoices_branch_id_foreign` (`branch_id`),
  KEY `msu_work_invoices_created_by_foreign` (`created_by`),
  CONSTRAINT `msu_work_invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_invoices_msu_work_order_id_foreign` FOREIGN KEY (`msu_work_order_id`) REFERENCES `msu_work_orders` (`id`),
  CONSTRAINT `msu_work_invoices_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_work_invoices`
--

LOCK TABLES `msu_work_invoices` WRITE;
/*!40000 ALTER TABLE `msu_work_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_work_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_work_order_charges`
--

DROP TABLE IF EXISTS `msu_work_order_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_work_order_charges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_work_order_id` bigint unsigned NOT NULL,
  `charge_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_percentage` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_work_order_charges_msu_work_order_id_foreign` (`msu_work_order_id`),
  CONSTRAINT `msu_work_order_charges_msu_work_order_id_foreign` FOREIGN KEY (`msu_work_order_id`) REFERENCES `msu_work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_work_order_charges`
--

LOCK TABLES `msu_work_order_charges` WRITE;
/*!40000 ALTER TABLE `msu_work_order_charges` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_work_order_charges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_work_order_items`
--

DROP TABLE IF EXISTS `msu_work_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_work_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `msu_work_order_id` bigint unsigned NOT NULL,
  `work_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope_of_work` text COLLATE utf8mb4_unicode_ci,
  `hsn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Tax percent',
  `net_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `received_quantity` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'Invoiced quantity',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msu_work_order_items_msu_work_order_id_foreign` (`msu_work_order_id`),
  KEY `msu_work_order_items_unit_id_foreign` (`unit_id`),
  CONSTRAINT `msu_work_order_items_msu_work_order_id_foreign` FOREIGN KEY (`msu_work_order_id`) REFERENCES `msu_work_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `msu_work_order_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_work_order_items`
--

LOCK TABLES `msu_work_order_items` WRITE;
/*!40000 ALTER TABLE `msu_work_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_work_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msu_work_orders`
--

DROP TABLE IF EXISTS `msu_work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `msu_work_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wo_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wo_date` date NOT NULL,
  `contract_period_from` date DEFAULT NULL,
  `contract_period_to` date DEFAULT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurrence_source_id` bigint unsigned DEFAULT NULL,
  `next_occurrence_date` date DEFAULT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci,
  `delivery_date` date DEFAULT NULL,
  `date_of_commencement` date DEFAULT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `employee_master_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `shipping_detail_id` bigint unsigned DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `shipping_contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_gst` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_term_id` bigint unsigned DEFAULT NULL,
  `miscellaneous_charge_id` bigint unsigned DEFAULT NULL,
  `total_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `miscellaneous_charges` decimal(15,2) NOT NULL DEFAULT '0.00',
  `miscellaneous_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `igst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'wo_created',
  `cancel_reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msu_work_orders_wo_number_unique` (`wo_number`),
  KEY `msu_work_orders_vendor_id_foreign` (`vendor_id`),
  KEY `msu_work_orders_branch_id_foreign` (`branch_id`),
  KEY `msu_work_orders_employee_master_id_foreign` (`employee_master_id`),
  KEY `msu_work_orders_department_id_foreign` (`department_id`),
  KEY `msu_work_orders_shipping_detail_id_foreign` (`shipping_detail_id`),
  KEY `msu_work_orders_payment_term_id_foreign` (`payment_term_id`),
  KEY `msu_work_orders_miscellaneous_charge_id_foreign` (`miscellaneous_charge_id`),
  KEY `msu_work_orders_created_by_foreign` (`created_by`),
  KEY `msu_work_orders_recurrence_source_id_foreign` (`recurrence_source_id`),
  KEY `msu_work_orders_next_occurrence_date_index` (`next_occurrence_date`),
  CONSTRAINT `msu_work_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_employee_master_id_foreign` FOREIGN KEY (`employee_master_id`) REFERENCES `employee_masters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_miscellaneous_charge_id_foreign` FOREIGN KEY (`miscellaneous_charge_id`) REFERENCES `miscellaneous_charges` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_payment_term_id_foreign` FOREIGN KEY (`payment_term_id`) REFERENCES `payment_terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_recurrence_source_id_foreign` FOREIGN KEY (`recurrence_source_id`) REFERENCES `msu_work_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_shipping_detail_id_foreign` FOREIGN KEY (`shipping_detail_id`) REFERENCES `shipping_details` (`id`) ON DELETE SET NULL,
  CONSTRAINT `msu_work_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msu_work_orders`
--

LOCK TABLES `msu_work_orders` WRITE;
/*!40000 ALTER TABLE `msu_work_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `msu_work_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_requests`
--

DROP TABLE IF EXISTS `payment_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_request_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_order_id` bigint unsigned NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `request_date` datetime NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `payment_date` date DEFAULT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `paid_by` bigint unsigned DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_requests_payment_request_no_unique` (`payment_request_no`),
  KEY `payment_requests_requested_by_foreign` (`requested_by`),
  KEY `payment_requests_approved_by_foreign` (`approved_by`),
  KEY `payment_requests_paid_by_foreign` (`paid_by`),
  KEY `payment_requests_rejected_by_foreign` (`rejected_by`),
  KEY `payment_requests_purchase_order_id_status_index` (`purchase_order_id`,`status`),
  KEY `payment_requests_status_request_date_index` (`status`,`request_date`),
  CONSTRAINT `payment_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_requests_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_requests_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_requests_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_requests`
--

LOCK TABLES `payment_requests` WRITE;
/*!40000 ALTER TABLE `payment_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_terms`
--

DROP TABLE IF EXISTS `payment_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_terms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_mode` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_terms_term_name_unique` (`term_name`),
  UNIQUE KEY `payment_terms_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_terms`
--

LOCK TABLES `payment_terms` WRITE;
/*!40000 ALTER TABLE `payment_terms` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'msu-grn:approve','web','2026-10-13 07:10:00','2026-10-13 07:10:00'),(2,'msu-grn:cancel','web','2026-10-13 07:10:00','2026-10-13 07:10:00'),(3,'cost-centres:view','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(4,'cost-centres:create','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(5,'cost-centres:edit','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(6,'cost-centres:delete','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(7,'asset-custodian:view','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(8,'asset-custodian:create','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(9,'asset-custodian:edit','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(10,'asset-custodian:delete','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(11,'asset-custodian:status','web','2026-10-13 07:10:01','2026-10-13 07:10:01'),(12,'roles:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(13,'roles:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(14,'roles:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(15,'roles:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(16,'permissions:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(17,'permissions:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(18,'permissions:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(19,'permissions:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(20,'users:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(21,'users:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(22,'users:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(23,'users:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(24,'dashboard:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(25,'companies:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(26,'companies:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(27,'companies:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(28,'companies:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(29,'branch:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(30,'branch:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(31,'branch:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(32,'branch:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(33,'location:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(34,'location:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(35,'location:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(36,'location:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(37,'brands:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(38,'brands:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(39,'brands:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(40,'brands:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(41,'department:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(42,'department:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(43,'department:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(44,'department:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(45,'designation:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(46,'designation:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(47,'designation:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(48,'designation:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(49,'vendor:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(50,'vendor:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(51,'vendor:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(52,'vendor:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(53,'asset-master.view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(54,'asset-master.create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(55,'asset-master.edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(56,'asset-master.delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(57,'item-master:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(58,'item-master:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(59,'item-master:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(60,'item-master:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(61,'product-category:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(62,'product-category:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(63,'product-category:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(64,'product-category:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(65,'product-category:status','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(66,'product-sub-category:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(67,'product-sub-category:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(68,'product-sub-category:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(69,'product-sub-category:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(70,'product-sub-category:status','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(71,'unit:view','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(72,'unit:create','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(73,'unit:edit','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(74,'unit:delete','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(75,'unit:status','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(76,'payment-term:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(77,'payment-term:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(78,'payment-term:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(79,'payment-term:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(80,'payment-term:status','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(81,'miscellaneous-charge:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(82,'miscellaneous-charge:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(83,'miscellaneous-charge:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(84,'miscellaneous-charge:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(85,'miscellaneous-charge:status','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(86,'hsn:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(87,'hsn:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(88,'hsn:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(89,'hsn:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(90,'hsn:status','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(91,'employee-master:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(92,'employee-master:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(93,'employee-master:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(94,'employee-master:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(95,'employee-master:status','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(96,'shipping-detail:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(97,'shipping-detail:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(98,'shipping-detail:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(99,'shipping-detail:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(100,'shipping-detail:status','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(101,'msu-purchase-order:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(102,'msu-purchase-order:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(103,'msu-purchase-order:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(104,'msu-purchase-order:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(105,'msu-purchase-order:approve','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(106,'msu-purchase-order:cancel','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(107,'msu-work-order:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(108,'msu-work-order:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(109,'msu-work-order:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(110,'msu-work-order:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(111,'msu-work-order:approve','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(112,'msu-work-order:cancel','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(113,'msu-grn:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(114,'msu-grn:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(115,'msu-grn:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(116,'asset-types:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(117,'asset-types:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(118,'asset-types:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(119,'asset-types:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(120,'asset-category:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(121,'asset-category:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(122,'asset-category:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(123,'asset-category:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(124,'asset:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(125,'asset:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(126,'asset:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(127,'asset:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(128,'asset.assign.bulk:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(129,'asset.maintenance:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(130,'asset.maintenance:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(131,'asset.maintenance:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(132,'asset.maintenance:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(133,'asset.document:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(134,'asset.document:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(135,'asset.document:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(136,'asset.document:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(137,'asset.assignment:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(138,'asset.assignment:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(139,'asset.assignment:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(140,'asset.assignment:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(141,'asset.warranty:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(142,'asset.warranty:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(143,'asset.warranty:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(144,'asset.warranty:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(145,'asset.replacement:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(146,'asset.replacement:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(147,'asset.replacement:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(148,'asset.replacement:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(149,'asset.depreciation:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(150,'dashboard.asset:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(151,'indents:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(152,'indents:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(153,'indents:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(154,'indents:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(155,'indents:approve','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(156,'indents:reject','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(157,'indent.approve.manager','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(158,'indent.approve.it_head','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(159,'indent.approve.finance','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(160,'approval-workflows:manage','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(161,'purchase-requisitions:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(162,'purchase-requisition.view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(163,'purchase-requisition.process','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(164,'rfqs:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(165,'rfqs:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(166,'rfqs:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(167,'purchase-orders:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(168,'purchase-orders:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(169,'purchase-orders:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(170,'grns:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(171,'grns:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(172,'grns:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(173,'grns:approve','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(174,'grn.view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(175,'grn.create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(176,'grn.edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(177,'grn.approve','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(178,'procurement.dashboard:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(179,'procurement.reports:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(180,'settings:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(181,'reports.companywiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(182,'reports.branchwiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(183,'reports.brandwiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(184,'reports.vendorwiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(185,'reports.locationwiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(186,'reports.categorywiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(187,'reports.typewiseassets:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(188,'employees:view','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(189,'employees:create','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(190,'employees:edit','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(191,'employees:delete','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(192,'indent.approve.l1','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(193,'indent.approve.l2','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(194,'indent.approve.l3','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(195,'indent.approve.l4','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(196,'rfq.view','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(197,'rfq.create','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(198,'rfq.edit','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(199,'rfq.send','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(200,'rfq.delete','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(201,'quotation.view','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(202,'quotation.add','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(203,'quotation.compare','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(204,'quotation.select-vendor','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(205,'quotation.negotiate','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(206,'purchase-order.view','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(207,'purchase-order.create','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(208,'purchase-order.edit','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(209,'purchase-order.delete','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(210,'purchase-order.print','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(211,'purchase-order.cancel','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(212,'purchase-report.view','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(213,'grn.inspect','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(214,'purchase-management.dashboard','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(215,'indents:submit','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(216,'indents:send-back','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(217,'indents:cancel','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(218,'indents:print','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(219,'purchase-requisitions:create','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(220,'purchase-requisitions:edit','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(221,'purchase-requisitions:process','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(222,'purchase-requisitions:proceed-to-rfq','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(223,'purchase-requisitions:transfer-from-store','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(224,'purchase-requisitions:manual-close','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(225,'purchase-requisitions:print','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(226,'purchase-requisition.proceed-to-rfq','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(227,'purchase-requisition.transfer-from-store','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(228,'purchase-requisition.manual-close','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(229,'purchase-requisition.print','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(230,'purchase-requisition.close','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(231,'rfqs:send','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(232,'rfqs:delete','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(233,'rfq.close','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(234,'quotations:view','web','2026-10-13 07:11:43','2026-10-13 07:11:43'),(235,'quotations:create','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(236,'quotations:compare','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(237,'quotations:select-vendor','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(238,'purchase-orders:delete','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(239,'purchase-orders:print','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(240,'purchase-orders:cancel','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(241,'purchase-orders:approve','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(242,'purchase-order.submit','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(243,'purchase-order.approve','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(244,'purchase-order.reject','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(245,'purchase-order.send-back','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(246,'purchase-order.payment','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(247,'purchase-order.pay','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(248,'purchase-orders:payment','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(249,'grns:inspect','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(250,'grn.delete','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(251,'approval-templates:view','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(252,'approval-templates:create','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(253,'approval-templates:edit','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(254,'approval-templates:delete','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(255,'approval-templates:manage','web','2026-10-13 07:11:44','2026-10-13 07:11:44'),(256,'payment.view','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(257,'payments:view','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(258,'payment.create','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(259,'payments:create','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(260,'payment.approve','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(261,'payments:approve','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(262,'payment.pay','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(263,'payments:pay','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(264,'quality-inspection.view','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(265,'quality-inspections:view','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(266,'quality-inspection.perform','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(267,'quality-inspections:perform','web','2026-10-13 07:11:45','2026-10-13 07:11:45'),(268,'support-ticket:view','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(269,'support-ticket:create','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(270,'support-ticket:edit','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(271,'support-ticket:delete','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(272,'support-ticket:accept','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(273,'support-ticket:exit','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(274,'support-ticket:complete','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(275,'dashboard.supportticket:view','api','2026-10-16 09:19:22','2026-10-16 09:19:22'),(276,'support-ticket:view','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(277,'support-ticket:create','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(278,'support-ticket:edit','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(279,'support-ticket:delete','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(280,'support-ticket:accept','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(281,'support-ticket:exit','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(282,'support-ticket:complete','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(283,'dashboard.supportticket:view','web','2026-10-16 09:19:22','2026-10-16 09:19:22'),(284,'audit:view','web','2026-10-22 17:44:41','2026-10-22 17:44:41'),(285,'audit:create','web','2026-10-22 17:44:41','2026-10-22 17:44:41'),(286,'audit:edit','web','2026-10-22 17:44:41','2026-10-22 17:44:41'),(287,'audit:delete','web','2026-10-22 17:44:41','2026-10-22 17:44:41'),(288,'dashboard:view','api','2026-10-23 07:09:25','2026-10-23 07:09:25'),(289,'asset:view','api','2026-10-23 07:09:25','2026-10-23 07:09:25'),(290,'audit:view','api','2026-10-23 07:09:25','2026-10-23 07:09:25'),(291,'mobile.dashboard:view','web','2026-10-23 10:28:22','2026-10-23 10:28:22'),(292,'mobile.dashboard:view','api','2026-10-23 10:28:22','2026-10-23 10:28:22'),(293,'mobile.asset:view','web','2026-10-23 10:28:22','2026-10-23 10:28:22'),(294,'mobile.asset:view','api','2026-10-23 10:28:22','2026-10-23 10:28:22'),(295,'mobile.audit:view','web','2026-10-23 10:28:22','2026-10-23 10:28:22'),(296,'mobile.audit:view','api','2026-10-23 10:28:22','2026-10-23 10:28:22'),(297,'mobile.support-ticket:view','web','2026-10-23 10:28:22','2026-10-23 10:28:22'),(298,'mobile.support-ticket:view','api','2026-10-23 10:28:22','2026-10-23 10:28:22'),(299,'mobile.support-ticket:create','web','2026-10-23 10:28:22','2026-10-23 10:28:22'),(300,'mobile.support-ticket:create','api','2026-10-23 10:28:22','2026-10-23 10:28:22');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'mobile-app','d927d3513a0ac3ae6768483462b9b9b60cb7a40d896743c8573b72fb36ab728d','[\"*\"]',NULL,NULL,'2026-10-13 09:01:22','2026-10-13 09:01:22'),(2,'App\\Models\\User',1,'mobile-app','ec18b61250a76bc470f4ca04bc07b7b367e4af33c78632e338191062da61a519','[\"*\"]','2026-10-13 09:01:32',NULL,'2026-10-13 09:01:31','2026-10-13 09:01:32'),(3,'App\\Models\\User',1,'mobile-app','83ef3073f6cfe1da53ad321b56e3fd3f85e013f3351e67f54318a2f6c9c2e61f','[\"*\"]','2026-10-13 09:01:41',NULL,'2026-10-13 09:01:41','2026-10-13 09:01:41'),(4,'App\\Models\\User',1,'mobile-app','c1c2840b43c21978ded6e69aed1e48fab1fc16f663bfd5ed52de4bf3ea6fd9d8','[\"*\"]','2026-10-13 09:01:50',NULL,'2026-10-13 09:01:50','2026-10-13 09:01:50'),(5,'App\\Models\\User',1,'mobile-app','abad25642b40f0607243f6be00469f9f582c2f00a3cefc12de990066a071950d','[\"*\"]','2026-10-13 09:02:00',NULL,'2026-10-13 09:02:00','2026-10-13 09:02:00'),(6,'App\\Models\\User',1,'mobile-app','b020f37736adcf9c2170f768d19b2bded728d8fcccccdf2a194fa7066dad6d32','[\"*\"]','2026-10-13 09:43:48',NULL,'2026-10-13 09:39:43','2026-10-13 09:43:48'),(7,'App\\Models\\User',1,'mobile-app','35ad45d3232c8539d2d9dcfa85cb27999cbe4b7c600cc0f41c2ac3b1d05a1540','[\"*\"]','2026-10-13 10:07:04',NULL,'2026-10-13 10:02:49','2026-10-13 10:07:04'),(8,'App\\Models\\User',1,'mobile-app','7708e6049b90dcb131844adffc311ee958ec0f126727a8a134af4b6c625878cb','[\"*\"]','2026-10-13 10:23:50',NULL,'2026-10-13 10:13:22','2026-10-13 10:23:50'),(9,'App\\Models\\User',1,'mobile-app','30c26755388df8165f82a14f76375babc883e50bf1a09f50c0d71d812cfaab3c','[\"*\"]','2026-10-13 10:18:55',NULL,'2026-10-13 10:17:31','2026-10-13 10:18:55'),(10,'App\\Models\\User',1,'mobile-app','5ba7a021267a61b26f7ef6a6ff289bbcc5ef04c0057160654748d83e17ec565d','[\"*\"]',NULL,NULL,'2026-10-13 10:22:19','2026-10-13 10:22:19'),(11,'App\\Models\\User',1,'mobile-app','963d1f1847432355c35266e9f53e966cb01b126fae483fc1e881fb636a50228d','[\"*\"]','2026-10-13 10:22:49',NULL,'2026-10-13 10:22:49','2026-10-13 10:22:49'),(12,'App\\Models\\User',1,'mobile-app','b0a0c3f1e7330465d261e600fb378d115d4aeb7bda942287fd617a5e5abc244e','[\"*\"]','2026-10-13 10:38:09',NULL,'2026-10-13 10:38:07','2026-10-13 10:38:09'),(13,'App\\Models\\User',1,'mobile-app','54169b32762522c33846ea59660b9fabe6e99f8038ac4df4cbfa8d2394de0cf4','[\"*\"]','2026-10-13 10:39:31',NULL,'2026-10-13 10:39:29','2026-10-13 10:39:31'),(14,'App\\Models\\User',1,'mobile-app','e6b2abb370510f849115e3db5fba250754857724636769f41fada4aae716cd6e','[\"*\"]','2026-10-13 10:42:34',NULL,'2026-10-13 10:42:33','2026-10-13 10:42:34'),(15,'App\\Models\\User',1,'mobile-app','eca8f0d541bc019c31b56841d19676fdad6613ea6938acac8c0ebda1fd5c02c4','[\"*\"]','2026-10-13 10:50:35',NULL,'2026-10-13 10:50:34','2026-10-13 10:50:35'),(16,'App\\Models\\User',1,'mobile-app','333dc897af9aa7f8d9752755fddaa169fa164da9ad8daf5c0cf283568e828b16','[\"*\"]',NULL,NULL,'2026-10-13 11:13:40','2026-10-13 11:13:40'),(17,'App\\Models\\User',1,'api-token','ccaa125aa8a6d2a06fa5235cf73c9a3c24a80cc78d4adbbe57e587bc588a14ce','[\"*\"]','2026-10-14 05:20:23',NULL,'2026-10-14 05:16:21','2026-10-14 05:20:23'),(18,'App\\Models\\User',1,'api-token','16e88b1bd577c2b077446b08078ddc80832aa49c4635160c96ae0a82925c6ce6','[\"*\"]','2026-10-14 05:39:28',NULL,'2026-10-14 05:28:21','2026-10-14 05:39:28'),(19,'App\\Models\\User',2,'api-token','83186376f6e570fe872f0128c6d59cd8f4a95fdb6ab75974d8728c6197501e1e','[\"*\"]',NULL,NULL,'2026-10-14 06:23:44','2026-10-14 06:23:44'),(20,'App\\Models\\User',1,'api-token','dcf7cb94afa686e965b78edbc4ab3277ec708ea4c9b68b81ec3bd7e623f0c67b','[\"*\"]','2026-10-14 07:33:36',NULL,'2026-10-14 07:33:35','2026-10-14 07:33:36'),(21,'App\\Models\\User',1,'mobile-app','03addb2848a1220549004a1f5b33ce0eb8d5fd138fd5461688aeb86fbab7ddc5','[\"*\"]',NULL,NULL,'2026-10-16 07:17:08','2026-10-16 07:17:08'),(22,'App\\Models\\User',1,'mobile-app','1a53af0f153757214cad19d4b161ade64cb5206966d19b50050f7c3e31d589de','[\"*\"]','2026-10-18 17:58:13',NULL,'2026-10-16 07:22:13','2026-10-18 17:58:13'),(23,'App\\Models\\User',1,'test','f77e55483d6e4174ba317a033123ff05a3d23d5e484335868ddccffc8ecb6cd2','[\"*\"]','2026-10-16 09:29:37',NULL,'2026-10-16 09:29:37','2026-10-16 09:29:37'),(24,'App\\Models\\User',1,'test','de8f45f376737974ebf55a6fcac34527b01c426136d53ddcad1f6d885dd2f915','[\"*\"]','2026-10-16 09:30:08',NULL,'2026-10-16 09:30:08','2026-10-16 09:30:08'),(25,'App\\Models\\User',1,'test_audit','7fcfe4a5d400dd6c1bf8dba35ad533852efbd08ad22cb21e5249c8aa5615d5ab','[\"*\"]','2026-10-16 10:05:52',NULL,'2026-10-16 10:05:50','2026-10-16 10:05:52'),(26,'App\\Models\\User',1,'test_audit','cb35dc3a81618beafc41dbbe2b4d1f9e306295293beedf7be636fe6a6a7f6bb7','[\"*\"]','2026-10-16 10:06:49',NULL,'2026-10-16 10:06:48','2026-10-16 10:06:49'),(27,'App\\Models\\User',1,'test_audit','ee5c63d297d15bf0bf9f120fbf183303685e410eeb541a50c7301b455f2e0de7','[\"*\"]','2026-10-16 10:07:15',NULL,'2026-10-16 10:07:15','2026-10-16 10:07:15'),(28,'App\\Models\\User',1,'test','809d1a660b497fe88803d147d5af73476472281fdaedbcf491ccf09392951612','[\"*\"]','2026-10-17 06:19:49',NULL,'2026-10-17 06:19:48','2026-10-17 06:19:49'),(29,'App\\Models\\User',1,'test','d9004d5482976efc79f3ccd0c4760e123a0e3ac672f9571e64434c46251c675f','[\"*\"]','2026-10-17 06:20:36',NULL,'2026-10-17 06:20:35','2026-10-17 06:20:36'),(30,'App\\Models\\User',1,'mobile-app','55b94619d75bd686999fb23a45835b97fea3180303ecfc93cbfb7d8874f696e7','[\"*\"]','2026-10-18 23:47:33',NULL,'2026-10-18 23:46:24','2026-10-18 23:47:33'),(31,'App\\Models\\User',1,'mobile-app','ed72231387db19b37ca7bb8651a45d1c69c0143b457032e9f22ad3bec66b6416','[\"*\"]','2026-10-19 01:18:54',NULL,'2026-10-18 23:59:04','2026-10-19 01:18:54'),(32,'App\\Models\\User',1,'mobile-app','78d03220b94c18eb869a0c5a85ec85c7675f50d3e697ceed2272619c56254090','[\"*\"]','2026-10-19 01:26:43',NULL,'2026-10-19 01:23:51','2026-10-19 01:26:43'),(33,'App\\Models\\User',1,'mobile-app','7df55bc15d18a037044e20bbfd2352cb1ea61a5df14d74339b14c4af0701655c','[\"*\"]','2026-10-19 09:40:51',NULL,'2026-10-19 01:29:18','2026-10-19 09:40:51'),(34,'App\\Models\\User',1,'mobile-app','85b8aaa38ae2beb73af5a72789f3e4473ab354eab312ba018be1100058b766fd','[\"*\"]','2026-10-21 09:37:22',NULL,'2026-10-19 01:30:18','2026-10-21 09:37:22'),(35,'App\\Models\\User',1,'mobile-app','0edeedf25e0bf0b142360c684d524e450a2963201fb3f9b0f863f37fd89a1f58','[\"*\"]','2026-10-19 13:39:25',NULL,'2026-10-19 09:41:49','2026-10-19 13:39:25'),(36,'App\\Models\\User',1,'mobile-app','9b5a0b37d1b5e1b17517e9b3b6f3981515f4903d10ee1623c7e3a1d09666ac7c','[\"*\"]','2026-10-19 16:32:38',NULL,'2026-10-19 14:20:30','2026-10-19 16:32:38'),(37,'App\\Models\\User',1,'mobile-app','1b367279a105d8017680a8d3870c21e72b3d08b3b919955d8e1acbe676bb76e0','[\"*\"]','2026-10-22 16:53:16',NULL,'2026-10-19 15:34:36','2026-10-22 16:53:16'),(38,'App\\Models\\User',18,'auth_token','3908cae6bff2fe44896b777ca54be20ec44ef49a3af3a6744f7b21387cdac11c','[\"*\"]',NULL,NULL,'2026-10-19 16:42:09','2026-10-19 16:42:09'),(39,'App\\Models\\User',18,'mobile-app','82f4967ab3b2e60882fa050e31e7c9b0201ee34f2172713ff741da5186f20492','[\"*\"]',NULL,NULL,'2026-10-19 16:44:56','2026-10-19 16:44:56'),(40,'App\\Models\\User',19,'auth_token','777bf4705c2111bb29eaf5733d9f55f51f5a31b63c8b42e3b693e70513f7c3d0','[\"*\"]','2026-10-19 17:23:41',NULL,'2026-10-19 17:22:49','2026-10-19 17:23:41'),(41,'App\\Models\\User',1,'mobile-app','148273ef3a4a4c890f31e2d663364bab4ce1a7b54a09643384e98bc85d31e19b','[\"*\"]','2026-10-22 12:17:33',NULL,'2026-10-19 17:23:56','2026-10-22 12:17:33'),(42,'App\\Models\\User',1,'mobile-app','126e1d51b55353936a9f3a42467982726ea67bab681bc49035e62027006fea55','[\"*\"]','2026-10-21 15:47:36',NULL,'2026-10-21 09:45:51','2026-10-21 15:47:36'),(43,'App\\Models\\User',1,'mobile-app','8729dcf770a41cbb1361999fa16601c3a1654640a8786d046d74fe5e0f625035','[\"*\"]','2026-10-22 12:11:50',NULL,'2026-10-22 12:11:50','2026-10-22 12:11:50'),(44,'App\\Models\\User',1,'mobile-app','61a2089484f7f3aa22cc88f6a15174759848959dc8edf54d69b70bfa91352bfb','[\"*\"]','2026-10-22 12:17:04',NULL,'2026-10-22 12:17:04','2026-10-22 12:17:04'),(45,'App\\Models\\User',1,'mobile-app','c7178738b2b46ccd7ccb631f10dc74c24b60dd3e7b7424c285c6f621bf29e037','[\"*\"]','2026-10-22 12:17:20',NULL,'2026-10-22 12:17:20','2026-10-22 12:17:20'),(46,'App\\Models\\User',1,'mobile-app','8d8dbf6635bdd04f422f384db9ddfcd260ff5ea086723cf7f9ec9bfae64c49c6','[\"*\"]','2026-10-22 12:17:30',NULL,'2026-10-22 12:17:30','2026-10-22 12:17:30'),(47,'App\\Models\\User',1,'mobile-app','1cc4997afd8a06c878218e5c26cb08bb2083b3d5b7321a786260ce6c88790139','[\"*\"]',NULL,NULL,'2026-10-22 12:17:38','2026-10-22 12:17:38'),(48,'App\\Models\\User',1,'mobile-app','4d9230446cc2e3b4398d9e3f995ede2502f673a8677b7a5000f57635c469cc30','[\"*\"]','2026-10-22 12:20:44',NULL,'2026-10-22 12:20:44','2026-10-22 12:20:44'),(49,'App\\Models\\User',20,'mobile-app','ad8dee148f22377a1059b6ae8ed38961c110fa2548c11fe3e767a86d2ffa9335','[\"*\"]','2026-10-22 13:22:16',NULL,'2026-10-22 12:24:36','2026-10-22 13:22:16'),(50,'App\\Models\\User',1,'mobile-app','cfc3897fefdb42d0425d7f0adb763dd83089a435984516add6787da25f59f21e','[\"*\"]','2026-10-22 13:31:04',NULL,'2026-10-22 13:22:00','2026-10-22 13:31:04'),(51,'App\\Models\\User',20,'mobile-app','64101837bf8445bd062023283771f6c7c37c8207a9643ba22c834ea2ca21c0ae','[\"*\"]','2026-10-22 13:23:47',NULL,'2026-10-22 13:22:50','2026-10-22 13:23:47'),(52,'App\\Models\\User',1,'mobile-app','0675504f73238eea58bd8959887d2a0f45fbf3d3136272d4cf2fd7d39812d671','[\"*\"]','2026-10-22 14:51:38',NULL,'2026-10-22 13:24:19','2026-10-22 14:51:38'),(53,'App\\Models\\User',20,'mobile-app','2e362f1cd698159bf3e50f71f30280b7898039d5e31220f1a7557a7a24227ca0','[\"*\"]','2026-10-22 13:32:47',NULL,'2026-10-22 13:32:35','2026-10-22 13:32:47'),(54,'App\\Models\\User',20,'mobile-app','e3681b556ec48cc9c4c280f20c939476d04e69c4a0fc6d4fc3595978c71d65ba','[\"*\"]','2026-10-22 15:11:34',NULL,'2026-10-22 14:52:09','2026-10-22 15:11:34'),(55,'App\\Models\\User',20,'mobile-app','0e912ea6751b93a22a5173aa456f473d86fe856ea1fed0765c9a9da6bbdaac80','[\"*\"]','2026-10-22 15:25:33',NULL,'2026-10-22 15:25:19','2026-10-22 15:25:33'),(56,'App\\Models\\User',20,'mobile-app','5270bc0a19a30ff233554951c11d07b694e94823d5589c2be0972ab99db90bd7','[\"*\"]','2026-10-22 15:32:40',NULL,'2026-10-22 15:31:51','2026-10-22 15:32:40'),(57,'App\\Models\\User',20,'mobile-app','8b12e97202a7fb77685662f00b7a16c49891e780696c2cae3f5fcb5bfe4d1ee6','[\"*\"]','2026-10-22 15:37:33',NULL,'2026-10-22 15:33:29','2026-10-22 15:37:33'),(58,'App\\Models\\User',21,'mobile-app','d7a100ecc31b1160d6197a414383412023d7b9cd2b856be98b9ad89e391f7e2e','[\"*\"]',NULL,NULL,'2026-10-22 15:52:18','2026-10-22 15:52:18'),(59,'App\\Models\\User',21,'mobile-app','b6cf3bc4821f4bfb2b385d7163f32a21cbe5bb2458652f9d7e46002a95e6813f','[\"*\"]','2026-10-22 16:09:39',NULL,'2026-10-22 15:55:03','2026-10-22 16:09:39'),(60,'App\\Models\\User',1,'mobile-app','b80ec7a3d45c0be9f2d89523bad2dc4c43d5fe914b0af601ee632d3516ea90c1','[\"*\"]','2026-10-22 16:32:10',NULL,'2026-10-22 16:10:08','2026-10-22 16:32:10'),(61,'App\\Models\\User',21,'mobile-app','203c7813e15c42ed4411ba6333316b38a1e58d442e7e3df28f0ae8a3a3ee1e4d','[\"*\"]','2026-10-22 16:44:07',NULL,'2026-10-22 16:32:42','2026-10-22 16:44:07'),(62,'App\\Models\\User',21,'mobile-app','b15543e7a986c3fff99623dba93a3a3f745c875488b174e37c1c72e7e7ee425d','[\"*\"]','2026-10-22 16:56:31',NULL,'2026-10-22 16:47:37','2026-10-22 16:56:31'),(63,'App\\Models\\User',21,'mobile-app','4b54770d80b8128e36f9eacd3f27d4800531e2bc592ba684686b456f74d4f0b4','[\"*\"]','2026-10-22 17:03:55',NULL,'2026-10-22 16:55:29','2026-10-22 17:03:55'),(64,'App\\Models\\User',1,'mobile-app','eaa46462ecfafb8b47e1dc9805968aea2a43963ccea543c0a5c64cca6ff86edd','[\"*\"]','2026-10-22 17:16:18',NULL,'2026-10-22 17:04:21','2026-10-22 17:16:18'),(65,'App\\Models\\User',21,'mobile-app','7ba4a7551c8c085890bab0afa54f779ab2862e8cf500e4c9fa9855652377277d','[\"*\"]','2026-10-22 23:12:52',NULL,'2026-10-22 17:16:43','2026-10-22 23:12:52'),(66,'App\\Models\\User',21,'mobile-app','1c14e9069298f53e3bce6ec7191a2b2af42bec2d87f530512931c16ad451a4f4','[\"*\"]','2026-10-23 06:51:29',NULL,'2026-10-22 23:14:58','2026-10-23 06:51:29'),(67,'App\\Models\\User',21,'mobile-app','4992f61be7531a8b873abaa42cac8557385e2b3edd2b997593961698952dd92a','[\"*\"]','2026-10-23 16:48:00',NULL,'2026-10-23 07:23:07','2026-10-23 16:48:00'),(68,'App\\Models\\User',21,'mobile-app','d82c0df67ace85ba747da8d1067cde82be3b723cde4d29301019d42747f4c02a','[\"*\"]','2026-10-27 10:37:34',NULL,'2026-10-23 17:21:50','2026-10-27 10:37:34'),(69,'App\\Models\\User',1,'mobile-app','570307f93d3e0b40ba5032f1292ab1bbd24d184c8a18ba4ba4776c7a5e25514d','[\"*\"]','2026-10-27 11:16:32',NULL,'2026-10-27 10:37:40','2026-10-27 11:16:32'),(70,'App\\Models\\User',1,'mobile-app','73ce75ac0978c659404bd6aabd6c83492b11672db7cac424b8fe78d411607d65','[\"*\"]','2026-10-27 10:42:52',NULL,'2026-10-27 10:42:19','2026-10-27 10:42:52'),(71,'App\\Models\\User',21,'mobile-app','3d37eaf588a18c6fe7afcceab14c63aa0314c86f0f0689cb7c70af46f552ed14','[\"*\"]','2026-10-27 11:27:25',NULL,'2026-10-27 11:25:00','2026-10-27 11:27:25');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_approval_steps`
--

DROP TABLE IF EXISTS `po_approval_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_approval_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_id` bigint unsigned NOT NULL,
  `template_step_id` bigint unsigned DEFAULT NULL,
  `sequence` int unsigned NOT NULL,
  `assignee_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `approver_user_id` bigint unsigned DEFAULT NULL,
  `assignee_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `acted_by` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `acted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_approval_steps_seq_unique` (`po_id`,`sequence`),
  KEY `po_approval_steps_template_step_id_foreign` (`template_step_id`),
  KEY `po_approval_steps_approver_user_id_foreign` (`approver_user_id`),
  KEY `po_approval_steps_acted_by_foreign` (`acted_by`),
  CONSTRAINT `po_approval_steps_acted_by_foreign` FOREIGN KEY (`acted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `po_approval_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `po_approval_steps_po_id_foreign` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `po_approval_steps_template_step_id_foreign` FOREIGN KEY (`template_step_id`) REFERENCES `approval_template_steps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_approval_steps`
--

LOCK TABLES `po_approval_steps` WRITE;
/*!40000 ALTER TABLE `po_approval_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `po_approval_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_items`
--

DROP TABLE IF EXISTS `po_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_id` bigint unsigned NOT NULL,
  `indent_item_id` bigint unsigned DEFAULT NULL,
  `quotation_item_id` bigint unsigned DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` int NOT NULL,
  `has_free_qty` tinyint(1) NOT NULL DEFAULT '0',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nos',
  `price` decimal(12,2) NOT NULL,
  `mrp` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gst_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `gst_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cgst_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `sgst_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `igst_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rate_after_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `taxable_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `base_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qoh` decimal(15,2) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `free_qty` decimal(15,2) NOT NULL DEFAULT '0.00',
  `marketer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `epr` decimal(15,2) DEFAULT NULL,
  `uepr` decimal(15,2) DEFAULT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_category_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_sub_category_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_category_id` bigint unsigned DEFAULT NULL,
  `item_sub_category_id` bigint unsigned DEFAULT NULL,
  `conversion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_sub_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `po_items_po_id_foreign` (`po_id`),
  KEY `po_items_indent_item_id_foreign` (`indent_item_id`),
  KEY `po_items_quotation_item_id_foreign` (`quotation_item_id`),
  KEY `po_items_item_category_id_foreign` (`item_category_id`),
  KEY `po_items_item_sub_category_id_foreign` (`item_sub_category_id`),
  CONSTRAINT `po_items_indent_item_id_foreign` FOREIGN KEY (`indent_item_id`) REFERENCES `indent_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `po_items_item_category_id_foreign` FOREIGN KEY (`item_category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `po_items_item_sub_category_id_foreign` FOREIGN KEY (`item_sub_category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `po_items_po_id_foreign` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `po_items_quotation_item_id_foreign` FOREIGN KEY (`quotation_item_id`) REFERENCES `vendor_quotation_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_items`
--

LOCK TABLES `po_items` WRITE;
/*!40000 ALTER TABLE `po_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `po_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procurement_activity_logs`
--

DROP TABLE IF EXISTS `procurement_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procurement_activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_activity_subject_idx` (`subject_type`,`subject_id`),
  KEY `procurement_activity_logs_action_created_at_index` (`action`,`created_at`),
  KEY `procurement_activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `procurement_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `procurement_activity_logs_chk_1` CHECK (json_valid(`properties`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procurement_activity_logs`
--

LOCK TABLES `procurement_activity_logs` WRITE;
/*!40000 ALTER TABLE `procurement_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `procurement_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_category_name_unique` (`category_name`),
  UNIQUE KEY `product_categories_category_code_unique` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sub_categories`
--

DROP TABLE IF EXISTS `product_sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_sub_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_category_id` bigint unsigned NOT NULL,
  `sub_category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `psc_category_subname_unique` (`product_category_id`,`sub_category_name`),
  CONSTRAINT `product_sub_categories_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sub_categories`
--

LOCK TABLES `product_sub_categories` WRITE;
/*!40000 ALTER TABLE `product_sub_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_sub_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_payments`
--

DROP TABLE IF EXISTS `purchase_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_date` date NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_payments_created_by_foreign` (`created_by`),
  KEY `purchase_order_payments_purchase_order_id_payment_date_index` (`purchase_order_id`,`payment_date`),
  CONSTRAINT `purchase_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_payments_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_payments`
--

LOCK TABLES `purchase_order_payments` WRITE;
/*!40000 ALTER TABLE `purchase_order_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quotation_id` bigint unsigned DEFAULT NULL,
  `pr_id` bigint unsigned DEFAULT NULL,
  `indent_id` bigint unsigned DEFAULT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `purchase_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `grn_store` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_terms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_advance_payment_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_quantity` int NOT NULL DEFAULT '0',
  `sub_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `taxable_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_gst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_cgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_sgst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_igst` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `created_by` bigint unsigned DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `current_sequence` int unsigned DEFAULT NULL,
  `assigned_approver_id` bigint unsigned DEFAULT NULL,
  `approval_template_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `grn_store_id` bigint unsigned DEFAULT NULL,
  `po_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po_source` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `store_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_po_no_unique` (`po_no`),
  UNIQUE KEY `purchase_orders_quotation_id_unique` (`quotation_id`),
  KEY `purchase_orders_vendor_id_foreign` (`vendor_id`),
  KEY `purchase_orders_pr_id_foreign` (`pr_id`),
  KEY `purchase_orders_created_by_foreign` (`created_by`),
  KEY `purchase_orders_grn_store_id_foreign` (`grn_store_id`),
  KEY `purchase_orders_indent_id_foreign` (`indent_id`),
  KEY `purchase_orders_department_id_foreign` (`department_id`),
  KEY `purchase_orders_store_id_foreign` (`store_id`),
  KEY `purchase_orders_assigned_approver_id_foreign` (`assigned_approver_id`),
  KEY `purchase_orders_approval_template_id_foreign` (`approval_template_id`),
  CONSTRAINT `purchase_orders_approval_template_id_foreign` FOREIGN KEY (`approval_template_id`) REFERENCES `approval_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_assigned_approver_id_foreign` FOREIGN KEY (`assigned_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_grn_store_id_foreign` FOREIGN KEY (`grn_store_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_pr_id_foreign` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requisitions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `vendor_quotations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requisitions`
--

DROP TABLE IF EXISTS `purchase_requisitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_requisitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pr_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indent_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `required_date` date DEFAULT NULL,
  `purchase_officer_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_from_approval_at` timestamp NULL DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `closed_by` bigint unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `close_reason` text COLLATE utf8mb4_unicode_ci,
  `rejected_reason` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fan_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requisition_store_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_requisitions_pr_no_unique` (`pr_no`),
  UNIQUE KEY `purchase_requisitions_indent_id_unique` (`indent_id`),
  KEY `purchase_requisitions_vendor_id_foreign` (`vendor_id`),
  KEY `purchase_requisitions_purchase_officer_id_foreign` (`purchase_officer_id`),
  KEY `purchase_requisitions_requisition_store_id_foreign` (`requisition_store_id`),
  KEY `purchase_requisitions_created_by_foreign` (`created_by`),
  KEY `purchase_requisitions_closed_by_foreign` (`closed_by`),
  CONSTRAINT `purchase_requisitions_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requisitions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requisitions_indent_id_foreign` FOREIGN KEY (`indent_id`) REFERENCES `indents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_requisitions_purchase_officer_id_foreign` FOREIGN KEY (`purchase_officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requisitions_requisition_store_id_foreign` FOREIGN KEY (`requisition_store_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requisitions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requisitions`
--

LOCK TABLES `purchase_requisitions` WRITE;
/*!40000 ALTER TABLE `purchase_requisitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_requisitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quality_inspections`
--

DROP TABLE IF EXISTS `quality_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quality_inspections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` bigint unsigned NOT NULL,
  `grn_item_id` bigint unsigned NOT NULL,
  `inspected_by` bigint unsigned DEFAULT NULL,
  `inspected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inspection_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quality_inspections_grn_item_id_foreign` (`grn_item_id`),
  KEY `quality_inspections_inspected_by_foreign` (`inspected_by`),
  KEY `quality_inspections_grn_id_grn_item_id_index` (`grn_id`,`grn_item_id`),
  KEY `quality_inspections_inspection_status_index` (`inspection_status`),
  CONSTRAINT `quality_inspections_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `grns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quality_inspections_grn_item_id_foreign` FOREIGN KEY (`grn_item_id`) REFERENCES `grn_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quality_inspections_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quality_inspections`
--

LOCK TABLES `quality_inspections` WRITE;
/*!40000 ALTER TABLE `quality_inspections` DISABLE KEYS */;
/*!40000 ALTER TABLE `quality_inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotation_negotiation_histories`
--

DROP TABLE IF EXISTS `quotation_negotiation_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotation_negotiation_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_quotation_id` bigint unsigned NOT NULL,
  `round` int unsigned NOT NULL,
  `old_price` decimal(15,2) NOT NULL,
  `new_price` decimal(15,2) NOT NULL,
  `buyer_remarks` text COLLATE utf8mb4_unicode_ci,
  `vendor_response` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `negotiated_by` bigint unsigned DEFAULT NULL,
  `negotiated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_negotiation_histories_negotiated_by_foreign` (`negotiated_by`),
  KEY `quotation_negotiation_histories_vendor_quotation_id_round_index` (`vendor_quotation_id`,`round`),
  CONSTRAINT `quotation_negotiation_histories_negotiated_by_foreign` FOREIGN KEY (`negotiated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotation_negotiation_histories_vendor_quotation_id_foreign` FOREIGN KEY (`vendor_quotation_id`) REFERENCES `vendor_quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotation_negotiation_histories`
--

LOCK TABLES `quotation_negotiation_histories` WRITE;
/*!40000 ALTER TABLE `quotation_negotiation_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotation_negotiation_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_task_branch_progress`
--

DROP TABLE IF EXISTS `recurring_task_branch_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_task_branch_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recurring_task_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `latest_status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latest_completed_at` timestamp NULL DEFAULT NULL,
  `collaborator_count` int unsigned NOT NULL DEFAULT '0',
  `current_occurrence_date` date DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rt_branch_progress_task_branch_unique` (`recurring_task_id`,`branch_id`),
  KEY `rt_branch_progress_task_branch_idx` (`recurring_task_id`,`branch_id`),
  KEY `rt_branch_progress_task_date_idx` (`recurring_task_id`,`current_occurrence_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_task_branch_progress`
--

LOCK TABLES `recurring_task_branch_progress` WRITE;
/*!40000 ALTER TABLE `recurring_task_branch_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_task_branch_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_task_occurrence_summary`
--

DROP TABLE IF EXISTS `recurring_task_occurrence_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_task_occurrence_summary` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recurring_task_id` bigint unsigned NOT NULL,
  `occurrence_date` date NOT NULL,
  `total_branch_count` int unsigned NOT NULL DEFAULT '0',
  `completed_branch_count` int unsigned NOT NULL DEFAULT '0',
  `pending_branch_count` int unsigned NOT NULL DEFAULT '0',
  `overdue_branch_count` int unsigned NOT NULL DEFAULT '0',
  `status_snapshot` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rt_occurrence_summary_task_date_unique` (`recurring_task_id`,`occurrence_date`),
  KEY `rt_occurrence_summary_task_date_idx` (`recurring_task_id`,`occurrence_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_task_occurrence_summary`
--

LOCK TABLES `recurring_task_occurrence_summary` WRITE;
/*!40000 ALTER TABLE `recurring_task_occurrence_summary` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_task_occurrence_summary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_task_tickets`
--

DROP TABLE IF EXISTS `recurring_task_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_task_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recurring_task_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `occurrence_date` date NOT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `attachment_paths` text COLLATE utf8mb4_unicode_ci,
  `completed_branch_ids` text COLLATE utf8mb4_unicode_ci,
  `branch_completions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `start_notification_sent_at` timestamp NULL DEFAULT NULL,
  `end_notification_sent_at` timestamp NULL DEFAULT NULL,
  `fallback_notification_sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rt_tickets_task_occurrence_unique` (`recurring_task_id`,`occurrence_date`),
  KEY `rt_tickets_task_date_branch_idx` (`recurring_task_id`,`occurrence_date`,`branch_id`),
  KEY `rt_tickets_task_id_idx` (`recurring_task_id`),
  KEY `rtt_occurrence_date_idx` (`occurrence_date`),
  CONSTRAINT `recurring_task_tickets_recurring_task_id_foreign` FOREIGN KEY (`recurring_task_id`) REFERENCES `recurring_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_task_tickets`
--

LOCK TABLES `recurring_task_tickets` WRITE;
/*!40000 ALTER TABLE `recurring_task_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_task_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_tasks`
--

DROP TABLE IF EXISTS `recurring_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint unsigned DEFAULT NULL,
  `task_no` int unsigned NOT NULL,
  `task_category_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `assignment_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `assigned_to` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `task_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `ticket_status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `frequency` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurring_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weekdays` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `months` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `schedule_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_lifetime` tinyint(1) NOT NULL DEFAULT '0',
  `last_generated_date` date DEFAULT NULL,
  `attachment_path` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `employee_completion_comment` text COLLATE utf8mb4_unicode_ci,
  `employee_completion_image_path` longtext COLLATE utf8mb4_unicode_ci,
  `ticket_completed_at` timestamp NULL DEFAULT NULL,
  `completed_count` int NOT NULL DEFAULT '0',
  `expected_count_until_now` int NOT NULL DEFAULT '0',
  `active_occurrence_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recurring_tasks_task_no_unique` (`task_no`),
  KEY `recurring_tasks_task_category_id_foreign` (`task_category_id`),
  KEY `recurring_tasks_department_id_foreign` (`department_id`),
  KEY `recurring_tasks_branch_id_foreign` (`branch_id`),
  KEY `recurring_tasks_assigned_to_foreign` (`assigned_to`),
  KEY `recurring_tasks_created_by_foreign` (`created_by`),
  CONSTRAINT `recurring_tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recurring_tasks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recurring_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recurring_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recurring_tasks_task_category_id_foreign` FOREIGN KEY (`task_category_id`) REFERENCES `task_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recurring_tasks_chk_1` CHECK (json_valid(`weekdays`)),
  CONSTRAINT `recurring_tasks_chk_2` CHECK (json_valid(`months`)),
  CONSTRAINT `recurring_tasks_chk_3` CHECK (json_valid(`schedule_config`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_tasks`
--

LOCK TABLES `recurring_tasks` WRITE;
/*!40000 ALTER TABLE `recurring_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rfq_attachments`
--

DROP TABLE IF EXISTS `rfq_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rfq_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rfq_id` bigint unsigned NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rfq_attachments_rfq_id_foreign` (`rfq_id`),
  KEY `rfq_attachments_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `rfq_attachments_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rfq_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rfq_attachments`
--

LOCK TABLES `rfq_attachments` WRITE;
/*!40000 ALTER TABLE `rfq_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `rfq_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rfq_vendors`
--

DROP TABLE IF EXISTS `rfq_vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rfq_vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rfq_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Invited',
  `invited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfq_vendors_rfq_vendor_unique` (`rfq_id`,`vendor_id`),
  KEY `rfq_vendors_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `rfq_vendors_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rfq_vendors_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rfq_vendors`
--

LOCK TABLES `rfq_vendors` WRITE;
/*!40000 ALTER TABLE `rfq_vendors` DISABLE KEYS */;
/*!40000 ALTER TABLE `rfq_vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rfqs`
--

DROP TABLE IF EXISTS `rfqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rfqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rfq_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rfq_date` date DEFAULT NULL,
  `pr_id` bigint unsigned NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `notes_to_vendor` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `closes_at` timestamp NULL DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `delivery_store` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_terms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_terms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warranty_required` tinyint(1) NOT NULL DEFAULT '0',
  `amc_required` tinyint(1) NOT NULL DEFAULT '0',
  `tax_included` tinyint(1) NOT NULL DEFAULT '0',
  `freight_included` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfqs_rfq_no_unique` (`rfq_no`),
  KEY `rfqs_pr_id_foreign` (`pr_id`),
  KEY `rfqs_created_by_foreign` (`created_by`),
  CONSTRAINT `rfqs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rfqs_pr_id_foreign` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rfqs`
--

LOCK TABLES `rfqs` WRITE;
/*!40000 ALTER TABLE `rfqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `rfqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1),(145,1),(146,1),(147,1),(148,1),(149,1),(150,1),(151,1),(152,1),(153,1),(154,1),(155,1),(156,1),(157,1),(158,1),(159,1),(160,1),(161,1),(162,1),(163,1),(164,1),(165,1),(166,1),(167,1),(168,1),(169,1),(170,1),(171,1),(172,1),(173,1),(174,1),(175,1),(176,1),(177,1),(178,1),(179,1),(180,1),(181,1),(182,1),(183,1),(184,1),(185,1),(186,1),(187,1),(188,1),(189,1),(190,1),(191,1),(192,1),(193,1),(194,1),(195,1),(196,1),(197,1),(198,1),(199,1),(200,1),(201,1),(202,1),(203,1),(204,1),(205,1),(206,1),(207,1),(208,1),(209,1),(210,1),(211,1),(212,1),(213,1),(214,1),(215,1),(216,1),(217,1),(218,1),(219,1),(220,1),(221,1),(222,1),(223,1),(224,1),(225,1),(226,1),(227,1),(228,1),(229,1),(230,1),(231,1),(232,1),(233,1),(234,1),(235,1),(236,1),(237,1),(238,1),(239,1),(240,1),(241,1),(242,1),(243,1),(244,1),(245,1),(246,1),(247,1),(248,1),(249,1),(250,1),(251,1),(252,1),(253,1),(254,1),(255,1),(256,1),(257,1),(258,1),(259,1),(260,1),(261,1),(262,1),(263,1),(264,1),(265,1),(266,1),(267,1),(276,1),(277,1),(278,1),(279,1),(280,1),(281,1),(282,1),(283,1),(284,1),(285,1),(286,1),(287,1),(291,1),(293,1),(295,1),(297,1),(299,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(22,2),(23,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,2),(41,2),(42,2),(43,2),(44,2),(45,2),(46,2),(47,2),(48,2),(49,2),(50,2),(51,2),(52,2),(53,2),(54,2),(55,2),(56,2),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(64,2),(65,2),(66,2),(67,2),(68,2),(69,2),(70,2),(71,2),(72,2),(73,2),(74,2),(75,2),(76,2),(77,2),(78,2),(79,2),(80,2),(81,2),(82,2),(83,2),(84,2),(85,2),(86,2),(87,2),(88,2),(89,2),(90,2),(91,2),(92,2),(93,2),(94,2),(95,2),(96,2),(97,2),(98,2),(99,2),(100,2),(101,2),(102,2),(103,2),(104,2),(105,2),(106,2),(107,2),(108,2),(109,2),(110,2),(111,2),(112,2),(113,2),(114,2),(115,2),(116,2),(117,2),(118,2),(119,2),(120,2),(121,2),(122,2),(123,2),(124,2),(125,2),(126,2),(127,2),(128,2),(129,2),(130,2),(131,2),(132,2),(133,2),(134,2),(135,2),(136,2),(137,2),(138,2),(139,2),(140,2),(141,2),(142,2),(143,2),(144,2),(145,2),(146,2),(147,2),(148,2),(149,2),(150,2),(151,2),(152,2),(153,2),(154,2),(155,2),(156,2),(157,2),(158,2),(159,2),(160,2),(161,2),(162,2),(163,2),(164,2),(165,2),(166,2),(167,2),(168,2),(169,2),(170,2),(171,2),(172,2),(173,2),(174,2),(175,2),(176,2),(177,2),(178,2),(179,2),(180,2),(181,2),(182,2),(183,2),(184,2),(185,2),(186,2),(187,2),(188,2),(189,2),(190,2),(191,2),(192,2),(193,2),(194,2),(195,2),(196,2),(197,2),(198,2),(199,2),(200,2),(201,2),(202,2),(203,2),(204,2),(205,2),(206,2),(207,2),(208,2),(209,2),(210,2),(211,2),(212,2),(213,2),(214,2),(215,2),(216,2),(217,2),(218,2),(219,2),(220,2),(221,2),(222,2),(223,2),(224,2),(225,2),(226,2),(227,2),(228,2),(229,2),(230,2),(231,2),(232,2),(233,2),(234,2),(235,2),(236,2),(237,2),(238,2),(239,2),(240,2),(241,2),(242,2),(243,2),(244,2),(245,2),(246,2),(247,2),(248,2),(249,2),(250,2),(251,2),(252,2),(253,2),(254,2),(255,2),(256,2),(257,2),(258,2),(259,2),(260,2),(261,2),(262,2),(263,2),(264,2),(265,2),(266,2),(267,2),(276,2),(277,2),(278,2),(279,2),(280,2),(281,2),(282,2),(283,2),(284,2),(285,2),(286,2),(287,2),(291,2),(293,2),(295,2),(297,2),(299,2),(1,3),(2,3),(3,3),(4,3),(5,3),(6,3),(12,3),(13,3),(14,3),(15,3),(20,3),(21,3),(22,3),(23,3),(24,3),(25,3),(26,3),(27,3),(28,3),(29,3),(30,3),(31,3),(32,3),(33,3),(34,3),(35,3),(36,3),(37,3),(38,3),(39,3),(40,3),(41,3),(42,3),(43,3),(44,3),(45,3),(46,3),(47,3),(48,3),(49,3),(50,3),(51,3),(52,3),(53,3),(54,3),(55,3),(56,3),(57,3),(58,3),(59,3),(60,3),(61,3),(62,3),(63,3),(64,3),(65,3),(66,3),(67,3),(68,3),(69,3),(70,3),(71,3),(72,3),(73,3),(74,3),(75,3),(76,3),(77,3),(78,3),(79,3),(80,3),(81,3),(82,3),(83,3),(84,3),(85,3),(86,3),(87,3),(88,3),(89,3),(90,3),(91,3),(92,3),(93,3),(94,3),(95,3),(96,3),(97,3),(98,3),(99,3),(100,3),(101,3),(102,3),(103,3),(104,3),(105,3),(106,3),(107,3),(108,3),(109,3),(110,3),(111,3),(112,3),(113,3),(114,3),(115,3),(116,3),(117,3),(118,3),(119,3),(124,3),(128,3),(129,3),(130,3),(131,3),(132,3),(133,3),(134,3),(135,3),(136,3),(137,3),(138,3),(139,3),(140,3),(141,3),(142,3),(143,3),(144,3),(145,3),(146,3),(147,3),(148,3),(149,3),(151,3),(152,3),(153,3),(154,3),(155,3),(156,3),(164,3),(167,3),(170,3),(172,3),(173,3),(177,3),(178,3),(179,3),(180,3),(181,3),(182,3),(183,3),(184,3),(185,3),(186,3),(187,3),(188,3),(189,3),(190,3),(191,3),(256,3),(257,3),(258,3),(259,3),(276,3),(277,3),(284,3),(291,3),(293,3),(295,3),(297,3),(299,3),(1,9),(2,9),(3,9),(4,9),(5,9),(6,9),(7,9),(8,9),(9,9),(10,9),(11,9),(12,9),(13,9),(14,9),(15,9),(16,9),(17,9),(18,9),(19,9),(20,9),(21,9),(22,9),(23,9),(24,9),(25,9),(26,9),(27,9),(28,9),(29,9),(30,9),(31,9),(32,9),(33,9),(34,9),(35,9),(36,9),(37,9),(38,9),(39,9),(40,9),(41,9),(42,9),(43,9),(44,9),(45,9),(46,9),(47,9),(48,9),(49,9),(50,9),(51,9),(52,9),(53,9),(54,9),(55,9),(56,9),(57,9),(58,9),(59,9),(60,9),(61,9),(62,9),(63,9),(64,9),(65,9),(66,9),(67,9),(68,9),(69,9),(70,9),(71,9),(72,9),(73,9),(74,9),(75,9),(76,9),(77,9),(78,9),(79,9),(80,9),(81,9),(82,9),(83,9),(84,9),(85,9),(86,9),(87,9),(88,9),(89,9),(90,9),(91,9),(92,9),(93,9),(94,9),(95,9),(96,9),(97,9),(98,9),(99,9),(100,9),(101,9),(102,9),(103,9),(104,9),(105,9),(106,9),(107,9),(108,9),(109,9),(110,9),(111,9),(112,9),(113,9),(114,9),(115,9),(116,9),(117,9),(118,9),(119,9),(120,9),(121,9),(122,9),(123,9),(124,9),(125,9),(126,9),(127,9),(128,9),(129,9),(130,9),(131,9),(132,9),(133,9),(134,9),(135,9),(136,9),(137,9),(138,9),(139,9),(140,9),(141,9),(142,9),(143,9),(144,9),(145,9),(146,9),(147,9),(148,9),(149,9),(150,9),(151,9),(152,9),(153,9),(154,9),(155,9),(156,9),(157,9),(158,9),(159,9),(160,9),(161,9),(162,9),(163,9),(164,9),(165,9),(166,9),(167,9),(168,9),(169,9),(170,9),(171,9),(172,9),(173,9),(174,9),(175,9),(176,9),(177,9),(178,9),(179,9),(180,9),(181,9),(182,9),(183,9),(184,9),(185,9),(186,9),(187,9),(188,9),(189,9),(190,9),(191,9),(192,9),(193,9),(194,9),(195,9),(196,9),(197,9),(198,9),(199,9),(200,9),(201,9),(202,9),(203,9),(204,9),(205,9),(206,9),(207,9),(208,9),(209,9),(210,9),(211,9),(212,9),(213,9),(214,9),(215,9),(216,9),(217,9),(218,9),(219,9),(220,9),(221,9),(222,9),(223,9),(224,9),(225,9),(226,9),(227,9),(228,9),(229,9),(230,9),(231,9),(232,9),(233,9),(234,9),(235,9),(236,9),(237,9),(238,9),(239,9),(240,9),(241,9),(242,9),(243,9),(244,9),(245,9),(246,9),(247,9),(248,9),(249,9),(250,9),(251,9),(252,9),(253,9),(254,9),(255,9),(256,9),(257,9),(258,9),(259,9),(260,9),(261,9),(262,9),(263,9),(264,9),(265,9),(266,9),(267,9),(276,9),(277,9),(278,9),(279,9),(280,9),(281,9),(282,9),(283,9),(284,9),(285,9),(286,9),(287,9),(291,9),(293,9),(295,9),(297,9),(299,9),(24,10),(162,10),(163,10),(167,10),(168,10),(169,10),(170,10),(172,10),(173,10),(174,10),(176,10),(196,10),(197,10),(198,10),(199,10),(200,10),(201,10),(202,10),(203,10),(204,10),(205,10),(206,10),(207,10),(208,10),(209,10),(210,10),(211,10),(212,10),(213,10),(214,10),(238,10),(24,11),(170,11),(171,11),(172,11),(173,11),(174,11),(175,11),(176,11),(213,11),(167,12),(178,12),(179,12),(206,12),(212,12),(214,12),(246,12),(247,12),(248,12),(167,14),(178,14),(179,14),(206,14),(212,14),(214,14),(246,14),(247,14),(248,14),(24,15),(161,15),(162,15),(164,15),(165,15),(166,15),(196,15),(197,15),(198,15),(199,15),(200,15),(201,15),(231,15),(232,15),(233,15),(234,15),(24,16),(161,16),(162,16),(164,16),(196,16),(201,16),(202,16),(205,16),(234,16),(235,16),(24,17),(161,17),(162,17),(164,17),(196,17),(201,17),(203,17),(204,17),(234,17),(236,17),(237,17);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'superadmin','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(2,'admin','web','2026-10-13 07:10:16','2026-10-13 07:10:16'),(3,'employee','web','2026-10-13 07:10:17','2026-10-13 07:10:17'),(9,'Super Admin','web','2026-10-13 07:11:10','2026-10-13 07:11:10'),(10,'Purchase Officer','web','2026-10-13 07:11:13','2026-10-13 07:11:13'),(11,'Store Keeper','web','2026-10-13 07:11:51','2026-10-13 07:11:51'),(12,'Accounts Department','web','2026-10-13 07:11:52','2026-10-13 07:11:52'),(14,'Finance & Accounts','web','2026-10-13 07:11:52','2026-10-13 07:11:52'),(15,'RFQ Creator','web','2026-10-13 07:11:53','2026-10-13 07:11:53'),(16,'Quotation Negotiator','web','2026-10-13 07:11:53','2026-10-13 07:11:53'),(17,'Vendor Selector','web','2026-10-13 07:11:53','2026-10-13 07:11:53');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `segments`
--

DROP TABLE IF EXISTS `segments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `segments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `segments_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `segments`
--

LOCK TABLES `segments` WRITE;
/*!40000 ALTER TABLE `segments` DISABLE KEYS */;
/*!40000 ALTER TABLE `segments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('0np1aTcnmeFVbJB2DRAwSKRtPX1jUAinUNGuBsDd',NULL,'216.180.246.170','Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/genomecrawler)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWDF1Ymt5cXZVZHZmSkxPQmdDNVhIY0Y3eUc2UGJHeGx5R2xBWTlkQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793072143),('2V90vdwxkR23CRkGPrrpvTE3zVAJNyMDnliB4qCY',NULL,'49.207.205.212','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoia2pqcDdBT0lwb0JwNWVwQUZMM294NlcyTk9rTk5Ldkc2SnRMV1Y1MiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793079310),('4ETjlpfb8pvKyoy3XzHuBgMzzEEvRRe9Mwn9XXc8',NULL,'49.207.205.212','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiT0VXSk1jTkwycjJQMUNFcjdtS1JLQW50VnJTdU8xZk9UR2pXdUxTWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793075714),('6j1Je7juyRaTMCMCOO6JDccgZCPHnx1lvLLRfMpC',NULL,'172.236.228.193','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQnFpaFNCZDVCNXpJektCV2pBWDh3dGF3UjRlTmVsdE43R2phTTNqOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793058319),('6zxlKDNYsIMv6bBtD4PO8se8EkMgUbqmumwonneY',NULL,'172.206.227.146','Mozilla/5.0 zgrab/0.x','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUF0ZTRDdmtRd0dkanlWY3B5S3dRMktiME1ERnNLQzNoUXNiVHZzSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793081944),('7g2BuqnWfAz8n9q9IUxUXazzAGhtN0s1f1V79Qrm',NULL,'216.180.246.170','Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/genomecrawler)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicFBab0dzVk8yRWoycWhXRWlNZjhBcGxqY1pMMXphOG1jWEtuaUNQRyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo4OiJodHRwOi8vXyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjg6Imh0dHA6Ly9fIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793071657),('92gEgE50n5WIAgzJpdsgDIc32KiKUSrdroMVAlcD',NULL,'34.140.3.155','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibjJ3SVFVekpkRkViTDFNMjJCcnB4WGJhNWNUS0M0RHVhVXMwSGl1YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793080821),('bWsFixUCrJYL18YfnbrfTJ3mTMktU0ypEnx3c5Zm',NULL,'172.236.228.193','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTGhRbXZhd1d3Y1NZSWt2UW9iSmp2RXNyZmFyWXB5UmIyVHBEQngwMyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793058318),('cAPzWPnhBTLgAyIyymvmmwpMvPNmDsWnuSDuMqHC',NULL,'34.140.3.155','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoidjROTGhPYmVDTkt0SkpQbllSdnExeFhzN0RYbmVlV1dndGJjMmI1VyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793080819),('d9dEmlTLeVK7pkDPyIbYzZn0cfh8Lan1rawptAjq',NULL,'86.54.31.32','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSWpoN2c1Y0FDSFJGSjhOSUVQRkh1ajdrdjJSMlVLREhYVU9CWFpPMyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793075413),('DFbIwJWbfU6Ioks8Xqw5dtE4JHqIT5LYaHBxw9Ce',NULL,'216.218.206.67','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUFnVWgwUnZtYnJTZkNUN0xQbjJBM3lhTFdTbGczdXR4MDJUQmFoSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793077551),('dgI81Xl4G4Hc6FrgmBtay4utKf0rNzJwK4CpCtxo',NULL,'216.218.206.67','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.41','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYXkyQTd6U1NENmVHMmo5N3c4U29ya2VrZjRyeWRTbTB3QTlkYkp5USI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793077368),('DJcuZejwsjepfKf3zLca365tnofUV0qGBJoOPGEO',NULL,'216.218.206.67','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTGZERHh2YWhMZjJSTDlrb2xrUVdsbkJjTFZmM2VsWHVzcXJRbGtTdiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793077550),('DpBu9YeLjThv5yakKwJuWKnKKSAVI78YfQg1ofNZ',NULL,'216.218.206.67','Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Ubuntu/11.04 Chromium/14.0.825.0 Chrome/14.0.825.0 Safari/535.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoicndxR2dSdVg4NmxaZDVwVldRUnJ0THVQcDdSaWFLZ2NEa1k4dktqMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793077587),('FPcmmQkLr8SVcKqrwvC6iNS2umIrs5KgMjlgnVcm',NULL,'49.207.205.212','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOGRMVDRNenVldG9zNUl5V1l4b0ZwekZMNGNXVGozVXhucXcwUXFFYSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cHM6Ly8xMy4yMzIuMTkxLjM1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793075791),('gBpg7xqcvZwkJ9p13xihzei6yz1DADVkQalmVDFJ',NULL,'147.185.133.252','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRXZPdUR2MU1ZQlpYekRyNGc3Z0hVeHFoRmJ0dXFyNXFpOHpGWG9PQSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo4OiJodHRwOi8vXyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjg6Imh0dHA6Ly9fIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793060107),('GFhf9y2oRAdQzmFhoblk2lVRO3CIu61Y2ZV7uNe7',NULL,'172.206.227.146','Mozilla/5.0 zgrab/0.x','YTo0OntzOjY6Il90b2tlbiI7czo0MDoia3RUeVhkV1lMSFFQZUJKVHJkNTlOSEp4Y1hPTkZPMGVNMGE4V0FzayI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cHM6Ly8xMy4yMzIuMTkxLjM1Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNSI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793081944),('GjOlscqukUfp7nven7GgArn6j9v2aB527W1MHdqp',NULL,'49.207.205.212','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibjZka0FsODlmMVNRSHd1ckFFcU9zWW9xajQ4RnpqUVVkRDNZa0pLYiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cHM6Ly8xMy4yMzIuMTkxLjM1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793079299),('gX3BHwzPNg6Mi5HN9hZmg30l2LjUTbW1UUiBuIqm',NULL,'159.89.192.15','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY1dTVFBYclBHVU01UzRCekpIOWFLOXByWFF3cVBGZzM4aEtHVDM2dyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793066457),('HFYPqHl2X8jujSOWMKjHPAWG7g2e57Axe7yzBKXE',NULL,'170.106.180.246','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiemtLNUV5MGFQZmVGRWtzMUVXTW4xUnNlZ3BNQWhwa3Ixd1pkSVBpTSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNjoiaHR0cDovLzEzLjIzMi4xOTEuMzUvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793069324),('HJrAZE07fx8J9YihcJ6JruBjq6yh8zzJVJAQFsos',NULL,'43.165.167.69','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaHdNaFllbHFCOExPWlQ2Y0dkZlY2QTlsVGtHalBYbUJCZlJhenNNSSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNjoiaHR0cDovLzEzLjIzMi4xOTEuMzUvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793081023),('HPD5QWZcldRjHqtRqNPTZ7aqoxYRhNoucmiTRmni',NULL,'34.77.137.207','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVXdQbWphU0d2UzI3RmFjMW9Dcm9PMFhJUTVuU0pvcnpsZGxLT1ZRWSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793064964),('ID03XSGMRMrUbTYDM143q3XQepyBbfv4srwLFrPJ',1,'49.207.205.212','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoibDA2emxOaTBZZHZqUmpSdDdiZE4yOWwzbjlReDRuWUFHcWxBU1h2RiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozOToiaHR0cDovLzEzLjIzMi4xOTEuMzUvbW9iaWxlLXBlcm1pc3Npb25zIjtzOjU6InJvdXRlIjtzOjI0OiJtb2JpbGUtcGVybWlzc2lvbnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1793080611),('IQW6sj315W0PrqPwBvrFbHNlRiXANOnlpMcsZAU7',NULL,'216.180.246.170','Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/genomecrawler)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWEFlQ2x2OGdOMlYzRFl0amRFOW1hTjJ4V3hiRlNkc1g5dHVodG85VSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793072143),('jaUu6m5R6Asr10ZmeRgGZF8NXw5QJswsSWGCoREo',NULL,'66.228.53.204','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUmNoeFhTMzlYc29XaXBadDE1Mklmb1NDaDdETVJIZkJXSm12MW9mTyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793061945),('jEoNCbTXuFR89ubaiFbl9EDMxvvxkORcL7EAQUX7',NULL,'167.99.35.192','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 ForestEngine/1.0 (+https://forestengine.net/#opt-out)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ2FLZnliZXBNTEUzS1RMTTVBNHM5dkh4ZWM5SVFHTEZwbjE2eDdmUyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793075535),('JSIk1d8oewjIevtbjDuqsSKcVTrztgSbQ1GmoD3h',NULL,'172.104.11.46','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUozQVpZb3hVZHh4VzB5ejVpTkJ0M3d4VFlFVlJkejVkQU9nV0tlNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793062282),('KNCe8wUma3e35iiCfZqIHkbH2p7pLgcjPVZYHL3d',NULL,'66.132.195.86','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMzZKZ2EwVDJHY1pKeTRyOHB4NDU0SWxmMmo2MFlTcUd3aFNwYUsxRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793055095),('lBNgAbDljxz85lrRjqvhAnCAJWllOiVkbN2MThb8',NULL,'34.140.3.155','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibXdielRqeWUyTjRLRWRyUXJGdFA4SG1QSzNxa0RoWGJjYTFTUUhxQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793080820),('lZHmYpQOhdFfQAfQxMnAdAXQkoMEJAZz1v9ag6P8',NULL,'172.232.108.36','Mozilla/5.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoia1hYVUQzYWREa28zYVhVY3RGTU9YaFVtNFRZQXQ5a2xaV00yenhaNSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cHM6Ly8xMy4yMzIuMTkxLjM1Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNSI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793078610),('LZlpkqp2fpeS3y7R9NfIEUv8HA1BhT5G4GFOZ6jI',NULL,'43.132.214.228','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiemFBUWt0T01Qc1YyY1pZQWE5UVpxSWt2ekpxTjZyZmRMZDBGQVpnaCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNjoiaHR0cDovLzEzLjIzMi4xOTEuMzUvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793054751),('mdq1ISlppinnGpGk2zi4msUn666N7M8MzpHC8I6Z',NULL,'172.232.108.36','Mozilla/5.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUWMxbWlvQlhZcHpTRjFXQ3ozSVF5aFozRHhCQ21BV3owQ29SZTVqSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793078610),('n45Jnb8BbK0TcVSqb0DFBl8WcNZutziRbhNJGYyH',NULL,'66.132.195.86','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoibHg0NDFtNnl1a2JQTnVtUGZFSUZtcTNxSlIyQnFWQ3ZRNFZVcGdHbSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793055090),('N6DfwOSHMky6dBTPzSbnuzp4GE4zGAkKOhzy0rN3',NULL,'178.128.84.112','Mozilla/5.0 zgrab/0.x','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYUZicFVwbWZZVGY2MTNibnVpMDRmRW5QUG9YNmJrVzNSZmVZQVFzUSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793064238),('NDQRnb8VMZjzlssoBXpoKqd0QD3itdgtHka24nxi',NULL,'34.140.3.155','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZU9SUVNCWFZUdDZVVktlMlptU2swT3VvUXA2OU9DS0NZaHRLRjAxWCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cHM6Ly8xMy4yMzIuMTkxLjM1Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vMTMuMjMyLjE5MS4zNSI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793080821),('nnVNW8sT80QnKIwp3CmiTg0FCCf4j6FcelTlVbTi',NULL,'111.92.94.135','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV2NOck5xQm9wazdTQjRjdnVxa2syMXBSZHdYUGgzdnE1MEZkYkhuayI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793079299),('OIpMp9T7eKUyNvmIbRqTAG1CmyPnTZPPEja7GNG8',NULL,'66.228.53.204','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZUQ3Rk9Kc2FXM0UwZGoxemJDajhFRzBTNExiV0FxcnFEUmFIeEhtWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793061945),('qzoBwTpncfUzaQWPhxikHQEptAgj2csQzPFU4zJo',NULL,'86.54.31.32','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMkhKdVNqSkt5ZjFFZnRqbVduWGlYc1VrQ3VNeWFvTDFDV0l1SXhFaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793075413),('sooHnOUOYnW6iEejUQEjaIVLjMQOaarX4971n7oK',NULL,'216.180.246.170','Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/genomecrawler)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicUZiaFBrdjhVdVdZOGx1b2Y3ZXN3TVdBNXEyd3JjbFFXemNLVThqbyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo4OiJodHRwOi8vXyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjg6Imh0dHA6Ly9fIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793071737),('tqjxpHWap6XYchXwJGWZsgxsR6gWY4AdOdVeD3fP',NULL,'134.122.16.198','Mozilla/5.0 zgrab/0.x','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicnR4V1BXVmNEYXhLS0RDbUtxSmMxdXRvNWo3akEzTHlKcE9QekJFbiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793079392),('UOhnLKYFE9DNEtK7YV9E838MF2OT4LCp9vynwqOI',NULL,'34.79.51.148','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOUJKVlRTZ3JEZTRNbVl3blZJWjZSSTd3STk5V1lNOUNWSWFtNXl2dyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793070340),('Uq3U5VzCFS8loKQNqkNplc4CN8NZiEJSPqmJ8FWc',NULL,'8.216.9.30','curl/7.74.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoibDl3YmlyNVFTN1hYSXBIVHJhZzF3WkZWZ0U5UGt3c0hPcXN5NlhTYSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793066680),('URio8SQyq5NdPNUxT1vsXMGzLuT8m6GwZRp7y5yc',NULL,'172.104.11.46','Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoibFlMWHVWOFJaMVlxRFFRRHdTd2hCTFJRQnVzeHFTRWRwZkM4TGJuUyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793062282),('w7PR6G6qzCcrd94NAhvyvDbpRcBSGqBf6on1yaVH',NULL,'8.216.9.30','curl/7.74.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidHExWGozZE1nc3YzTWpOenJkZm1uVkhlcG9rbTlXUVg3Sk5IVDA4cyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793066680),('WsEN3QjtrgfgQjnnoOuPJDeKpN9eAYFHjGDiitGR',NULL,'49.207.205.212','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMzVsN0d5WjBOU2JJTzVWT0J4Umc1YkY3TmtnVWlscUo5dWp0YlhJZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1793075547),('YeD7uXf52ozNmfcUncdFvqT8M6lTpTc9cI6tPPU5',NULL,'34.77.137.207','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWVuVmZMV3BJSlI2and2anZlUkJKTGNyandZV0poNGtDZTB6SFZpQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMy4yMzIuMTkxLjM1L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793064965),('yVnAmKjxYIAOvBRyRsB3UOt8L4D5vIiIzZXoht8b',NULL,'216.180.246.170','Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/genomecrawler)','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZGxvUFBOODE5bGxJMnVaZW5HV2JiUjdnWTIzdzczbElRT2s5cjd4NyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo4OiJodHRwOi8vXyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjg6Imh0dHA6Ly9fIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793071694),('YzroQJgPfyDqOtQVln5SS1SJOJVIInSdZvhP8D0Z',NULL,'94.154.43.189','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.6167.85 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUGE2S2ZtU3lWeUl1bVZkSzNiOE1UMkdkMU5nOVFFWTFEczBXWGVndiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMDoiaHR0cDovLzEzLjIzMi4xOTEuMzUiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793071361);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'warehouse_company_id','2','2026-10-13 07:11:54','2026-10-13 07:11:54'),(2,'warehouse_id','3','2026-10-13 07:11:54','2026-10-13 07:11:54'),(3,'warehouse_location','8','2026-10-13 07:11:54','2026-10-13 07:11:54'),(4,'pagination_size','10','2026-10-13 07:11:54','2026-10-13 07:11:54');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_details`
--

DROP TABLE IF EXISTS `shipping_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shipping_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gst_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_details`
--

LOCK TABLES `shipping_details` WRITE;
/*!40000 ALTER TABLE `shipping_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipping_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sub_segments`
--

DROP TABLE IF EXISTS `sub_segments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_segments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `segment_id` bigint unsigned NOT NULL,
  `subsegment_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_segments_segment_id_subsegment_name_unique` (`segment_id`,`subsegment_name`),
  CONSTRAINT `sub_segments_segment_id_foreign` FOREIGN KEY (`segment_id`) REFERENCES `segments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_segments`
--

LOCK TABLES `sub_segments` WRITE;
/*!40000 ALTER TABLE `sub_segments` DISABLE KEYS */;
/*!40000 ALTER TABLE `sub_segments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_activities`
--

DROP TABLE IF EXISTS `support_ticket_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_activities_support_ticket_id_foreign` (`support_ticket_id`),
  KEY `support_ticket_activities_user_id_foreign` (`user_id`),
  CONSTRAINT `support_ticket_activities_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_activities_chk_1` CHECK (json_valid(`meta`))
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_activities`
--

LOCK TABLES `support_ticket_activities` WRITE;
/*!40000 ALTER TABLE `support_ticket_activities` DISABLE KEYS */;
INSERT INTO `support_ticket_activities` VALUES (1,11,1,'status_changed','{\"old\":\"closed\",\"new\":\"reopen\"}','2026-10-19 13:31:51','2026-10-19 13:31:51'),(2,11,1,'reopened',NULL,'2026-10-19 13:31:51','2026-10-19 13:31:51'),(3,21,1,'created',NULL,'2026-10-19 13:36:58','2026-10-19 13:36:58'),(4,21,1,'commented','{\"message\":\"i will solve the issue.\"}','2026-10-19 13:38:47','2026-10-19 13:38:47'),(5,21,1,'commented','{\"message\":\"solve as soon as possible\"}','2026-10-19 13:39:25','2026-10-19 13:39:25'),(6,20,1,'status_changed','{\"old\":\"completed\",\"new\":\"closed\"}','2026-10-22 11:30:29','2026-10-22 11:30:29'),(7,20,1,'status_changed','{\"old\":\"closed\",\"new\":\"reopen\"}','2026-10-22 11:30:32','2026-10-22 11:30:32'),(8,22,20,'created',NULL,'2026-10-22 14:55:30','2026-10-22 14:55:30'),(9,22,1,'accepted',NULL,'2026-10-22 18:01:31','2026-10-22 18:01:31'),(10,22,1,'status_changed','{\"old\":\"accepted\",\"new\":\"work_in_progress\"}','2026-10-22 18:04:29','2026-10-22 18:04:29'),(11,23,21,'created',NULL,'2026-10-23 10:42:00','2026-10-23 10:42:00'),(12,23,1,'accepted',NULL,'2026-10-23 10:42:37','2026-10-23 10:42:37'),(13,23,1,'status_changed','{\"old\":\"accepted\",\"new\":\"work_in_progress\"}','2026-10-23 10:42:56','2026-10-23 10:42:56'),(17,23,20,'commented','{\"message\":\"Automated test reply from mobile audit verification.\"}','2026-10-23 11:04:18','2026-10-23 11:04:18'),(38,23,1,'status_changed','{\"old\":\"work_in_progress\",\"new\":\"completed\"}','2026-10-23 11:26:38','2026-10-23 11:26:38'),(39,23,1,'status_changed','{\"old\":\"completed\",\"new\":\"closed\"}','2026-10-23 11:27:17','2026-10-23 11:27:17'),(40,23,21,'status_changed','{\"old\":\"closed\",\"new\":\"reopen\"}','2026-10-23 11:28:32','2026-10-23 11:28:32'),(41,23,21,'reopened',NULL,'2026-10-23 11:28:32','2026-10-23 11:28:32'),(42,23,1,'status_changed','{\"old\":\"reopen\",\"new\":\"work_in_progress\"}','2026-10-23 11:32:30','2026-10-23 11:32:30'),(43,23,1,'status_changed','{\"old\":\"work_in_progress\",\"new\":\"completed\"}','2026-10-23 11:37:39','2026-10-23 11:37:39'),(44,23,21,'status_changed','{\"old\":\"completed\",\"new\":\"closed\"}','2026-10-23 11:38:10','2026-10-23 11:38:10'),(45,23,21,'closed',NULL,'2026-10-23 11:38:10','2026-10-23 11:38:10'),(46,23,21,'status_changed','{\"old\":\"closed\",\"new\":\"reopen\"}','2026-10-23 11:38:56','2026-10-23 11:38:56'),(47,23,21,'reopened',NULL,'2026-10-23 11:38:56','2026-10-23 11:38:56');
/*!40000 ALTER TABLE `support_ticket_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_assignments`
--

DROP TABLE IF EXISTS `support_ticket_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `assigned_by` bigint unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_assignments_user_id_foreign` (`user_id`),
  KEY `support_ticket_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `support_ticket_assignments_support_ticket_id_user_id_index` (`support_ticket_id`,`user_id`),
  CONSTRAINT `support_ticket_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_assignments_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_assignments`
--

LOCK TABLES `support_ticket_assignments` WRITE;
/*!40000 ALTER TABLE `support_ticket_assignments` DISABLE KEYS */;
INSERT INTO `support_ticket_assignments` VALUES (1,1,1,1,'2026-10-16 04:21:14',1,NULL,'2026-10-16 04:21:14','2026-10-16 04:21:14'),(2,2,1,1,'2026-10-15 09:21:14',1,NULL,'2026-10-15 09:21:14','2026-10-16 06:21:14'),(3,3,1,1,'2026-10-14 09:21:14',1,NULL,'2026-10-14 09:21:14','2026-10-16 08:21:14'),(4,4,1,1,'2026-10-12 09:21:14',1,NULL,'2026-10-12 09:21:14','2026-10-15 09:21:14'),(5,5,1,1,'2026-10-10 09:21:14',1,NULL,'2026-10-10 09:21:14','2026-10-14 09:21:14'),(6,22,1,1,'2026-10-22 18:01:31',1,NULL,'2026-10-22 18:01:31','2026-10-22 18:01:31'),(7,23,1,1,'2026-10-23 10:42:37',1,NULL,'2026-10-23 10:42:37','2026-10-23 10:42:37');
/*!40000 ALTER TABLE `support_ticket_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_commands`
--

DROP TABLE IF EXISTS `support_ticket_commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_commands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `ticket_response` text COLLATE utf8mb4_unicode_ci,
  `voice_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recording_duration` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filenames` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  KEY `support_ticket_commands_support_ticket_id_foreign` (`support_ticket_id`),
  KEY `support_ticket_commands_user_id_foreign` (`user_id`),
  CONSTRAINT `support_ticket_commands_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_commands_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_commands_chk_1` CHECK (json_valid(`filenames`))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_commands`
--

LOCK TABLES `support_ticket_commands` WRITE;
/*!40000 ALTER TABLE `support_ticket_commands` DISABLE KEYS */;
INSERT INTO `support_ticket_commands` VALUES (1,21,1,'i will solve the issue.',NULL,NULL,'2026-10-19 13:38:47','2026-10-19 13:38:47',NULL,NULL),(2,21,1,'solve as soon as possible',NULL,NULL,'2026-10-19 13:39:25','2026-10-19 13:39:25',NULL,NULL),(3,23,20,'Automated test reply from mobile audit verification.',NULL,NULL,'2026-10-23 11:04:18','2026-10-23 11:04:18',NULL,NULL);
/*!40000 ALTER TABLE `support_ticket_commands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_status_logs`
--

DROP TABLE IF EXISTS `support_ticket_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_status_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint unsigned NOT NULL,
  `old_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_status_logs_support_ticket_id_foreign` (`support_ticket_id`),
  KEY `support_ticket_status_logs_changed_by_foreign` (`changed_by`),
  CONSTRAINT `support_ticket_status_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_status_logs_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_status_logs`
--

LOCK TABLES `support_ticket_status_logs` WRITE;
/*!40000 ALTER TABLE `support_ticket_status_logs` DISABLE KEYS */;
INSERT INTO `support_ticket_status_logs` VALUES (1,1,NULL,'open',1,'2026-10-16 04:21:14','2026-10-16 04:21:14'),(2,2,NULL,'accepted',1,'2026-10-15 09:21:14','2026-10-16 06:21:14'),(3,3,NULL,'work_in_progress',1,'2026-10-14 09:21:14','2026-10-16 08:21:14'),(4,4,NULL,'completed',1,'2026-10-12 09:21:14','2026-10-15 09:21:14'),(5,5,NULL,'closed',1,'2026-10-10 09:21:14','2026-10-14 09:21:14'),(6,11,'closed','reopen',1,'2026-10-19 13:31:51','2026-10-19 13:31:51'),(7,20,'completed','closed',1,'2026-10-22 11:30:29','2026-10-22 11:30:29'),(8,20,'closed','reopen',1,'2026-10-22 11:30:32','2026-10-22 11:30:32'),(9,22,'accepted','work_in_progress',1,'2026-10-22 18:04:29','2026-10-22 18:04:29'),(10,23,'accepted','work_in_progress',1,'2026-10-23 10:42:56','2026-10-23 10:42:56'),(25,23,'work_in_progress','completed',1,'2026-10-23 11:26:38','2026-10-23 11:26:38'),(26,23,'completed','closed',1,'2026-10-23 11:27:17','2026-10-23 11:27:17'),(27,23,'closed','reopen',21,'2026-10-23 11:28:32','2026-10-23 11:28:32'),(28,23,'reopen','work_in_progress',1,'2026-10-23 11:32:30','2026-10-23 11:32:30'),(29,23,'work_in_progress','completed',1,'2026-10-23 11:37:39','2026-10-23 11:37:39'),(30,23,'completed','closed',21,'2026-10-23 11:38:10','2026-10-23 11:38:10'),(31,23,'closed','reopen',21,'2026-10-23 11:38:56','2026-10-23 11:38:56');
/*!40000 ALTER TABLE `support_ticket_status_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `priority` enum('low','high','urgent','normal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `due_date` date DEFAULT NULL,
  `asset_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('open','accepted','work_in_progress','hold','in_purchase','completed','closed','reopen','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ticket_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `close_ticket_commands` text COLLATE utf8mb4_unicode_ci,
  `completed_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `completed_ticket_commands` text COLLATE utf8mb4_unicode_ci,
  `completed_time` int DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_tickets_company_id_foreign` (`company_id`),
  KEY `support_tickets_branch_id_foreign` (`branch_id`),
  KEY `support_tickets_department_id_foreign` (`department_id`),
  KEY `support_tickets_asset_id_foreign` (`asset_id`),
  KEY `support_tickets_created_by_foreign` (`created_by`),
  KEY `support_tickets_updated_by_foreign` (`updated_by`),
  KEY `support_tickets_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `support_tickets_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_chk_1` CHECK (json_valid(`completed_files`))
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
INSERT INTO `support_tickets` VALUES (1,'Dell Latitude 7420 Screen Flickering',1,1,1,'high','2026-10-18',1,'Display flickers intermittently when running high workloads or moving the lid.','open',1,1,'2026-10-16 04:21:14','2026-10-16 04:21:14',NULL,NULL,NULL,0.00,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Keyboard Key Replacement & Servicing',1,1,1,'normal','2026-10-19',1,'Spacebar and enter key are sticky, needs internal cleaning and key switch replacement.','accepted',1,1,'2026-10-15 09:21:14','2026-10-16 06:21:14',NULL,NULL,NULL,500.00,NULL,NULL,NULL,NULL,NULL,NULL),(3,'Ethernet Port & VPN Network Issue in Accounts',1,1,1,'urgent','2026-10-17',NULL,'Wall ethernet jack loose; intermittent disconnects from company internal VPN.','work_in_progress',1,1,'2026-10-14 09:21:14','2026-10-16 08:21:14',NULL,NULL,NULL,1200.00,NULL,NULL,NULL,NULL,NULL,NULL),(4,'Quarterly OS & Antivirus Security Patching',1,1,1,'low','2026-10-15',1,'Routine security patches and endpoint protection definitions updated successfully.','completed',1,1,'2026-10-12 09:21:14','2026-10-15 09:21:14',NULL,NULL,NULL,0.00,NULL,NULL,NULL,NULL,NULL,'2026-10-15 09:21:14'),(5,'RAM Upgrade Request (16GB -> 32GB)',1,1,1,'normal','2026-10-13',1,'Workstation RAM module replaced and tested with memtest. Ticket resolved.','closed',1,1,'2026-10-10 09:21:14','2026-10-14 09:21:14',NULL,NULL,NULL,3500.00,'Installed DDR4 16GB module. Verified hardware diagnostics.',NULL,NULL,NULL,NULL,NULL),(6,'Printer Paper Jam on 3rd Floor',1,1,1,'normal','2026-10-31',1,'Automated ticket for system maintenance: Printer Paper Jam on 3rd Floor','open',1,1,'2026-10-17 06:19:10','2026-10-17 06:19:10',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,'VPN Authentication Failure from Remote Branch',1,1,1,'high','2026-10-22',1,'Automated ticket for system maintenance: VPN Authentication Failure from Remote Branch','work_in_progress',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,'Conference Room Audio Echoing',1,1,1,'low','2026-10-21',1,'Automated ticket for system maintenance: Conference Room Audio Echoing','accepted',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,'Core Switch Port 14 Flapping',1,1,1,'urgent','2026-10-20',1,'Automated ticket for system maintenance: Core Switch Port 14 Flapping','open',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,'Monitor Display Discoloration',1,1,1,'normal','2026-10-30',1,'Automated ticket for system maintenance: Monitor Display Discoloration','completed',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,'Replace Faulty Laptop Charger',1,1,1,'low','2026-10-22',1,'Automated ticket for system maintenance: Replace Faulty Laptop Charger','reopen',1,1,'2026-10-17 06:19:11','2026-10-19 13:31:51',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,'Outlook Crashing on Windows 11',1,1,1,'normal','2026-10-29',1,'Automated ticket for system maintenance: Outlook Crashing on Windows 11','hold',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(13,'Request Access to Production Database',1,1,1,'high','2026-10-24',1,'Automated ticket for system maintenance: Request Access to Production Database','open',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(14,'UPS Battery Replacement Required',1,1,1,'urgent','2026-10-22',1,'Automated ticket for system maintenance: UPS Battery Replacement Required','work_in_progress',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(15,'Install Visual Studio Code & Git',1,1,1,'low','2026-10-31',1,'Automated ticket for system maintenance: Install Visual Studio Code & Git','completed',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(16,'New Joiner Laptop Provisioning',1,1,1,'high','2026-10-27',1,'Automated ticket for system maintenance: New Joiner Laptop Provisioning','accepted',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(17,'Biometric Scanner Not Syncing',1,1,1,'urgent','2026-10-26',1,'Automated ticket for system maintenance: Biometric Scanner Not Syncing','open',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(18,'Wi-Fi Signal Drop in Cafeteria',1,1,1,'normal','2026-10-22',1,'Automated ticket for system maintenance: Wi-Fi Signal Drop in Cafeteria','work_in_progress',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(19,'Slow Performance on CAD Workstation',1,1,1,'high','2026-10-30',1,'Automated ticket for system maintenance: Slow Performance on CAD Workstation','hold',1,1,'2026-10-17 06:19:11','2026-10-17 06:19:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(20,'Annual Antivirus License Renewal',1,1,1,'normal','2026-10-26',1,'Automated ticket for system maintenance: Annual Antivirus License Renewal','reopen',1,1,'2026-10-17 06:19:11','2026-10-22 11:30:32',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,7511,'2026-10-22 11:30:29'),(21,'camera not working',2,1,1,'normal','2026-10-19',4,NULL,'open',1,NULL,'2026-10-19 13:36:58','2026-10-19 13:36:58',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,'camera not working',1,2,2,'normal','2026-10-22',1,NULL,'work_in_progress',20,1,'2026-10-22 14:55:30','2026-10-22 18:04:29',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(23,'screen is not working',1,2,3,'high','2026-10-24',12,NULL,'reopen',21,21,'2026-10-23 10:42:00','2026-10-23 11:38:56',NULL,NULL,1,0.00,NULL,'f2343b118e8fd84484b12e2914205409.png','[\"f2343b118e8fd84484b12e2914205409.png\"]','mo',56,'2026-10-23 11:38:10');
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_categories`
--

DROP TABLE IF EXISTS `task_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_categories`
--

LOCK TABLES `task_categories` WRITE;
/*!40000 ALTER TABLE `task_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_report_collaborators`
--

DROP TABLE IF EXISTS `task_report_collaborators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_report_collaborators` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_report_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_report_collaborators_task_report_id_user_id_unique` (`task_report_id`,`user_id`),
  KEY `task_report_collaborators_user_id_foreign` (`user_id`),
  CONSTRAINT `task_report_collaborators_task_report_id_foreign` FOREIGN KEY (`task_report_id`) REFERENCES `task_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_report_collaborators_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_report_collaborators`
--

LOCK TABLES `task_report_collaborators` WRITE;
/*!40000 ALTER TABLE `task_report_collaborators` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_report_collaborators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_reports`
--

DROP TABLE IF EXISTS `task_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recurring_task_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `occurrence_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `accepted_by_user_id` bigint unsigned DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `completed_by_user_id` bigint unsigned DEFAULT NULL,
  `collaborator_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `access_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_reports_task_branch_occ_unique` (`recurring_task_id`,`branch_id`,`occurrence_date`),
  KEY `task_reports_recurring_task_id_index` (`recurring_task_id`),
  KEY `task_reports_branch_id_index` (`branch_id`),
  KEY `task_reports_occurrence_date_index` (`occurrence_date`),
  KEY `tr_task_occurrence_idx` (`recurring_task_id`,`occurrence_date`),
  KEY `tr_status_occurrence_idx` (`status`,`occurrence_date`),
  KEY `task_reports_accepted_by_user_id_index` (`accepted_by_user_id`),
  KEY `task_reports_completed_by_user_id_index` (`completed_by_user_id`),
  CONSTRAINT `task_reports_chk_1` CHECK (json_valid(`attachment_paths`)),
  CONSTRAINT `task_reports_chk_2` CHECK (json_valid(`collaborator_user_ids`)),
  CONSTRAINT `task_reports_chk_3` CHECK (json_valid(`access_user_ids`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_reports`
--

LOCK TABLES `task_reports` WRITE;
/*!40000 ALTER TABLE `task_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_user`
--

DROP TABLE IF EXISTS `task_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_user_task_id_user_id_unique` (`task_id`,`user_id`),
  KEY `task_user_user_id_foreign` (`user_id`),
  CONSTRAINT `task_user_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_user`
--

LOCK TABLES `task_user` WRITE;
/*!40000 ALTER TABLE `task_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `department_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_category_id_foreign` (`category_id`),
  KEY `tasks_department_id_foreign` (`department_id`),
  KEY `tasks_branch_id_foreign` (`branch_id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `task_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_unit_name_unique` (`unit_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mile_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reporting_status` tinyint(1) NOT NULL DEFAULT '0',
  `company_id` bigint unsigned DEFAULT NULL,
  `is_left` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `is_user` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation_id` bigint unsigned DEFAULT NULL,
  `segment_id` bigint unsigned DEFAULT NULL,
  `department_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `dealer_location_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `subsegment_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `reporting_to` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `mobile_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `employee_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `left_date` date DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `is_department_head` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_emp_code_unique` (`emp_code`),
  KEY `users_designation_id_foreign` (`designation_id`),
  KEY `users_segment_id_foreign` (`segment_id`),
  KEY `users_branch_id_foreign` (`branch_id`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_segment_id_foreign` FOREIGN KEY (`segment_id`) REFERENCES `segments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_chk_1` CHECK (json_valid(`department_id`)),
  CONSTRAINT `users_chk_2` CHECK (json_valid(`dealer_location_id`)),
  CONSTRAINT `users_chk_3` CHECK (json_valid(`subsegment_id`)),
  CONSTRAINT `users_chk_4` CHECK (json_valid(`reporting_to`))
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,NULL,'Super Admin','admin@gmail.com',NULL,'$2y$12$/uj9YpgMkOUhjrFSmBgPuemm2na7e4GnLb/X4kzF9zGFntkQVPCni',NULL,'2026-10-13 07:10:16','2026-10-13 07:10:16',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(2,NULL,NULL,'Indent Employee','employee@ks.com',NULL,'$2y$12$MSeATgOpcIe3MVj.CPX6l.z/.wUM3NAkpHh1zuIgJgw0nJvJmRpIq',NULL,'2026-10-13 07:10:18','2026-10-13 07:11:47',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(3,NULL,NULL,'L1 Manager','l1@ks.com',NULL,'$2y$12$q9lkcNs91BvQrWdQUitg9e6KQbJhWGkDVI6opid.BBSHv9vSWkffC',NULL,'2026-10-13 07:10:18','2026-10-13 07:11:47',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(4,NULL,NULL,'L2 IT Head','l2@ks.com',NULL,'$2y$12$u739auSERwwLt093wWz7CunWwBcqtwjYsXE617RES5GBslMMlAgUW',NULL,'2026-10-13 07:10:18','2026-10-13 07:11:48',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(5,NULL,NULL,'L3 Finance','l3@ks.com',NULL,'$2y$12$/3WdR7xYYzL1ug74KdhJuuA0vILASWruRtvD4x5HQs0yNisxs2/5m',NULL,'2026-10-13 07:10:19','2026-10-13 07:11:48',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(6,NULL,NULL,'L4 Director','l4@ks.com',NULL,'$2y$12$ua.oCKul/6L0O2ljwsqu0.1x8XxzMucmsubFcenckTygfVrXmllru',NULL,'2026-10-13 07:10:19','2026-10-13 07:11:48',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(7,NULL,NULL,'Purchase Officer','purchase.officer@ks.com',NULL,'$2y$12$7VmULzc061npgJX6XYSsauPmGkBBTPlxKO9xun00oAJojkgntti5q',NULL,'2026-10-13 07:11:51','2026-10-13 07:11:51',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(8,NULL,NULL,'Store Keeper','store123@gmail.com',NULL,'$2y$12$ThLxnVN9Kr9DHwGUnSRpV.hjA/JW4aouzJNIlGIj09jdLz88RP8K.',NULL,'2026-10-13 07:11:51','2026-10-13 07:11:52',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(9,NULL,NULL,'Accounts Officer','accounts@ks.com',NULL,'$2y$12$drYEUIeehf4toHtRiKEME.MVNghyTE4R7x7bcrtlTX8Rsdt9zyQ7u',NULL,'2026-10-13 07:11:52','2026-10-13 07:11:52',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(10,NULL,NULL,'User 1 - RFQ Creator','rfq.creator@ks.com',NULL,'$2y$12$9.2BKcP58o/sguyBvzLireU6eNmou1TIayUbODldUSkWGYmpbKS4a',NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53',0,NULL,'no','yes',NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(11,NULL,NULL,'User 2 - Quotation Negotiator','quotation.negotiator@ks.com',NULL,'$2y$12$olVuz50VHXAwCjG.aa2KrurYPbA8GVsSMrvqazLGuRp3s3dUkbaT.',NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53',0,NULL,'no','yes',NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(12,NULL,NULL,'User 3 - Vendor Selector','vendor.selector@ks.com',NULL,'$2y$12$6iEfg1eDWrpTQEeVyu/NeuBg9SmXzAHzHNUnrsUWxSchsh.tJ4YSy',NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53',0,NULL,'no','yes',NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(13,'EMP-0UXXVX',NULL,'Linwood Kihn Jr.','kavon.howell@example.com',NULL,'$2y$12$QTTuNhcioU76VizVXyvkeeVHlbS2vgLhrUXjvj.lbvLdBOHlteioi',NULL,'2026-10-13 07:11:55','2026-10-13 07:11:55',0,1,'no','yes',6,NULL,NULL,NULL,NULL,NULL,'9012235341','2025-02-11','inactive',NULL,12,NULL,0),(14,'EMP-EPSDKI',NULL,'Mrs. Claire Funk PhD','dibbert.ruben@example.com',NULL,'$2y$12$LWxQEJaWVWVMH0kyAMDmG.f3iaU8ZIWCQeGpfeAjoEawxNLg7HHsK',NULL,'2026-10-13 07:11:56','2026-10-13 07:11:56',0,1,'no','yes',3,NULL,NULL,NULL,NULL,NULL,'9057043287','2025-09-25','inactive',NULL,17,NULL,0),(15,'EMP-7DI5JF',NULL,'Miss Melyna McClure DDS','alfonso.beier@example.org',NULL,'$2y$12$u7rb2iqcU7ChJScE7zaVAODVY3NanNyPSD/10hV951Cq.uc4tufs2',NULL,'2026-10-13 07:11:56','2026-10-13 07:11:56',0,2,'no','yes',4,NULL,NULL,NULL,NULL,NULL,'9338388758','2024-08-25','active',NULL,6,NULL,0),(16,'EMP-6XFZNY',NULL,'Prof. Bonita Gleason','colleen32@example.net',NULL,'$2y$12$GJ1oE5N7J2cHh9CNZVnVkueoM../D.ZMc1TPT8ATOh9oOKzmIu0R6',NULL,'2026-10-13 07:11:56','2026-10-13 07:11:56',0,2,'no','yes',6,NULL,NULL,NULL,NULL,NULL,'9903006454','2023-12-23','active',NULL,8,NULL,0),(17,NULL,NULL,'Admin','admin@admin.com',NULL,'$2y$12$LcCqKqHpl/6Z2CDyn/AsSOo4valwN15T.MEMLgjRKZi423nQ9Msly',NULL,'2026-10-13 07:24:52','2026-10-13 07:24:52',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(18,NULL,NULL,'Test User','testuser_dev_check_999@example.com',NULL,'$2y$12$GGD1WF0wLcVRxEgufrx4l.0uWF7lR7OudMgEJy/5Nsua4x8Z0FJL.',NULL,'2026-10-19 16:42:09','2026-10-19 16:42:09',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(19,NULL,NULL,'Rahul','rahul@gmail.com',NULL,'$2y$12$OTXrw5ETgrrMY34thEmnb.PbL5/v9F1.o2T2OECN7D3dAolFXNSA2',NULL,'2026-10-19 17:22:49','2026-10-19 17:22:49',0,NULL,'no','yes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,NULL,NULL,0),(20,'321432',NULL,'Test2','Test2@gmail.com',NULL,'$2y$12$NMMauq2yn60YH0rWQ/y.i.nqloFWA17Ba2jiGhGTSIT96InYNiOiC',NULL,'2026-10-22 11:53:24','2026-10-22 11:53:24',0,1,'no','yes',9,NULL,NULL,NULL,NULL,NULL,'7676767676','2026-10-22','active',NULL,NULL,2,0),(21,'3214329',NULL,'Test1','test@gmail.com',NULL,'$2y$12$5UvLjlh9Kke/D2dAVvNPdOhz5TcZk2m/Tzh8q/BYOoPOZq1Qn8XO2',NULL,'2026-10-22 15:52:04','2026-10-22 15:55:54',0,1,'no','yes',9,NULL,NULL,NULL,NULL,NULL,'7676767673','1970-01-01','active',NULL,NULL,2,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_quotation_items`
--

DROP TABLE IF EXISTS `vendor_quotation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_quotation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint unsigned NOT NULL,
  `indent_item_id` bigint unsigned DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nos',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `negotiated_unit_price` decimal(15,2) DEFAULT NULL,
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `negotiated_total` decimal(15,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vq_items_quotation_indent_item_unique` (`quotation_id`,`indent_item_id`),
  KEY `vendor_quotation_items_indent_item_id_foreign` (`indent_item_id`),
  CONSTRAINT `vendor_quotation_items_indent_item_id_foreign` FOREIGN KEY (`indent_item_id`) REFERENCES `indent_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `vendor_quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_quotation_items`
--

LOCK TABLES `vendor_quotation_items` WRITE;
/*!40000 ALTER TABLE `vendor_quotation_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_quotation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_quotations`
--

DROP TABLE IF EXISTS `vendor_quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rfq_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating_snapshot` decimal(3,2) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `delivery_days` int DEFAULT NULL,
  `warranty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `negotiated_total_cost` decimal(15,2) DEFAULT NULL,
  `negotiation_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_remarks` text COLLATE utf8mb4_unicode_ci,
  `vendor_response` text COLLATE utf8mb4_unicode_ci,
  `negotiations_locked_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_selected` tinyint(1) NOT NULL DEFAULT '0',
  `quoted_at` timestamp NULL DEFAULT NULL,
  `selected_at` timestamp NULL DEFAULT NULL,
  `selected_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_quotations_rfq_vendor_unique` (`rfq_id`,`vendor_id`),
  KEY `vendor_quotations_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_quotations_selected_by_foreign` (`selected_by`),
  CONSTRAINT `vendor_quotations_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_quotations_selected_by_foreign` FOREIGN KEY (`selected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_quotations_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_quotations`
--

LOCK TABLES `vendor_quotations` WRITE;
/*!40000 ALTER TABLE `vendor_quotations` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `brands` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_company_code_unique` (`company_code`),
  KEY `vendors_created_by_foreign` (`created_by`),
  KEY `vendors_updated_by_foreign` (`updated_by`),
  KEY `vendors_status_index` (`status`),
  CONSTRAINT `vendors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendors_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendors_chk_1` CHECK (json_valid(`brands`))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (1,NULL,'Tech Supplies India Pvt Ltd','9876543210',NULL,'sales@techsupplies.in','33ABCDE1234F1Z5',NULL,'12, Industrial Estate','Coimbatore','Tamil Nadu','India','active',NULL,'Authorized IT hardware distributor',NULL,NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53'),(2,NULL,'Smart IT Solutions','9843012345',NULL,'info@smartitsolutions.in','33AACCS9876L1Z9',NULL,'Gandhipuram Main Road','Coimbatore','Tamil Nadu','India','active',NULL,'Laptop & desktop wholesale supplier',NULL,NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53'),(3,NULL,'Delta Office Systems','9787612345',NULL,'contact@deltaoffice.in','33BBCCD5678A1Z1',NULL,'Perundurai Road','Erode','Tamil Nadu','India','active',NULL,'Office automation products',NULL,NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53'),(4,NULL,'NextGen Tech Mart','9790012345',NULL,'support@nextgentech.in','33DDDDE4444Q1Z8',NULL,'Avinashi Road','Tiruppur','Tamil Nadu','India','active',NULL,'Network & server equipment vendor',NULL,NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53'),(5,NULL,'Prime Solutions','9003012345',NULL,'hello@primesolutions.in','33EEEFF9999P1Z2',NULL,'RS Puram','Coimbatore','Tamil Nadu','India','inactive',NULL,'Seasonal supplier',NULL,NULL,'2026-10-13 07:11:53','2026-10-13 07:11:53');
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-27  6:20:04
