<script>
    (function () {
        @if(session('ldr_success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(trim(session('ldr_success') . (session('ldr_reference_no') ? ' Reference number: ' . session('ldr_reference_no') : ''))),
                confirmButtonColor: '#2563eb'
            }).then(function (result) {
                var purchaseOrderUrl = @json(session('ldr_open_adpo_url'));

                if (result.isConfirmed && purchaseOrderUrl) {
                    window.open(purchaseOrderUrl, '_blank', 'noopener');
                }
            });
        @elseif($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Unable to continue',
                text: @json(implode("\n", $errors->all())),
                confirmButtonColor: '#2563eb'
            });
        @elseif(!empty($serviceError))
            Swal.fire({
                icon: 'warning',
                title: 'Service unavailable',
                text: @json($serviceError),
                confirmButtonColor: '#2563eb'
            });
        @endif

        document.querySelectorAll('.ldr-cancel-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'Cancel this request?',
                    text: 'This action cannot be undone.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, cancel it',
                    cancelButtonText: 'Keep request',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#667085',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    })();
</script>
