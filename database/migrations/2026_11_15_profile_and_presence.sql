-- Run once in phpMyAdmin on the existing smu_exit_exam database.
-- This adds profile photo storage and lightweight online/away/offline presence.
ALTER TABLE users
  ADD COLUMN profile_photo VARCHAR(255) NULL AFTER last_login,
  ADD COLUMN presence_status ENUM('online','away','offline') NOT NULL DEFAULT 'offline' AFTER profile_photo,
  ADD COLUMN last_seen_at DATETIME NULL AFTER presence_status;
