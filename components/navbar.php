<?php
$categories = getCategories($pdo);
$breaking   = getBreakingNews($pdo);
$currentPath = $_SERVER['REQUEST_URI'];
?>

<!-- ═══════════════════════════════════════ TOP BAR ═══════════════════════════════════════ -->
<div class="bg-gray-900 text-gray-400 text-xs py-1.5">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
    <span><?= date('l, F j, Y') ?></span>
    <div class="flex items-center gap-4">
      <!-- Facebook -->
      <a href="#" aria-label="Facebook" class="hover:text-red-400 transition-colors duration-200">
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.883v2.273h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
        </svg>
      </a>
      <!-- Twitter / X -->
      <a href="#" aria-label="Twitter" class="hover:text-red-400 transition-colors duration-200">
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
        </svg>
      </a>
      <!-- Instagram -->
      <a href="#" aria-label="Instagram" class="hover:text-red-400 transition-colors duration-200">
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
        </svg>
      </a>
      <!-- YouTube -->
      <a href="#" aria-label="YouTube" class="hover:text-red-400 transition-colors duration-200">
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
        </svg>
      </a>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════ LOGO BAR ═══════════════════════════════════════ -->
<div class="bg-white border-b border-gray-100 py-5">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between gap-4">
    <!-- Left: Est. tag -->
    <div class="hidden md:block text-xs text-gray-400 tracking-widest uppercase font-medium">
      Est.&nbsp;2020
    </div>

    <!-- Center: Logo -->
    <a href="/index.php" class="text-center flex-shrink-0 mx-auto md:mx-0">
      <h1 class="font-serif text-4xl font-black text-gray-900 tracking-tight leading-none">
        Daily<span class="text-red-600">.</span>News
      </h1>
      <p class="text-xs text-gray-400 tracking-[0.2em] uppercase mt-0.5">Your Trusted News Source</p>
    </a>

    <!-- Right: Search -->
    <form action="/news.php" method="GET" class="hidden md:flex items-center">
      <input
        type="text"
        name="q"
        placeholder="Search news…"
        value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        class="border border-gray-300 rounded-l-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent w-52 transition"
      >
      <button
        type="submit"
        aria-label="Search"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-r-lg text-sm transition-colors duration-200 flex items-center gap-1"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
      </button>
    </form>
  </div>
</div>

<!-- ═════════════════════════════════ CATEGORY NAV (Sticky) ═════════════════════════════════ -->
<nav class="bg-red-600 text-white shadow-lg sticky top-0 z-50" id="main-nav">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between">

      <!-- Nav links (desktop) -->
      <div class="hidden md:flex items-center overflow-x-auto scrollbar-hide">
        <a
          href="/index.php"
          class="shrink-0 px-4 py-3 text-sm font-medium hover:bg-red-700 transition-colors duration-150 whitespace-nowrap border-b-2 border-transparent hover:border-white <?= ($currentPath === '/index.php' || $currentPath === '/') ? 'bg-red-700 border-white' : '' ?>"
        >Home</a>
        <a
          href="/news.php"
          class="shrink-0 px-4 py-3 text-sm font-medium hover:bg-red-700 transition-colors duration-150 whitespace-nowrap border-b-2 border-transparent hover:border-white <?= (strpos($currentPath, '/news.php') === 0 && empty($_GET['cat'])) ? 'bg-red-700 border-white' : '' ?>"
        >All News</a>
        <?php foreach ($categories as $cat): ?>
          <a
            href="/news.php?cat=<?= urlencode($cat['slug']) ?>"
            class="shrink-0 px-4 py-3 text-sm font-medium hover:bg-red-700 transition-colors duration-150 whitespace-nowrap border-b-2 border-transparent hover:border-white <?= (isset($_GET['cat']) && $_GET['cat'] === $cat['slug']) ? 'bg-red-700 border-white' : '' ?>"
          ><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
        <a
          href="/about.php"
          class="shrink-0 px-4 py-3 text-sm font-medium hover:bg-red-700 transition-colors duration-150 whitespace-nowrap border-b-2 border-transparent hover:border-white <?= (strpos($currentPath, '/about.php') === 0) ? 'bg-red-700 border-white' : '' ?>"
        >About</a>
        <a
          href="/contact.php"
          class="shrink-0 px-4 py-3 text-sm font-medium hover:bg-red-700 transition-colors duration-150 whitespace-nowrap border-b-2 border-transparent hover:border-white <?= (strpos($currentPath, '/contact.php') === 0) ? 'bg-red-700 border-white' : '' ?>"
        >Contact</a>
      </div>

      <!-- Mobile: Logo in nav + Hamburger -->
      <div class="flex md:hidden items-center justify-between w-full py-2">
        <a href="/index.php" class="font-serif text-xl font-black tracking-tight">
          Daily<span class="text-red-200">.</span>News
        </a>
        <button
          id="mobile-menu-btn"
          class="p-2 rounded-md hover:bg-red-700 transition-colors duration-150"
          aria-label="Toggle menu"
          aria-expanded="false"
          aria-controls="mobile-menu"
        >
          <svg id="hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg id="close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Mobile search icon (desktop search is hidden on mobile) -->
      <a href="/news.php" class="hidden md:hidden p-2 shrink-0" aria-label="Search">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
      </a>
    </div>
  </div>

  <!-- Mobile Menu Dropdown -->
  <div id="mobile-menu" class="md:hidden hidden bg-red-700 border-t border-red-500">
    <div class="px-2 py-2 space-y-0.5">
      <a href="/index.php" class="block px-4 py-2.5 text-sm font-medium rounded hover:bg-red-800 transition">Home</a>
      <a href="/news.php" class="block px-4 py-2.5 text-sm font-medium rounded hover:bg-red-800 transition">All News</a>
      <?php foreach ($categories as $cat): ?>
        <a href="/news.php?cat=<?= urlencode($cat['slug']) ?>" class="block px-4 py-2.5 text-sm font-medium rounded hover:bg-red-800 transition">
          <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>
      <a href="/about.php" class="block px-4 py-2.5 text-sm font-medium rounded hover:bg-red-800 transition">About</a>
      <a href="/contact.php" class="block px-4 py-2.5 text-sm font-medium rounded hover:bg-red-800 transition">Contact</a>
      <!-- Mobile search form -->
      <div class="px-4 py-3">
        <form action="/news.php" method="GET" class="flex">
          <input type="text" name="q" placeholder="Search news…" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            class="flex-1 border-0 rounded-l-lg px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
          <button type="submit" class="bg-gray-900 hover:bg-black text-white px-3 py-2 rounded-r-lg text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</nav>

<!-- ══════════════════════════════ BREAKING NEWS TICKER ══════════════════════════════════ -->
<?php if (!empty($breaking)): ?>
<div class="bg-gray-900 text-white text-xs py-2 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-3">
    <span class="bg-red-600 text-white px-3 py-1 rounded font-bold text-xs shrink-0 uppercase tracking-widest animate-pulse">
      Breaking
    </span>
    <div class="overflow-hidden flex-1">
      <div class="ticker-wrap">
        <div class="ticker-content">
          <?php foreach ($breaking as $b): ?>
            <span class="mr-6 text-gray-300">&#9654;</span>
            <a
              href="/article.php?slug=<?= urlencode($b['slug']) ?>"
              class="mr-10 hover:text-red-400 transition-colors duration-200 font-medium"
            ><?= htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8') ?></a>
          <?php endforeach; ?>
          <!-- Duplicate for seamless loop -->
          <?php foreach ($breaking as $b): ?>
            <span class="mr-6 text-gray-300">&#9654;</span>
            <a
              href="/article.php?slug=<?= urlencode($b['slug']) ?>"
              class="mr-10 hover:text-red-400 transition-colors duration-200 font-medium"
            ><?= htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8') ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
