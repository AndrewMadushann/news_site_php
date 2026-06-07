<?php
/**
 * Admin Sidebar Component
 *
 * Layout (top → bottom):
 *   1. Brand / logo
 *   2. Scrollable navigation
 *   3. User card + Logout (pinned to bottom)
 *
 * Supports a collapsed (icon-only) state toggled by the user.
 * State persists in localStorage as `admin.sidebarCollapsed`.
 */

$currentFile    = basename($_SERVER['PHP_SELF']);
$unreadMessages = $unreadMessages ?? 0;
$adminUser      = $adminUser ?? [
    'username' => $_SESSION['admin_username'] ?? 'Administrator',
    'role'     => $_SESSION['admin_role']     ?? 'Admin',
];

$navClass = function (array $files) use ($currentFile): string {
    return in_array($currentFile, $files, true)
        ? 'sidebar-nav-item active'
        : 'sidebar-nav-item';
};

$avatarChar = strtoupper(substr($adminUser['username'] ?? 'A', 0, 1));
?>

<!-- Mobile overlay -->
<div id="sidebar-overlay" aria-hidden="true"></div>

<aside id="admin-sidebar" aria-label="Admin navigation">

    <!-- ── Brand ─────────────────────────────────────────── -->
    <div class="sidebar-brand">
        <a href="/admin/index.php" class="sidebar-brand-link">
            <div class="sidebar-brand-icon" aria-hidden="true">
                <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 3a3 3 0 110 6 3 3 0 010-6zm0 14c-3 0-5.5-1.567-5.5-3.5S9 13 12 13s5.5 1.567 5.5 3.5S15 20 12 20z"/>
                </svg>
            </div>
            <div class="sidebar-brand-text">
                <p class="sidebar-brand-title">News Admin</p>
                <p class="sidebar-brand-subtitle">Control Panel</p>
            </div>
        </a>
        <button id="sidebar-collapse-btn" class="sidebar-collapse-btn" type="button"
                aria-label="Collapse sidebar" aria-controls="admin-sidebar">
            <svg class="sidebar-collapse-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- ── Navigation (scrollable) ──────────────────────── -->
    <nav class="sidebar-nav" aria-label="Sidebar navigation">

        <p class="sidebar-section-label">Main</p>

        <a href="/admin/index.php" class="<?= $navClass(['index.php']) ?>" data-tip="Dashboard">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="/admin/news.php" class="<?= $navClass(['news.php', 'news-form.php']) ?>" data-tip="News Articles">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v10a2 2 0 01-2 2zM15 4v4a1 1 0 001 1h4M9 9h1M9 13h6M9 17h6"/>
            </svg>
            <span class="nav-label">News Articles</span>
        </a>

        <a href="/admin/categories.php" class="<?= $navClass(['categories.php']) ?>" data-tip="Categories">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <span class="nav-label">Categories</span>
        </a>

        <p class="sidebar-section-label">Content</p>

        <a href="/admin/messages.php" class="<?= $navClass(['messages.php', 'message-view.php']) ?>" data-tip="Messages">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="nav-label">Messages</span>
            <?php if ($unreadMessages > 0): ?>
                <span class="unread-badge" aria-label="<?= (int)$unreadMessages ?> unread">
                    <?= (int)$unreadMessages > 99 ? '99+' : (int)$unreadMessages ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="/admin/about.php" class="<?= $navClass(['about.php']) ?>" data-tip="About Page">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="nav-label">About Page</span>
        </a>

        <p class="sidebar-section-label">System</p>

        <a href="/admin/settings.php" class="<?= $navClass(['settings.php']) ?>" data-tip="Settings">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="nav-label">Settings</span>
        </a>

        <a href="/index.php" target="_blank" rel="noopener noreferrer"
           class="sidebar-nav-item" data-tip="View Site">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            <span class="nav-label">View Site</span>
        </a>

    </nav>

    <!-- ── Footer: User + Logout ─────────────────────────── -->
    <div class="sidebar-footer">
        <div class="sidebar-user" title="<?= htmlspecialchars($adminUser['username'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="sidebar-user-avatar"><?= htmlspecialchars($avatarChar, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="sidebar-user-meta">
                <p class="sidebar-user-name"><?= htmlspecialchars($adminUser['username'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="sidebar-user-role"><?= htmlspecialchars($adminUser['role'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
        <a href="/admin/logout.php" data-confirm="Log out of the admin panel?"
           class="sidebar-logout" data-tip="Logout">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="nav-label">Logout</span>
        </a>
    </div>

</aside>

<!-- ══════════════════════════════════════════════════
     ADMIN TOP BAR
     ══════════════════════════════════════════════════ -->
<div class="admin-topbar">
    <div class="flex items-center gap-3">
        <button id="admin-sidebar-toggle" class="topbar-icon-btn md:hidden"
                aria-label="Toggle sidebar" aria-expanded="true" aria-controls="admin-sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div>
            <h1 class="text-base font-semibold text-gray-900 leading-none tracking-tight">
                <?= htmlspecialchars($adminPageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <nav aria-label="Breadcrumb" class="hidden sm:flex items-center gap-1 mt-1">
                <a href="/admin/index.php" class="text-xs text-gray-400 hover:text-red-600 transition-colors">Admin</a>
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-xs text-gray-600 font-medium">
                    <?= htmlspecialchars($adminPageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </nav>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="/admin/news-form.php"
           class="hidden sm:inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            New Article
        </a>

        <a href="/admin/messages.php" class="topbar-icon-btn relative"
           aria-label="Messages<?= $unreadMessages > 0 ? " ($unreadMessages unread)" : '' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <?php if ($unreadMessages > 0): ?>
                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                    <?= min((int)$unreadMessages, 9) ?><?= $unreadMessages > 9 ? '+' : '' ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="/admin/settings.php" aria-label="Profile settings"
           class="w-8 h-8 bg-red-600 hover:bg-red-700 rounded-full flex items-center justify-center text-white font-semibold text-sm transition-colors">
            <?= htmlspecialchars($avatarChar, ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>
</div>

<script>
(function () {
    'use strict';

    var body         = document.body;
    var sidebar      = document.getElementById('admin-sidebar');
    var overlay      = document.getElementById('sidebar-overlay');
    var mobileToggle = document.getElementById('admin-sidebar-toggle');
    var collapseBtn  = document.getElementById('sidebar-collapse-btn');
    if (!sidebar) return;

    var STORAGE_KEY  = 'admin.sidebarCollapsed';
    var DESKTOP_BP   = 768;
    var isDesktop    = function () { return window.innerWidth >= DESKTOP_BP; };

    // ── Collapsed state (desktop) ────────────────────────
    function applyCollapsed(collapsed) {
        body.classList.toggle('sidebar-collapsed', collapsed);
        if (collapseBtn) {
            collapseBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }
    }
    try {
        applyCollapsed(localStorage.getItem(STORAGE_KEY) === '1');
    } catch (_) { /* localStorage unavailable */ }

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            var nowCollapsed = !body.classList.contains('sidebar-collapsed');
            applyCollapsed(nowCollapsed);
            try { localStorage.setItem(STORAGE_KEY, nowCollapsed ? '1' : '0'); } catch (_) {}
        });
    }

    // ── Mobile sidebar (overlay) ─────────────────────────
    function openMobileSidebar() {
        sidebar.classList.remove('sidebar-hidden');
        if (overlay) { overlay.classList.add('active'); overlay.setAttribute('aria-hidden', 'false'); }
        if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'true');
    }
    function closeMobileSidebar() {
        sidebar.classList.add('sidebar-hidden');
        if (overlay) { overlay.classList.remove('active'); overlay.setAttribute('aria-hidden', 'true'); }
        if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
    }

    if (!isDesktop()) sidebar.classList.add('sidebar-hidden');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.contains('sidebar-hidden') ? openMobileSidebar() : closeMobileSidebar();
        });
    }
    if (overlay) overlay.addEventListener('click', closeMobileSidebar);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !isDesktop() && !sidebar.classList.contains('sidebar-hidden')) {
            closeMobileSidebar();
        }
    });

    window.addEventListener('resize', function () {
        if (isDesktop()) {
            sidebar.classList.remove('sidebar-hidden');
            if (overlay) overlay.classList.remove('active');
        } else {
            sidebar.classList.add('sidebar-hidden');
        }
    });

    // ── Confirm logout ───────────────────────────────────
    var logoutLink = sidebar.querySelector('a[data-confirm]');
    if (logoutLink) {
        logoutLink.addEventListener('click', function (e) {
            if (!window.confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    }
})();
</script>
