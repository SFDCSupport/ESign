<div class="col-md-4 col-sm-4">
    <div class="card document-template-cards">
        <div class="card-body">
            <a href="{{ route('esign.documents.show', $document) }}">
                <h5 class="card-title">{{ $document->title }}</h5>
            </a>
            <p class="user-date text-secondary mb-1">
                <i class="fa fa-user"></i>
                <span>{{ $document->creator->name }}</span>
            </p>
            <p class="user-date text-secondary mb-1">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ $document->created_at }}</span>
            </p>
            <div class="space-y">
                <div class="space-y-inner">
                    <a
                        href=""
                        class="text-secondary"
                        title="{{ __('esign::label.edit') }}"
                    >
                        <i class="fa fa-edit"></i>
                    </a>
                    <a
                        href=""
                        class="text-secondary"
                        title="{{ __('esign::label.copy') }}"
                    >
                        <i class="fa fa-copy"></i>
                    </a>
                    <a
                        href=""
                        class="text-secondary"
                        title="{{ __('esign::label.delete') }}"
                    >
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
