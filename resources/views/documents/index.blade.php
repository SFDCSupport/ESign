<x-esign::layout :title="__('esign::label.dashboard')">
    @pushonce('css')
        <style>
            .navbar-header {
                border-bottom: 1px solid #cdebff;
            }
        </style>
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
                    <div class="text-center pt-2 pb-2 mb-2 text-light">
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
                            class="btn-light"
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

    <section class="temp-section">
        <livewire:esign-documents-component />
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
