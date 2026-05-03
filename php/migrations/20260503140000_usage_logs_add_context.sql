-- Voeg context toe aan usage_logs voor feature telemetry
-- context: vrij tekstveld (IP voor ouder events, feature variant naam, ...)
ALTER TABLE usage_logs ADD COLUMN IF NOT EXISTS context VARCHAR(255) NULL DEFAULT NULL;
