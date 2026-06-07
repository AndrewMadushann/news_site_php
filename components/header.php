<?php
$pageTitle = $pageTitle ?? 'Daily News';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= isset($metaDesc) ? htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') : 'Daily News - Your trusted source for breaking news, politics, sports, business and more.' ?>">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Daily News</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- ════════════════════════════════════════════════════════════════════
       Editorial type system
       • Fraunces       — display headlines (variable serif, modern editorial)
       • Lora           — article body text (designed for screen reading)
       • Inter Variable — UI, navigation, metadata, captions
       ════════════════════════════════════════════════════════════════════ -->
  <link rel="preconnect" href="https://rsms.me/">
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700;9..144,800;9..144,900&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">

  <style>
    /* Crisp rendering for all three families. */
    html {
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-rendering: optimizeLegibility;
    }
    body {
      font-family: 'InterVariable', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      font-feature-settings: 'cv11', 'ss01', 'ss03';
      font-optical-sizing: auto;
      letter-spacing: -0.01em;
    }
    .font-serif,
    .font-display,
    h1.font-serif, h2.font-serif, h3.font-serif {
      font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
      font-optical-sizing: auto;
      letter-spacing: -0.02em;
    }
  </style>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { red: '#DC2626', dark: '#111827', accent: '#EF4444' }
          },
          fontFamily: {
            sans:    ['InterVariable', 'Inter', 'system-ui', 'sans-serif'],
            serif:   ['Fraunces', 'Playfair Display', 'Georgia', 'serif'],
            display: ['Fraunces', 'Playfair Display', 'Georgia', 'serif'],
            body:    ['Lora', 'Georgia', 'serif'],
          }
        }
      }
    }
  </script>
</head>
<body class="bg-white text-gray-900 font-sans antialiased">
