@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('store.common.map')
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form ref="myForm" autocomplete="off">
                        <div class="form-row">
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.服務分類') }}<span class="red">*</span></label>
                                <select v-model="editData.store_service_category_id" id="store_service_category_id" ref="store_service_category_id" class="form-control select2-single" required>
                                    <option value="">{{ __('default.請選擇') }}</option>
                                    <option v-for="(category, i) in store_service_category" :value="category.id">@{{ category.name }}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.服務名稱') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.name" ref="name" placeholder="{{ __('default.服務名稱') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.費用') }}</label>
                                <div class="form-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">＄</span>
                                        <input type="number" class="form-control" v-model="editData.price" @input="validateInt(editData.price)" placeholder="{{ __('default.費用') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.費用呈現起字') }}</label>
                                <div class="custom-switch custom-switch-secondary mb-2">
                                    <input class="custom-switch-input" id="price_up" type="checkbox" v-model="editData.price_up" true-value="1" false-value="0">
                                    <label class="custom-switch-btn" for="price_up"></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.訂金') }}</label>
                                <div class="form-group">
                                    <div class="input-group-append">
                                        <span class="input-group-text">＄</span>
                                        <input type="number" class="form-control" v-model="editData.deposit" @input="validateInt(editData.deposit)" placeholder="{{ __('default.訂金') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.服務時間(分鐘)') }}</label>
                                <select class="form-control" v-model="editData.service_time">
                                    <option v-for="(service_time, i) in service_times" :value="service_time">@{{ service_time }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>{{ __('default.服務說明') }}</label>
                                <textarea class="form-control" rows="4" v-model="editData.introduce"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>{{ __('default.材料成本') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" v-model="editData.material_cost_percentage" ref="material_cost_percentage" @input="validateMaterialCostPercentage()">
                                    <div class="input-group-append pr-1">
                                        <span class="input-group-text">%</span>
                                    </div>

                                    <div class="input-group-append">
                                        <span class="input-group-text">＄</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control" v-model="editData.material_cost" ref="material_cost" @input="validateMaterialCost()">

                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>{{ __('default.助理費用') }}</label>
                            <button type="button" class="btn btn-primary ml-auto btn-xs mb-2" @click="addAssistant" v-if="editData.assistant_fees && editData.assistant_fees.length < 5"><i class="iconsminds-add btn-group-icon"></i>{{ __('default.新增') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col"></th>
                                            <th scope="col">{{ __('default.助理項目') }}</th>
                                            <th scope="col">{{ __('default.助理費用(%)') }}</th>
                                            <th scope="col">{{ __('default.助理費用(＄)') }}</th>
                                            <th scope="col">{{ __('default.員工業績扣助理費用') }}</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(assistant,i) in editData.assistant_fees">
                                            <td scope="row" class="text-center">@{{ i+1 }}</td>
                                            <td scope="row">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" v-model="assistant.name">
                                                </div>
                                            </td>
                                            <td scope="row">
                                                <div class="input-group">
                                                    <input type="number" step="1" class="form-control" v-model="assistant.percentage" @input="validateAssistant('percentage')">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td scope="row">
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">＄</span>
                                                    </div>
                                                    <input type="number" step="0.01" class="form-control" v-model="assistant.fee" @input="validateAssistant('fee')">
                                                </div>
                                            </td>
                                            <td scope="row" class="text-center">
                                                <input type="checkbox" class="form-control-input" v-model="assistant.is_active" true-value="1" false-value="0" >
                                            </td>
                                            <td scope="row">
                                                <button type="button" class="btn btn-outline-danger nohide btn-xs mb-2" @click="deleteAssistant(i)"><i class="simple-icon-trash btn-group-icon"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
    var vc = new Vue({
        el:'#vc',
        data:{
            id              : '{{ $id?:0 }}',
            editData        : {},
            store_service_category : [],
            service_time_level : '{{ $storeSetting->service_time_level?:0 }}',
            service_times   : [],
            words           : {
                "type" : "{{ $id?__('服務編輯'):__('服務新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store_service' }}/"+this.id;
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;

                        if(vc.editData.assistant_fees==null){
                            vc.editData.assistant_fees = [];
                        }
                        else if(vc.editData.assistant_fees && !Array.isArray(vc.editData.assistant_fees)){
                            vc.editData.assistant_fees = JSON.parse(vc.editData.assistant_fees);
                        }
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            getStoreServiceCategory(){
                let url = "{{ config('services.API_URL').'/store_service_category' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        store_id : '{{ $store->id }}',
                        is_active : 1,
                    },
                    dataType: 'json',
                    success(data){
                        vc.store_service_category = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            addAssistant(){
                this.editData.assistant_fees.push({
                    name:'',
                    percentage:0,
                    fee:0,
                    is_active:1,
                })
            },
            deleteAssistant(i){
                this.editData.assistant_fees.splice(i, 1);
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_service' }}";

                if(vc.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/store_service' }}/"+this.id;
                }
                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                vc.editData.store_service_category_id = $('#store_service_category_id').val();
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        if(method=='POST'){
                            location.href = '{{ route("store.service.list") }}';
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
            validateInt(field){
                if(field==''){
                    field = 0;
                }
                else{
                    field = field.replace(/^(0+)|[^\d]+/g,'')
                }
            },
            validateMaterialCost() {
                this.editData.material_cost_percentage = 0;
                if (this.editData.material_cost == '') {
                    this.editData.material_cost = 0;
                } else if (parseFloat(this.editData.material_cost) != 0) {
                    let parsedValue = parseFloat(this.editData.material_cost.replace(/^(0+)|[^\d.]+/g, ''));
                    // 判断小数部分是否超过两位
                    let decimalCount = parsedValue.toString().split(".")[1]?.length || 0;

                    if (decimalCount > 2) {
                        this.editData.material_cost = parsedValue.toFixed(2);
                    } else {
                        this.editData.material_cost = parsedValue.toString();
                    }
                }
            },
            validateMaterialCostPercentage() {
                this.editData.material_cost = 0;
                if (this.editData.material_cost_percentage == '') {
                    this.editData.material_cost_percentage = 0;
                } else if (parseFloat(this.editData.material_cost_percentage) !== 0) {
                    parsedValue = parseFloat(this.editData.material_cost_percentage.replace(/^(0+)|[^\d.]+/g, ''));

                    if (parsedValue > 100) {
                        this.editData.material_cost_percentage = 100;
                    }
                    else{
                        let decimalCount = parsedValue.toString().split(".")[1]?.length || 0;

                        if (decimalCount > 2) {
                            this.editData.material_cost_percentage = parsedValue.toFixed(2);
                        } else {
                            this.editData.material_cost_percentage = parsedValue.toString();
                        }
                    }
                }
            },
            validateAssistant(type) {
                $.each(this.editData.assistant_fees, function (i, obj) {
                    if (obj.percentage == '') {
                        obj.percentage = 0;
                    } else {
                        obj.percentage = parseFloat(obj.percentage.replace(/^(0+)|[^\d.]+/g, ''));
                        if (obj.percentage > 100) obj.percentage = 100;
                    }

                    if (obj.fee == '') {
                        obj.fee = 0;
                    } else {
                        obj.fee = parseFloat(obj.fee.toString().replace(/^(0+)|[^\d.]+/g, ''));
                    }

                    if (type == 'fee') {
                        if (parseFloat(obj.fee) > 0) obj.percentage = 0;
                        else obj.fee = 0;
                    } else {
                        if (parseFloat(obj.percentage) > 0) obj.fee = 0;
                        else obj.percentage = 0;
                    }
                });
            }
        },
        created: function(){
            if(this.id){
                this.get();
            }
        },
        mounted: function(){
            this.getStoreServiceCategory();
            for (let i = 0; i <= 600; i += parseInt(this.service_time_level)) {
                this.service_times.push(i);
            }
            $('.maptitle').html(this.words.type);
            $('.map2name').html(this.words.type);
        }
    });
</script>
@stop