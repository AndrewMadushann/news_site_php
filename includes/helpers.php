<?php
/**
 * helpers.php
 * General-purpose utility functions for the news site.
 */

// ----------------------------------------------------------------
// Output sanitization
// ----------------------------------------------------------------

function sanitize(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ----------------------------------------------------------------
// Slug generation
// ----------------------------------------------------------------

function createSlug(string $text): string {
    $slug = false;
    if (function_exists('transliterator_transliterate')) {
        $slug = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    }
    if ($slug === false) {
        $slug = strtolower($text);
    }
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

function makeSlug(string $text): string {
    return createSlug($text);
}

function uniqueSlug(PDO $pdo, string $baseSlug, string $table, int $excludeId = 0): string {
    $slug    = $baseSlug;
    $counter = 1;
    while (true) {
        $sql  = "SELECT COUNT(*) FROM `{$table}` WHERE slug = :slug AND id != :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
        if ((int) $stmt->fetchColumn() === 0) {
            break;
        }
        $slug = $baseSlug . '-' . $counter++;
    }
    return $slug;
}

// ----------------------------------------------------------------
// Pagination
// ----------------------------------------------------------------

/**
 * Calculate pagination metadata.
 *
 * Arg order matches every caller in this project: (total, perPage, page).
 *
 * @return array{
 *   total:int, per_page:int, current_page:int, last_page:int,
 *   offset:int, from:int, to:int, has_prev:bool, has_next:bool
 * }
 */
function paginate(int $totalItems, int $perPage = 10, int $currentPage = 1): array {
    $perPage     = max(1, $perPage);
    $totalItems  = max(0, $totalItems);
    $lastPage    = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = min(max(1, $currentPage), $lastPage);
    $offset      = ($currentPage - 1) * $perPage;
    $from        = $totalItems === 0 ? 0 : $offset + 1;
    $to          = min($offset + $perPage, $totalItems);

    return [
        'total'        => $totalItems,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'last_page'    => $lastPage,
        'offset'       => $offset,
        'from'         => $from,
        'to'           => $to,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $lastPage,
    ];
}

// ----------------------------------------------------------------
// CSRF protection
// ----------------------------------------------------------------

function generateCsrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (!hash_equals($sessionToken, $token)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}

// ----------------------------------------------------------------
// Date / time helpers
// ----------------------------------------------------------------

/**
 * Human-readable "time ago" string.
 */
function timeAgo(?string $datetime): string {
    if (empty($datetime)) {
        return '';
    }
    try {
        $now  = new DateTimeImmutable();
        $then = new DateTimeImmutable($datetime);
    } catch (Exception) {
        return '';
    }
    $diff = $now->diff($then);

    if ($diff->y > 0) return $diff->y . ' year'   . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month'  . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'    . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'   . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * Format a MySQL datetime as a short display date, e.g. "Jun 6, 2026".
 * Returns an empty string for null/empty/invalid input.
 */
function formatDate(?string $datetime, string $format = 'M j, Y'): string {
    if (empty($datetime)) {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts === false ? '' : date($format, $ts);
}

// ----------------------------------------------------------------
// Asset / upload path resolver
// ----------------------------------------------------------------

/**
 * Resolve a path stored in the database to a web-relative URL.
 *
 * Handles three input shapes:
 *  - Full URL ("http://..." / "https://..." / "//..."): returned as-is.
 *  - Path containing a slash (e.g. "assets/images/seed/foo.jpg"): returned
 *    with a leading "/".
 *  - Bare filename (e.g. "foo.jpg"): prepended with $uploadDir
 *    (default "/uploads/images").
 *
 * Returns null if the input is empty or a placeholder ("#").
 */
function assetPath(?string $stored, string $uploadDir = '/uploads/images'): ?string {
    $stored = trim((string) $stored);
    if ($stored === '' || $stored === '#') {
        return null;
    }
    if (preg_match('#^(https?:)?//#', $stored)) {
        return $stored;
    }
    if (str_contains($stored, '/')) {
        return '/' . ltrim($stored, '/');
    }
    return rtrim($uploadDir, '/') . '/' . $stored;
}

// ----------------------------------------------------------------
// String helpers
// ----------------------------------------------------------------

function truncate(string $text, int $limit = 150, string $ending = '...'): string {
    $clean = strip_tags($text);
    if (mb_strlen($clean, 'UTF-8') <= $limit) {
        return $clean;
    }
    return rtrim(mb_substr($clean, 0, $limit, 'UTF-8')) . $ending;
}

// ----------------------------------------------------------------
// Flash messages
// ----------------------------------------------------------------

function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ----------------------------------------------------------------
// Pagination renderer
// ----------------------------------------------------------------

function renderPagination(array $pagination, string $baseUrl, array $extraParams = []): string {
    if (($pagination['last_page'] ?? 1) <= 1) {
        return '';
    }

    $current = (int) $pagination['current_page'];
    $last    = (int) $pagination['last_page'];
    $html    = '<nav aria-label="Pagination" class="flex items-center justify-center space-x-1 mt-8">';

    $buildUrl = static function (int $page) use ($baseUrl, $extraParams): string {
        $params         = $extraParams;
        $params['page'] = $page;
        $qs             = http_build_query($params);
        $sep            = str_contains($baseUrl, '?') ? '&' : '?';
        return htmlspecialchars($baseUrl . $sep . $qs, ENT_QUOTES, 'UTF-8');
    };

    $btnBase     = 'px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 ';
    $btnActive   = $btnBase . 'bg-red-600 text-white cursor-default';
    $btnInactive = $btnBase . 'bg-white text-gray-700 border border-gray-300 hover:bg-red-50 hover:text-red-600';
    $btnDisabled = $btnBase . 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed';

    if (!empty($pagination['has_prev'])) {
        $html .= '<a href="' . $buildUrl($current - 1) . '" class="' . $btnInactive . '" aria-label="Previous page">&laquo; Prev</a>';
    } else {
        $html .= '<span class="' . $btnDisabled . '" aria-disabled="true">&laquo; Prev</span>';
    }

    $window = 2;
    $start  = max(1, $current - $window);
    $end    = min($last, $current + $window);

    if ($start > 1) {
        $html .= '<a href="' . $buildUrl(1) . '" class="' . $btnInactive . '">1</a>';
        if ($start > 2) $html .= '<span class="' . $btnDisabled . '">…</span>';
    }

    for ($page = $start; $page <= $end; $page++) {
        $html .= $page === $current
            ? '<span class="' . $btnActive . '" aria-current="page">' . $page . '</span>'
            : '<a href="' . $buildUrl($page) . '" class="' . $btnInactive . '">' . $page . '</a>';
    }

    if ($end < $last) {
        if ($end < $last - 1) $html .= '<span class="' . $btnDisabled . '">…</span>';
        $html .= '<a href="' . $buildUrl($last) . '" class="' . $btnInactive . '">' . $last . '</a>';
    }

    if (!empty($pagination['has_next'])) {
        $html .= '<a href="' . $buildUrl($current + 1) . '" class="' . $btnInactive . '" aria-label="Next page">Next &raquo;</a>';
    } else {
        $html .= '<span class="' . $btnDisabled . '" aria-disabled="true">Next &raquo;</span>';
    }

    return $html . '</nav>';
}
