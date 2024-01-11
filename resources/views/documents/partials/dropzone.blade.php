@props([
    'id' => 'document',
    'page' => 'outer',
    'multiple' => false,
    'maxFiles' => 1,
    'maxSize' => 1024*3,
    'allowed' => '.pdf',
])

<div class="container">
    <div class="row">
        <div id="{{ $id }}" class="dropzone"
             data-page="{{ $page }}"
             data-multiple="{{ $multiple }}"
             data-max-files="{{ $maxFiles }}"
             data-max-size="{{ $maxSize }}"
             data-allowed="{{ $allowed }}">
            <div class="dz-default dz-message">
                <h3>{{ $title ??  'Drop files here or click to upload.'}}</h3>
                <p class="text-muted">{{ $desc ?? 'Any related files you can upload' }} <br>
                    <small>One file can be max {{ $maxSize / 1000 }} MB</small></p>
            </div>
        </div>
    </div>
</div>

@pushonce('js')
    <script>
        $(() => {
            //
        });
    </script>
@endpushonce
