<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();

$errors   = [];
$adminId  = (int)$_SESSION['admin_id'];

// ── Fetch current admin ───────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    destroySession();
    header('Location: login.php');
    exit;
}

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $action = $_POST['action'] ?? '';

    // ── Update profile ──
    if ($action === 'profile') {
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail    = trim($_POST['email'] ?? '');

        if ($newUsername === '') {
            $errors[] = 'Username is required.';
        }
        if ($newEmail !== '' && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Check username uniqueness (exclude self)
        if (empty($errors) && $newUsername !== '') {
            $dup = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $dup->execute([$newUsername, $adminId]);
            if ($dup->fetch()) {
                $errors[] = 'This username is already taken.';
            }
        }

        if (empty($errors)) {
            $pdo->prepare(
                "UPDATE admins SET username = ?, email = ?, updated_at = NOW() WHERE id = ?"
            )->execute([$newUsername, $newEmail, $adminId]);

            // Update session
            $_SESSION['admin_username'] = $newUsername;
            $_SESSION['admin_email']    = $newEmail;

            setFlash('success', 'Profile updated successfully.');
            header('Location: settings.php');
            exit;
        }

        // Repopulate on error
        $admin['username'] = $newUsername;
        $admin['email']    = $newEmail;
    }

    // ── Change password ──
    if ($action === 'password') {
        $currentPwd = $_POST['current_password'] ?? '';
        $newPwd     = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';

        if ($currentPwd === '') {
            $errors[] = 'Current password is required.';
        } elseif (!password_verify($currentPwd, $admin['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }

        if ($newPwd === '') {
            $errors[] = 'New password is required.';
        } elseif (strlen($newPwd) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if ($newPwd !== '' && $newPwd !== $confirmPwd) {
            $errors[] = 'New password and confirmation do not match.';
        }

        if (empty($errors)) {
            $hash = password_hash($newPwd, PASSWORD_BCRYPT);
            $pdo->prepare(
                "UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?"
            )->execute([$hash, $adminId]);
            setFlash('success', 'Password changed successfully.');
            header('Location: settings.php');
            exit;
        }
    }
}

$adminPageTitle = 'Settings';
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
            <h1 class="text-2xl font-bold text-gray-800">Account Settings</h1>
            <p class="text-gray-500 text-sm mt-0.5">Update your profile and password</p>
        </div>

        <!-- Errors -->
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

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 max-w-4xl">

            <!-- Profile section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-800">Profile Information</h2>
                </div>

                <form method="POST" action="settings.php" class="p-6 space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="profile">

                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?>"
                               autocomplete="username"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400"
                               required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($admin['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="admin@example.com"
                               autocomplete="email"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-800">Change Password</h2>
                </div>

                <form method="POST" action="settings.php" class="p-6 space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="password">

                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password"
                               autocomplete="current-password"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                               required>
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="new_password" name="new_password"
                               autocomplete="new-password"
                               placeholder="At least 8 characters"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                               required minlength="8">
                        <!-- Password strength bar -->
                        <div class="mt-2">
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-full rounded-full transition-all duration-300 w-0"></div>
                            </div>
                            <p id="strengthLabel" class="text-xs text-gray-400 mt-1"></p>
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               autocomplete="new-password"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400"
                               required>
                        <p id="matchLabel" class="text-xs mt-1 hidden"></p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Account info card -->
        <div class="mt-6 max-w-4xl bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Account Information</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Role</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                        <?= htmlspecialchars($admin['role'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Username</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Member Since</p>
                    <p class="font-medium text-gray-800">
                        <?= isset($admin['created_at']) ? date('M Y', strtotime($admin['created_at'])) : '—' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Last Updated</p>
                    <p class="font-medium text-gray-800">
                        <?= isset($admin['updated_at']) ? date('M j, Y', strtotime($admin['updated_at'])) : '—' ?>
                    </p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
// Password strength indicator
const newPwdInput     = document.getElementById('new_password');
const confirmPwdInput = document.getElementById('confirm_password');
const strengthBar     = document.getElementById('strengthBar');
const strengthLabel   = document.getElementById('strengthLabel');
const matchLabel      = document.getElementById('matchLabel');

newPwdInput.addEventListener('input', function() {
    const val = this.value;
    let score  = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { label: '', color: '', width: '0%' },
        { label: 'Weak', color: 'bg-red-500', width: '25%' },
        { label: 'Fair', color: 'bg-orange-400', width: '50%' },
        { label: 'Good', color: 'bg-yellow-400', width: '75%' },
        { label: 'Strong', color: 'bg-green-500', width: '100%' },
    ];

    const level = val.length === 0 ? 0 : score;
    strengthBar.className = 'h-full rounded-full transition-all duration-300 ' + (levels[level]?.color || '');
    strengthBar.style.width = levels[level]?.width || '0%';
    strengthLabel.textContent = val.length > 0 ? levels[level]?.label : '';
    strengthLabel.className = 'text-xs mt-1 ' + (level >= 3 ? 'text-green-600' : level >= 2 ? 'text-yellow-600' : 'text-red-500');
});

function checkMatch() {
    const match = newPwdInput.value === confirmPwdInput.value && confirmPwdInput.value !== '';
    const mismatch = confirmPwdInput.value !== '' && newPwdInput.value !== confirmPwdInput.value;
    matchLabel.classList.toggle('hidden', !confirmPwdInput.value);
    if (match) {
        matchLabel.textContent = '✓ Passwords match';
        matchLabel.className = 'text-xs mt-1 text-green-600';
    } else if (mismatch) {
        matchLabel.textContent = '✗ Passwords do not match';
        matchLabel.className = 'text-xs mt-1 text-red-500';
    }
}

confirmPwdInput.addEventListener('input', checkMatch);
newPwdInput.addEventListener('input', checkMatch);
</script>
</body>
</html>
