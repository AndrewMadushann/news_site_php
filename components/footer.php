<?php
// Footer is self-contained — no external dependencies required.
$footerYear = date('Y');
$footerCategories = [
    ['name' => 'Politics',      'slug' => 'politics'],
    ['name' => 'Sports',        'slug' => 'sports'],
    ['name' => 'Business',      'slug' => 'business'],
    ['name' => 'Technology',    'slug' => 'technology'],
    ['name' => 'Entertainment', 'slug' => 'entertainment'],
    ['name' => 'World',         'slug' => 'world'],
];
?>

<!-- ════════════════════════════════════ FOOTER ════════════════════════════════════ -->
<footer class="bg-gray-900 text-gray-300 mt-16">

  <!-- Divider accent line -->
  <div class="h-1 bg-gradient-to-r from-red-700 via-red-600 to-red-700"></div>

  <!-- Main footer grid -->
  <div class="max-w-7xl mx-auto px-4 py-14">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

      <!-- ── Column 1: Brand & Social ── -->
      <div class="lg:col-span-1">
        <a href="/index.php" class="inline-block mb-4">
          <h2 class="font-serif text-3xl font-black text-white tracking-tight">
            Daily<span class="text-red-500">.</span>News
          </h2>
        </a>
        <p class="text-sm text-gray-400 leading-relaxed mb-6">
          Your trusted source for breaking news, in-depth analysis, and stories that matter — delivered around the clock.
        </p>
        <!-- Social Icons -->
        <div class="flex items-center gap-3">
          <!-- Facebook -->
          <a href="#" aria-label="Facebook"
             class="w-9 h-9 bg-gray-800 hover:bg-red-600 rounded-full flex items-center justify-center transition-colors duration-200 group">
            <svg class="w-4 h-4 fill-current text-gray-400 group-hover:text-white transition-colors" viewBox="0 0 24 24">
              <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.883v2.273h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
            </svg>
          </a>
          <!-- Twitter / X -->
          <a href="#" aria-label="Twitter"
             class="w-9 h-9 bg-gray-800 hover:bg-red-600 rounded-full flex items-center justify-center transition-colors duration-200 group">
            <svg class="w-4 h-4 fill-current text-gray-400 group-hover:text-white transition-colors" viewBox="0 0 24 24">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
          </a>
          <!-- Instagram -->
          <a href="#" aria-label="Instagram"
             class="w-9 h-9 bg-gray-800 hover:bg-red-600 rounded-full flex items-center justify-center transition-colors duration-200 group">
            <svg class="w-4 h-4 fill-current text-gray-400 group-hover:text-white transition-colors" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
            </svg>
          </a>
          <!-- YouTube -->
          <a href="#" aria-label="YouTube"
             class="w-9 h-9 bg-gray-800 hover:bg-red-600 rounded-full flex items-center justify-center transition-colors duration-200 group">
            <svg class="w-4 h-4 fill-current text-gray-400 group-hover:text-white transition-colors" viewBox="0 0 24 24">
              <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- ── Column 2: Quick Links ── -->
      <div>
        <h3 class="text-white text-sm font-bold uppercase tracking-widest mb-5 pb-2 border-b border-gray-700">
          Quick Links
        </h3>
        <ul class="space-y-2.5">
          <?php
          $quickLinks = [
            ['label' => 'Home',        'href' => '/index.php'],
            ['label' => 'All News',    'href' => '/news.php'],
            ['label' => 'About Us',    'href' => '/about.php'],
            ['label' => 'Contact',     'href' => '/contact.php'],
            ['label' => 'Privacy Policy', 'href' => '/privacy.php'],
            ['label' => 'Terms of Use',   'href' => '/terms.php'],
          ];
          foreach ($quickLinks as $link):
          ?>
          <li>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="text-sm text-gray-400 hover:text-red-400 transition-colors duration-200 flex items-center gap-2 group">
              <svg class="w-3 h-3 text-red-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- ── Column 3: Categories ── -->
      <div>
        <h3 class="text-white text-sm font-bold uppercase tracking-widest mb-5 pb-2 border-b border-gray-700">
          Categories
        </h3>
        <ul class="space-y-2.5">
          <?php foreach ($footerCategories as $cat): ?>
          <li>
            <a href="/news.php?cat=<?= urlencode($cat['slug']) ?>"
               class="text-sm text-gray-400 hover:text-red-400 transition-colors duration-200 flex items-center gap-2 group">
              <svg class="w-3 h-3 text-red-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- ── Column 4: About & Contact ── -->
      <div>
        <h3 class="text-white text-sm font-bold uppercase tracking-widest mb-5 pb-2 border-b border-gray-700">
          Get in Touch
        </h3>
        <p class="text-sm text-gray-400 leading-relaxed mb-6">
          Daily News has been delivering accurate, unbiased reporting since 2020. Our dedicated team of journalists works tirelessly to bring you the stories that shape our world.
        </p>
        <ul class="space-y-3">
          <!-- Address -->
          <li class="flex items-start gap-3">
            <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-sm text-gray-400">123 Media Street, Colombo 03, Sri Lanka</span>
          </li>
          <!-- Email -->
          <li class="flex items-center gap-3">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <a href="mailto:news@dailynews.lk"
               class="text-sm text-gray-400 hover:text-red-400 transition-colors duration-200">news@dailynews.lk</a>
          </li>
          <!-- Phone -->
          <li class="flex items-center gap-3">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <a href="tel:+94112345678"
               class="text-sm text-gray-400 hover:text-red-400 transition-colors duration-200">+94 11 234 5678</a>
          </li>
        </ul>
      </div>

    </div><!-- /grid -->
  </div><!-- /max-w-7xl -->

  <!-- ── Bottom Bar ── -->
  <div class="border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500">
      <span>&copy; <?= $footerYear ?> Daily News. All rights reserved.</span>
      <div class="flex items-center gap-4">
        <a href="/privacy.php" class="hover:text-red-400 transition-colors duration-200">Privacy Policy</a>
        <span class="text-gray-700">&bull;</span>
        <a href="/terms.php"   class="hover:text-red-400 transition-colors duration-200">Terms of Use</a>
        <span class="text-gray-700">&bull;</span>
        <a href="/sitemap.php" class="hover:text-red-400 transition-colors duration-200">Sitemap</a>
      </div>
    </div>
  </div>

</footer>

<!-- Scroll-to-top button -->
<button
  id="scroll-to-top"
  aria-label="Scroll to top"
  class="fixed bottom-6 right-6 z-50 w-10 h-10 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 opacity-0 pointer-events-none"
>
  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
  </svg>
</button>

<script src="/assets/js/main.js"></script>
</body>
</html>
