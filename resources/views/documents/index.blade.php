<x-esign::layout :title="__('esign::label.dashboard')">
    @pushonce('css')
        <style></style>
    @endpushonce

    <section class="grey-bg-section border-top">
        <section class="mb-2">
            <div class="container">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-1 mb-3 border-bottom"
                >
                    <h1 class="h2 mb-2">
                        {{ __('esign::label.document_templates') }}
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <x-esign::partials.button
                                id="uploadDocument"
                                :value="__('esign::label.upload')"
                                icon="upload"
                                class="btn-secondary"
                            />
                        </div>
                        <x-esign::partials.button
                            :value="__('esign::label.create')"
                            icon="plus"
                            class="btn-primary"
                            id="createDocumentBtn"
                        />
                    </div>
                </div>
            </div>
        </section>
        <div class="container">
            <div class="row">
                @each('esign::documents.partials.document', $documents, 'document')
            </div>
        </div>

        @php($dropZoneID = \Illuminate\Support\Str::random(12))

        @include('esign::partials.dropzone', [
            'id' => $dropZoneID,
        ])
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
            });
        </script>
    @endpush
</x-esign::layout>
