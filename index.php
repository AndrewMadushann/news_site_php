<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/functions.php';

try {
    $featured   = getFeaturedNews($pdo, 5);
    $todayNews  = getTodayNews($pdo, 8);
    $latest     = getNews($pdo, ['limit' => 6]);
    $categories = getCategories($pdo);

    $categoryRows = [];
    foreach ($categories as $cat) {
        $rows = getNews($pdo, ['category_slug' => $cat['slug'], 'limit' => 3]);
        if (!empty($rows)) {
            $categoryRows[$cat['slug']] = [
                'name'     => $cat['name'],
                'slug'     => $cat['slug'],
                'articles' => $rows,
            ];
        }
    }
} catch (PDOException $e) {
    error_log('[index.php] ' . $e->getMessage());
    $featured = $todayNews = $latest = $categories = $categoryRows = [];
}

$pageTitle = 'Home';
$metaDesc = 'Daily News - Your trusted source for breaking news, politics, sports, business, technology and more.';
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ========================================================
     HERO SECTION
     ======================================================== -->
<section class="relative w-full bg-gray-900">
<?php if (!empty($featured)): ?>
    <?php $main = $featured[0]; ?>
    <?php if (count($featured) >= 4): ?>
    <!-- Two-column layout: main (2/3) + side stack (1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 min-h-[520px]">
        <!-- Main featured article -->
        <a href="article.php?slug=<?= htmlspecialchars($main['slug']) ?>"
           class="relative col-span-2 block group overflow-hidden">
            <img
                src="<?= htmlspecialchars($main['image'] ?? 'assets/images/placeholder.jpg') ?>"
                alt="<?= htmlspecialchars($main['title']) ?>"
                class="w-full h-full object-cover min-h-[520px] group-hover:scale-105 transition-transform duration-700"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-8">
                <?php if (!empty($main['category_name'])): ?>
                <span class="inline-block bg-red-600 text-white text-xs font-bold uppercase tracking-widest px-3 py-1 rounded mb-3">
                    <?= htmlspecialchars($main['category_name']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($main['is_breaking'])): ?>
                <span class="inline-block bg-yellow-400 text-black text-xs font-bold uppercase tracking-widest px-3 py-1 rounded mb-3 ml-2 animate-pulse">
                    🔴 Breaking
                </span>
                <?php endif; ?>
                <h1 class="font-serif text-3xl lg:text-4xl font-bold text-white leading-tight mb-3 group-hover:text-red-300 transition-colors">
                    <?= htmlspecialchars($main['title']) ?>
                </h1>
                <?php if (!empty($main['summary'])): ?>
                <p class="text-gray-300 text-sm leading-relaxed line-clamp-2 mb-4 max-w-2xl">
                    <?= htmlspecialchars($main['summary']) ?>
                </p>
                <?php endif; ?>
                <span class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                    Read More
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </span>
            </div>
        </a>
        <!-- Side stack: articles 1-3 -->
        <div class="flex flex-col divide-y divide-gray-700 bg-gray-900">
            <?php foreach (array_slice($featured, 1, 3) as $side): ?>
            <a href="article.php?slug=<?= htmlspecialchars($side['slug']) ?>"
               class="flex items-center gap-3 p-4 hover:bg-gray-800 transition group flex-1">
                <div class="flex-shrink-0 w-24 h-20 overflow-hidden rounded-lg">
                    <img
                        src="<?= htmlspecialchars($side['image'] ?? 'assets/images/placeholder.jpg') ?>"
                        alt="<?= htmlspecialchars($side['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    >
                </div>
                <div class="min-w-0 flex-1">
                    <?php if (!empty($side['category_name'])): ?>
                    <span class="text-red-400 text-[10px] font-bold uppercase tracking-wider">
                        <?= htmlspecialchars($side['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <h3 class="text-white text-sm font-semibold leading-snug line-clamp-3 group-hover:text-red-300 transition-colors mt-1">
                        <?= htmlspecialchars($side['title']) ?>
                    </h3>
                    <p class="text-gray-400 text-xs mt-1">
                        <?= htmlspecialchars(timeAgo($side['published_at'] ?? $side['created_at'])) ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
            <!-- Bottom CTA -->
            <div class="p-4 flex items-center justify-center">
                <a href="news.php" class="text-red-400 hover:text-red-300 text-sm font-semibold flex items-center gap-1 transition">
                    View All News
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Single featured article (full-width) -->
    <a href="article.php?slug=<?= htmlspecialchars($main['slug']) ?>"
       class="relative block group overflow-hidden min-h-[480px]">
        <img
            src="<?= htmlspecialchars($main['image'] ?? 'assets/images/placeholder.jpg') ?>"
            alt="<?= htmlspecialchars($main['title']) ?>"
            class="w-full h-[480px] object-cover group-hover:scale-105 transition-transform duration-700"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
        <div class="absolute bottom-0 left-0 right-0 max-w-4xl mx-auto px-6 pb-12">
            <?php if (!empty($main['category_name'])): ?>
            <span class="inline-block bg-red-600 text-white text-xs font-bold uppercase tracking-widest px-3 py-1 rounded mb-3">
                <?= htmlspecialchars($main['category_name']) ?>
            </span>
            <?php endif; ?>
            <h1 class="font-serif text-4xl lg:text-5xl font-bold text-white leading-tight mb-3">
                <?= htmlspecialchars($main['title']) ?>
            </h1>
            <?php if (!empty($main['summary'])): ?>
            <p class="text-gray-300 text-base leading-relaxed line-clamp-2 mb-5 max-w-3xl">
                <?= htmlspecialchars($main['summary']) ?>
            </p>
            <?php endif; ?>
            <span class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-6 py-3 rounded-lg transition">
                Read More
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </span>
        </div>
    </a>
    <?php endif; ?>
<?php else: ?>
    <!-- Placeholder hero if no featured articles -->
    <div class="min-h-[420px] bg-gradient-to-br from-red-900 via-gray-900 to-black flex items-center justify-center">
        <div class="text-center px-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-600/20 mb-6">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
            </div>
            <h2 class="font-serif text-4xl font-bold text-white mb-3">Welcome to Daily News</h2>
            <p class="text-gray-400 text-lg mb-6">Your trusted source for breaking news and in-depth coverage.</p>
            <a href="news.php" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-lg transition">
                Browse All News
            </a>
        </div>
    </div>
<?php endif; ?>
</section>

<!-- ========================================================
     TODAY'S NEWS SECTION
     ======================================================== -->
<section class="bg-gray-50 border-t border-b border-gray-200 py-10">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Heading -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 bg-red-600 rounded-full"></div>
                <h2 class="text-xl font-bold text-gray-900">
                    Today —
                    <span class="text-red-600"><?= date('F j, Y') ?></span>
                </h2>
            </div>
            <a href="news.php?date=<?= date('Y-m-d') ?>" class="text-red-600 hover:text-red-700 text-sm font-semibold flex items-center gap-1 transition">
                See All
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <?php if (!empty($todayNews)): ?>
        <!-- Scrollable row -->
        <div class="flex gap-5 overflow-x-auto pb-3 scrollbar-hide -mx-1 px-1"
             style="scrollbar-width:none; -ms-overflow-style:none;">
            <?php foreach ($todayNews as $t): ?>
            <a href="article.php?slug=<?= htmlspecialchars($t['slug']) ?>"
               class="flex-shrink-0 w-60 bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                <div class="relative h-36 overflow-hidden">
                    <img
                        src="<?= htmlspecialchars($t['image'] ?? 'assets/images/placeholder.jpg') ?>"
                        alt="<?= htmlspecialchars($t['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    >
                    <?php if (!empty($t['category_name'])): ?>
                    <span class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded">
                        <?= htmlspecialchars($t['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($t['is_breaking'])): ?>
                    <span class="absolute top-2 right-2 bg-yellow-400 text-black text-[10px] font-bold px-2 py-0.5 rounded animate-pulse">
                        LIVE
                    </span>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <h3 class="text-gray-900 text-sm font-semibold leading-snug line-clamp-3 group-hover:text-red-600 transition-colors">
                        <?= htmlspecialchars($t['title']) ?>
                    </h3>
                    <p class="text-gray-400 text-xs mt-2">
                        <?= htmlspecialchars(timeAgo($t['published_at'] ?? $t['created_at'])) ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-10 text-gray-400">
            <p class="text-sm">No articles published today yet. Check back later.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ========================================================
     LATEST NEWS GRID
     ======================================================== -->
<section class="py-14 bg-white">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section heading -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 bg-red-600 rounded-full"></div>
                <h2 class="text-2xl font-bold text-gray-900">Latest News</h2>
            </div>
            <a href="news.php" class="text-red-600 hover:text-red-700 text-sm font-semibold flex items-center gap-1 transition">
                View All →
            </a>
        </div>

        <?php if (!empty($latest)): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($latest as $article): ?>
            <a href="article.php?slug=<?= htmlspecialchars($article['slug']) ?>"
               class="news-card rounded-xl overflow-hidden bg-white shadow-sm hover:shadow-lg transition-all duration-300 group flex flex-col border border-gray-100">
                <!-- Image -->
                <div class="relative h-48 overflow-hidden">
                    <img
                        src="<?= htmlspecialchars($article['image'] ?? 'assets/images/placeholder.jpg') ?>"
                        alt="<?= htmlspecialchars($article['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    >
                    <?php if (!empty($article['category_name'])): ?>
                    <span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded">
                        <?= htmlspecialchars($article['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($article['is_breaking'])): ?>
                    <span class="absolute top-3 right-3 bg-yellow-400 text-black text-[10px] font-bold px-2 py-0.5 rounded">
                        Breaking
                    </span>
                    <?php endif; ?>
                </div>
                <!-- Card body -->
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-gray-900 font-semibold text-base leading-snug line-clamp-2 group-hover:text-red-600 transition-colors mb-2">
                        <?= htmlspecialchars($article['title']) ?>
                    </h3>
                    <?php if (!empty($article['summary'])): ?>
                    <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 flex-1">
                        <?= htmlspecialchars($article['summary']) ?>
                    </p>
                    <?php endif; ?>
                    <!-- Footer -->
                    <div class="mt-4 flex items-center justify-between text-xs text-gray-400 border-t border-gray-100 pt-3">
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
        <?php else: ?>
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            <p>No articles available yet.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ========================================================
     CATEGORY SECTIONS
     ======================================================== -->
<?php
$bgAlternate = ['bg-gray-50', 'bg-white'];
$catIndex = 0;
foreach (array_slice($categoryRows, 0, 4) as $slug => $catData):
    $bg = $bgAlternate[$catIndex % 2];
    $catIndex++;
?>
<section class="<?= $bg ?> py-12 border-t border-gray-100">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section heading -->
        <div class="flex items-center justify-between mb-7">
            <div>
                <h2 class="text-xl font-bold text-gray-900 relative inline-block">
                    <?= htmlspecialchars($catData['name']) ?>
                    <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-red-600 rounded-full"></span>
                </h2>
            </div>
            <a href="news.php?cat=<?= htmlspecialchars($catData['slug']) ?>"
               class="text-red-600 hover:text-red-700 text-sm font-semibold transition flex items-center gap-1">
                See More →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($catData['articles'] as $ca): ?>
            <a href="article.php?slug=<?= htmlspecialchars($ca['slug']) ?>"
               class="flex gap-4 items-start group bg-white hover:bg-red-50 rounded-xl p-3 transition-colors border border-transparent hover:border-red-100">
                <div class="flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden">
                    <img
                        src="<?= htmlspecialchars($ca['image'] ?? 'assets/images/placeholder.jpg') ?>"
                        alt="<?= htmlspecialchars($ca['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    >
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-gray-900 text-sm font-semibold leading-snug line-clamp-3 group-hover:text-red-600 transition-colors">
                        <?= htmlspecialchars($ca['title']) ?>
                    </h3>
                    <p class="text-gray-400 text-xs mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?= htmlspecialchars(timeAgo($ca['published_at'] ?? $ca['created_at'])) ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
