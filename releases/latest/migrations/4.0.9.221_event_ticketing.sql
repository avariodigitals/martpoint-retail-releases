-- ============================================================
-- 4.0.9.221 — Event Ticketing
-- ============================================================
-- Sell tickets to dated events through the online store and the till.
--
-- An Event is a first-class offering that rides the EXISTING item pipeline:
-- a ticket product is a db_items row with product_type='event' (the column is
-- VARCHAR(20), so no widening is needed — 'event' joins 'digital'/'course'/
-- 'membership' as a non-physical type). Because every storefront theme and
-- uploaded HTML theme already renders standardized db_items rows, tickets are
-- purchasable in ALL templates without per-theme code.
--
--   db_events          event master, linked to its ticket item via item_id
--   db_event_sessions  one sellable date/time with a seat capacity
--   db_event_tickets   one row PER SEAT issued at fulfilment, code + status
--
-- Seats are counted per session (sold vs capacity); db_items.stock is left
-- alone for event items, so warehouse stock logic never sees a ticket.
-- Idempotent: every statement is guarded, safe to re-run.

-- ------------------------------------------------------------
-- db_events
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    item_id INT NULL DEFAULT NULL COMMENT 'ticket product in db_items (product_type=event)',
    event_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    venue VARCHAR(200) NULL,
    starts_on DATE NULL,
    ends_on DATE NULL,
    status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active 0=archived',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_status (store_id, status),
    UNIQUE INDEX uniq_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- db_event_sessions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_event_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    event_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    capacity INT NOT NULL DEFAULT 100 COMMENT 'total seats this session sells',
    sold INT NOT NULL DEFAULT 0 COMMENT 'seats committed by paid/pending orders',
    status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=open 0=closed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event (event_id),
    INDEX idx_store_date (store_id, session_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- db_event_tickets — one row per seat, so batch purchases still
-- check in seat-by-seat at the door.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_event_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    event_id INT NOT NULL,
    session_id INT NOT NULL,
    ticket_code VARCHAR(32) NOT NULL,
    holder_name VARCHAR(200) NULL,
    holder_phone VARCHAR(50) NULL,
    holder_email VARCHAR(200) NULL,
    qty_seats INT NOT NULL DEFAULT 1,
    source ENUM('online','pos') NOT NULL DEFAULT 'online',
    online_order_id INT NULL DEFAULT NULL,
    order_line_id INT NULL DEFAULT NULL,
    sale_id INT NULL DEFAULT NULL,
    status ENUM('sold','redeemed','void') NOT NULL DEFAULT 'sold',
    redeemed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX uniq_code (ticket_code),
    INDEX idx_session_status (session_id, status),
    INDEX idx_order (online_order_id),
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Order lines: allow 'event' in the item_type ENUM and carry the
-- chosen session on the selling line. Fresh installs may not have
-- db_online_order_items yet (it is created by the 4.0.2 chain);
-- register the migration after the storefront chain in version
-- order, and guard on table existence regardless.
-- ------------------------------------------------------------
SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_order_items');

SET @s = IF(@t>0,'ALTER TABLE db_online_order_items
  MODIFY COLUMN item_type ENUM(''product'',''service'',''digital'',''course'',''membership'',''bundle'',''bundle_component'',''addon'',''event'') NOT NULL DEFAULT ''product''','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='event_session_id');
SET @s = IF(@t>0, IF(@c=0,'ALTER TABLE db_online_order_items ADD COLUMN event_session_id INT NULL','SELECT 1'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Guard so cancel/expiry releases an event line's seats exactly once, the
-- same job stock_state does for physical stock (event lines never touch it).
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='seats_released');
SET @s = IF(@t>0, IF(@c=0,'ALTER TABLE db_online_order_items ADD COLUMN seats_released TINYINT(1) NOT NULL DEFAULT 0','SELECT 1'), 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ------------------------------------------------------------
-- product_type comment: document 'event' alongside the existing
-- virtual types (column is VARCHAR(20); no type change needed).
-- ------------------------------------------------------------
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_items' AND COLUMN_NAME='product_type');
SET @s = IF(@c>0,'ALTER TABLE `db_items` MODIFY COLUMN `product_type` VARCHAR(20) NOT NULL DEFAULT ''physical'' COMMENT ''physical | service | digital | course | membership | event''','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
