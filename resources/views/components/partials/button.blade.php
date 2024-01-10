@props([
    'disabled' => false,
    'type' => 'button',
    'value' => null,
])
<button
    {{ $disabled ? 'disabled' : '' }}
    {{
        $attributes->merge([
            'type' => $type,
            'class' => 'btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 me-2',
        ])
    }}
>
    @if ($attributes->has('icon'))
        <i class="fa fa-{{ $attributes->get('icon') }}"></i>
    @endif

    {{ $value ?? $slot }}
</button>
