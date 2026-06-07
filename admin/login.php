<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error    = '';
$attempts = $_SESSION['login_attempts'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($attempts >= 5) {
        $error = 'Too many failed attempts. Please wait and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['login_attempts'] = 0;
            setAdminSession($admin);
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_attempts'] = ++$attempts;
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — NewsAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #1a0505 40%, #2d0a0a 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }
        .input-field {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #dc2626;
            outline: none;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
        }
        .input-field::placeholder {
            color: rgba(255,255,255,0.35);
        }
        .btn-login {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        }
        .logo-ring {
            background: linear-gradient(135deg, #dc2626, #7f1d1d);
            box-shadow: 0 0 30px rgba(220, 38, 38, 0.4);
        }
        .particle {
            position: fixed;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.15);
            animation: float linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Decorative particles -->
    <div class="particle" style="width:8px;height:8px;left:15%;animation-duration:12s;animation-delay:0s;"></div>
    <div class="particle" style="width:12px;height:12px;left:35%;animation-duration:18s;animation-delay:3s;"></div>
    <div class="particle" style="width:6px;height:6px;left:55%;animation-duration:15s;animation-delay:6s;"></div>
    <div class="particle" style="width:10px;height:10px;left:75%;animation-duration:20s;animation-delay:1s;"></div>
    <div class="particle" style="width:7px;height:7px;left:85%;animation-duration:14s;animation-delay:9s;"></div>

    <div class="glass-card rounded-2xl p-8 w-full max-w-md relative z-10">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="logo-ring w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">NewsAdmin</h1>
            <p class="text-gray-400 text-sm mt-1">Sign in to your dashboard</p>
        </div>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-300 text-sm"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <!-- Brute force warning -->
        <?php if ($attempts > 0 && $attempts < 5): ?>
            <div class="mb-4 text-center">
                <span class="text-yellow-400 text-xs font-medium">
                    <?= 5 - $attempts ?> attempt<?= (5 - $attempts) !== 1 ? 's' : '' ?> remaining
                </span>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="" novalidate>
            <div class="mb-5">
                <label for="username" class="block text-gray-300 text-sm font-medium mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                        class="input-field w-full pl-10 pr-4 py-3 rounded-xl text-sm"
                    >
                </div>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        autocomplete="current-password"
                        required
                        class="input-field w-full pl-10 pr-12 py-3 rounded-xl text-sm"
                    >
                    <button type="button" id="togglePwd"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-300 transition-colors">
                        <svg class="w-5 h-5" id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <?php if ($attempts >= 5): ?>
                <button type="submit" disabled
                        class="w-full py-3 rounded-xl text-white font-semibold text-sm opacity-50 cursor-not-allowed bg-gray-600">
                    Account Locked — Too Many Attempts
                </button>
            <?php else: ?>
                <button type="submit"
                        class="btn-login w-full py-3 rounded-xl text-white font-semibold text-sm">
                    Sign In to Dashboard
                </button>
            <?php endif; ?>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600 text-xs">
                &copy; <?= date('Y') ?> NewsAdmin. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        const togglePwd = document.getElementById('togglePwd');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePwd.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.innerHTML = isPassword
                ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
                : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        });
    </script>
</body>
</html>
