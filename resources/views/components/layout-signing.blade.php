@props(['title' => ''])

<!DOCTYPE html>
<html lang="en" dir="ltr" style="direction: ltr" class="ltr">
    <head>
        <meta charset="utf-8" />
        <meta name="theme-color" content="#712cf9" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="document-id" content="{{ $documentId ?? '' }}" />
        <title>
            {{ __('esign::label.app_name').(! blank($title) ? ' - '.$title : '') }}
            
        </title>

        <link
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://unpkg.com/filepond/dist/filepond.css"
            rel="stylesheet"
        />
        <link
            href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
            rel="stylesheet"
        />
        <link
            href="{{ url('vendor/esign/css/bootstrap.min.css') }}"
            rel="stylesheet"
        />
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        />
        <link
            href="{{ url('vendor/esign/css/style.css') }}"
            rel="stylesheet"
        />

        @stack('headJs')
        @stack('css')
    </head>
    <body>
        <noscript>
            <div class="alert alert-danger text-center" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                Site not functional without JavaScript enabled
            </div>
        </noscript>
        <x-esign::partials.header />
        {{ $slot }}
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="{{ url('vendor/esign/js/bootstrap.bundle.min.js') }}"></script>
        @include('esign::partials.common-scripts')
        @stack('js')
    </body>
</html>
