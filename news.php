<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/functions.php';

$catSlug = filter_input(INPUT_GET, 'cat',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$date    = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$search  = filter_input(INPUT_GET, 'q',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$filters = array_filter([
    'category_slug' => $catSlug,
    'date'          => $date,
    'search'        => $search,
], static fn($v) => $v !== '' && $v !== null);

try {
    $total      = countNews($pdo, $filters);
    $pag        = paginate($total, $perPage, $page);
    $articles   = getNews($pdo, $filters + ['limit' => $perPage, 'offset' => $pag['offset']]);
    $categories = getCategories($pdo);
} catch (PDOException $e) {
    error_log('[news.php] ' . $e->getMessage());
    $total      = 0;
    $pag        = paginate(0, $perPage, 1);
    $articles   = [];
    $categories = [];
}

// Active category label
$activeCatName = 'All News';
foreach ($categories as $c) {
    if ($c['slug'] === $catSlug) {
        $activeCatName = $c['name'] . ' News';
        break;
    }
}

$pageTitle = $catSlug ? ucfirst($catSlug) . ' News' : 'All News';
$metaDesc  = 'Browse all news articles' . ($catSlug ? " in {$catSlug}" : '') . '.';
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ========================================================
     PAGE HEADER + FILTER BAR
     ======================================================== -->
<div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- Heading + count -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($activeCatName) ?></h1>
                <?php if ($total > 0): ?>
                <p class="text-sm text-gray-500 mt-0.5"><?= number_format($total) ?> articles found</p>
                <?php endif; ?>
            </div>
            <!-- Search form -->
            <form method="GET" action="news.php" class="flex items-center gap-2">
                <?php if ($catSlug): ?>
                <input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>">
                <?php endif; ?>
                <input
                    type="date"
                    name="date"
                    value="<?= htmlspecialchars($date) ?>"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search news…"
                        class="border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent w-48 sm:w-64"
                    >
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    Go
                </button>
                <?php if ($search || $date || $catSlug): ?>
                <a href="news.php" class="text-gray-500 hover:text-red-600 text-sm font-medium transition px-2">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Category pills -->
        <div class="flex flex-wrap gap-2">
            <a href="news.php<?= $search ? '?q='.urlencode($search) : '' ?>"
               class="px-4 py-1.5 rounded-full text-xs font-semibold transition
                      <?= empty($catSlug) ? 'bg-red-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-red-600' ?>">
                All
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="news.php?cat=<?= urlencode($cat['slug']) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
               class="px-4 py-1.5 rounded-full text-xs font-semibold transition
                      <?= $catSlug === $cat['slug'] ? 'bg-red-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-red-600' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ========================================================
     MAIN CONTENT
     ======================================================== -->
<main class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if (!empty($articles)): ?>
        <!-- Results info -->
        <p class="text-sm text-gray-500 mb-6">
            Showing
            <span class="font-semibold text-gray-700">
                <?= number_format($pag['from']) ?>–<?= number_format(min($pag['to'], $total)) ?>
            </span>
            of
            <span class="font-semibold text-gray-700"><?= number_format($total) ?></span>
            articles
        </p>

        <!-- News grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php foreach ($articles as $article): ?>
            <a href="article.php?slug=<?= htmlspecialchars($article['slug']) ?>"
               class="news-card bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group flex flex-col border border-gray-100">
                <!-- Image -->
                <div class="relative overflow-hidden aspect-video">
                    <img
                        src="<?= htmlspecialchars($article['image'] ?? 'assets/images/placeholder.jpg') ?>"
                        alt="<?= htmlspecialchars($article['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    >
                    <?php if (!empty($article['category_name'])): ?>
                    <span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded shadow">
                        <?= htmlspecialchars($article['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($article['is_breaking'])): ?>
                    <span class="absolute top-3 right-3 bg-yellow-400 text-black text-[10px] font-bold px-2 py-0.5 rounded">
                        Breaking
                    </span>
                    <?php endif; ?>
                </div>
                <!-- Body -->
                <div class="p-5 flex flex-col flex-1">
                    <h2 class="text-gray-900 font-semibold text-base leading-snug line-clamp-2 group-hover:text-red-600 transition-colors mb-2">
                        <?= htmlspecialchars($article['title']) ?>
                    </h2>
                    <?php if (!empty($article['summary'])): ?>
                    <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 flex-1">
                        <?= htmlspecialchars($article['summary']) ?>
                    </p>
                    <?php endif; ?>
                    <!-- Footer -->
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <?= htmlspecialchars(formatDate($article['published_at'] ?? $article['created_at'])) ?>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <?= number_format((int)($article['views'] ?? 0)) ?>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php
        $baseUrl = 'news.php?';
        $params = [];
        if ($catSlug)  $params[] = 'cat='  . urlencode($catSlug);
        if ($date)     $params[] = 'date=' . urlencode($date);
        if ($search)   $params[] = 'q='    . urlencode($search);
        $baseUrl .= implode('&', $params);
        if (!empty($params)) $baseUrl .= '&';
        echo renderPagination($pag, $baseUrl);
        ?>

        <?php else: ?>
        <!-- Empty state -->
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-24 h-24 rounded-full bg-red-50 flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">No Articles Found</h2>
            <p class="text-gray-500 text-sm mb-6 max-w-sm">
                We couldn't find any articles matching your search. Try different keywords or browse all news.
            </p>
            <a href="news.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                Browse All News
            </a>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
