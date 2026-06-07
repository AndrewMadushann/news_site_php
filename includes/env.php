<?php
/**
 * env.php
 *
 * Minimal .env loader — reads simple KEY=value lines, ignores comments
 * and blank lines, and exposes values via getenv() / $_ENV / $_SERVER.
 *
 * Why a custom loader?
 *   • Zero Composer dependency — works in plain XAMPP/MAMP/CLI installs.
 *   • Loaded once at bootstrap; cached at the process level.
 *
 * Safety:
 *   • Existing environment variables (real env vars) take precedence over
 *     values in .env, so production deploys can override without editing
 *     the file.
 *   • The .env file should be excluded from version control (.gitignore).
 */

declare(strict_types=1);

if (!function_exists('loadEnv')) {

    function loadEnv(string $path): void {
        static $loaded = [];
        if (isset($loaded[$path]) || !is_readable($path)) {
            return;
        }
        $loaded[$path] = true;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eq));
            $val = trim(substr($line, $eq + 1));

            // Strip surrounding quotes if present.
            if (
                (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))
            ) {
                $val = substr($val, 1, -1);
            }

            // Don't overwrite existing real environment variables.
            if (getenv($key) !== false) {
                continue;
            }

            putenv("{$key}={$val}");
            $_ENV[$key]    = $val;
            $_SERVER[$key] = $val;
        }
    }

    /**
     * Retrieve an env value with a default fallback.
     */
    function env(string $key, string|int|null $default = null): string|int|null {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }
}

// Auto-load .env from the project root on include.
loadEnv(dirname(__DIR__) . '/.env');
