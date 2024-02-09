<div>
    <section class="mb-2" id="documentsSection">
        <div class="container">
            <div
                class="align-items-center pt-4 pb-1 mb-4 border-bottom text-center"
            >
                <h1 class="h2 mb-2">
                    {{ __('esign::label.document_templates') }}
                </h1>

                <p class="mb-2">
                    Lorem Ipsum is simply dummy text of the printing and
                    typesetting industry.
                    <br />
                    Lorem Ipsum has been the industry's standard dummy text ever
                    since the 1500s,
                </p>
            </div>
            <fieldset class="filter-wrapper">
                <div class="filter-wrapper-inner">
                    <div class="filters-group">
                        <a
                            href="javascript: void(0);"
                            class="filter-link @if($filter === 'all') active @endif"
                            wire:click="setFilter('all')"
                            wire:loading.attr="disabled"
                        >
                            {{ __('esign::label.all') }}
                        </a>
                        @foreach (\NIIT\ESign\Enum\DocumentStatus::options() as $key => $label)
                            <a
                                href="javascript: void(0);"
                                class="filter-link @if($filter === $key) active @endif"
                                wire:click="setFilter('{{ $key }}')"
                                wire:loading.attr="disabled"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="form-outline filter-search-sec">
                        <input
                            type="search"
                            id="documentsSearch"
                            wire:model.live.debounce.250ms="search"
                            class="form-control form-control-sm"
                            placeholder="{{ __('esign::label.type_query') }}"
                            aria-label="{{ __('esign::label.search') }}"
                        />
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="container">
            <div class="w-100" wire:loading>
                <div
                    class="d-flex justify-content-center align-items-center text-center"
                >
                    {{ __('esign::label.loading') }}
                </div>
            </div>
            <div
                class="row documentsContainer"
                id="documentsContainer"
                wire:loading.remove
            >
                @forelse ($documents as $document)
                    @php($documentStatus = $document->status->value)
                    <div
                        wire:key="{{ $document->id }}"
                        class="col-md-4 col-sm-4"
                        data-document-status="{{ $documentStatus }}"
                    >
                        <div
                            class="card document-template-cards status-{{ $document->status }}"
                        >
                            <div class="card-body">
                                <a
                                    href="{{ route('esign.documents.show', $document) }}"
                                >
                                    <p class="template_type">
                                        <span>
                                            {{ __('esign::label.'.$documentStatus) }}
                                        </span>
                                    </p>
                                    <h5 class="card-title documentTitle">
                                        {{ $document->title }}
                                    </h5>

                                    <p
                                        class="text-secondary document-user-date"
                                    >
                                        <i class="fa fa-user"></i>
                                        <span>
                                            {{ $document->creator->name }}
                                        </span>
                                    </p>
                                    <p
                                        class="text-secondary document-user-date mb-1"
                                    >
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>
                                            {{ $document->created_at->format('d-M-Y H:i A') }}
                                        </span>
                                    </p>
                                </a>
                                <div class="space-y">
                                    <div class="space-y-inner">
                                        @php($isDraft = ($document->statusIs(\NIIT\ESign\Enum\DocumentStatus::DRAFT)))

                                        @if ($isDraft)
                                            <a
                                                href="{{ route('esign.documents.show', $document) }}"
                                                class="text-secondary"
                                                title="{{ __('esign::label.edit') }}"
                                            >
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endif

                                        <a
                                            href="{{ route('esign.documents.copy', $document) }}"
                                            class="text-secondary"
                                            title="{{ __('esign::label.copy') }}"
                                        >
                                            <i class="fa fa-copy"></i>
                                        </a>
                                        @if ($isDraft)
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
                                                    onclick="event.preventDefault();$(document).trigger('loader:show');this.closest('form').submit();"
                                                >
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center no_file_found_section">
                        <img
                            src="{{ url('vendor/esign/images/thinking.png') }}"
                            class="no_file_found_icon"
                        />
                        {!! __('esign::label.documents_not_exists') !!}
                    </div>
                @endforelse

                @if (! blank($documents ?? []))
                    {{ $documents->links(data: ['scrollTo' => '#documentsSection']) }}
                @endif
            </div>
        </div>
    </section>
</div>
