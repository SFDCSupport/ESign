<x-esign::modal
    id="audit_log"
    size="modal-xl"
    :title="__('esign::label.audit_log')"
>
    <x-slot name="body">
        <table class="datatable border" style="display: none">
            <thead>
                <tr>
                    <th scope="col">
                        {{ __('esign::label.executor') }}
                    </th>
                    <th scope="col">
                        {{ __('esign::label.action') }}
                    </th>
                    <th scope="col">
                        {{ __('esign::label.time') }}
                    </th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </x-slot>
    <x-slot name="footer">
        <div class="d-flex justify-content-between align-items-center">
            <x-esign::partials.button
                icon="print"
                class="btn-sm btn-dark add-part-btn"
            >
                {{ __('esign::label.print') }}
            </x-esign::partials.button>
            <x-esign::partials.button
                icon="close"
                data-bs-dismiss="modal"
                class="btn-sm btn-primary"
            >
                {{ __('esign::label.close') }}
            </x-esign::partials.button>
        </div>
    </x-slot>

    @push('js')
        <script>
            $(() => {
                const auditLogModal = $('#audit_log_modal');

                $(document)
                    .on('show.bs.modal', '#' + auditLogModal.attr('id'), () => {
                        $(document).trigger('loader:show');

                        $.post('{{ route('esign.audit-log', $document) }}', {
                            _token: '{{ csrf_token() }}',
                            signer_id:
                                $(this).attr('data-signer-id') ?? getSignerId(),
                        })
                            .done((r) => {
                                const table = auditLogModal.find('table');
                                const _p = (msg) =>
                                    `<p class="text-center m-0 p-0">${msg}</p>`;

                                if (r.status !== 1) {
                                    const errMsg =
                                        r.message ??
                                        '{{ __('esign::validations.something_went_wrong') }}';

                                    $(_p(errMsg)).insertAfter(table);
                                    toast('error', errMsg);

                                    return;
                                }

                                if (blank(r.data)) {
                                    $(
                                        _p(
                                            '{{ __('esign::label.no_audit_log') }}',
                                        ),
                                    ).insertAfter(table);

                                    return;
                                }

                                const tBody = table.find('tbody');

                                collect(r.data ?? []).each((d) => {
                                    tBody.append(`<tr>
                                <td>${d.executor}</td>
                                <td>${d.action}</td>
                                <td>${d.time}</td>
                            </tr>`);
                                });

                                table.show();
                            })
                            .fail((r) => toast('error', r.responseText))
                            .complete(() => $(document).trigger('loader:hide'));
                    })
                    .on(
                        'hidden.bs.modal',
                        '#' + auditLogModal.attr('id'),
                        () => {
                            const table = auditLogModal.find('table');

                            table.hide();
                            table.find('tbody').html('');

                            auditLogModal.removeAttr('data-signer-id');
                        },
                    );
            });
        </script>
    @endpush
</x-esign::modal>
