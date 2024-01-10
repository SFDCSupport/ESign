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
    @isset($icon)
        <i class="fa fa-{{ $icon }}"></i>
    @endisset

    {{ $value ?? $slot }}
</button>
