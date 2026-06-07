<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/functions.php';

// ── CSRF token generation ────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors      = [];
$success     = false;
$oldInput    = [];

// ── POST handler ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF verification
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $errors['csrf'] = 'Invalid security token. Please try again.';
    } else {

        // Collect & sanitize
        $name    = trim(filter_input(INPUT_POST, 'name',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email   = trim(filter_input(INPUT_POST, 'email',   FILTER_SANITIZE_EMAIL) ?? '');
        $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

        $oldInput = compact('name', 'email', 'subject', 'message');

        // Validation
        if (strlen($name) < 2 || strlen($name) > 100) {
            $errors['name'] = 'Name must be between 2 and 100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (strlen($subject) < 3 || strlen($subject) > 200) {
            $errors['subject'] = 'Subject must be between 3 and 200 characters.';
        }
        if (strlen($message) < 10 || strlen($message) > 5000) {
            $errors['message'] = 'Message must be between 10 and 5000 characters.';
        }

        // Insert if valid
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO contacts (name, email, subject, message, created_at)
                     VALUES (?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$name, $email, $subject, $message]);
                // Regenerate CSRF token after successful submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                // PRG redirect
                header('Location: contact.php?sent=1');
                exit;
            } catch (PDOException $e) {
                error_log('[contact.php] insert failed: ' . $e->getMessage());
                $errors['db'] = 'Unable to send your message. Please try again later.';
            }
        }
    }
}

// Check for success flash
$sent = isset($_GET['sent']) && $_GET['sent'] === '1';

// Fetch contact info HTML block from site_content
try {
    $contactBlock = getSiteContent($pdo, 'contact_info');
} catch (PDOException $e) {
    error_log('[contact.php] ' . $e->getMessage());
    $contactBlock = null;
}

$pageTitle = 'Contact Us';
$metaDesc  = 'Get in touch with the Daily News editorial team. We\'d love to hear from you.';
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ========================================================
     PAGE HERO
     ======================================================== -->
<div class="bg-gradient-to-br from-gray-900 via-red-950 to-gray-900 text-white py-14">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block bg-red-600/20 text-red-300 text-xs font-bold uppercase tracking-widest px-4 py-1.5 rounded-full mb-4 border border-red-600/30">
            Get in Touch
        </span>
        <h1 class="font-serif text-4xl md:text-5xl font-bold mb-3">Contact Us</h1>
        <p class="text-gray-300 text-lg max-w-xl mx-auto leading-relaxed">
            Have a news tip, question, or feedback? We'd love to hear from you.
        </p>
    </div>
</div>

<!-- ========================================================
     BREADCRUMB
     ======================================================== -->
<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="index.php" class="hover:text-red-600 transition">Home</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Contact</span>
        </nav>
    </div>
</div>

<!-- ========================================================
     MAIN CONTENT
     ======================================================== -->
<main class="bg-gray-50 py-14">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- CONTACT FORM -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="font-serif text-2xl font-bold text-gray-900 mb-2">Send Us a Message</h2>
                    <p class="text-gray-500 text-sm mb-7">Fill out the form below and we'll get back to you as soon as possible.</p>

                    <!-- Success message -->
                    <?php if ($sent): ?>
                    <div class="flex items-start gap-4 bg-green-50 border border-green-200 text-green-800 rounded-xl p-5 mb-7">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">Message Sent Successfully!</p>
                            <p class="text-sm text-green-700 mt-0.5">Thank you for reaching out. Our team will respond within 1–2 business days.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- CSRF error -->
                    <?php if (!empty($errors['csrf'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                        <?= htmlspecialchars($errors['csrf']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- DB error -->
                    <?php if (!empty($errors['db'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?= htmlspecialchars($errors['db']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" action="contact.php" novalidate class="space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <!-- Name + Email row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>"
                                    placeholder="John Doe"
                                    maxlength="100"
                                    class="w-full border rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition
                                           <?= !empty($errors['name']) ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' ?>"
                                >
                                <?php if (!empty($errors['name'])): ?>
                                <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <?= htmlspecialchars($errors['name']) ?>
                                </p>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                                    placeholder="you@example.com"
                                    maxlength="200"
                                    class="w-full border rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition
                                           <?= !empty($errors['email']) ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' ?>"
                                >
                                <?php if (!empty($errors['email'])): ?>
                                <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <?= htmlspecialchars($errors['email']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="subject"
                                name="subject"
                                value="<?= htmlspecialchars($oldInput['subject'] ?? '') ?>"
                                placeholder="What is this regarding?"
                                maxlength="200"
                                class="w-full border rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition
                                       <?= !empty($errors['subject']) ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' ?>"
                            >
                            <?php if (!empty($errors['subject'])): ?>
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <?= htmlspecialchars($errors['subject']) ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                rows="7"
                                placeholder="Write your message here…"
                                maxlength="5000"
                                class="w-full border rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition resize-none
                                       <?= !empty($errors['message']) ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white' ?>"
                            ><?= htmlspecialchars($oldInput['message'] ?? '') ?></textarea>
                            <?php if (!empty($errors['message'])): ?>
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <?= htmlspecialchars($errors['message']) ?>
                            </p>
                            <?php endif; ?>
                            <p class="text-gray-400 text-xs mt-1 text-right" id="char-count">0 / 5000</p>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold py-3.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2 text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            <!-- SIDEBAR -->
            <aside class="space-y-6">
                <!-- Contact details -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 text-base mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Our Information
                    </h3>
                    <div class="prose prose-sm max-w-none text-gray-700 prose-p:my-1 prose-a:text-red-600 prose-strong:text-gray-900">
                        <?php if ($contactBlock && !empty($contactBlock['content'])): ?>
                            <?= $contactBlock['content'] ?>
                        <?php else: ?>
                            <p class="text-gray-400 text-sm py-4 text-center">Contact details coming soon.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Office hours -->
                <div class="bg-gray-900 rounded-2xl p-6 text-white">
                    <h3 class="font-bold text-base mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Office Hours
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Monday – Friday</span>
                            <span class="text-white font-medium">9:00 AM – 6:00 PM</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Saturday</span>
                            <span class="text-white font-medium">10:00 AM – 2:00 PM</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Sunday</span>
                            <span class="text-red-400 font-medium">Closed</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-700 text-xs text-gray-500">
                        For breaking news tips, we monitor email 24/7.
                    </div>
                </div>

                <!-- Social links -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 text-base mb-4">Follow Us</h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="#" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                        <a href="#" class="flex items-center gap-2 bg-black hover:bg-gray-800 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            Twitter
                        </a>
                        <a href="#" class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
// Character counter for message textarea
var textarea = document.getElementById('message');
var counter  = document.getElementById('char-count');
if (textarea && counter) {
    counter.textContent = textarea.value.length + ' / 5000';
    textarea.addEventListener('input', function () {
        counter.textContent = this.value.length + ' / 5000';
        counter.classList.toggle('text-red-500', this.value.length > 4800);
        counter.classList.toggle('text-gray-400', this.value.length <= 4800);
    });
}
</script>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
