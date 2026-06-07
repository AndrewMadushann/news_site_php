<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

$errors = [];

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $action = $_POST['action'] ?? '';

    // ── Add category ──
    if ($action === 'add') {
        $name   = trim($_POST['name'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if ($name === '') {
            $errors[] = 'Category name is required.';
        } else {
            $slug = makeSlug($name);

            // Check duplicate slug
            $dup = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $dup->execute([$slug]);
            if ($dup->fetch()) {
                $errors[] = 'A category with this name already exists.';
            } else {
                $ins = $pdo->prepare("INSERT INTO categories (name, slug, status, created_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$name, $slug, $status]);
                setFlash('success', 'Category "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" added.');
                header('Location: categories.php');
                exit;
            }
        }
    }

    // ── Edit category ──
    if ($action === 'edit') {
        $editId = (int)($_POST['edit_id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if ($editId === 0 || $name === '') {
            $errors[] = 'Invalid edit request.';
        } else {
            $slug = makeSlug($name);

            // Check duplicate slug (exclude self)
            $dup = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $dup->execute([$slug, $editId]);
            if ($dup->fetch()) {
                $errors[] = 'Another category with this name already exists.';
            } else {
                $upd = $pdo->prepare("UPDATE categories SET name=?, slug=?, status=? WHERE id=?");
                $upd->execute([$name, $slug, $status, $editId]);
                setFlash('success', 'Category updated successfully.');
                header('Location: categories.php');
                exit;
            }
        }
    }

    // ── Delete category ──
    if ($action === 'delete') {
        $delId = (int)($_POST['del_id'] ?? 0);
        if ($delId > 0) {
            // Check if articles exist
            $cnt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE category_id = ?");
            $cnt->execute([$delId]);
            if ((int)$cnt->fetchColumn() > 0) {
                setFlash('error', 'Cannot delete: this category still has articles assigned to it.');
            } else {
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
                setFlash('success', 'Category deleted.');
            }
        }
        header('Location: categories.php');
        exit;
    }

    // ── Toggle status ──
    if ($action === 'toggle') {
        $togId = (int)($_POST['tog_id'] ?? 0);
        if ($togId > 0) {
            $pdo->prepare(
                "UPDATE categories SET status = IF(status='active','inactive','active') WHERE id=?"
            )->execute([$togId]);
            setFlash('success', 'Category status toggled.');
        }
        header('Location: categories.php');
        exit;
    }

    // If errors, fall through to render page again
}

// ── Fetch categories with article counts ──────────────────────────────────────
$editId  = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editCat = null;
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}

$catsStmt = $pdo->query(
    "SELECT c.*, COUNT(n.id) AS article_count
     FROM categories c
     LEFT JOIN news n ON n.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name"
);
$categories = $catsStmt->fetchAll();

$adminPageTitle = 'Categories';
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
            <h1 class="text-2xl font-bold text-gray-800">Categories</h1>
            <p class="text-gray-500 text-sm mt-0.5">Manage news categories</p>
        </div>

        <!-- Validation errors -->
        <?php if (!empty($errors)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Add / Edit form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-base font-semibold text-gray-800 mb-4">
                        <?= $editCat ? '✏️ Edit Category' : '+ Add Category' ?>
                    </h2>
                    <form method="POST" action="categories.php<?= $editId > 0 ? '?edit=' . $editId : '' ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="<?= $editCat ? 'edit' : 'add' ?>">
                        <?php if ($editCat): ?>
                            <input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="catName" class="block text-sm font-semibold text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="catName" name="name"
                                   value="<?= htmlspecialchars($editCat['name'] ?? ($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="e.g. Politics"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                                   required>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="status" value="active"
                                           <?= ($editCat['status'] ?? 'active') === 'active' ? 'checked' : '' ?>
                                           class="accent-green-600 w-4 h-4">
                                    <span class="text-sm text-gray-700">Active</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="status" value="inactive"
                                           <?= ($editCat['status'] ?? '') === 'inactive' ? 'checked' : '' ?>
                                           class="accent-gray-400 w-4 h-4">
                                    <span class="text-sm text-gray-700">Inactive</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                                <?= $editCat ? 'Update' : 'Add Category' ?>
                            </button>
                            <?php if ($editCat): ?>
                                <a href="categories.php"
                                   class="flex-1 text-center py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categories table -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-800">All Categories</h2>
                        <span class="text-xs text-gray-400"><?= count($categories) ?> total</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-left">
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Articles</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">No categories yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr class="hover:bg-gray-50 transition-colors <?= (int)$cat['id'] === $editId ? 'bg-red-50' : '' ?>">
                                            <td class="px-5 py-3.5 text-gray-400 font-mono text-xs"><?= $cat['id'] ?></td>
                                            <td class="px-5 py-3.5 font-medium text-gray-800">
                                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">
                                                <?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <?php if ($cat['status'] === 'active'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600">
                                                    <?= (int)$cat['article_count'] ?>
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    <!-- Edit -->
                                                    <a href="categories.php?edit=<?= $cat['id'] ?>"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors">
                                                        Edit
                                                    </a>

                                                    <!-- Toggle -->
                                                    <form method="POST" action="categories.php" class="inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="tog_id" value="<?= $cat['id'] ?>">
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors">
                                                            <?= $cat['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                                        </button>
                                                    </form>

                                                    <!-- Delete -->
                                                    <form method="POST" action="categories.php" class="inline"
                                                          onsubmit="return confirm('Delete this category? This cannot be undone.')">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="del_id" value="<?= $cat['id'] ?>">
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-lg transition-colors">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>
</body>
</html>
