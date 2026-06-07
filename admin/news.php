<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Fetch image path before deletion
            $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if ($row) {
                // Delete uploaded image file if it sits under uploads/news/
                if (!empty($row['image']) && str_starts_with($row['image'], 'uploads/news/')) {
                    $imagePath = dirname(__DIR__) . '/' . $row['image'];
                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
                $del = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $del->execute([$id]);
                setFlash('success', 'Article deleted successfully.');
            } else {
                setFlash('error', 'Article not found.');
            }
        }
    }

    header('Location: news.php?' . http_build_query(['status' => $_POST['status_filter'] ?? '', 'search' => $_POST['search_filter'] ?? '']));
    exit;
}

// ── GET filters & pagination ──────────────────────────────────────────────────
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$conditions = [];
$params     = [];

if ($statusFilter === 'published' || $statusFilter === 'draft') {
    $conditions[] = 'n.status = ?';
    $params[]     = $statusFilter;
}

if ($search !== '') {
    $conditions[] = 'n.title LIKE ?';
    $params[]     = '%' . $search . '%';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM news n $where");
$countStmt->execute($params);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($totalRows / $perPage);

// Fetch articles
$dataParams   = array_merge($params, [$perPage, $offset]);
$articleStmt  = $pdo->prepare(
    "SELECT n.id, n.title, n.status, n.is_featured, n.is_breaking, n.created_at, n.views,
            c.name AS category_name
     FROM news n
     LEFT JOIN categories c ON n.category_id = c.id
     $where
     ORDER BY n.created_at DESC
     LIMIT ? OFFSET ?"
);
$articleStmt->execute($dataParams);
$articles = $articleStmt->fetchAll();

$adminPageTitle = 'News Management';
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

        <!-- Header row -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">News Articles</h1>
                <p class="text-gray-500 text-sm mt-0.5"><?= number_format($totalRows) ?> article<?= $totalRows !== 1 ? 's' : '' ?> total</p>
            </div>
            <a href="news-form.php"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Article
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
            <form method="GET" action="" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Search by title…"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
                </div>
                <div class="w-44">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Status</label>
                    <select name="status"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400">
                        <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <?php if ($search !== '' || $statusFilter !== ''): ?>
                    <a href="news.php"
                       class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition-colors">
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Featured</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Breaking</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Views</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($articles)): ?>
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                                    <?= $search || $statusFilter ? 'No articles match your filters.' : 'No articles yet. <a href="news-form.php" class="text-red-600 hover:underline">Create the first one</a>.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($articles as $i => $article): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">
                                        <?= $offset + $i + 1 ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-gray-800 max-w-xs truncate">
                                            <?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">
                                        <?= htmlspecialchars($article['category_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <?php if ($article['status'] === 'published'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Published</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <?php if ($article['is_featured']): ?>
                                            <span class="text-amber-500" title="Featured">★</span>
                                        <?php else: ?>
                                            <span class="text-gray-300">★</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <?php if ($article['is_breaking']): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600">LIVE</span>
                                        <?php else: ?>
                                            <span class="text-gray-300 text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap text-xs">
                                        <?= date('M j, Y', strtotime($article['created_at'])) ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500 text-xs">
                                        <?= number_format((int)$article['views']) ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <a href="news-form.php?id=<?= $article['id'] ?>"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <form method="POST" action="news.php"
                                                  onsubmit="return confirm('Delete this article? This cannot be undone.')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                                <input type="hidden" name="status_filter" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="search_filter" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-lg transition-colors">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
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

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalRows) ?> of <?= $totalRows ?>
                    </p>
                    <div class="flex items-center gap-1">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="?page=<?= $p ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($search) ?>"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors
                                      <?= $p === $page ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>
</body>
</html>
