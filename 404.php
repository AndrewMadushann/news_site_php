<?php
// 404 page — standalone, no DB needed
http_response_code(404);

$pageTitle = '404 — Page Not Found';
$metaDesc  = 'The page you are looking for could not be found.';

// Only include header/footer if those files exist
$headerPath = __DIR__ . '/components/header.php';
$navbarPath = __DIR__ . '/components/navbar.php';
$footerPath = __DIR__ . '/components/footer.php';

if (file_exists($headerPath)) require_once $headerPath;
if (file_exists($navbarPath)) require_once $navbarPath;

// If header is unavailable, output a minimal doctype
if (!file_exists($headerPath)):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | Daily News</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; } .font-serif { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-gray-50">
<?php endif; ?>

<!-- ========================================================
     404 MAIN SECTION
     ======================================================== -->
<main class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-red-50 flex items-center justify-center py-16 px-4">
    <div class="max-w-2xl mx-auto text-center">

        <!-- Animated 404 graphic -->
        <div class="relative inline-block mb-8">
            <!-- Background glow -->
            <div class="absolute inset-0 blur-3xl bg-red-200 opacity-30 rounded-full scale-150"></div>

            <!-- Big 404 -->
            <div class="relative">
                <div class="font-serif text-[10rem] md:text-[14rem] font-black leading-none tracking-tighter select-none"
                     style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 50%, #7f1d1d 100%);
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            background-clip: text;">
                    404
                </div>
                <!-- Newspaper icon overlay -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-10">
                    <svg class="w-40 h-40 text-red-900" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Heading -->
        <h1 class="font-serif text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight">
            Page Not Found
        </h1>

        <!-- Subtitle -->
        <p class="text-gray-500 text-lg leading-relaxed mb-3 max-w-md mx-auto">
            We couldn't find the page you're looking for. It may have been moved, deleted, or never existed.
        </p>
        <p class="text-gray-400 text-sm mb-10">
            Error 404 — The requested URL was not found on this server.
        </p>

        <!-- Action buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
            <a href="index.php"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3.5 rounded-xl transition shadow-lg shadow-red-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Back to Home
            </a>
            <a href="news.php"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 font-semibold px-8 py-3.5 rounded-xl transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                Browse All News
            </a>
        </div>

        <!-- Helpful links -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 text-left max-w-sm mx-auto shadow-sm">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">You might be looking for:</p>
            <ul class="space-y-2">
                <?php
                $helpLinks = [
                    ['href' => 'index.php',   'label' => 'Home Page',         'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['href' => 'news.php',    'label' => 'Latest News',       'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                    ['href' => 'about.php',   'label' => 'About Us',          'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['href' => 'contact.php', 'label' => 'Contact',           'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ];
                foreach ($helpLinks as $link):
                ?>
                <li>
                    <a href="<?= $link['href'] ?>"
                       class="flex items-center gap-3 text-sm text-gray-600 hover:text-red-600 transition py-1.5 group">
                        <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-red-50 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4 text-gray-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $link['icon'] ?>"/>
                            </svg>
                        </span>
                        <?= htmlspecialchars($link['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Decorative newspaper dots -->
        <div class="mt-12 flex items-center justify-center gap-2 opacity-20">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="w-2 h-2 rounded-full bg-red-600" style="animation: pulse 2s infinite; animation-delay: <?= $i * 0.2 ?>s;"></div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<?php
if (file_exists($footerPath)) require_once $footerPath;
if (!file_exists($headerPath)):
?>
</body>
</html>
<?php endif; ?>
