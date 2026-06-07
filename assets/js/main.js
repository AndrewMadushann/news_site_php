/**
 * Daily News — Main JavaScript
 * Handles: mobile menu, reading progress, scroll-to-top,
 *          lazy images, search debounce, nav highlight, flash dismiss.
 */

'use strict';

/* ═══════════════════════════════════════════════════
   1. Mobile Menu Toggle
   ═══════════════════════════════════════════════════ */
(function initMobileMenu() {
  const btn       = document.getElementById('mobile-menu-btn');
  const menu      = document.getElementById('mobile-menu');
  const hamburger = document.getElementById('hamburger-icon');
  const closeIcon = document.getElementById('close-icon');

  if (!btn || !menu) return;

  let isOpen = false;

  function openMenu() {
    menu.classList.remove('hidden');
    // Trigger reflow for animation
    menu.getBoundingClientRect();
    menu.classList.add('menu-open');
    hamburger && hamburger.classList.add('hidden');
    closeIcon && closeIcon.classList.remove('hidden');
    btn.setAttribute('aria-expanded', 'true');
    isOpen = true;
  }

  function closeMenu() {
    menu.classList.remove('menu-open');
    menu.classList.add('hidden');
    hamburger && hamburger.classList.remove('hidden');
    closeIcon && closeIcon.classList.add('hidden');
    btn.setAttribute('aria-expanded', 'false');
    isOpen = false;
  }

  btn.addEventListener('click', () => {
    isOpen ? closeMenu() : openMenu();
  });

  // Close on outside click
  document.addEventListener('click', (e) => {
    if (isOpen && !menu.contains(e.target) && !btn.contains(e.target)) {
      closeMenu();
    }
  });

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen) closeMenu();
  });
})();

/* ═══════════════════════════════════════════════════
   2. Reading Progress Bar
   ═══════════════════════════════════════════════════ */
(function initReadingProgress() {
  const articleBody = document.querySelector('.article-body');
  if (!articleBody) return;

  // Create progress bar element
  const bar = document.createElement('div');
  bar.id = 'reading-progress';
  document.body.insertBefore(bar, document.body.firstChild);

  function updateProgress() {
    const articleTop    = articleBody.getBoundingClientRect().top + window.scrollY;
    const articleBottom = articleTop + articleBody.offsetHeight;
    const windowBottom  = window.scrollY + window.innerHeight;
    const totalHeight   = articleBody.offsetHeight;
    const scrolled      = windowBottom - articleTop;

    const percent = Math.min(100, Math.max(0, (scrolled / totalHeight) * 100));
    bar.style.width = percent + '%';
  }

  window.addEventListener('scroll', updateProgress, { passive: true });
  updateProgress();
})();

/* ═══════════════════════════════════════════════════
   3. Scroll-to-Top Button
   ═══════════════════════════════════════════════════ */
(function initScrollToTop() {
  const btn = document.getElementById('scroll-to-top');
  if (!btn) return;

  const THRESHOLD = 300;

  function onScroll() {
    if (window.scrollY > THRESHOLD) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  }

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

/* ═══════════════════════════════════════════════════
   4. Lazy Loading Images (IntersectionObserver)
   ═══════════════════════════════════════════════════ */
(function initLazyImages() {
  if (!('IntersectionObserver' in window)) {
    // Fallback: load all images immediately
    document.querySelectorAll('img[data-src]').forEach((img) => {
      img.src = img.dataset.src;
      if (img.dataset.srcset) img.srcset = img.dataset.srcset;
      img.classList.add('loaded');
    });
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          delete img.dataset.src;
        }
        if (img.dataset.srcset) {
          img.srcset = img.dataset.srcset;
          delete img.dataset.srcset;
        }

        img.addEventListener('load', () => {
          img.classList.add('loaded');
        }, { once: true });

        // If already cached and loaded synchronously
        if (img.complete) {
          img.classList.add('loaded');
        }

        observer.unobserve(img);
      });
    },
    {
      rootMargin: '200px 0px', // start loading 200px before entering viewport
      threshold: 0,
    }
  );

  document.querySelectorAll('img[data-src], .lazy-img').forEach((img) => {
    observer.observe(img);
  });
})();

/* ═══════════════════════════════════════════════════
   5. Fade-In Sections on Scroll
   ═══════════════════════════════════════════════════ */
(function initFadeInSections() {
  if (!('IntersectionObserver' in window)) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
  );

  document.querySelectorAll('.fade-in-section').forEach((el) => observer.observe(el));
})();

/* ═══════════════════════════════════════════════════
   6. Search Input Debounce + UX Feedback
   ═══════════════════════════════════════════════════ */
(function initSearchDebounce() {
  const searchInputs = document.querySelectorAll('input[name="q"]');
  if (!searchInputs.length) return;

  let debounceTimer = null;

  searchInputs.forEach((input) => {
    const form = input.closest('form');
    if (!form) return;

    // Visual feedback: add spinner placeholder while debouncing
    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);

      const query = input.value.trim();

      if (query.length === 0) return;

      // Visual cue: subtle border flash
      input.style.borderColor = '#DC2626';

      debounceTimer = setTimeout(() => {
        // Reset border when idle
        input.style.borderColor = '';

        // Optional: if you want live search via fetch, put it here.
        // For now we just give UX feedback and let the form submit naturally.
      }, 350);
    });

    // Keyboard shortcut: Ctrl/Cmd+K to focus search
    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        input.focus();
        input.select();
      }
    });
  });
})();

/* ═══════════════════════════════════════════════════
   7. Category Nav Auto-Highlight (based on current URL)
   ═══════════════════════════════════════════════════ */
(function initNavHighlight() {
  const nav = document.querySelector('#main-nav');
  if (!nav) return;

  const currentPath   = window.location.pathname;
  const currentSearch = window.location.search;
  const params        = new URLSearchParams(currentSearch);
  const currentCat    = params.get('cat') || '';

  nav.querySelectorAll('a').forEach((link) => {
    const url        = new URL(link.href, window.location.origin);
    const linkPath   = url.pathname;
    const linkParams = new URLSearchParams(url.search);
    const linkCat    = linkParams.get('cat') || '';

    const isHome    = linkPath === '/index.php' && currentPath === '/index.php';
    const isAllNews = linkPath === '/news.php'  && currentPath === '/news.php' && !currentCat && !linkCat;
    const isCatMatch = linkPath === '/news.php' && linkCat && linkCat === currentCat;
    const isOther   = linkPath !== '/index.php' && linkPath !== '/news.php' && linkPath === currentPath;

    if (isHome || isAllNews || isCatMatch || isOther) {
      link.classList.add('bg-red-700', 'border-white');
      link.classList.remove('border-transparent');
      link.setAttribute('aria-current', 'page');
    }
  });
})();

/* ═══════════════════════════════════════════════════
   8. Flash Message Auto-Dismiss (after 5 seconds)
   ═══════════════════════════════════════════════════ */
(function initFlashMessages() {
  const flashes = document.querySelectorAll('.flash-message');
  if (!flashes.length) return;

  flashes.forEach((flash) => {
    // Auto-dismiss after 5s
    const timer = setTimeout(() => dismissFlash(flash), 5000);

    // Manual close button
    const closeBtn = flash.querySelector('[data-flash-close]');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        clearTimeout(timer);
        dismissFlash(flash);
      });
    }
  });

  function dismissFlash(el) {
    el.classList.add('dismissing');
    el.addEventListener('animationend', () => el.remove(), { once: true });
  }
})();

/* ═══════════════════════════════════════════════════
   9. Sticky Navbar Shadow on Scroll
   ═══════════════════════════════════════════════════ */
(function initNavShadow() {
  const nav = document.getElementById('main-nav');
  if (!nav) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 10) {
      nav.classList.add('shadow-xl');
    } else {
      nav.classList.remove('shadow-xl');
    }
  }, { passive: true });
})();

/* ═══════════════════════════════════════════════════
   10. Confirm Delete Actions
   ═══════════════════════════════════════════════════ */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;

  const msg = btn.dataset.confirm || 'Are you sure you want to delete this item?';
  if (!window.confirm(msg)) {
    e.preventDefault();
    e.stopPropagation();
  }
});
