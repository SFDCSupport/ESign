<x-esign::layout :title="__('esign::label.dashboard')">
    @pushonce('css')
        <style></style>
    @endpushonce

    <section class="grey-bg-section upload-bg-sec pb-1">
        <img
            src="{{ url('vendor/esign/images/esign-banner.png') }}"
            class="banner-home"
        />
        <img
            src="{{ url('vendor/esign/images/digi-banner2.png') }}"
            class="banner-home banner-home2"
        />
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-center pt-2 pb-2 mb-2">
                        <h1 class="h2">Sign your document</h1>
                        <p class="mb-0">
                            Lorem Ipsum is simply dummy text of the printing and
                            typesetting industry.
                            <br />
                            Lorem Ipsum has been the industry's standard dummy
                            text ever since the 1500s,
                        </p>
                    </div>
                </div>

                <div class="col-sm-12 text-center mb-md-0">
                    <div class="mb-2 mb-md-0">
                        <x-esign::partials.button
                            id="uploadDocument"
                            :value="__('esign::label.upload')"
                            icon="upload"
                            class="btn-secondary"
                        />
                        <x-esign::partials.button
                            :value="__('esign::label.create')"
                            icon="plus"
                            class="btn-primary"
                            id="createDocumentBtn"
                        />
                    </div>
                </div>
            </div>

            <div class="filepond-section-custom mb-0">
                @php($dropZoneID = \Illuminate\Support\Str::random(12))

                @include('esign::partials.dropzone', [
                    'id' => $dropZoneID,
                ])
            </div>
        </div>
    </section>

    <section class="bg-white">
        <section class="mb-2">
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
                        Lorem Ipsum has been the industry's standard dummy text
                        ever since the 1500s,
                    </p>
                </div>
                <fieldset class="filter-wrapper mb-2">
                    @foreach (__('esign::dropdown.document_filters') ?? [] as $key => $label)
                        <a
                            href="javascript: void(0);"
                            class="filter-link @if($key === 'all') active @endif"
                            data-filter="{{ $key }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </fieldset>
                <div class="form-outline mb-4">
                    <input
                        type="search"
                        id="documentsSearch"
                        class="form-control form-control-sm"
                        placeholder="{{ __('esign::label.type_query') }}"
                        aria-label="{{ __('esign::label.search') }}"
                    />
                </div>
            </div>
            <div class="container">
                <div class="row documentsContainer" id="documentsContainer">
                    @each('esign::documents.partials.document', $documents, 'document')
                </div>
            </div>
        </section>
    </section>

    @include('esign::documents.modals.add-document')

    @push('js')
        <script>
            $(function () {
                $(document)
                    .on('click', '#uploadDocument', () => {
                        $('#{{ $dropZoneID }}').trigger('click');
                    })
                    .on('click', '#createDocumentBtn', () => {
                        $(document).trigger('modal:add-document:show', {
                            callback: (r) => {
                                if (r && r.redirect) {
                                    $(location).attr('href', r.redirect);
                                }
                            },
                        });
                    });

                const filterBtns = $('.filter-link[data-filter]').on(
                    'click',
                    function () {
                        const _t = $(this);
                        const status = _t.attr('data-filter');
                        const selector = $('div.documentsContainer > div');

                        if (status === 'all') {
                            selector.fadeIn(450);
                        } else {
                            const _ele = $(
                                `[data-document-status="${status}"]`,
                            ).fadeIn(450);
                            selector.not(_ele).hide();
                        }

                        filterBtns.removeClass('active');
                        _t.addClass('active');
                    },
                );
                const documentsSearch = $('#documentsSearch').on(
                    'input',
                    function () {
                        const val = $.trim($(this).val());
                        const matcher = new RegExp(val, 'gi');

                        filterBtns.removeClass('active');

                        $('div.documentsContainer > div')
                            .show()
                            .not(function () {
                                return matcher.test(
                                    $(this).find('.documentTitle').text(),
                                );
                            })
                            .hide();

                        if (blank(val)) {
                            $('.filter-link[data-filter="all"]').trigger(
                                'click',
                            );
                        }
                    },
                );
            });
        </script>
    @endpush
</x-esign::layout>
