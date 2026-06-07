<?php
/**
 * functions.php
 * Domain-level query functions for the news site.
 *
 * Every function accepts a PDO instance as its first argument and
 * returns clean data arrays ready for use in view templates.
 */

// ----------------------------------------------------------------
// News queries
// ----------------------------------------------------------------

/**
 * Retrieve a paginated list of news articles with optional filters.
 *
 * Accepts EITHER the options-array form:
 *     getNews($pdo, ['category_id' => 3, 'limit' => 5])
 *
 * OR the positional form used by older callers:
 *     getNews($pdo, $filters, $limit, $offset)
 *
 * Supported filter keys:
 *   - category_id   (int)
 *   - category_slug (string)
 *   - status        (string, default 'published')
 *   - featured      (bool)
 *   - breaking      (bool)
 *   - search        (string)
 *   - limit         (int, default 10)
 *   - offset        (int, default 0)
 *   - order_by      (whitelisted column)
 *   - order_dir     ('ASC'|'DESC')
 *
 * @return array<int, array<string, mixed>>
 */
function getNews(PDO $pdo, array $options = [], ?int $limit = null, ?int $offset = null): array {
    if ($limit !== null)  $options['limit']  = $limit;
    if ($offset !== null) $options['offset'] = $offset;

    $limit       = (int)  ($options['limit']  ?? 10);
    $offset      = (int)  ($options['offset'] ?? 0);
    $categoryId  = isset($options['category_id']) ? (int) $options['category_id'] : null;
    $categorySlug = isset($options['category_slug']) && $options['category_slug'] !== ''
                    ? (string) $options['category_slug'] : null;
    $status      =        $options['status']    ?? 'published';
    $featured    = (bool) ($options['featured'] ?? false);
    $breaking    = (bool) ($options['breaking'] ?? false);
    $search      =        $options['search']    ?? null;
    $date        =        $options['date']      ?? null;
    $orderDir    = strtoupper($options['order_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $allowedOrderBy = ['published_at', 'created_at', 'views', 'title', 'id'];
    $orderBy        = $options['order_by'] ?? 'published_at';
    if (!in_array($orderBy, $allowedOrderBy, true)) {
        $orderBy = 'published_at';
    }

    [$whereClause, $params] = _buildNewsWhere(
        $status, $categoryId, $categorySlug, $featured, $breaking, $search, $date
    );

    $sql = "
        SELECT
            n.id, n.title, n.slug, n.summary, n.image, n.author,
            n.status, n.is_featured, n.is_breaking, n.views,
            n.published_at, n.created_at,
            c.id   AS category_id,
            c.name AS category_name,
            c.slug AS category_slug
        FROM news n
        INNER JOIN categories c ON c.id = n.category_id
        {$whereClause}
        ORDER BY n.{$orderBy} {$orderDir}
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function countNews(PDO $pdo, array $options = []): int {
    $categoryId   = isset($options['category_id']) ? (int) $options['category_id'] : null;
    $categorySlug = isset($options['category_slug']) && $options['category_slug'] !== ''
                    ? (string) $options['category_slug'] : null;
    $status   =        $options['status']   ?? 'published';
    $featured = (bool) ($options['featured'] ?? false);
    $breaking = (bool) ($options['breaking'] ?? false);
    $search   =        $options['search']   ?? null;
    $date     =        $options['date']     ?? null;

    [$whereClause, $params] = _buildNewsWhere(
        $status, $categoryId, $categorySlug, $featured, $breaking, $search, $date
    );

    $sql  = "SELECT COUNT(*) FROM news n INNER JOIN categories c ON c.id = n.category_id {$whereClause}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

/**
 * Build a shared WHERE clause + bound params for news queries.
 *
 * @return array{0: string, 1: array<string, mixed>}
 */
function _buildNewsWhere(
    string $status,
    ?int $categoryId,
    ?string $categorySlug,
    bool $featured,
    bool $breaking,
    ?string $search,
    ?string $date
): array {
    $where  = ['n.status = :status'];
    $params = [':status' => $status];

    if ($categoryId !== null) {
        $where[] = 'n.category_id = :category_id';
        $params[':category_id'] = $categoryId;
    }

    if ($categorySlug !== null) {
        $where[] = 'c.slug = :category_slug';
        $params[':category_slug'] = $categorySlug;
    }

    if ($featured) $where[] = 'n.is_featured = 1';
    if ($breaking) $where[] = 'n.is_breaking = 1';

    if ($search !== null && $search !== '') {
        $where[] = '(n.title LIKE :search OR n.summary LIKE :search2)';
        $params[':search']  = '%' . $search . '%';
        $params[':search2'] = '%' . $search . '%';
    }

    if ($date !== null && $date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $where[] = 'DATE(COALESCE(n.published_at, n.created_at)) = :date';
        $params[':date'] = $date;
    }

    return ['WHERE ' . implode(' AND ', $where), $params];
}

/**
 * Retrieve a single published article by slug and increment its view counter.
 */
function getArticleBySlug(PDO $pdo, string $slug): ?array {
    $sql = "
        SELECT
            n.id, n.title, n.slug, n.summary, n.body, n.image, n.author,
            n.status, n.is_featured, n.is_breaking, n.views,
            n.published_at, n.created_at, n.updated_at,
            c.id   AS category_id,
            c.name AS category_name,
            c.slug AS category_slug
        FROM news n
        INNER JOIN categories c ON c.id = n.category_id
        WHERE n.slug = :slug
          AND n.status = 'published'
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $article = $stmt->fetch();

    if ($article === false) {
        return null;
    }

    $pdo->prepare("UPDATE news SET views = views + 1 WHERE id = :id")
        ->execute([':id' => $article['id']]);

    $article['views'] = (int) $article['views'] + 1;
    return $article;
}

// ----------------------------------------------------------------
// Category queries
// ----------------------------------------------------------------

function getCategories(PDO $pdo, bool $withCount = false): array {
    $sql = $withCount
        ? "SELECT c.id, c.name, c.slug, COUNT(n.id) AS article_count
           FROM categories c
           LEFT JOIN news n ON n.category_id = c.id AND n.status = 'published'
           GROUP BY c.id, c.name, c.slug
           ORDER BY c.name ASC"
        : "SELECT id, name, slug FROM categories ORDER BY name ASC";

    return $pdo->query($sql)->fetchAll();
}

// ----------------------------------------------------------------
// Specialised news fetchers
// ----------------------------------------------------------------

function getFeaturedNews(PDO $pdo, int $limit = 5): array {
    return getNews($pdo, [
        'featured'  => true,
        'limit'     => $limit,
        'order_by'  => 'published_at',
        'order_dir' => 'DESC',
    ]);
}

function getTodayNews(PDO $pdo, int $limit = 10): array {
    $sql = "
        SELECT
            n.id, n.title, n.slug, n.summary, n.image, n.author,
            n.is_featured, n.is_breaking, n.views, n.published_at, n.created_at,
            c.name AS category_name,
            c.slug AS category_slug
        FROM news n
        INNER JOIN categories c ON c.id = n.category_id
        WHERE n.status = 'published'
          AND DATE(COALESCE(n.published_at, n.created_at)) = CURDATE()
        ORDER BY COALESCE(n.published_at, n.created_at) DESC
        LIMIT :limit
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getBreakingNews(PDO $pdo, int $limit = 5): array {
    return getNews($pdo, [
        'breaking'  => true,
        'limit'     => $limit,
        'order_by'  => 'published_at',
        'order_dir' => 'DESC',
    ]);
}

function getRelatedNews(PDO $pdo, int $categoryId, int $excludeId, int $limit = 4): array {
    if ($categoryId <= 0) return [];

    $sql = "
        SELECT
            n.id, n.title, n.slug, n.summary, n.image, n.author,
            n.published_at, n.created_at, n.views,
            c.name AS category_name,
            c.slug AS category_slug
        FROM news n
        INNER JOIN categories c ON c.id = n.category_id
        WHERE n.status      = 'published'
          AND n.category_id = :category_id
          AND n.id          != :exclude_id
        ORDER BY n.published_at DESC
        LIMIT :limit
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':exclude_id',  $excludeId,  PDO::PARAM_INT);
    $stmt->bindValue(':limit',       $limit,      PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// ----------------------------------------------------------------
// Site content
// ----------------------------------------------------------------

/**
 * Retrieve a single editable content block by its page_key.
 *
 * @return array{title:string, content:string}|null
 */
function getSiteContent(PDO $pdo, string $pageKey): ?array {
    $stmt = $pdo->prepare(
        "SELECT title, content FROM site_content WHERE page_key = :k LIMIT 1"
    );
    $stmt->execute([':k' => $pageKey]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

// ----------------------------------------------------------------
// Admin dashboard statistics
// ----------------------------------------------------------------

function getDashboardStats(PDO $pdo): array {
    $newsCounts = $pdo->query("
        SELECT
            COUNT(*)                          AS total_articles,
            SUM(status = 'published')         AS published_articles,
            SUM(status = 'draft')             AS draft_articles,
            SUM(status = 'archived')          AS archived_articles,
            COALESCE(SUM(views), 0)           AS total_views
        FROM news
    ")->fetch();

    $totalCategories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $unreadContacts  = (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();

    $recentArticles = $pdo->query("
        SELECT n.id, n.title, n.slug, n.status, n.is_featured, n.views,
               n.published_at, n.created_at, c.name AS category_name
        FROM news n
        INNER JOIN categories c ON c.id = n.category_id
        ORDER BY n.created_at DESC
        LIMIT 5
    ")->fetchAll();

    return [
        'total_articles'     => (int) ($newsCounts['total_articles']     ?? 0),
        'published_articles' => (int) ($newsCounts['published_articles'] ?? 0),
        'draft_articles'     => (int) ($newsCounts['draft_articles']     ?? 0),
        'archived_articles'  => (int) ($newsCounts['archived_articles']  ?? 0),
        'total_views'        => (int) ($newsCounts['total_views']        ?? 0),
        'total_categories'   => $totalCategories,
        'unread_contacts'    => $unreadContacts,
        'recent_articles'    => $recentArticles,
    ];
}
