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
                    <select v-model="store_employee_id" class="form-control select2-single">
                        <option value="0">{{ __("default.請選擇員工") }}</option>
                        <option v-for="(store_employee, i) in store_employees" :value="store_employee.id">@{{ store_employee.show_data }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- <h5 class="mb-4">{{ __('default.店家資料') }}</h5> -->
                    <form autocomplete="off" v-for="(store_employee, i) in store_employees" v-if="store_employee_id==0 || store_employee_id==store_employee.id">
                        <div class="form-row">
                            <div class="col-md-12">
                                <label class="col-form-label">@{{ store_employee.show_data }}</label>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-3">
                                <label class="col-form-label">{{ __('default.前台開放預約') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" :id="'allow_booking'+store_employee.id" type="checkbox" v-model="store_employee.allow_booking" @change="checkSave(store_employee)" true-value="1" false-value="0">
                                    <label class="custom-switch-btn" :for="'allow_booking'+store_employee.id"></label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="col-form-label">{{ __('default.允許預約時間加總超過營業時間') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" :id="'allow_over_end_time'+store_employee.id" type="checkbox" v-model="store_employee.allow_over_end_time" @change="checkSave(store_employee)" true-value="1" false-value="0">
                                    <label class="custom-switch-btn" :for="'allow_over_end_time'+store_employee.id"></label>
                                </div>
                            </div>
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('default.開放當日預約') }}</label>
                                    <div class="custom-switch custom-switch-secondary mb-2">
                                        <input class="custom-switch-input" :id="'allow_same_day'+store_employee.id" type="checkbox" v-model="store_employee.allow_same_day" @change="checkSave(store_employee)" true-value="1" false-value="0">
                                        <label class="custom-switch-btn" :for="'allow_same_day'+store_employee.id"></label>
                                    </div>
                                    <label class="col-form-label">{{ __('default.當日預約須提前幾小時') }}</label>
                                    <select class="form-control" v-model="store_employee.same_day_can_booking_hour" @change="checkSave(store_employee)">
                                        <option v-for="hour in 13" :key="hour-1" :value="hour-1">@{{ hour-1 }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            store_employee_id   : 0,
            store_employees     : [],
            searchData  : {
                'is_active' : 1,
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'sort'      : '',
                'direction' : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store_employee' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: this.searchData,
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
            checkSave(editData){
                let method = "PUT";
                let url = "{{ config('services.API_URL').'/store_employee' }}/"+editData.id;
                editData.updated_name = "{{ auth()->guard('store')->user()->name }}";

                $.ajax({
                    method: method,
                    url: url,
                    data: editData,
                    dataType: 'json',
                    success(data){
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
        },
        mounted: function(){
        }
    });
</script>
@stop