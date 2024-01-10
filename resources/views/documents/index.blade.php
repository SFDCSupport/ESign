<x-esign::layout-app :title="__('esign::label.dashboard')">
    @pushonce('css')
        <style></style>
    @endpushonce

    <section class="grey-bg-section border-top">
        <section class="mb-2">
            <div class="container">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-1 mb-0"
                >
                    <h1 class="h2">
                        {{ __('esign::label.document_templates') }}
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <x-esign::partials.button :value="__('esign::label.upload')"
                                                      class="btn-outline-secondary" />
                        </div>
                        <x-esign::partials.button :value="__('esign::label.create')"
                                                  icon="plus" class="btn-outline-secondary"
                                                  data-bs-toggle="modal" data-bs-target="#addDocumentModal"/>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <div class="container">
            <div class="row">
                @each('esign::documents.partials.document', $documents, 'document')
            </div>
        </div>

        <x-esign::partials.filepond-uploader />
    </section>

    @include('esign::documents.modals.add-document')
</x-esign::layout-app>
