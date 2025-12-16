@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('store.common.map')
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs separator-tabs ml-0 mb-5" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="base-tab" data-toggle="tab" href="#base" role="tab" aria-controls="base" aria-selected="true">
                        {{ __('基本資料') }}
                    </a>
                </li>
                <li class="nav-item" v-if="id>0 || id=='undefined'">
                    <a class="nav-link" id="imagedata-tab" data-toggle="tab" href="#imagedata" role="tab" aria-controls="imagedata" aria-selected="true">
                        {{ __('相關資料') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane show active" id="base" role="tabpanel" aria-labelledby="base-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <img class="img-thumbnail border-0 rounded-circle list-thumbnail align-self-center" v-if="id>0" :src="editData.photo_url">
                                        </div>
                                        <!-- <div class="form-group col-md-6">
                                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('確認儲存') }}</button>
                                        </div> -->
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ __('帳號') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.account" ref="account" placeholder="{{ __('帳號') }}" readonly="true">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>{{ __('加盟類別') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" :value="editData.apply_type_text" ref="apply_type" placeholder="{{ __('加盟類別') }}" readonly="true">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ __('姓名') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.name" ref="name" placeholder="{{ __('姓名') }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>{{ __('電話') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.cellphone" ref="cellphone" placeholder="{{ __('聯絡電話') }}">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ __('生日') }}</label>
                                            <div class="input-group-append">
                                                <input id="birthday" type="text" class="form-control" v-model="editData.birthday" placeholder="{{ __('生日') }}">
                                                <span class="input-group-text" @click="editData.birthday=''" v-if="editData.birthday">X</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ __('聯絡人姓名') }}</label>
                                            <input type="text" class="form-control" v-model="editData.contact_name" ref="contact_name" placeholder="{{ __('聯絡人姓名') }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>{{ __('聯絡人電話') }}</label>
                                            <input type="text" class="form-control" v-model="editData.contact_phone" ref="contact_phone" placeholder="{{ __('聯絡人電話') }}">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ __('區域') }}</label>
                                            <select class="form-control" v-model="editData.city_area_list_id">
                                                <option value="0">{{ __('請選擇') }}</option>
                                                <template v-for="(citylist, i) in city_area_list">
                                                    <option v-for="(area, i) in citylist.areas" :value="area.id">@{{ area.name }}</option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>{{ __('地址') }}</label>
                                            <input type="text" class="form-control" v-model="editData.address" ref="address" placeholder="{{ __('地址') }}">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ __('銀行') }}</label>
                                            <select class="form-control" v-model="editData.bank_list_id" disabled="true">
                                                <option value="0">{{ __('請選擇') }}</option>
                                                <option v-for="(list, i) in bank_list" :value="list.id">@{{ list.show_name }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>{{ __('銀行分行') }}</label>
                                            <input type="text" class="form-control" v-model="editData.bank_sub_code" ref="bank_sub_code" placeholder="{{ __('分行') }}" readonly="true">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>{{ __('銀行帳號') }}</label>
                                            <input type="text" class="form-control" v-model="editData.bank_account" ref="bank_account" placeholder="{{ __('帳號') }}" readonly="true">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>{{ __('電訊經驗') }}</label>：
                                            <label>@{{ editData.telecom_company_experience_text }}</label>
                                            <!-- <select class="form-control select2-multiple" id="telecom_company_experience" v-model="editData.telecom_company_experience" multiple="multiple">
                                                <option value="PCCW">PCCW</option>
                                                <option value="HGC">HGC</option>
                                                <option value="HKT">HKT</option>
                                                <option value="CMHK">CMHK</option>
                                                <option value="其他">其他</option>
                                            </select> -->
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('電訊工種') }}</label>：
                                            <label>@{{ editData.telecom_occupation_text }}</label>
                                            <!-- <select class="form-control" id="telecom_occupation" v-model="editData.telecom_occupation" multiple="multiple">
                                                <option></option>
                                            </select> -->
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('電訊年資') }}</label>：
                                            <label>@{{ editData.telecom_age }}</label>
                                            <!-- <select class="form-control" v-model="editData.telecom_age">
                                                <option value="0">0</option>
                                                <option :value="i" v-for="i in 20">@{{ i }}</option>
                                                <option value="100">20年以上</option>
                                            </select> -->
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>{{ __('備註') }}</label>
                                            <textarea class="form-control" rows="4" v-model="editData.memo"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-row mt-4">
                                        <button type="button" class="btn btn-primary d-block col-12" @click="checkSave">{{ __('確認儲存') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane show" id="imagedata" role="tabpanel" aria-labelledby="imagedata-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4 mb-1" v-show="editData.sid_url">
                                            <label><b>{{ __('身分證') }}</b></label>：
                                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="editData.sid_url" @click="showOriginalImage(editData.sid_url)">
                                        </div>
                                        <div class="col-4 mb-1" v-show="editData.safe_card_url">
                                            <label><b>{{ __('平安卡') }}</b></label>：
                                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="editData.safe_card_url" @click="showOriginalImage(editData.safe_card_url)">
                                        </div>
                                        <div class="col-4 mb-1" v-show="editData.bank_url">
                                            <label><b>{{ __('銀行戶口') }}</b></label>：
                                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="editData.bank_url" @click="showOriginalImage(editData.bank_url)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="originalImageModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img v-if="originalImagePath" :src="originalImagePath" style="max-width: 100%">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('關閉') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        components: {
            vuejsDatepicker
        },
        data:{
            id              : '{{ $id?:0 }}',
            editData        : {
                photo_url : '{{ \Storage::disk('s3')->url('files/image/employee.png') }}',
            },
            city_area_list  : [],
            bank_list       : [],
            originalImagePath : '',
            words           : {
                "map1name" : "{{ __('menu.客戶查詢') }}",
                "type" : "{{ $id?__('客戶編輯'):__('客戶新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store_customer' }}/"+this.id;
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;
                        $('#birthday').val(vc.editData.birthday);
                        $('#birthday').datepicker({
                            format: 'yyyy-mm-dd',
                            endDate: new Date(),
                            showOnFocus : true,
                        }).on('changeDate', function(e){
                            vc.editData.birthday = $('#birthday').val();
                        });
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            init(){
                url = "{{ config('services.API_URL').'/city_area_list/list' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                    },
                    dataType: 'json',
                    success(data){
                        vc.city_area_list = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });

                url = "{{ config('services.API_URL').'/bank_list' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        per_page : 0,
                        is_active : 1,
                    },
                    dataType: 'json',
                    success(data){
                        vc.bank_list = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            dateChange(selectedDate){
                if (selectedDate) {
                    this.editData.birthday = moment(selectedDate).format('YYYY-MM-DD');
                } else {
                    this.editData.birthday = '';
                }
            },
            showOriginalImage(path){
                this.originalImagePath = path;
                $('#originalImageModal').modal('show');
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_customer' }}";

                if(vc.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/store_customer' }}/"+this.id;
                }
                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                // vc.editData.telecom_company_experience = $('#telecom_company_experience').val();
                // vc.editData.telecom_occupation = $('#telecom_occupation').val();
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
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
            this.init();
        },
        mounted: function(){
            $('.maptitle').html(this.words.type);
            $('.map1name').html(this.words.map1name);
            $('.map2name').html(this.words.type);
        }
    });
</script>
@stop