-- Add email verification columns
ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER password_hash;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL AFTER is_verified;

-- Mark all existing users as verified to avoid breaking current logins
UPDATE users SET is_verified = 1;
