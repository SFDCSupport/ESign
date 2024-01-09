@props([
    'id' => 'document',
    'multi' => false,
    'maxFiles' => 1,
    'maxSize' => '3MB',
    'mimes' => 'application/pdf',
])

<input
    type="file"
    class="filepond"
    name="{{ $id }}"
    {{ $multi ? 'multiple' : '' }}
    data-accepted-file-types="{{ $mimes }}"
    data-max-file-size="{{ $maxSize }}"
    data-max-files="{{ $maxFiles }}"
/>
