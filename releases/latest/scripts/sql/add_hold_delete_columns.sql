-- Add hold_delete approval columns to existing db_approval_settings table
-- Run this in phpMyAdmin SQL tab for your martpoint database

ALTER TABLE db_approval_settings
ADD COLUMN hold_delete_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN hold_delete_approval_method VARCHAR(30) NOT NULL DEFAULT 'none';
