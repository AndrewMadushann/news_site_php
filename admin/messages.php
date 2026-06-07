<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

// ── POST: delete message ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    if (($_POST['action'] ?? '') === 'delete') {
        $delId = (int)($_POST['id'] ?? 0);
        if ($delId > 0) {
            $pdo->prepare("DELETE FROM contacts WHERE id = ?")->execute([$delId]);
            setFlash('success', 'Message deleted.');
        }
    }

    header('Location: messages.php');
    exit;
}

// ── Fetch messages ────────────────────────────────────────────────────────────
$messagesStmt = $pdo->query(
    "SELECT id, name, email, subject, created_at, is_read
     FROM contacts
     ORDER BY is_read ASC, created_at DESC"
);
$messages   = $messagesStmt->fetchAll();
$unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();

$adminPageTitle = 'Messages';
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
        <div class="flex items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800">Contact Inbox</h1>
                    <?php if ($unreadCount > 0): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-600 text-white">
                            <?= $unreadCount ?> unread
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-gray-500 text-sm mt-0.5"><?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?> total</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    No messages in your inbox.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <tr class="hover:bg-gray-50 transition-colors <?= !$msg['is_read'] ? 'bg-blue-50 hover:bg-blue-50' : '' ?>">
                                    <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">#<?= $msg['id'] ?></td>
                                    <td class="px-5 py-3.5">
                                        <span class="font-<?= !$msg['is_read'] ? 'semibold' : 'medium' ?> text-gray-800">
                                            <?= htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500 text-xs">
                                        <?= htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="px-5 py-3.5 max-w-xs">
                                        <p class="<?= !$msg['is_read'] ? 'font-semibold text-gray-800' : 'text-gray-600' ?> truncate">
                                            <?= htmlspecialchars($msg['subject'] ?? '(No subject)', ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">
                                        <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <?php if (!$msg['is_read']): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                                Unread
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                                Read
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <!-- View -->
                                            <a href="message-view.php?id=<?= $msg['id'] ?>"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </a>

                                            <!-- Delete -->
                                            <form method="POST" action="messages.php"
                                                  onsubmit="return confirm('Delete this message permanently?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
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
        </div>

    </main>
</div>
</body>
</html>
