<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

// ── Resolve message ───────────────────────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    header('Location: messages.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    setFlash('error', 'Message not found.');
    header('Location: messages.php');
    exit;
}

// Auto-mark as read
if (!$message['is_read']) {
    $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?")->execute([$id]);
}

// ── POST: delete ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare("DELETE FROM contacts WHERE id = ?")->execute([$id]);
        setFlash('success', 'Message deleted.');
        header('Location: messages.php');
        exit;
    }
}

$adminPageTitle = 'View Message';
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
                <h1 class="text-2xl font-bold text-gray-800">Message Detail</h1>
                <p class="text-gray-500 text-sm mt-0.5">From: <?= htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="messages.php"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                ← Back to Inbox
            </a>
        </div>

        <div class="max-w-3xl">

            <!-- Message card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">

                <!-- Sender info header -->
                <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <span class="text-red-600 font-bold text-lg">
                                <?= strtoupper(mb_substr($message['name'], 0, 1)) ?>
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-lg font-bold text-gray-800">
                                <?= htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                            <p class="text-sm text-gray-500">
                                <a href="mailto:<?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>"
                                   class="text-blue-600 hover:underline">
                                    <?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-gray-400">
                                <?= date('F j, Y', strtotime($message['created_at'])) ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?= date('g:i A', strtotime($message['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Subject -->
                <div class="px-6 py-4 border-b border-gray-50">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Subject</p>
                    <p class="text-base font-semibold text-gray-800">
                        <?= htmlspecialchars($message['subject'] ?? '(No subject)', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>

                <!-- Message body -->
                <div class="px-6 py-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Message</p>
                    <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap bg-gray-50 rounded-xl p-4">
                        <?= htmlspecialchars($message['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap gap-3">
                <!-- Reply via email -->
                <a href="mailto:<?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>?subject=Re%3A+<?= rawurlencode($message['subject'] ?? '') ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Reply via Email
                </a>

                <!-- Back -->
                <a href="messages.php"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    ← Back to Inbox
                </a>

                <!-- Delete -->
                <form method="POST" action="message-view.php?id=<?= $id ?>"
                      onsubmit="return confirm('Delete this message permanently?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold rounded-xl border border-red-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Message
                    </button>
                </form>
            </div>

        </div>

    </main>
</div>
</body>
</html>
