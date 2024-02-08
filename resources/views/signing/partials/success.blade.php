<section class="bg-white container signingCompleted">
    <div class="send-recipients-sec mt-2 form-completed-successfull">
        <div class="text-center">
            <h1>
                <span class="fas fa-check-circle check-solid-sign"></span>
            </h1>
            <h3 class="successfull-completed-msg mb-4 mt-4">
                {{ __('esign::label.signing_completed_msg') }}
            </h3>
            <div class="Submissions-btns-grp mt-3 mb-3">
                <a
                    href="javascript: void(0);"
                    class="btn btn-outline-dark"
                    onclick="sendCopyViaEmail()"
                >
                    <em class="far fa-envelope"></em>
                    {{ __('esign::label.send_copy_via_email') }}
                </a>
                <a
                    href="@isset($signedDocumentUrl) {{ $signedDocumentUrl }} @else javascript: void(0); @endisset"
                    target="_blank"
                    class="btn btn-download downloadBtn"
                >
                    <em class="fa fa-download"></em>
                    {{ __('esign::label.download') }}
                </a>
            </div>
            {!! __('esign::label.signed_with_msg') !!}
        </div>
    </div>

    <script>
        const sendCopyViaEmail = () => {
            $(document).trigger('loader:show');

            $.ajax({
                url: '{{ route('esign.signing.send-copy', ['signing_url' => $signer->url]) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                headers: @json(request()->signingHeaders()),
                success: (r) => {
                    const isDone = r.status === 1;

                    toast(
                        isDone ? 'success' : 'error',
                        r.msg ??
                            (isDone
                                ? '{{ __('esign::label.document_copy_sent') }}'
                                : '{{ __('esign::validations.something_went_wrong') }}'),
                    );
                },
                error: (x) => toast('error', x.responseText),
                complete: () => $(document).trigger('loader:hide'),
            });
        };
    </script>
</section>
