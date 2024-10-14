<style>
    .invalid-feedback {
        display: contents;
    }
</style>
@if (isset($fields) && count($fields) > 0)
    <div {{ $attributes->merge(['class' => 'row p-20']) }}>
        @foreach ($fields as $field)
            <div class="col-md-4">
                <div class="form-group">
                    @if ($field->type == 'text')
                        <x-forms.text
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldPlaceholder="$field->label"
                            :fieldRequired="($field->required == 'yes') ? 'true' : 'false'"
                            :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.text>

                    @elseif($field->type == 'password')
                        <x-forms.password
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldPlaceholder="$field->label"
                            :fieldRequired="($field->required === 'yes') ? true : false"
                            :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.password>

                    @elseif($field->type == 'number')
                        <x-forms.number
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldPlaceholder="$field->label"
                            :fieldRequired="($field->required === 'yes') ? true : false"
                            :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.number>

                    @elseif($field->type == 'textarea')
                        <x-forms.textarea :fieldLabel="$field->label"
                              fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                              fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                              :fieldRequired="($field->required === 'yes') ? true : false"
                              :fieldPlaceholder="$field->label"
                              :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.textarea>

                    @elseif($field->type == 'radio')
                        <div class="form-group my-3">
                            <x-forms.label
                                fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                :fieldLabel="$field->label"
                                :fieldRequired="($field->required === 'yes') ? true : false">
                            </x-forms.label>
                            <div class="d-flex flex-wrap">
                                <input type="hidden" name="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                       id="{{$field->field_name.'_'.$field->id}}"/>
                                @foreach ($field->values as $key => $value)
                                    <x-forms.radio
                                        fieldId="optionsRadios{{ $key . $field->id }}"
                                        :fieldLabel="$value"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldValue="$value"
                                        :checked="($model && $model->custom_fields_data['field_'.$field->id] == $value) ? true : false"
                                        :fieldRequired="($field->required === 'yes') ? true : false"
                                    />
                                @endforeach
                            </div>
                        </div>

                    @elseif($field->type == 'select')
                        <div class="form-group my-3">
                            <x-forms.label
                                fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                :fieldLabel="$field->label"
                                :fieldRequired="($field->required === 'yes') ? true : false">
                            </x-forms.label>
                            {!! Form::select('custom_fields_data[' . $field->name . '_' . $field->id . ']', $field->values, $model ? $model->custom_fields_data['field_' . $field->id] : '', ['class' => 'form-control select-picker']) !!}
                        </div>

                    @elseif($field->type == 'date')
                        <x-forms.datepicker custom="true"
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldRequired="($field->required === 'yes') ? true : false"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldValue="($model && $model->custom_fields_data['field_'.$field->id] != '') ? \Carbon\Carbon::parse($model->custom_fields_data['field_'.$field->id])->format(companyOrGlobalSetting()->date_format) : now()->format(companyOrGlobalSetting()->date_format)"
                            :fieldPlaceholder="$field->label"/>

                    @elseif($field->type == 'checkbox')
                        <div class="col-md-12 p-0">
                            <div class="form-group my-3">
                                <x-forms.label
                                    fieldId="custom_fields_data[{{ $field->field_name . '_' . $field->id }}]"
                                    :fieldLabel="$field->label"
                                    :fieldRequired="($field->required === 'yes') ? true : false">
                                </x-forms.label>
                                <div class="d-flex flex-wrap checkbox-{{$field->id}}">
                                    @php
                                        $checkedValues = '';

                                        foreach (json_decode($field->values) as $key => $value) {
                                            if ($model && $model->custom_fields_data['field_'.$field->id] != '' && in_array($value ,explode(', ', $model->custom_fields_data['field_'.$field->id]))) {

                                                $checkedValues .= ($checkedValues == '') ? $value : ', '.$value;
                                            }
                                        }
                                    @endphp

                                    <input type="hidden"
                                           name="custom_fields_data[{{$field->field_name.'_'.$field->id}}]"
                                           id="{{$field->field_name.'_'.$field->id}}"
                                            value="{{ $checkedValues }}"
                                           >
                                    @foreach (json_decode($field->values) as $key => $value)
                                        <div class="col-6 p-0">

                                            <x-forms.checkbox
                                                fieldId="optionsRadios{{ $key . $field->id }}"
                                                :fieldLabel="$value"
                                                :fieldName="$field->field_name.'_'.$field->id.'[]'"
                                                :fieldValue="$value"
                                                :checked="$model && $model->custom_fields_data['field_'.$field->id] != '' && in_array($value ,explode(', ', $model->custom_fields_data['field_'.$field->id]))"
                                                onchange="checkboxChange('checkbox-{{$field->id}}', '{{$field->field_name.'_'.$field->id}}')"
                                                :fieldRequired="($field->required === 'yes') ? true : false"/>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    @elseif ($field->type == 'file')
                        <input type="hidden" name="custom_fields_data[{{$field->name.'_'.$field->id}}]"
                               value="{{ $model ? $model->custom_fields_data['field_'.$field->id]:''}}">
                        <x-forms.file
                            class="custom-field-file"
                            :fieldLabel="$field->label"
                            :fieldRequired="($field->required === 'yes') ? true : false"
                            :fieldName="'custom_fields_data[' . $field->name . '_time_' . $field->id . ']'"
                            :fieldId="'custom_fields_data[' . $field->name . '_' . $field->id . ']'"
                            :fieldValue="$model ? ($model->custom_fields_data['field_' . $field->id] != '' ? asset_url_local_s3('custom_fields/' .$model->custom_fields_data['field_' . $field->id]) : '') : ''"
                        />
                    @elseif ($field->type == 'datatime')
                        @php
                            $date = '';
                            $time = '';
                            if($model && $model->custom_fields_data['field_'.$field->id] != ''){
                                $dateTimeArray = explode(' ', $model->custom_fields_data['field_'.$field->id]);
                                $date = $dateTimeArray[0];
                                $time = $dateTimeArray[1];
                            }
                        @endphp
                        <div class="d-flex">
                            <x-forms.datepicker custom="true"
                                fieldId="custom_fields_datetime[{{ $field->name . '_date_' . $field->id }}]"
                                :fieldRequired="($field->required === 'yes') ? true : false"
                                :fieldLabel="$field->label . ' Date'"
                                fieldName="custom_fields_datetime[{{ $field->name . '_date_' . $field->id }}]"
                                :fieldValue="($date != '') ? \Carbon\Carbon::parse($date)->format(companyOrGlobalSetting()->date_format) : now()->format(companyOrGlobalSetting()->date_format)"
                                :fieldPlaceholder="$field->label"/>
                            <div class="bootstrap-timepicker timepicker ml-2">
                                <x-forms.text 
                                    :fieldLabel="$field->label . ' Time'"
                                    :fieldPlaceholder="__('placeholders.hours')"
                                    :fieldName="'custom_fields_datetime[' . $field->name . '_time_' . $field->id . ']'"
                                    :fieldId="'custom_fields_datetime[' . $field->name . '_time_' . $field->id . ']'"
                                    :fieldRequired="$field->required === 'yes'" 
                                    :fieldValue="($time != '') ? \Carbon\Carbon::parse($time)->format(company()->time_format) : now()->format(company()->time_format)"
                                    />
                            </div>
                        </div>
                    @endif

                    <div class="form-control-focus"></div>
                    <span class="help-block"></span>
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
    $(document).ready(function() {
        $('.bootstrap-timepicker.timepicker').find('input').timepicker({
            @if (company()->time_format == 'H:i')
                showMeridian: false
            @endif
        });
    });
</script>
