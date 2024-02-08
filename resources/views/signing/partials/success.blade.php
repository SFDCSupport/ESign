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
                    class="btn btn-outline-dark sendCopyViaEmailBtn"
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
</section>
