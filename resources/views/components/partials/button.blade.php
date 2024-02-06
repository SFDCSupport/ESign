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
        <em class="fa fa-{{ $attributes->get('icon') }}"></em>
    @endif

    {{ $value ?? $slot }}
</button>
