<?php
$adminPageTitle = $adminPageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?> — News Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font: Inter Variable (best modern UI font) -->
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <link rel="stylesheet" href="/assets/css/admin.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { red: '#DC2626', dark: '#0F172A' }
                    },
                    fontFamily: {
                        sans: ['InterVariable', 'Inter', 'system-ui', 'sans-serif']
                    },
                    letterSpacing: {
                        tight: '-0.015em',
                        tighter: '-0.025em'
                    }
                }
            }
        }
    </script>
</head>
<body class="text-gray-900 antialiased">
