-- Voeg context toe aan usage_logs voor feature telemetry
-- context: vrij tekstveld (IP voor ouder events, feature variant naam, ...)
-- MySQL-compatible (geen IF NOT EXISTS ondersteuning in oudere versies)
ALTER TABLE usage_logs ADD COLUMN context VARCHAR(255) NULL DEFAULT NULL;
