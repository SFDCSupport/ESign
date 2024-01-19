@php($documentStatus = $document->status->value)
<div class="col-md-4 col-sm-4" data-document-status="{{ $documentStatus }}">
    <div class="card document-template-cards status-{{ $document->status }}">
        <div class="card-body">
            <a href="{{ route('esign.documents.show', $document) }}">
                <p class="template_type">
                    <span>{{ __('esign::label.'.$documentStatus) }}</span>
                </p>
                <h5 class="card-title documentTitle">
                    {{ $document->title }}
                </h5>

                <p class="text-secondary document-user-date">
                    <i class="fa fa-user"></i>
                    <span>{{ $document->creator->name }}</span>
                </p>
                <p class="text-secondary document-user-date mb-1">
                    <i class="fas fa-calendar-alt"></i>
                    <span>
                        {{ $document->created_at->format('d-M-Y H:i A') }}
                    </span>
                </p>
            </a>
            <div class="space-y">
                <div class="space-y-inner">
                    <a
                        href="{{ route('esign.documents.show', $document) }}"
                        class="text-secondary"
                        title="{{ __('esign::label.edit') }}"
                    >
                        <i class="fa fa-edit"></i>
                    </a>
                    <a
                        href="{{ route('esign.documents.copy', $document) }}"
                        class="text-secondary"
                        title="{{ __('esign::label.copy') }}"
                    >
                        <i class="fa fa-copy"></i>
                    </a>
                    <form
                        method="POST"
                        action="{{ route('esign.documents.destroy', $document) }}"
                    >
                        @method('DELETE')
                        @csrf
                        <a
                            href="{{ route('esign.documents.destroy', $document) }}"
                            class="text-secondary"
                            title="{{ __('esign::label.delete') }}"
                            onclick="event.preventDefault();this.closest('form').submit();"
                        >
                            <i class="fa fa-trash"></i>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
