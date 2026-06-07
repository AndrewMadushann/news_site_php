<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/functions.php';

try {
    $aboutContent = getSiteContent($pdo, 'about');
    $contactBlock = getSiteContent($pdo, 'contact_info');
} catch (PDOException $e) {
    error_log('[about.php] ' . $e->getMessage());
    $aboutContent = null;
    $contactBlock = null;
}

$pageTitle = 'About Us';
$metaDesc  = 'Learn more about Daily News — our mission, team, and commitment to quality journalism.';
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ========================================================
     PAGE HERO
     ======================================================== -->
<div class="bg-gradient-to-br from-gray-900 via-red-950 to-gray-900 text-white py-16">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block bg-red-600/20 text-red-300 text-xs font-bold uppercase tracking-widest px-4 py-1.5 rounded-full mb-4 border border-red-600/30">
            Who We Are
        </span>
        <h1 class="font-serif text-4xl md:text-5xl font-bold mb-4">
            <?= htmlspecialchars($aboutContent['title'] ?? 'About Daily News') ?>
        </h1>
        <p class="text-gray-300 text-lg max-w-2xl mx-auto leading-relaxed">
            Trusted journalism since day one — bringing you accurate, timely, and in-depth coverage.
        </p>
    </div>
</div>

<!-- ========================================================
     BREADCRUMB
     ======================================================== -->
<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="index.php" class="hover:text-red-600 transition">Home</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">About Us</span>
        </nav>
    </div>
</div>

<!-- ========================================================
     MAIN CONTENT
     ======================================================== -->
<main class="bg-white py-14">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- Main content (2/3) -->
            <div class="lg:col-span-2">
                <?php if (!empty($aboutContent['content'])): ?>
                <!-- Render trusted admin HTML content -->
                <div class="prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-red-600 prose-strong:text-gray-900 article-body">
                    <?= $aboutContent['content'] ?>
                </div>
                <?php else: ?>
                <!-- Default fallback content -->
                <div class="prose prose-lg max-w-none">
                    <h2 class="font-serif text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Daily News is committed to delivering accurate, impartial, and timely journalism to our readers.
                        We believe that an informed public is the foundation of a healthy democracy.
                    </p>
                    <h2 class="font-serif text-2xl font-bold text-gray-900 mb-4">Our Values</h2>
                    <ul class="space-y-3 text-gray-600 mb-6">
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold mt-0.5">✓</span>
                            <span><strong>Accuracy:</strong> We verify every fact before publishing.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold mt-0.5">✓</span>
                            <span><strong>Independence:</strong> Our editorial decisions are free from commercial or political influence.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold mt-0.5">✓</span>
                            <span><strong>Transparency:</strong> We are open about our sources and methods.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold mt-0.5">✓</span>
                            <span><strong>Fairness:</strong> We give a voice to all sides of every story.</span>
                        </li>
                    </ul>
                    <h2 class="font-serif text-2xl font-bold text-gray-900 mb-4">Our Team</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Our newsroom is staffed by experienced journalists, editors, photographers, and digital producers
                        dedicated to bringing you the stories that matter most.
                    </p>
                </div>
                <?php endif; ?>

                <!-- Stats row -->
                <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <?php
                    $stats = [
                        ['value' => '10+',   'label' => 'Years of Journalism'],
                        ['value' => '500K+', 'label' => 'Monthly Readers'],
                        ['value' => '50+',   'label' => 'Staff Journalists'],
                        ['value' => '24/7',  'label' => 'News Coverage'],
                    ];
                    foreach ($stats as $s):
                    ?>
                    <div class="text-center p-5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-3xl font-bold text-red-600 mb-1"><?= $s['value'] ?></div>
                        <div class="text-xs text-gray-500 font-medium"><?= $s['label'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sidebar (1/3) -->
            <aside class="space-y-6">
                <!-- Contact Info Card -->
                <div class="bg-gray-900 rounded-2xl p-6 text-white">
                    <h3 class="font-bold text-lg mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Contact Info
                    </h3>
                    <div class="text-gray-300 text-sm space-y-2 prose prose-invert prose-sm max-w-none prose-p:my-1 prose-a:text-red-400">
                        <?php if ($contactBlock && !empty($contactBlock['content'])): ?>
                            <?= $contactBlock['content'] ?>
                        <?php else: ?>
                            <p class="text-gray-400">No contact information available.</p>
                        <?php endif; ?>
                    </div>
                    <a href="contact.php" class="mt-6 block text-center bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                        Send Us a Message →
                    </a>
                </div>

                <!-- Quick links -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 text-base mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <?php
                        $links = [
                            ['href' => 'news.php',    'label' => 'Browse All News'],
                            ['href' => 'contact.php', 'label' => 'Contact Us'],
                        ];
                        foreach ($links as $link):
                        ?>
                        <li>
                            <a href="<?= htmlspecialchars($link['href']) ?>"
                               class="flex items-center gap-2 text-gray-600 hover:text-red-600 text-sm font-medium transition py-1.5 border-b border-gray-100 last:border-0">
                                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <?= htmlspecialchars($link['label']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
