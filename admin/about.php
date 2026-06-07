<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

$errors = [];

// ── Load existing content ─────────────────────────────────────────────────────
$aboutStmt = $pdo->prepare("SELECT * FROM site_content WHERE page_key = 'about' LIMIT 1");
$aboutStmt->execute();
$about = $aboutStmt->fetch();

$contactStmt = $pdo->prepare("SELECT * FROM site_content WHERE page_key = 'contact_info' LIMIT 1");
$contactStmt->execute();
$contactInfo = $contactStmt->fetch();

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $section = $_POST['section'] ?? '';

    if ($section === 'about') {
        $title   = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        if (empty($errors)) {
            if ($about) {
                $pdo->prepare(
                    "UPDATE site_content SET title = ?, content = ?, updated_at = NOW() WHERE page_key = 'about'"
                )->execute([$title, $content]);
            } else {
                $pdo->prepare(
                    "INSERT INTO site_content (page_key, title, content, created_at, updated_at) VALUES ('about', ?, ?, NOW(), NOW())"
                )->execute([$title, $content]);
            }
            setFlash('success', 'About page updated successfully.');
            header('Location: about.php');
            exit;
        }
    }

    if ($section === 'contact') {
        $contactContent = $_POST['contact_content'] ?? '';

        if ($contactInfo) {
            $pdo->prepare(
                "UPDATE site_content SET content = ?, updated_at = NOW() WHERE page_key = 'contact_info'"
            )->execute([$contactContent]);
        } else {
            $pdo->prepare(
                "INSERT INTO site_content (page_key, title, content, created_at, updated_at) VALUES ('contact_info', 'Contact Information', ?, NOW(), NOW())"
            )->execute([$contactContent]);
        }
        setFlash('success', 'Contact info updated successfully.');
        header('Location: about.php');
        exit;
    }
}

$adminPageTitle = 'About Page';
require_once '../components/admin-header.php';
?>
<div class="flex min-h-screen bg-gray-50">
    <?php require_once '../components/admin-sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-auto">

        <!-- Flash message -->
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="mb-4 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">About Page Content</h1>
            <p class="text-gray-500 text-sm mt-0.5">Edit the About Us page and contact information</p>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Tab navigation -->
        <div class="flex gap-1 mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-1 max-w-xs">
            <button onclick="showTab('about')" id="tab-about"
                    class="flex-1 py-2 px-4 rounded-xl text-sm font-semibold transition-colors bg-red-600 text-white">
                About Us
            </button>
            <button onclick="showTab('contact')" id="tab-contact"
                    class="flex-1 py-2 px-4 rounded-xl text-sm font-semibold transition-colors text-gray-600 hover:bg-gray-50">
                Contact Info
            </button>
        </div>

        <!-- About section -->
        <div id="section-about">
            <form method="POST" action="about.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="section" value="about">

                <div class="space-y-5">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="about_title" class="block text-sm font-semibold text-gray-700 mb-2">
                            Page Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="about_title" name="title"
                               value="<?= htmlspecialchars($about['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="About Us"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                               required>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="about_content" class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
                        <textarea id="about_content" name="content"><?= htmlspecialchars($about['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Save About Page
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Contact Info section -->
        <div id="section-contact" class="hidden">
            <form method="POST" action="about.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="section" value="contact">

                <div class="space-y-5">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="contact_content" class="block text-sm font-semibold text-gray-700 mb-2">Contact Information</label>
                        <p class="text-xs text-gray-400 mb-3">Add your address, phone, email, social links, map embed, etc.</p>
                        <textarea id="contact_content" name="contact_content"><?= htmlspecialchars($contactInfo['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Save Contact Info
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </main>
</div>

<script src="https://cdn.tiny.cloud/1/<?= htmlspecialchars(env('TINYMCE_API_KEY', 'no-api-key'), ENT_QUOTES, 'UTF-8') ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#about_content, #contact_content',
    plugins: 'link image lists table code media',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | table | code',
    height: 500,
    promotion: false
});

function showTab(name) {
    ['about', 'contact'].forEach(function(t) {
        document.getElementById('section-' + t).classList.toggle('hidden', t !== name);
        const btn = document.getElementById('tab-' + t);
        if (t === name) {
            btn.classList.add('bg-red-600', 'text-white');
            btn.classList.remove('text-gray-600', 'hover:bg-gray-50');
        } else {
            btn.classList.remove('bg-red-600', 'text-white');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50');
        }
    });
}
</script>
</body>
</html>
