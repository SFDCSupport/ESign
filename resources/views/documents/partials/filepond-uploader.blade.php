@props([
    'id' => 'document',
    'name' => $name ?? 'document',
    'page' => 'outer',
    'multi' => false,
    'maxFiles' => 1,
    'maxSize' => '3MB',
    'mimes' => 'application/pdf',
])

<div class="container">
    <div class="row">
        <main class="col-md-12 ms-sm-auto col-lg-12 pb-4 mb-2">
            <div class="filepond-section-custom">
                <input
                    type="file"
                    class="filepond"
                    id="{{ $id }}"
                    name="{{ $name }}"
                    {{ $multi ? 'multiple' : '' }}
                    data-page="{{ $page }}"
                    data-accepted-file-types="{{ $mimes }}"
                    data-max-file-size="{{ $maxSize }}"
                    data-max-files="{{ $maxFiles }}"
                />
            </div>
        </main>
    </div>
</div>
