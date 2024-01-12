@props([
    'id' => 'document',
    'page' => 'outer',
    'multiple' => false,
    'maxFiles' => 1,
    'maxFileSize' => 1024 * 3,
    'allowed' => '.pdf',
])

@pushonce('css')
    <link
        rel="stylesheet"
        href="{{ url('vendor/esign/css/dropzone-6.0.0.css') }}"
        type="text/css"
    />
@endpushonce

<div class="container pt-3 pb-2">
    <div class="row">
        <div class="col-sm-12">
        <div
            id="{{ $id }}"
            class="dropzone"
            data-page="{{ $page }}"
            data-multiple="{{ $multiple }}"
            data-max-files="{{ $maxFiles }}"
            data-max-size="{{ $maxFileSize }}"
            data-allowed="{{ $allowed }}"
        >
            <div class="dz-default dz-message">
                <h3>{{ $title ?? 'Drop files here or click to upload.' }}</h3>
                <p class="text-muted">
                    {{ $desc ?? 'Any related files you can upload' }}
                    <br />
                    <small>
                        One file can be max {{ $maxFileSize / 1000 }} MB
                    </small>
                </p>
            </div>
        </div>
    </div>
    </div>
</div>

@push('js')
    <script src="{{ url('vendor/esign/js/dropzone-6.0.0.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;

        $(() => {
            const _dropzoneEle = $('#{{ $id }}');
            const isOuterPageDropzone = () =>
                _dropzoneEle.data('page') === 'outer';

            _dropzoneEle.dropzone({
                url: '/esign/upload/document',
                uploadMultiple: {{ $multiple ? 'true' : 'false' }},
                maxFilesize: {{ $maxFileSize / 1000 }},
                acceptedFiles: '{!! $allowed !!}',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                init: function () {
                    this.on('addedfile', function (file) {
                        if (isOuterPageDropzone()) {
                            $(document).trigger('modal:add-document:show', {
                                callback: (r) => {
                                    if (r && r.id) {
                                        file.id = r.id;
                                        _dropzoneEle.data(
                                            'redirect',
                                            r.redirect || '',
                                        );

                                        this.processFile(file);
                                    } else {
                                        this.removeFile(file);
                                    }
                                },
                            });
                        } else {
                            file.id = getDocumentId();
                        }
                    })
                        .on('complete', function () {
                            console.log('complete');
                        })
                        .on('drop', function () {
                            console.log('drop');
                        })
                        .on('processing', function (file) {
                            if (isOuterPageDropzone() && null === file.id) {
                                this.removeFile(file);
                            }
                        })
                        .on('sending', function (file, xhr, formData) {
                            if (file.id) {
                                formData.append('id', file.id);
                            }
                        })
                        .on('success', function (file, response) {
                            if (isOuterPageDropzone()) {
                                if (_dropzoneEle.data('redirect')) {
                                    $(location).attr(
                                        'href',
                                        _dropzoneEle.data('redirect'),
                                    );
                                }
                            } else {
                                location.reload(true);
                            }
                        })
                        .on('removedfile', function (file) {
                            console.log('removedfile');
                        })
                        .on('error', function (file, response) {
                            console.log('error');
                        });
                },
            });
        });
    </script>
@endpush
