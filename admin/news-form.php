<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

// ── Load existing article (edit mode) ─────────────────────────────────────────
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;
$isEdit  = false;
$errors  = [];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if (!$article) {
        setFlash('error', 'Article not found.');
        header('Location: news.php');
        exit;
    }
    $isEdit = true;
}

// ── Fetch categories ──────────────────────────────────────────────────────────
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $postId       = (int)($_POST['article_id'] ?? 0);
    $title        = trim($_POST['title'] ?? '');
    $categoryId   = (int)($_POST['category_id'] ?? 0);
    $summary      = trim($_POST['summary'] ?? '');
    $body         = $_POST['body'] ?? '';
    $publishedAt  = $_POST['published_at'] ?? date('Y-m-d');
    $status       = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';
    $isFeatured   = isset($_POST['is_featured']) ? 1 : 0;
    $isBreaking   = isset($_POST['is_breaking']) ? 1 : 0;

    // Validation
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($categoryId === 0) {
        $errors[] = 'Please select a category.';
    }
    if (trim(strip_tags($body)) === '') {
        $errors[] = 'Article body is required.';
    }

    // Image upload
    $imageName = $postId > 0 ? ($_POST['current_image'] ?? '') : '';

    if (!empty($_FILES['image']['name'])) {
        $allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType  = mime_content_type($_FILES['image']['tmp_name']);
        $maxSize   = 5 * 1024 * 1024; // 5 MB

        if (!in_array($fileType, $allowed)) {
            $errors[] = 'Image must be JPEG, PNG, WebP, or GIF.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Image file size must be under 5 MB.';
        } else {
            $uploadDir = dirname(__DIR__) . '/uploads/news/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName   = uniqid('news_', true) . '.' . strtolower($ext);
            $destPath  = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                // Delete old image (only files inside uploads/news/)
                if ($imageName && str_starts_with($imageName, 'uploads/news/')) {
                    $oldPath = dirname(__DIR__) . '/' . $imageName;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $imageName = 'uploads/news/' . $newName;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    // Generate or reuse slug
    $slug = $article['slug'] ?? '';
    if ($slug === '') {
        $slug = uniqueSlug($pdo, createSlug($title), 'news', $postId);
    }

    if (empty($errors)) {
        if ($postId > 0) {
            // UPDATE
            $upd = $pdo->prepare(
                "UPDATE news SET title=?, slug=?, category_id=?, summary=?, body=?, image=?,
                 published_at=?, status=?, is_featured=?, is_breaking=?, updated_at=NOW()
                 WHERE id=?"
            );
            $upd->execute([$title, $slug, $categoryId, $summary, $body, $imageName,
                           $publishedAt, $status, $isFeatured, $isBreaking, $postId]);
            setFlash('success', 'Article updated successfully.');
        } else {
            // INSERT
            $ins = $pdo->prepare(
                "INSERT INTO news (title, slug, category_id, summary, body, image, published_at,
                 status, is_featured, is_breaking, views, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())"
            );
            $ins->execute([$title, $slug, $categoryId, $summary, $body, $imageName,
                           $publishedAt, $status, $isFeatured, $isBreaking]);
            setFlash('success', 'Article created successfully.');
        }

        header('Location: news.php');
        exit;
    }

    // Re-populate $article on validation failure
    $article = [
        'id'           => $postId,
        'title'        => $title,
        'slug'         => $slug,
        'category_id'  => $categoryId,
        'summary'      => $summary,
        'body'         => $body,
        'image'        => $imageName,
        'published_at' => $publishedAt,
        'status'       => $status,
        'is_featured'  => $isFeatured,
        'is_breaking'  => $isBreaking,
    ];
    $isEdit = $postId > 0;
}

$adminPageTitle = $isEdit ? 'Edit Article' : 'Add New Article';
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

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Edit Article' : 'Add New Article' ?></h1>
                <p class="text-gray-500 text-sm mt-0.5"><?= $isEdit ? 'Modify the article details below.' : 'Fill in the details to publish a new article.' ?></p>
            </div>
            <a href="news.php"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                ← Back to News
            </a>
        </div>

        <!-- Validation Errors -->
        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200">
                <p class="text-red-700 font-semibold text-sm mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" action="news-form.php<?= $isEdit ? '?id=' . ($article['id'] ?? '') : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="article_id" value="<?= (int)($article['id'] ?? 0) ?>">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($article['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                <!-- Left column (main content) -->
                <div class="xl:col-span-2 space-y-5">

                    <!-- Title -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               value="<?= htmlspecialchars($article['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Enter article title…"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                               required>
                    </div>

                    <!-- Summary -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="summary" class="block text-sm font-semibold text-gray-700 mb-2">Summary</label>
                        <textarea id="summary" name="summary" rows="3"
                                  placeholder="Short description shown in article listings…"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400 resize-none"><?= htmlspecialchars($article['summary'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Body (TinyMCE) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="body" class="block text-sm font-semibold text-gray-700 mb-2">
                            Body <span class="text-red-500">*</span>
                        </label>
                        <textarea id="body" name="body"><?= htmlspecialchars($article['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Image upload -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                            Featured Image
                        </label>

                        <?php if (!empty($article['image'])): $imgUrl = assetPath($article['image'], '/uploads/news'); ?>
                            <div class="mb-3">
                                <p class="text-xs text-gray-500 mb-2">Current image:</p>
                                <img src="<?= htmlspecialchars('..' . $imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Current image"
                                     class="w-48 h-32 object-cover rounded-xl border border-gray-200">
                            </div>
                        <?php endif; ?>

                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-red-300 transition-colors cursor-pointer"
                             onclick="document.getElementById('image').click()">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Click to upload or drag &amp; drop</p>
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP, GIF · Max 5 MB</p>
                            <input type="file" id="image" name="image" accept="image/*" class="hidden"
                                   onchange="previewImage(this)">
                        </div>
                        <div id="imagePreview" class="mt-3 hidden">
                            <img id="previewImg" src="#" alt="Preview"
                                 class="w-48 h-32 object-cover rounded-xl border border-gray-200">
                        </div>
                    </div>

                </div>

                <!-- Right column (meta) -->
                <div class="space-y-5">

                    <!-- Publish settings -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Publish Settings</h3>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="status" value="published"
                                           <?= ($article['status'] ?? 'draft') === 'published' ? 'checked' : '' ?>
                                           class="accent-green-600 w-4 h-4">
                                    <span class="text-sm text-gray-700 font-medium">Published</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="status" value="draft"
                                           <?= ($article['status'] ?? 'draft') === 'draft' ? 'checked' : '' ?>
                                           class="accent-yellow-500 w-4 h-4">
                                    <span class="text-sm text-gray-700 font-medium">Draft</span>
                                </label>
                            </div>
                        </div>

                        <!-- Published Date -->
                        <div class="mb-4">
                            <label for="published_at" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Published Date</label>
                            <input type="date" id="published_at" name="published_at"
                                   value="<?= htmlspecialchars(!empty($article['published_at']) ? date('Y-m-d', strtotime($article['published_at'])) : date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
                        </div>

                        <!-- Flags -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-amber-50 transition-colors">
                                <input type="checkbox" name="is_featured" value="1"
                                       <?= !empty($article['is_featured']) ? 'checked' : '' ?>
                                       class="w-4 h-4 accent-amber-500 rounded">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Featured</span>
                                    <p class="text-xs text-gray-400">Show in featured section</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-red-50 transition-colors">
                                <input type="checkbox" name="is_breaking" value="1"
                                       <?= !empty($article['is_breaking']) ? 'checked' : '' ?>
                                       class="w-4 h-4 accent-red-600 rounded">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Breaking News</span>
                                    <p class="text-xs text-gray-400">Mark as breaking / live</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                                required>
                            <option value="">— Select category —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= (int)($article['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($categories)): ?>
                            <p class="text-xs text-orange-600 mt-2">
                                No active categories found. <a href="categories.php" class="underline">Add one</a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Action buttons -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-3">
                        <button type="submit"
                                class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            <?= $isEdit ? 'Update Article' : 'Publish Article' ?>
                        </button>
                        <a href="news.php"
                           class="block text-center w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                            Cancel
                        </a>
                    </div>

                </div>
            </div>
        </form>

    </main>
</div>

<script src="https://cdn.tiny.cloud/1/<?= htmlspecialchars(env('TINYMCE_API_KEY', 'no-api-key'), ENT_QUOTES, 'UTF-8') ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#body',
    plugins: 'link image lists table code media',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | table | code',
    height: 500,
    promotion: false
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
