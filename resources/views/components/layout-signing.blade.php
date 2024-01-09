@props(['title' => ''])

    <!doctype html>
<html lang="en" dir="ltr" style="direction: ltr;" class="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('esign::label.app_name') . (!blank($title) ? ' - ' . $title : '') }}</title>

    @stack('headJs')

    {{ Vite::useHotFile('vendor/esign/esign.hot')
        ->useBuildDirectory('vendor/esign')
        ->withEntryPoints(['resources/sass/signing.scss', 'resources/js/signing.js']) }}

    @stack('css')
</head>
<body>
<noscript>
    <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle"></i> Site not functional without JavaScript enabled
    </div>
</noscript>
<x-esign::partials.header/>
{{ $slot }}
@stack('js')
</body>
</html>
