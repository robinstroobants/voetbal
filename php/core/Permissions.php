<?php
class Permissions {
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_COACH = 'coach';
    const ROLE_MANAGER = 'manager'; // Afgevaardigde
    const ROLE_PARENT = 'parent';

    // Permissies
    const PERM_MANAGE_GAMES = 'manage_games';
    const PERM_MANAGE_PLAYERS = 'manage_players';
    const PERM_EDIT_SCORES = 'edit_scores';
    const PERM_GENERATE_LINEUPS = 'generate_lineups';
    const PERM_MANAGE_TEAM_SETTINGS = 'manage_team_settings';
    const PERM_VIEW_SYSTEM_LOGS = 'view_system_logs';
    const PERM_MANAGE_TENANTS = 'manage_tenants';
    const PERM_USE_THEORY_WIZARD = 'use_theory_wizard';

    private static $rolePermissions = [
        self::ROLE_SUPERADMIN => [
            // Heeft in theorie alles
            self::PERM_MANAGE_TENANTS,
            self::PERM_VIEW_SYSTEM_LOGS,
            self::PERM_MANAGE_TEAM_SETTINGS,
            self::PERM_MANAGE_PLAYERS,
            self::PERM_MANAGE_GAMES,
            self::PERM_EDIT_SCORES,
            self::PERM_GENERATE_LINEUPS,
            self::PERM_USE_THEORY_WIZARD
        ],
        self::ROLE_ADMIN => [
            self::PERM_MANAGE_TEAM_SETTINGS,
            self::PERM_MANAGE_PLAYERS,
            self::PERM_MANAGE_GAMES,
            self::PERM_EDIT_SCORES,
            self::PERM_GENERATE_LINEUPS,
            self::PERM_USE_THEORY_WIZARD
        ],
        self::ROLE_COACH => [
            self::PERM_MANAGE_TEAM_SETTINGS,
            self::PERM_MANAGE_PLAYERS,
            self::PERM_MANAGE_GAMES,
            self::PERM_EDIT_SCORES,
            self::PERM_GENERATE_LINEUPS
        ],
        self::ROLE_MANAGER => [
            self::PERM_MANAGE_PLAYERS, 
            self::PERM_MANAGE_GAMES,
            self::PERM_GENERATE_LINEUPS
            // Let op: GEEN edit_scores en GEEN manage_team_settings
        ],
        self::ROLE_PARENT => [
            // Read-only
        ]
    ];

    public static function hasPermission($permission) {
        $role = $_SESSION['role'] ?? '';
        $original_role = $_SESSION['original_role'] ?? '';
        
        // Superadmin overrules everything
        if ($role === self::ROLE_SUPERADMIN || $original_role === self::ROLE_SUPERADMIN) {
            return true;
        }
        
        // Beta users get early access to theory wizard
        if ($permission === self::PERM_USE_THEORY_WIZARD && isset($_SESSION['is_beta_user']) && $_SESSION['is_beta_user'] == 1) {
            return true;
        }

        if (isset(self::$rolePermissions[$role])) {
            return in_array($permission, self::$rolePermissions[$role]);
        }

        return false;
    }

    public static function enforce($permission) {
        if (!self::hasPermission($permission)) {
            header("HTTP/1.1 403 Forbidden");
            echo "<h1 style='text-align:center; margin-top: 50px; font-family: sans-serif;'>403 Geen Toegang</h1>";
            echo "<p style='text-align:center;'><a href='/'>Ga terug naar start</a></p>";
            exit;
        }
    }
}
