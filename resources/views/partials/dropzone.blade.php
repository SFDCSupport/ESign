@props([
    'id' => 'document',
    'page' => 'outer',
    'multiple' => false,
    'maxFiles' => 1,
    'maxFileSize' => 1024*3,
    'allowed' => '.pdf',
])

@pushonce('css')
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
@endpushonce

<div class="container">
    <div class="row">
        <div id="{{ $id }}" class="dropzone"
             data-page="{{ $page }}"
             data-multiple="{{ $multiple }}"
             data-max-files="{{ $maxFiles }}"
             data-max-size="{{ $maxFileSize }}"
             data-allowed="{{ $allowed }}">
            <div class="dz-default dz-message">
                <h3>{{ $title ??  'Drop files here or click to upload.'}}</h3>
                <p class="text-muted">{{ $desc ?? 'Any related files you can upload' }} <br>
                    <small>One file can be max {{ $maxFileSize / 1000 }} MB</small></p>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        Dropzone.autoDiscover = false;

        $(function () {
            $("#{{ $id }}").dropzone({
                url: "/esign/upload/document",
                uploadMultiple: {{ $multiple ? 'true' : 'false' }},
                maxFilesize: {{ $maxFileSize / 1000 }},
                acceptedFiles: "{!! $allowed !!}",
                headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
            });
        })
    </script>
@endpush
