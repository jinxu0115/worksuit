<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.invoices.addPaymentDetails')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createPaymentDetails">
                <div class="row">
                    <div class="col-sm-12">
                        <x-forms.text fieldId="title" :fieldLabel="__('modules.invoices.title')"
                            fieldName="title" fieldRequired="true" :fieldPlaceholder="__('placeholders.invoices.title')">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <x-forms.label fieldId="" class="text-capitalize" :fieldLabel="__('modules.invoices.paymentDetails')">
                        </x-forms.label>
                        <textarea class="form-control" name="payment_details" id="payment_details" rows="4"
                            placeholder="@lang('placeholders.invoices.BankDetails')"></textarea>
                    </div>
                </div>
    </x-form>
</div>

<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-payment-details" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-payment-details').click(function() {
        var url = "{{ route('invoices-payment-details.store') }}";
        $.easyAjax({
            url: url,
            container: '#createPaymentDetails',
            type: "POST",
            data: $('#createPaymentDetails').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    $(MODAL_LG).modal('hide');
                    window.location.reload();
                }
            }
        })
    });

</script>

