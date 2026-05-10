-- ============================================================
-- CRITICAL PERFORMANCE FIX — Run on ALL production servers
-- ============================================================
-- Date: 2026-05-10
-- Priority: #1 on Jo (actively slow), preventive on others
--
-- These are non-blocking ADD INDEX on tables under 300K rows.
-- Safe to run during live traffic. No downtime needed.
--
-- Expected Impact on Jo:
--   - 2.5 min query → under 50ms
--   - 1-4 sec queries → under 100ms
-- ============================================================

-- ──────────────────────────────────────────────────────────────
-- 1. official_messages (120K rows on Jo)
--    Fixes: 2 min 22 sec COUNT query, 4 sec SELECT queries
--    Root cause: full-scans 120K rows on every user_id + type filter
-- ──────────────────────────────────────────────────────────────
ALTER TABLE `official_messages`
  ADD INDEX `idx_om_user_type_created` (`user_id`, `type`, `created_at`);

ALTER TABLE `official_messages`
  ADD INDEX `idx_om_type_feature` (`type`, `feature`);

-- ──────────────────────────────────────────────────────────────
-- 2. user_official_messages (276K rows on Jo)
--    Fixes: subquery join in the 2.5-min query
--    Root cause: ZERO indexes beyond PK, every join full-scans
-- ──────────────────────────────────────────────────────────────
ALTER TABLE `user_official_messages`
  ADD INDEX `idx_uom_msg_user` (`official_message_id`, `user_id`);

-- ──────────────────────────────────────────────────────────────
-- 3. live_times (79K rows on Jo)
--    Fixes: 1.4 sec query on every room entry
--    Root cause: ZERO indexes beyond PK
-- ──────────────────────────────────────────────────────────────
ALTER TABLE `live_times`
  ADD INDEX `idx_lt_uid_created` (`uid`, `created_at`);

-- ──────────────────────────────────────────────────────────────
-- 4. gift_logs (150K rows on Jo)
--    Fixes: 3.9 sec CP lovely ranking queries
--    Root cause: no index on cp_id column
-- ──────────────────────────────────────────────────────────────
ALTER TABLE `gift_logs`
  ADD INDEX `idx_gl_cp_id_created` (`cp_id`, `created_at`);

-- ──────────────────────────────────────────────────────────────
-- 5. bd_agency_host_sallaries (2,579 rows on Rixo)
--    Fixes: agencies/* endpoints taking 1-8.7 seconds
--    Root cause: ZERO indexes beyond PK, joins full-scan every time
-- ──────────────────────────────────────────────────────────────
ALTER TABLE `bd_agency_host_sallaries`
  ADD INDEX `idx_bahs_agency_created` (`agency_id`, `created_at`);

ALTER TABLE `bd_agency_host_sallaries`
  ADD INDEX `idx_bahs_bd_id` (`bd_id`);

ALTER TABLE `bd_agency_host_sallaries`
  ADD INDEX `idx_bahs_bd_year_amount` (`bd_id`, `year`, `amount`);

-- ============================================================
-- VERIFICATION — Run after applying indexes to confirm
-- ============================================================
-- Check that indexes were created:
SHOW INDEX FROM `official_messages` WHERE Key_name LIKE 'idx_om%';
SHOW INDEX FROM `user_official_messages` WHERE Key_name LIKE 'idx_uom%';
SHOW INDEX FROM `live_times` WHERE Key_name LIKE 'idx_lt%';
SHOW INDEX FROM `gift_logs` WHERE Key_name LIKE 'idx_gl%';
SHOW INDEX FROM `bd_agency_host_sallaries` WHERE Key_name LIKE 'idx_bahs%';
