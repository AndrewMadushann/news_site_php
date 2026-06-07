<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/functions.php';

$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$slug) { header('Location: news.php'); exit; }

$article = getArticleBySlug($pdo, $slug);
if (!$article) {
    http_response_code(404);
    $pageTitle = '404 - Article Not Found';
    require_once 'components/header.php';
    require_once 'components/navbar.php';
    echo '<div class="max-w-2xl mx-auto px-4 py-24 text-center"><h1 class="text-4xl font-bold text-gray-800 mb-4">Article Not Found</h1><p class="text-gray-500 mb-8">The article you are looking for does not exist or has been removed.</p><a href="news.php" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">Browse All News</a></div>';
    require_once 'components/footer.php';
    echo '</body></html>';
    exit;
}

// Increment view counter
$pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

// Related articles
$related = getRelatedNews($pdo, $article['category_id'] ?? 0, $article['id'], 4);

// Prev/Next
$prev = $pdo->prepare("SELECT title, slug FROM news WHERE id < ? AND status='published' ORDER BY id DESC LIMIT 1");
$prev->execute([$article['id']]);
$prevArticle = $prev->fetch();

$next = $pdo->prepare("SELECT title, slug FROM news WHERE id > ? AND status='published' ORDER BY id ASC LIMIT 1");
$next->execute([$article['id']]);
$nextArticle = $next->fetch();

// Reading time estimate
$wordCount   = str_word_count(strip_tags($article['body'] ?? ''));
$readingTime = max(1, (int)ceil($wordCount / 200));

$pageTitle = $article['title'];
$metaDesc  = !empty($article['summary']) ? strip_tags($article['summary']) : truncate(strip_tags($article['body'] ?? ''), 155);
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ========================================================
     READING PROGRESS BAR
     ======================================================== -->
<div id="reading-progress"
     class="fixed top-0 left-0 h-0.5 bg-red-600 z-50 transition-all duration-75"
     style="width: 0%"></div>

<script>
window.addEventListener('scroll', function () {
    var el  = document.getElementById('reading-progress');
    var body = document.body;
    var html = document.documentElement;
    var docH = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
    var scrolled = (window.scrollY / (docH - window.innerHeight)) * 100;
    el.style.width = Math.min(scrolled, 100) + '%';
});
</script>

<!-- ========================================================
     BREADCRUMB
     ======================================================== -->
<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-gray-500 flex-wrap">
            <a href="index.php" class="hover:text-red-600 transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Home
            </a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <?php if (!empty($article['category_name'])): ?>
            <a href="news.php?cat=<?= htmlspecialchars($article['category_slug'] ?? '') ?>"
               class="hover:text-red-600 transition">
                <?= htmlspecialchars($article['category_name']) ?>
            </a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <?php endif; ?>
            <span class="text-gray-700 font-medium truncate max-w-xs">
                <?= htmlspecialchars(truncate($article['title'], 50)) ?>
            </span>
        </nav>
    </div>
</div>

<!-- ========================================================
     ARTICLE MAIN
     ======================================================== -->
<main class="bg-white py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- ARTICLE HEADER -->
        <header class="mb-8">
            <!-- Category badge -->
            <div class="flex items-center gap-2 mb-4 flex-wrap">
                <?php if (!empty($article['category_name'])): ?>
                <a href="news.php?cat=<?= htmlspecialchars($article['category_slug'] ?? '') ?>"
                   class="inline-block bg-red-600 text-white text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full">
                    <?= htmlspecialchars($article['category_name']) ?>
                </a>
                <?php endif; ?>
                <?php if (!empty($article['is_breaking'])): ?>
                <span class="inline-flex items-center gap-1 bg-yellow-400 text-black text-xs font-bold px-3 py-1 rounded-full animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 inline-block"></span>
                    Breaking News
                </span>
                <?php endif; ?>
            </div>

            <!-- H1 Title -->
            <h1 class="font-serif text-3xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
                <?= htmlspecialchars($article['title']) ?>
            </h1>

            <!-- Meta bar -->
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-500 pb-6 border-b border-gray-200">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <?= htmlspecialchars(formatDate($article['published_at'] ?? $article['created_at'])) ?>
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <?= number_format((int)($article['views'] ?? 0)) ?> views
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?= $readingTime ?> min read
                </span>
                <?php if (!empty($article['category_name'])): ?>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <?= htmlspecialchars($article['category_name']) ?>
                </span>
                <?php endif; ?>
            </div>
        </header>

        <!-- HERO IMAGE -->
        <?php if (!empty($article['image'])): ?>
        <div class="mb-8">
            <img
                src="<?= htmlspecialchars($article['image']) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                class="w-full rounded-xl object-cover max-h-96 shadow-lg"
            >
            <?php if (!empty($article['image_caption'])): ?>
            <p class="text-xs text-gray-400 mt-2 text-center italic">
                <?= htmlspecialchars($article['image_caption']) ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ARTICLE SUMMARY (lead) -->
        <?php if (!empty($article['summary'])): ?>
        <p class="text-xl text-gray-600 leading-relaxed font-medium border-l-4 border-red-600 pl-5 mb-8 italic">
            <?= htmlspecialchars($article['summary']) ?>
        </p>
        <?php endif; ?>

        <!-- ARTICLE BODY -->
        <article class="article-body prose prose-lg max-w-none
                        prose-headings:font-serif prose-headings:text-gray-900
                        prose-p:text-gray-700 prose-p:leading-relaxed
                        prose-a:text-red-600 prose-a:no-underline hover:prose-a:underline
                        prose-blockquote:border-red-600 prose-blockquote:bg-red-50 prose-blockquote:rounded-r-lg prose-blockquote:py-1
                        prose-img:rounded-xl prose-img:shadow-md
                        prose-strong:text-gray-900
                        mb-10">
            <?= $article['body'] ?>
        </article>

        <!-- SHARE BUTTONS -->
        <div class="border-t border-b border-gray-200 py-6 mb-8">
            <p class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Share this article</p>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Facebook -->
                <?php $shareUrl = 'https://' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'dailynews.com') . '/article.php?slug=' . htmlspecialchars($article['slug']); ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </a>
                <!-- Twitter/X -->
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($article['title']) ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-black hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    Twitter
                </a>
                <!-- WhatsApp -->
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $shareUrl) ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
                <!-- Copy Link -->
                <button
                    onclick="copyArticleLink('<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>', this)"
                    class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Copy Link
                </button>
            </div>
        </div>

        <!-- TAGS / CATEGORY LINK -->
        <?php if (!empty($article['category_name'])): ?>
        <div class="flex flex-wrap items-center gap-2 mb-8">
            <span class="text-sm text-gray-500 font-medium">Filed under:</span>
            <a href="news.php?cat=<?= htmlspecialchars($article['category_slug'] ?? '') ?>"
               class="inline-block bg-red-50 text-red-600 border border-red-100 text-sm font-semibold px-3 py-1 rounded-full hover:bg-red-600 hover:text-white transition">
                <?= htmlspecialchars($article['category_name']) ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- PREV / NEXT NAVIGATION -->
        <?php if ($prevArticle || $nextArticle): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-12 border-t border-gray-200 pt-8">
            <?php if ($prevArticle): ?>
            <a href="article.php?slug=<?= htmlspecialchars($prevArticle['slug']) ?>"
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:border-red-300 hover:bg-red-50 transition group">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 group-hover:bg-red-600 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Previous</p>
                    <p class="text-sm text-gray-800 font-medium line-clamp-2 group-hover:text-red-600 transition-colors">
                        <?= htmlspecialchars($prevArticle['title']) ?>
                    </p>
                </div>
            </a>
            <?php else: ?><div></div><?php endif; ?>

            <?php if ($nextArticle): ?>
            <a href="article.php?slug=<?= htmlspecialchars($nextArticle['slug']) ?>"
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:border-red-300 hover:bg-red-50 transition group text-right sm:flex-row-reverse">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 group-hover:bg-red-600 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Next</p>
                    <p class="text-sm text-gray-800 font-medium line-clamp-2 group-hover:text-red-600 transition-colors">
                        <?= htmlspecialchars($nextArticle['title']) ?>
                    </p>
                </div>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- RELATED ARTICLES -->
        <?php if (!empty($related)): ?>
        <section class="border-t border-gray-200 pt-10">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-6 bg-red-600 rounded-full"></div>
                <h2 class="text-xl font-bold text-gray-900">Related Articles</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php foreach ($related as $rel): ?>
                <a href="article.php?slug=<?= htmlspecialchars($rel['slug']) ?>"
                   class="group flex flex-col rounded-xl overflow-hidden border border-gray-100 hover:shadow-md transition-shadow bg-white">
                    <div class="relative h-36 overflow-hidden">
                        <img
                            src="<?= htmlspecialchars($rel['image'] ?? 'assets/images/placeholder.jpg') ?>"
                            alt="<?= htmlspecialchars($rel['title']) ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >
                    </div>
                    <div class="p-3 flex-1">
                        <h3 class="text-gray-800 text-sm font-semibold leading-snug line-clamp-3 group-hover:text-red-600 transition-colors">
                            <?= htmlspecialchars($rel['title']) ?>
                        </h3>
                        <p class="text-gray-400 text-xs mt-2">
                            <?= htmlspecialchars(timeAgo($rel['published_at'] ?? $rel['created_at'])) ?>
                        </p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>

<script>
function copyArticleLink(url, btn) {
    navigator.clipboard.writeText(url).then(function () {
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
        btn.classList.add('bg-green-100', 'text-green-700');
        setTimeout(function () { btn.innerHTML = orig; btn.classList.remove('bg-green-100', 'text-green-700'); }, 2000);
    });
}
</script>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
