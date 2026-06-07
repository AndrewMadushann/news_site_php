<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/upload.php';

requireLogin();

$errors = [];

// ── POST handler ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $title       = trim($_POST['title'] ?? '');
    $editionDate = trim($_POST['edition_date'] ?? '');
    $status      = isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0;

    // Validate
    if (empty($title))       $errors[] = 'Edition title is required.';
    if (empty($editionDate)) $errors[] = 'Edition date is required.';
    if (empty($_FILES['pdf_file']['name'])) $errors[] = 'A PDF file is required.';

    // Validate date format
    if (!empty($editionDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $editionDate)) {
        $errors[] = 'Invalid date format.';
    }

    $pdfFilename   = null;
    $thumbFilename = null;

    if (empty($errors)) {
        // Handle PDF upload
        try {
            $uploadDir   = dirname(__DIR__) . '/uploads/papers/';
            $pdfFilename = handlePdfUpload($_FILES['pdf_file'], $uploadDir);
        } catch (Exception $e) {
            $errors[] = 'PDF upload failed: ' . $e->getMessage();
        }

        // Handle optional thumbnail
        if (empty($errors) && !empty($_FILES['thumbnail']['name'])) {
            try {
                $thumbFilename = handleImageUpload($_FILES['thumbnail'], $uploadDir);
            } catch (Exception $e) {
                $errors[] = 'Thumbnail upload failed: ' . $e->getMessage();
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO papers (title, edition_date, pdf_path, thumbnail, status)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$title, $editionDate, $pdfFilename, $thumbFilename, $status]);

        setFlash('success', 'Edition "' . $title . '" uploaded successfully.');
        header('Location: papers.php');
        exit;
    }
}

$adminPageTitle = 'Upload e-Paper Edition';
require_once '../components/admin-header.php';
?>
<div class="flex min-h-screen bg-gray-50">
    <?php require_once '../components/admin-sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-auto">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="index.php" class="hover:text-gray-700 transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="papers.php" class="hover:text-gray-700 transition-colors">e-Papers</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-800 font-medium">Upload Edition</span>
        </nav>

        <div class="max-w-2xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Upload e-Paper Edition</h1>
                <p class="text-gray-500 text-sm mt-1">Upload a new newspaper PDF edition for public viewing.</p>
            </div>

            <!-- Errors -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                    <p class="text-sm font-semibold text-red-700 mb-2">Please fix the following errors:</p>
                    <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <form method="POST" action="paper-form.php" enctype="multipart/form-data"
                  class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Edition Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                           value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. Daily News — June 7, 2026"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
                </div>

                <!-- Edition Date -->
                <div>
                    <label for="edition_date" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Edition Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="edition_date" name="edition_date" required
                           value="<?= htmlspecialchars($_POST['edition_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
                </div>

                <!-- PDF File -->
                <div>
                    <label for="pdf_file" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        PDF File <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-red-300 transition-colors cursor-pointer"
                         onclick="document.getElementById('pdf_file').click()">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500" id="pdf-label">Click to select a PDF file</p>
                        <p class="text-xs text-gray-400 mt-1">PDF only · Maximum 25 MB</p>
                    </div>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf,application/pdf"
                           class="hidden" onchange="updatePdfLabel(this)">
                </div>

                <!-- Thumbnail -->
                <div>
                    <label for="thumbnail" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Cover Thumbnail <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-red-300 transition-colors cursor-pointer"
                         onclick="document.getElementById('thumbnail').click()">
                        <div id="thumb-preview" class="hidden mb-3">
                            <img id="thumb-img" src="" alt="Preview" class="h-24 mx-auto rounded object-cover">
                        </div>
                        <svg id="thumb-icon" class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500" id="thumb-label">Click to select a cover image</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP · Maximum 3 MB</p>
                    </div>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                           class="hidden" onchange="previewThumbnail(this)">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Visibility</label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="1"
                                   <?= (!isset($_POST['status']) || $_POST['status'] === '1') ? 'checked' : '' ?>
                                   class="w-4 h-4 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-gray-700">Visible to public</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="0"
                                   <?= (isset($_POST['status']) && $_POST['status'] === '0') ? 'checked' : '' ?>
                                   class="w-4 h-4 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-gray-700">Hidden (draft)</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload Edition
                    </button>
                    <a href="papers.php"
                       class="inline-flex items-center px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </main>
</div>

<script>
function updatePdfLabel(input) {
    const label = document.getElementById('pdf-label');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        label.textContent = file.name + ' (' + sizeMB + ' MB)';
        label.classList.add('text-green-600');
    }
}

function previewThumbnail(input) {
    const label   = document.getElementById('thumb-label');
    const preview = document.getElementById('thumb-preview');
    const icon    = document.getElementById('thumb-icon');
    const img     = document.getElementById('thumb-img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
            icon.classList.add('hidden');
            label.textContent = input.files[0].name;
            label.classList.add('text-green-600');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
