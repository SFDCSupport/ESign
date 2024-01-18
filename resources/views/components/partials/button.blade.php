@props([
    'disabled' => false,
    'type' => 'button',
    'value' => null,
    'redirectUrl' => '',
])
<button
    {{ $disabled ? 'disabled' : '' }}
    {{
        $attributes->merge([
            'type' => $type,
            'class' => 'btn align-items-center gap-1 me-2',
        ])
    }}
    {{ $redirectUrl ? 'onclick=location.href="'.$redirectUrl.'"' : '' }}
>
    @if ($attributes->has('icon'))
        <i class="fa fa-{{ $attributes->get('icon') }}"></i>
    @endif

    {{ $value ?? $slot }}
</button>
