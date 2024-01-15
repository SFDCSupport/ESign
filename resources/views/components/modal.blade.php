@props([
    'id',
    'title',
    'body' => null,
    'footer' => null,
    'canClose' => true,
    'size' => 'modal-lg',
    'role' => 'document',
    'backdrop' => null,
])

@php($id = ($id ?? \Illuminate\Support\Str::random(12)).'_modal')

<div
    class="modal fade"
    @if ($backdrop) data-backdrop="{{ $backdrop }}" @endif
    id="{{ $id }}"
    tabindex="-1"
    role="dialog"
    aria-labelledby="{{ $title }}"
    aria-hidden="true"
>
    <div class="modal-dialog {{ $size }}" role="{{ $role }}">
        <div class="modal-content">
            @if ($title)
                <div class="modal-header">
                    <h5 class="modal-title fs-5" id="{{ $id }}_title">
                        {{ $title }}
                    </h5>
                    @if ($canClose)
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('esign::label.close') }}"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    @endif
                </div>
            @endif

            <div class="modal-body">
                {{ $body ?? $slot ?? '' }}
            </div>
            <div class="modal-footer">
                {{ $footer ?? '' }}
            </div>
        </div>
    </div>
</div>
