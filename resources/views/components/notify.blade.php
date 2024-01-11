<div>
    <div
        id="toastr"
        class="toast-container position-fixed bottom-0 end-0 p-3"
    ></div>

    <template id="toastTemplate">
        <div
            class="toast toast-__CLASS"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
        >
            <div class="toast-body">__BODY</div>
        </div>
    </template>

    <script>
        let getToastClass = function (type) {
            switch (type) {
                case 'error':
                case 'warning':
                    return 'danger';
                case 'info':
                case 'success':
                default:
                    return 'success';
            }
        };

        $(function () {
            $(document).on('click', '#toastr .toast', function (e) {
                e.stopImmediatePropagation();

                $(this).remove();
            });
        });

        function toast(status, message) {
            let toastContainer = $('#toastr');
            let toastr = $('#toastTemplate').html();

            toastContainer.append(
                toastr
                    .replace('__CLASS', status + ' show')
                    .replace('__BODY', message),
            );

            if ((toastr = $('#toastr .toast')).length > 0) {
                setTimeout(() => {
                    toastr.remove();
                }, {{ config('survey.notify.timeout') }});
            }
        }

        $.ajaxSetup({
            global: true,
            beforeSend: function (x) {},
            complete: function (x, s) {
                if (x.responseJSON) {
                    if (
                        (errors = x.responseJSON.errors ?? undefined) !==
                        undefined
                    ) {
                        $.each(errors, function (k, v) {
                            toast(getToastClass('error'), v.message ?? v);
                        });
                    }

                    if (
                        (notify = x.responseJSON.notify ?? undefined) !==
                            undefined &&
                        !blank(notify.message)
                    ) {
                        toast(
                            getToastClass(
                                notify.class ??
                                    (x.status === 200 ? 'success' : 'error'),
                            ),
                            notify.message,
                        );
                    }
                }
            },
            success: function (r, s, x) {},
            error: function (x, s, e) {},
        });
    </script>
</div>
