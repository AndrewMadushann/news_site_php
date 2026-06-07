<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/upload.php';

requireLogin();

// ── POST handler ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT pdf_path, thumbnail FROM papers WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if ($row) {
                // Delete PDF file
                if (!empty($row['pdf_path'])) {
                    $pdfPath = dirname(__DIR__) . '/uploads/papers/' . basename($row['pdf_path']);
                    if (file_exists($pdfPath)) @unlink($pdfPath);
                }
                // Delete thumbnail
                if (!empty($row['thumbnail'])) {
                    $thumbPath = dirname(__DIR__) . '/uploads/papers/' . basename($row['thumbnail']);
                    if (file_exists($thumbPath)) @unlink($thumbPath);
                }
                $pdo->prepare("DELETE FROM papers WHERE id = ?")->execute([$id]);
                setFlash('success', 'Edition deleted successfully.');
            } else {
                setFlash('error', 'Edition not found.');
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE papers SET status = 1 - status WHERE id = ?")->execute([$id]);
            setFlash('success', 'Edition status updated.');
        }
    }

    header('Location: papers.php');
    exit;
}

// ── GET: list papers ───────────────────────────────────────────────────────────
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$totalRows  = (int)$pdo->query("SELECT COUNT(*) FROM papers")->fetchColumn();
$totalPages = (int)ceil($totalRows / $perPage);

$stmt = $pdo->prepare(
    "SELECT * FROM papers ORDER BY edition_date DESC LIMIT ? OFFSET ?"
);
$stmt->execute([$perPage, $offset]);
$papers = $stmt->fetchAll();

$adminPageTitle = 'e-Paper Editions';
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
                <h1 class="text-2xl font-bold text-gray-800">e-Paper Editions</h1>
                <p class="text-gray-500 text-sm mt-0.5"><?= number_format($totalRows) ?> edition<?= $totalRows !== 1 ? 's' : '' ?> total</p>
            </div>
            <a href="paper-form.php"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Edition
            </a>
        </div>

        <!-- Papers Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cover</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Edition Date</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Uploaded</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($papers)): ?>
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    No editions uploaded yet.
                                    <a href="paper-form.php" class="text-red-600 hover:underline ml-1">Upload the first edition</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($papers as $i => $paper): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">
                                        <?= $offset + $i + 1 ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <?php if (!empty($paper['thumbnail'])): ?>
                                            <img src="/uploads/papers/<?= htmlspecialchars(basename($paper['thumbnail']), ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="Cover"
                                                 class="w-10 h-14 object-cover rounded shadow-sm border border-gray-200">
                                        <?php else: ?>
                                            <div class="w-10 h-14 bg-gray-100 rounded flex items-center justify-center border border-gray-200">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-gray-800 max-w-xs truncate">
                                            <?= htmlspecialchars($paper['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">
                                        <?= date('d M Y', strtotime($paper['edition_date'])) ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <?php if ($paper['status']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Visible</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                                        <?= date('d M Y', strtotime($paper['created_at'])) ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <!-- View PDF -->
                                            <a href="/paper-view.php?id=<?= $paper['id'] ?>" target="_blank"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </a>
                                            <!-- Toggle status -->
                                            <form method="POST" action="papers.php">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= $paper['id'] ?>">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition-colors"
                                                        title="<?= $paper['status'] ? 'Hide edition' : 'Show edition' ?>">
                                                    <?= $paper['status'] ? 'Hide' : 'Show' ?>
                                                </button>
                                            </form>
                                            <!-- Delete -->
                                            <form method="POST" action="papers.php"
                                                  onsubmit="return confirm('Delete this edition? The PDF and thumbnail will also be deleted.')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $paper['id'] ?>">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-lg transition-colors">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
                            <a href="?page=<?= $p ?>"
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
