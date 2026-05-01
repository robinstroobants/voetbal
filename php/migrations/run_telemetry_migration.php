<?php
/**
 * Eenmalige migratie: voeg nieuwe telemetry kolommen toe.
 * Verwijder dit bestand na uitvoering.
 */
require_once dirname(__DIR__) . '/core/getconn.php';

$migrations = [
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS page VARCHAR(100) NULL",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS page_load_ms INT DEFAULT 0",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS php_time_ms FLOAT DEFAULT 0",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS php_memory_mb FLOAT DEFAULT 0",
    "ALTER TABLE client_telemetry ADD COLUMN IF NOT EXISTS identifier_full VARCHAR(255) NULL",
];

$results = [];
foreach ($migrations as $sql) {
    try {
        $pdo->exec($sql);
        $results[] = "✅ OK: $sql";
    } catch (Exception $e) {
        $results[] = "⚠️ SKIP (" . $e->getMessage() . "): $sql";
    }
}

echo implode("\n", $results) . "\n\nKlaar!";
