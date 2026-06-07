<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

// Fetch stats
$totalNews    = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$published    = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$drafts       = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'draft'")->fetchColumn();
$unreadMsgs   = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
$todayNews    = $pdo->query("SELECT COUNT(*) FROM news WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Recent 5 articles
$recentStmt = $pdo->query(
    "SELECT n.id, n.title, n.status, n.created_at, n.views, c.name AS category_name
     FROM news n
     LEFT JOIN categories c ON n.category_id = c.id
     ORDER BY n.created_at DESC
     LIMIT 5"
);
$recentArticles = $recentStmt->fetchAll();

$adminPageTitle = 'Dashboard';
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

        <!-- Page header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-500 text-sm mt-1">Welcome back, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>! Here's what's happening today.</p>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
            <!-- Total News -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total News</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format((int)$totalNews) ?></p>
                </div>
            </div>

            <!-- Published -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Published</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format((int)$published) ?></p>
                </div>
            </div>

            <!-- Drafts -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Drafts</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format((int)$drafts) ?></p>
                </div>
            </div>

            <!-- Unread Messages -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow <?= $unreadMsgs > 0 ? 'ring-2 ring-red-200' : '' ?>">
                <div class="w-12 h-12 rounded-xl <?= $unreadMsgs > 0 ? 'bg-red-100' : 'bg-gray-100' ?> flex items-center justify-center flex-shrink-0 relative">
                    <svg class="w-6 h-6 <?= $unreadMsgs > 0 ? 'text-red-600' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <?php if ($unreadMsgs > 0): ?>
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-xs font-bold"><?= min($unreadMsgs, 9) ?></span>
                        </span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Unread Messages</p>
                    <p class="text-2xl font-bold <?= $unreadMsgs > 0 ? 'text-red-600' : 'text-gray-800' ?>"><?= number_format((int)$unreadMsgs) ?></p>
                </div>
            </div>

            <!-- Today's News -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Today's News</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format((int)$todayNews) ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Quick Actions</h2>
            <div class="flex flex-wrap gap-3">
                <a href="news-form.php"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Article
                </a>
                <a href="categories.php"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Manage Categories
                </a>
                <a href="messages.php"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    View Messages
                    <?php if ($unreadMsgs > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"><?= $unreadMsgs ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Recent Articles -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Recent Articles</h2>
                <a href="news.php" class="text-red-600 hover:text-red-700 text-sm font-medium">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($recentArticles)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    No articles yet. <a href="news-form.php" class="text-red-600 hover:underline">Create one</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentArticles as $article): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-gray-400 font-mono text-xs">#<?= $article['id'] ?></td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-800 line-clamp-1 max-w-xs">
                                            <?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= htmlspecialchars($article['category_name'] ?? 'Uncategorised', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($article['status'] === 'published'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                Published
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                Draft
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                        <?= date('M j, Y', strtotime($article['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="news-form.php?id=<?= $article['id'] ?>"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 text-xs font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
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
