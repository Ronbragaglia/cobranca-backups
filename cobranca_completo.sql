-- ============================================
-- BANCO DE DADOS COBRANCA - DUMP COMPLETO
-- ============================================
-- Gerado em: 2026-02-03
-- Sistema: Cobrança API - Laravel
-- ============================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS `cobranca` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cobranca`;

-- ============================================
-- TABELA: users
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: password_reset_tokens
-- ============================================
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: sessions
-- ============================================
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: cache
-- ============================================
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: cache_locks
-- ============================================
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: jobs
-- ============================================
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` unsigned smallint NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: job_batches
-- ============================================
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: failed_jobs
-- ============================================
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: personal_access_tokens
-- ============================================
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: tenants
-- ============================================
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subdomain` varchar(255) NOT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `subscription_status` varchar(255) DEFAULT 'inactive',
  `evolution_api_key` varchar(255) DEFAULT NULL,
  `evolution_api_url` varchar(255) DEFAULT NULL,
  `evolution_instances` json DEFAULT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `custom_qr_enabled` tinyint(1) DEFAULT 0,
  `max_whatsapp_instances` int DEFAULT 1,
  `max_messages_per_month` int DEFAULT 500,
  `current_messages_month` int DEFAULT 0,
  `usage_reset_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_subdomain_unique` (`subdomain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: tenant_settings
-- ============================================
DROP TABLE IF EXISTS `tenant_settings`;
CREATE TABLE `tenant_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `whatsapp_reminder_days_before` json DEFAULT NULL COMMENT 'Dias antes do vencimento para enviar lembretes (ex: [3, 1])',
  `whatsapp_reminder_on_due_date` tinyint(1) DEFAULT 1 COMMENT 'Enviar lembrete no dia do vencimento',
  `whatsapp_reminder_days_after` json DEFAULT NULL COMMENT 'Dias após o vencimento para enviar lembretes (ex: [1, 3, 7])',
  `whatsapp_enabled` tinyint(1) DEFAULT 1 COMMENT 'Lembretes de WhatsApp habilitados',
  `default_currency` varchar(3) DEFAULT 'BRL' COMMENT 'Moeda padrão',
  `default_due_days` int DEFAULT 7 COMMENT 'Dias padrão de vencimento',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_settings_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_settings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: plans
-- ============================================
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'BRL',
  `interval` int DEFAULT 1 COMMENT 'Intervalo em meses',
  `stripe_price_id` varchar(255) DEFAULT NULL,
  `max_whatsapp_instances` int DEFAULT 1,
  `max_messages_per_month` int DEFAULT 500,
  `unlimited_messages` tinyint(1) DEFAULT 0,
  `api_access` tinyint(1) DEFAULT 1,
  `custom_qr` tinyint(1) DEFAULT 0,
  `analytics` tinyint(1) DEFAULT 0,
  `priority_support` tinyint(1) DEFAULT 0,
  `features` json DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `sort_order` int DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: subscriptions
-- ============================================
DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `stripe_status` varchar(255) DEFAULT NULL,
  `status` enum('active','past_due','canceled','incomplete','incomplete_expired','trialing','unpaid') DEFAULT 'incomplete',
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `current_messages_month` int DEFAULT 0,
  `current_whatsapp_instances` int DEFAULT 0,
  `usage_reset_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_stripe_subscription_id_unique` (`stripe_subscription_id`),
  KEY `subscriptions_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `subscriptions_stripe_subscription_id_index` (`stripe_subscription_id`),
  KEY `subscriptions_tenant_id_foreign` (`tenant_id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  CONSTRAINT `subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: cobrancas
-- ============================================
DROP TABLE IF EXISTS `cobrancas`;
CREATE TABLE `cobrancas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` varchar(255) DEFAULT 'pendente',
  `data_vencimento` date DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `notificacao_whatsapp_status` varchar(255) DEFAULT NULL,
  `ultimo_envio_whatsapp` timestamp NULL DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `whatsapp_reminders_sent` json DEFAULT NULL,
  `stripe_payment_link` varchar(255) DEFAULT NULL,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cobrancas_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `cobrancas_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: cobranca_envios
-- ============================================
DROP TABLE IF EXISTS `cobranca_envios`;
CREATE TABLE `cobranca_envios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cobranca_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) NOT NULL COMMENT 'email, whatsapp',
  `status` varchar(255) NOT NULL COMMENT 'enviado, falhou, simulado',
  `data` timestamp NOT NULL,
  `erro` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cobranca_envios_cobranca_id_foreign` (`cobranca_id`),
  CONSTRAINT `cobranca_envios_cobranca_id_foreign` FOREIGN KEY (`cobranca_id`) REFERENCES `cobrancas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: api_keys
-- ============================================
DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE `api_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `prefix` varchar(10) DEFAULT NULL,
  `abilities` json DEFAULT NULL,
  `rate_limit_per_minute` int DEFAULT 60,
  `rate_limit_per_hour` int DEFAULT 1000,
  `total_requests` int DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_keys_key_unique` (`key`),
  KEY `api_keys_tenant_id_active_index` (`tenant_id`,`active`),
  KEY `api_keys_key_index` (`key`),
  KEY `api_keys_prefix_index` (`prefix`),
  KEY `api_keys_tenant_id_foreign` (`tenant_id`),
  KEY `api_keys_user_id_foreign` (`user_id`),
  CONSTRAINT `api_keys_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `api_keys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: message_templates
-- ============================================
DROP TABLE IF EXISTS `message_templates`;
CREATE TABLE `message_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `type` enum('cobranca','lembrete','agradecimento','custom') DEFAULT 'custom',
  `content` text NOT NULL,
  `variables` json DEFAULT NULL COMMENT 'Variáveis disponíveis no template',
  `is_default` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `usage_count` int DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_templates_tenant_id_type_index` (`tenant_id`,`type`),
  KEY `message_templates_tenant_id_foreign` (`tenant_id`),
  KEY `message_templates_user_id_foreign` (`user_id`),
  CONSTRAINT `message_templates_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: audit_logs
-- ============================================
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `success` tinyint(1) DEFAULT 1,
  `error_message` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_logs_action_created_at_index` (`action`,`created_at`),
  KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `audit_logs_tenant_id_foreign` (`tenant_id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `audit_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA: beta_testers
-- ============================================
DROP TABLE IF EXISTS `beta_testers`;
CREATE TABLE `beta_testers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `segment` varchar(255) DEFAULT NULL,
  `status` enum('pending','invited','accepted','active','inactive') DEFAULT 'pending',
  `invited_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `discount_percentage` int DEFAULT 50,
  `notes` text,
  `feedback_score` int DEFAULT NULL,
  `referrals_count` int DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `beta_testers_email_unique` (`email`),
  KEY `beta_testers_status_index` (`status`),
  KEY `beta_testers_email_index` (`email`),
  KEY `beta_testers_invited_at_index` (`invited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DADOS DOS SEEDERS
-- ============================================

-- ============================================
-- DADOS: plans
-- ============================================
INSERT INTO `plans` (`id`, `name`, `slug`, `description`, `price`, `currency`, `interval`, `max_whatsapp_instances`, `max_messages_per_month`, `unlimited_messages`, `api_access`, `custom_qr`, `analytics`, `priority_support`, `features`, `active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Básico', 'basic', 'Ideal para pequenos negócios que estão começando', 97.00, 'BRL', 1, 1, 500, 0, 1, 0, 0, 0, '[\"1 instância do WhatsApp\", \"500 mensagens por mês\", \"Acesso à API\", \"Upload de CSV\", \"Templates básicos\", \"Suporte por email\"]', 1, 1, NOW(), NOW()),
(2, 'Pro', 'pro', 'Para empresas em crescimento que precisam de mais recursos', 297.00, 'BRL', 1, 3, 5000, 0, 1, 1, 1, 0, '[\"3 instâncias do WhatsApp\", \"5.000 mensagens por mês\", \"Acesso à API completa\", \"Upload de CSV\", \"Templates avançados\", \"QR personalizado\", \"Analytics e relatórios\", \"Suporte prioritário\"]', 1, 2, NOW(), NOW()),
(3, 'Enterprise', 'enterprise', 'Para grandes empresas com alto volume de mensagens', 997.00, 'BRL', 1, 10, 0, 1, 1, 1, 1, '[\"10 instâncias do WhatsApp\", \"Mensagens ilimitadas\", \"Acesso à API completa\", \"Upload de CSV\", \"Templates avançados\", \"QR personalizado\", \"Analytics avançados\", \"Suporte dedicado 24/7\", \"SLA garantido\", \"Integração customizada\"]', 1, 3, NOW(), NOW());

-- ============================================
-- DADOS: tenants
-- ============================================
INSERT INTO `tenants` (`id`, `name`, `subdomain`, `stripe_customer_id`, `subscription_status`, `evolution_api_key`, `evolution_api_url`, `evolution_instances`, `qr_code_image`, `custom_qr_enabled`, `max_whatsapp_instances`, `max_messages_per_month`, `current_messages_month`, `usage_reset_at`, `active`, `trial_ends_at`, `created_at`, `updated_at`) VALUES
(1, 'Principal', 'principal', NULL, 'active', NULL, NULL, NULL, NULL, 0, 1, 500, 0, NULL, 1, NULL, NOW(), NOW()),
(2, 'Demo Tenant', 'demo', NULL, 'active', NULL, NULL, NULL, NULL, 0, 1, 500, 0, NULL, 1, NULL, NOW(), NOW());

-- ============================================
-- DADOS: tenant_settings
-- ============================================
INSERT INTO `tenant_settings` (`id`, `tenant_id`, `whatsapp_reminder_days_before`, `whatsapp_reminder_on_due_date`, `whatsapp_reminder_days_after`, `whatsapp_enabled`, `default_currency`, `default_due_days`, `created_at`, `updated_at`) VALUES
(1, 1, '[3, 1]', 1, '[1, 3, 7]', 1, 'BRL', 7, NOW(), NOW()),
(2, 2, '[3, 1]', 1, '[1, 3, 7]', 1, 'BRL', 7, NOW(), NOW());

-- ============================================
-- DADOS: users
-- ============================================
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `tenant_id`, `created_at`, `updated_at`) VALUES
(1, 'Admin Principal', 'admin@seucrm.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
(2, 'Admin Demo', 'demo@seucrm.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NOW(), NOW()),
(3, 'Admin', 'admin@cobranca.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW());

-- ============================================
-- DADOS: subscriptions
-- ============================================
INSERT INTO `subscriptions` (`id`, `tenant_id`, `plan_id`, `stripe_subscription_id`, `stripe_customer_id`, `stripe_status`, `status`, `trial_ends_at`, `starts_at`, `ends_at`, `canceled_at`, `current_messages_month`, `current_whatsapp_instances`, `usage_reset_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, 'active', NULL, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), NULL, 0, 0, DATE_FORMAT(NOW(), '%Y-%m-01'), NOW(), NOW()),
(2, 2, 1, NULL, NULL, NULL, 'active', NULL, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), NULL, 0, 0, DATE_FORMAT(NOW(), '%Y-%m-01'), NOW(), NOW());

-- ============================================
-- DADOS: cobrancas
-- ============================================
INSERT INTO `cobrancas` (`id`, `descricao`, `valor`, `status`, `data_vencimento`, `telefone`, `notificacao_whatsapp_status`, `ultimo_envio_whatsapp`, `stripe_customer_id`, `whatsapp_reminders_sent`, `stripe_payment_link`, `tenant_id`, `created_at`, `updated_at`) VALUES
(1, 'Cobrança Cliente A', 150.00, 'pendente', DATE_ADD(NOW(), INTERVAL 7 DAY), '(11) 99999-9999', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(2, 'Cobrança Cliente B', 200.50, 'pago', DATE_ADD(NOW(), INTERVAL 14 DAY), '(21) 88888-8888', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(3, 'Cobrança Cliente C', 75.25, 'pendente', DATE_ADD(NOW(), INTERVAL 21 DAY), '(31) 77777-7777', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(4, 'Cobrança Cliente D', 300.00, 'cancelado', DATE_ADD(NOW(), INTERVAL 30 DAY), '(41) 66666-6666', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(5, 'Cobrança Cliente E', 125.75, 'pendente', DATE_ADD(NOW(), INTERVAL 10 DAY), '(51) 55555-5555', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(6, 'Cobrança Cliente F', 450.00, 'pago', DATE_ADD(NOW(), INTERVAL 5 DAY), '(61) 44444-4444', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(7, 'Cobrança Cliente G', 89.99, 'pendente', DATE_ADD(NOW(), INTERVAL 15 DAY), '(71) 33333-3333', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(8, 'Cobrança Cliente H', 250.00, 'pago', DATE_ADD(NOW(), INTERVAL 20 DAY), '(81) 22222-2222', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(9, 'Cobrança Cliente I', 175.50, 'pendente', DATE_ADD(NOW(), INTERVAL 25 DAY), '(91) 11111-1111', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()),
(10, 'Cobrança Cliente J', 99.00, 'cancelado', DATE_ADD(NOW(), INTERVAL 12 DAY), '(85) 00000-0000', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW());

-- ============================================
-- DADOS: api_keys (exemplo)
-- ============================================
INSERT INTO `api_keys` (`id`, `tenant_id`, `user_id`, `name`, `key`, `prefix`, `abilities`, `rate_limit_per_minute`, `rate_limit_per_hour`, `total_requests`, `last_used_at`, `active`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'API Key Principal', 'cobranca_' SHA2(CONCAT('cobranca_', RAND()), 256), 'cobranca', '[\"*\"]', 60, 1000, 0, NULL, 1, NULL, NOW(), NOW());

-- ============================================
-- FIM DO DUMP
-- ============================================
