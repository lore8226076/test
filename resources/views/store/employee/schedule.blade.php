@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('store.common.map')
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <select v-model="store_employee_id" id="store_employee_id" @change="get" class="form-control select2-single">
                        <option value="0">{{ __("default.請選擇員工") }}</option>
                        <option v-for="(store_employee, i) in store_employees" :value="store_employee.id">@{{ store_employee.show_data }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row" v-if="store_employee_id>0">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form ref="myForm" autocomplete="off">
                        <div class="form-row">
                            <button type="button" class="btn btn-primary badge-primary-color d-block" data-toggle="modal" data-target="#speedCopyModal">{{ __('default.進階排班') }}</button>
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期一') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week1_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week1_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期二') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week2_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week2_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期三') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week3_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week3_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期四') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week4_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week4_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期五') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week5_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week5_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期六') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week6_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week6_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 col-xs-12">
                                <label class="col-form-label">{{ __('default.星期日') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="week7_work" type="checkbox" checked="">
                                    <label class="custom-switch-btn" for="week7_work"></label>
                                </div>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.上班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                            <div class="col-5">
                                <label>{{ __('default.下班時間') }}</label>
                                <select class="form-control">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @for ($minute = 0; $minute < 60; $minute += 15)
                                            <option>{{ sprintf('%02d:%02d', $hour, $minute) }}</option>
                                        @endfor
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-row mt-4">
                            <button type="button" class="btn btn-primary d-block col-12" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $('#store_employee_id').on('change', function(){
            vc.store_employee_id = $(this).val();
            vc.get();
        })
    })
    var vc = new Vue({
        el:'#vc',
        data:{
            id                  : '{{ $id?:0 }}',
            store_employees     : [],
            store_employee_id   : 0,
            editData            : {},
            words               : {
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store_employee_schedule' }}/"+this.store_employee_id;
                if(this.store_employee_id){
                    $.ajax({
                        method: "GET",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            vc.editData = data.data;
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                }
            },
            getStoreEmployees(){
                let url = "{{ config('services.API_URL').'/store_employee' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'store_employees__store_id' : '{{ auth()->guard("store")->user()->store_id }}',
                    },
                    dataType: 'json',
                    success(data){
                        vc.store_employees = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    },
                });
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_employee_schedule' }}";

                if(vc.store_employee_id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/store_employee_schedule' }}/"+this.store_employee_id;
                }
                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        if(method=='POST'){
                            location.href = '{{ route("store.employee.list") }}';
                        }
                        vc.get();
                        sNotify(data.message);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if(typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
        },
        created: function(){
            this.get();
            this.getStoreEmployees();
        },
        mounted: function(){
        }
    });
</script>
@stop