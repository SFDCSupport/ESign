<!doctype html>
<html lang="en" dir="ltr" style="direction: ltr;" class="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>

    @stack('headJs')

    {{ Vite::useHotFile('vendor/esign/esign.hot')
        ->useBuildDirectory('vendor/esign')
        ->withEntryPoints(['resources/css/app.scss', 'resources/js/app.js']) }}

    @stack('css')
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> Site not functional without JavaScript enabled
    </div>
</noscript>
{{ $slot }}
@stack('js')
</body>
</html>
