<div class="col-xl-12 col-lg-12 col-md-12 w-100 p-20">
    <div class="table-responsive">
        <x-table class="table-bordered">
            <x-slot name="thead">
                <th>@lang('modules.invoices.title')</th>
                <th>@lang('modules.invoices.paymentDetails')</th>
                <th class="text-right pr-20">@lang('app.action')</th>
            </x-slot>
            @forelse($payments as $payment)
                <tr class="row{{ $payment->id }}">
                    <td>
                        {{ $payment->title }}
                    </td>
                    <td>
                        {!! !empty($payment->payment_details) ? nl2br(e($payment->payment_details)) : '--' !!}

                    </td>
                    <td class="text-right pr-20">
                        <div class="task_view mr-1">
                            <a href="javascript:;" data-payment-id="{{ $payment->id }}"
                                class="edit-payment task_view_more d-flex align-items-center">
                                <i class="fa fa-edit mr-1"></i> @lang('app.edit')
                            </a>
                        </div>
                        <div class="task_view">
                            <a href="javascript:;" data-payment-id="{{ $payment->id }}"
                                class="delete-payment task_view_more d-flex align-items-center justify-content-center dropdown-toggle">
                                <i class="fa fa-trash mr-1"></i> @lang('app.delete')
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        <x-cards.no-record icon="file" :message="__('messages.noPaymentAdded')" />
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>
</div>

<script>

    $('.edit-payment').click(function() {
        var paymentID = $(this).data('payment-id');
        var url = "{{ route('invoices-payment-details.edit', ':id') }}";
        url = url.replace(':id', paymentID);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('.delete-payment').click(function() {
        var id = $(this).data('payment-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('invoices-payment-details.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('.row' + id).fadeOut(100);
                        }
                    }
                });
            }
        });
    });

    $('.set_default_unit').click(function() {
        var unitID = $(this).data('unit-id');
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('unit-type.set_default') }}",
            type: "POST",
            data: {
                unitID: unitID,
                _token: token
            },
            blockUI: true,
            container: '#editSettings',
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });
</script>
