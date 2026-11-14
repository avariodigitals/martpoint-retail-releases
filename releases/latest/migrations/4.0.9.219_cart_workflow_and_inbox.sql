-- Retain storefront carts and inbox history; add workflow dispatch bindings only.
CREATE TABLE IF NOT EXISTS db_communication_cart_sends (
 send_id INT NOT NULL PRIMARY KEY,
 store_id INT NOT NULL,
 cart_id INT NOT NULL,
 reminder_slot INT NOT NULL,
 claimed_at DATETIME NULL,
 UNIQUE KEY cart_slot (store_id,cart_id,reminder_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_inbox_replies (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 conversation_id INT NOT NULL,
 request_key CHAR(40) NOT NULL,
 payload_hash CHAR(64) NOT NULL,
 status VARCHAR(15) NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL,
 UNIQUE KEY reply_replay (store_id,request_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_auto_templates
 (template_key,name,description,segment_key,subject,message,interval_days,enabled)
 VALUES ('abandoned_cart','Recover abandoned carts','Follow up on eligible online-store carts. Stops after a purchase or opt-out. Up to three reminders, at least 24 hours apart.','abandoned_carts','You left items in your cart','Hi {name}, you left items at {store}. Continue your checkout: {cart_url}',1,1);
-- Template access must be explicitly assigned to a plan by the administrator.

-- Inbox content must not share a table with legacy billing messages.
CREATE TABLE IF NOT EXISTS db_messaging_conversations (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 channel VARCHAR(20) NOT NULL DEFAULT 'whatsapp',
 external_conversation_id VARCHAR(255) NULL,
 customer_phone VARCHAR(50) NULL,
 customer_name VARCHAR(255) NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'open',
 last_message_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY store_channel (store_id,channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_inbox_messages (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 conversation_id INT NOT NULL,
 direction VARCHAR(10) NOT NULL DEFAULT 'inbound',
 message_type VARCHAR(20) NOT NULL DEFAULT 'text',
 body TEXT NULL,
 meta_message_id VARCHAR(255) NULL,
 status VARCHAR(15) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY conversation_message (conversation_id,id),
 KEY provider_message (meta_message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET @copy_inbox := IF(
 (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_messaging_messages' AND column_name IN ('id','conversation_id','direction','message_type','body','meta_message_id','status','created_at'))=8
 AND (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_messaging_messages' AND column_name='account_id')=0,
 'INSERT IGNORE INTO db_communication_inbox_messages (id,conversation_id,direction,message_type,body,meta_message_id,status,created_at) SELECT id,conversation_id,direction,message_type,body,meta_message_id,status,created_at FROM db_messaging_messages', 'SELECT 1');
PREPARE inbox_copy FROM @copy_inbox;
EXECUTE inbox_copy;
DEALLOCATE PREPARE inbox_copy;
