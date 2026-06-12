<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ config('app.description') }}">
        <meta name="theme-color" content="#0f172a">

        <title inertia>{{ config('app.name', 'DraftCircle') }}</title>

        <script>
            (() => {
                const theme = localStorage.getItem('theme') ?? 'dark';
                const dark = theme === 'dark'
                    || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.classList.toggle('dark', dark);
            })();
        </script>

        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|lora:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @routes
        @vite(['resources/js/app.ts', 'resources/js/demo-overlay-mount.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <div id="demo-root"></div>
    </body>
</html>
