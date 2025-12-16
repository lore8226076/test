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
                    <a :class="['nav-link', nav_active=='base'?'active':'' ]" @click="vc.nav_active='base'" data-toggle="tab" role="tab" aria-controls="base" aria-selected="true">
                        {{ __('基本資料') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a :class="['nav-link', nav_active=='sv'?'active':'' ]" @click="vc.nav_active='sv'" data-toggle="tab" role="tab" aria-controls="sv" aria-selected="true">
                        {{ __('SV資料') }}
                    </a>
                </li>
                <li class="nav-item" v-show="editData.order_checkins">
                    <a :class="['nav-link', nav_active=='checkin'?'active':'' ]" @click="vc.nav_active='checkin'" data-toggle="tab" role="tab" aria-controls="checkin" aria-selected="true">
                        {{ __('到場打卡') }}
                    </a>
                </li>
                <li class="nav-item" v-show="editData.order_sign_receipt_files">
                    <a :class="['nav-link', nav_active=='sign_receipt'?'active':'' ]" @click="vc.nav_active='sign_receipt'" data-toggle="tab" role="tab" aria-controls="sign_receipt" aria-selected="true">
                        {{ __('客戶簽收') }}
                    </a>
                </li>
                <li class="nav-item" v-show="editData.order_finish_files">
                    <a :class="['nav-link', nav_active=='finish'?'active':'' ]" @click="vc.nav_active='finish'" data-toggle="tab" role="tab" aria-controls="finish" aria-selected="true">
                        {{ __('完工相片') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div :class="['tab-pane', 'show', nav_active=='base'?'active':'' ]" id="base" role="tabpanel" aria-labelledby="base-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>{{ __('Case No.') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.no">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('認領人') }}</label>
                                            <input type="text" class="form-control" :value="editData.store_customer?editData.store_customer.name:''" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('認領人ID') }}</label>
                                            <input type="text" class="form-control" :value="editData.store_customer?editData.store_customer.id:''" readonly>
                                        </div>
                                    </div>
                                    <hr>
                                    <h4>{{ __('工程資料') }}</h4>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>{{ __('安裝服務類型') }}<span class="red">*</span></label>
                                            <select class="form-control" v-model="editData.repair_type">
                                                <option value="0">{{ __('請選擇') }}</option>
                                                @foreach(\App\Models\Orders::getRepairTypes() as $key => $name)
                                                    <option value="{{ $key }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('地點類型') }}<span class="red">*</span></label>
                                            <select class="form-control" v-model="editData.place_type">
                                                <option value="0">{{ __('請選擇') }}</option>
                                                @foreach(\App\Models\Orders::getPlaceTypes() as $key => $name)
                                                    <option value="{{ $key }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('需要師傅人數') }}<span class="red">*</span></label>
                                            <select class="form-control" v-model="editData.need_man">
                                                <option v-for="n in 20" :value="n">@{{ n }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>{{ __('工程地區') }}<span class="red">*</span></label>
                                            <select class="form-control" v-model="editData.city_area_list_id">
                                                <option value="0">{{ __('請選擇') }}</option>
                                                <template v-for="(citylist, i) in city_area_list">
                                                    <option v-for="(area, i) in citylist.areas" :value="area.id">@{{ area.name }}</option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-8">
                                            <label>{{ __('詳細地址') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.address">
                                        </div>
                                    </div>
                                    <hr>
                                    <h4>{{ __('客人資料') }}</h4>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>{{ __('姓') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.contact_last_name">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('名') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.contact_name">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('稱謂') }}<span class="red">*</span></label>
                                            <select class="form-control" v-model="editData.contact_gender">
                                                <option value="0">{{ __('女士') }}</option>
                                                <option value="1">{{ __('先生') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ __('聯絡電話') }}<span class="red">*</span></label>
                                            <input type="text" class="form-control" v-model="editData.contact_phone">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label>{{ __('說明文件') }}</label>
                                            <label class="badge badge-pill btn-primary mr-2 mb-2 btn-upload">
                                                <input type="file" id="document_path" class="sr-only " name="file" @change="handleDocumentPathChange">{{ __('上傳檔案') }}
                                            </label>
                                            @{{ new_document?new_document.name:'' }}
                                            <a v-show="editData.document_url" :href="editData.document_url" class="badge badge-pill badge-primary-color" target="_blank"><b>{{ __('查看說明文件') }}</b></a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="col-form-label">{{ __('緊急') }}</label>
                                            <div class="custom-switch custom-switch-secondary mb-2">
                                                <input class="custom-switch-input" :id="'is_crash'" type="checkbox" v-model="editData.is_crash" true-value="1" false-value="0">
                                                <label class="custom-switch-btn" :for="'is_crash'"></label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="col-form-label">{{ __('夜單') }}</label>
                                            <div class="custom-switch custom-switch-secondary mb-2">
                                                <input class="custom-switch-input" :id="'is_night'" type="checkbox" v-model="editData.is_night" true-value="1" false-value="0">
                                                <label class="custom-switch-btn" :for="'is_night'"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>{{ __('師傅專業技能/特別要求') }}</label>
                                            <input type="text" class="form-control" v-model="editData.skill_memo">
                                        </div>
                                        <div class="col-md-12">
                                            <label>{{ __('備註') }}</label>
                                            <input type="text" class="form-control" v-model="editData.memo">
                                        </div>
                                    </div>
                                    <div class="form-row mt-4">
                                        <button type="button" class="btn btn-primary d-block col-12" @click="vc.nav_active='sv'">{{ __('下一步') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="['tab-pane', 'show', nav_active=='sv'?'active':'' ]" id="sv" role="tabpanel" aria-labelledby="sv-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <label>{{ __('師傅SV') }}<span class="red">*</span></label>
                                            <select class="form-control" v-model="editData.need_sv">
                                                <option :value="0">{{ __('不需要') }}</option>
                                                <option :value="1">{{ __('需要') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    <section v-show="editData.need_sv==0">
                                        <div class="row">
                                            <div class="form-group col-3">
                                                <label>{{ __('預計開工日') }}<span class="red">*</span></label>
                                                <div class="input-group-append">
                                                    <input id="pre_work_start_date" type="text" class="form-control" v-model="editData.pre_work_start_date" placeholder="{{ __('預計開工日') }}">
                                                    <span class="input-group-text" @click="editData.pre_work_start_date=''" v-show="editData.pre_work_start_date">X</span>
                                                </div>
                                            </div>
                                            <div class="form-group col-3">
                                                <label>{{ __('預計完工日') }}<span class="red">*</span></label>
                                                <div class="input-group-append">
                                                    <input id="pre_work_end_date" type="text" class="form-control" v-model="editData.pre_work_end_date" placeholder="{{ __('預計完工日') }}">
                                                    <span class="input-group-text" @click="editData.pre_work_end_date=''" v-show="editData.pre_work_end_date">X</span>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <label>{{ __('預計需工日') }}<span class="red">*</span></label>
                                                <select class="form-control" v-model="editData.pre_work_days">
                                                    <option v-for="n in 31" :value="n-1">@{{ n-1 }}</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <label>{{ __('預計需工時') }}<span class="red">*</span></label>
                                                <select class="form-control" v-model="editData.pre_work_hours">
                                                    <option v-for="n in 24" :value="n-1">@{{ n-1 }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <h4>{{ __('工程金額') }}<span class="red">*</span></h4>
                                        <div class="row">
                                            <div class="form-group col-3" v-for="(list, i) in editData.fees">
                                                <label>T+@{{ list.day }}</label>
                                                <input type="text" class="form-control" v-model="list.fee" @keyup="handleFeeReportChange">
                                            </div>
                                        </div>
                                        <!-- <div class="row">
                                            <div class="form-group col-md-12">
                                                <label>{{ __('報價單') }}<span class="red">*</span></label>
                                                <label class="badge badge-pill btn-primary mr-2 mb-2 btn-upload">
                                                    <input type="file" id="fee_report" class="sr-only " name="file" @change="handleFeeReportChange">{{ __('上傳檔案') }}
                                                </label>
                                                @{{ new_fee_report?new_fee_report.name:'' }}
                                                <a v-show="editData.fee_report_url" :href="editData.fee_report_url" class="badge badge-pill badge-primary-color" target="_blank"><b>{{ __('查看報價單') }}</b></a>
                                            </div>
                                        </div> -->
                                        <h4>{{ __('Item list') }}</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>{{ __('放線路線') }}</label>
                                                <input type="text" class="form-control" v-model="editData.sv_line">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4 col-md-6 col-xs-12 mt-2" v-for="(list, i) in editData.sv_items">
                                                <label>@{{ list.name }}</label>
                                                <input type="text" class="form-control" v-model="list.value">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12 mt-2">
                                                <label>{{ __('相關檔案') }}<span class="red">*</span> ({{ __('如SD圖、TP圖 etc..') }})</label>
                                                <label class="badge badge-pill btn-primary mr-2 mb-2 btn-upload">
                                                    <input type="file" id="sv_path" class="sr-only " name="file" @change="handleSvPathChange">{{ __('上傳檔案') }}
                                                </label>
                                                @{{ new_sv_path?new_sv_path.name:'' }}
                                                <a v-show="editData.sv_url" :href="editData.sv_url" class="badge badge-pill badge-primary-color" target="_blank"><b>{{ __('查看相關檔案') }}</b></a>
                                            </div>
                                        </div>
                                        <div class="row" v-show="editData.order_sv_files">
                                            <div class="col-2" v-for="(sv_file, i) in editData.order_sv_files">
                                                <img class="img-thumbnail border-0 list-thumbnail align-self-center w-100" :src="sv_file.path_url" @click="showOriginalImage(sv_file.path_url)">
                                            </div>
                                        </div>
                                        <hr>
                                    </section>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <select class="form-control" v-model="editData.status">
                                                @foreach(\App\Models\Orders::getStatuses() as $key => $name)
                                                    @if($id || ($id==0 && $key<=1))
                                                        <option value="{{ $key }}">{{ $name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <button type="button" class="btn btn-primary d-block" @click="vc.nav_active='base'">{{ __('上一步') }}</button>
                                        <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave()" v-if="editData.status!=12">{{ __('確認儲存') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="['tab-pane', 'show', nav_active=='checkin'?'active':'' ]" id="checkin" role="tabpanel" aria-labelledby="checkin-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row list disable-text-selection" data-check-all="checkAll">
                                        <div class="col-xl-3 col-lg-4 col-12 col-sm-6 mb-4" v-for="(list, i) in editData.order_checkins" @click="showOriginalImage(list.path_url)">
                                            <div class="card">
                                                <div class="position-relative">
                                                    <img class="card-img-top" :src="list.path_url" >
                                                </div>
                                                <div class="row">
                                                    <div class="col-12 text-center">
                                                        <p class="list-item-heading mb-4 pt-1">@{{ list.created_at }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="['tab-pane', 'show', nav_active=='sign_receipt'?'active':'' ]" id="sign_receipt" role="tabpanel" aria-labelledby="sign_receipt-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row list disable-text-selection" data-check-all="checkAll">
                                        <div class="col-xl-3 col-lg-4 col-12 col-sm-6 mb-4" v-for="(list, i) in editData.order_sign_receipt_files" @click="showOriginalImage(list.path_url)">
                                            <div class="card">
                                                <div class="position-relative">
                                                    <img class="card-img-top" :src="list.path_url" >
                                                </div>
                                                <div class="row">
                                                    <div class="col-12 text-center">
                                                        <p class="list-item-heading mb-4 pt-1">@{{ list.created_at }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="['tab-pane', 'show', nav_active=='finish'?'active':'' ]" id="finish" role="tabpanel" aria-labelledby="finish-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row list disable-text-selection" data-check-all="checkAll">
                                        <div class="col-xl-3 col-lg-4 col-12 col-sm-6 mb-4" v-for="(list, i) in editData.order_finish_files" @click="showOriginalImage(list.path_url)">
                                            <div class="card">
                                                <div class="position-relative">
                                                    <img class="card-img-top" :src="list.path_url" >
                                                </div>
                                                <div class="row">
                                                    <div class="col-12 text-center">
                                                        <p class="list-item-heading mb-4 pt-1">@{{ list.created_at }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <button type="button" class="btn btn-primary d-block col-12" @click="checkFinishPass()" v-if="editData.status==9">{{ __('施工審核通過') }}</button>
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
        data:{
            id                  : '{{ $id?:0 }}',
            nav_active          : 'base',
            editData            : {},
            sv_item_v1          :  {!! json_encode(\App\Models\Orders::getSvItemV1()) !!},
            new_document        : false,
            new_fee_report      : false,
            new_sv_path         : false,
            originalImagePath   : '',
            city_area_list      : [],
            words               : {
                "data_warning"  : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/order' }}/"+this.id;
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;
                        // 1010    1000    0   派工單查詢
                        // 1012    1000    0   執行中工單
                        // 1015    1000    0   完工單查詢
                        // 1019    1000    0   待撥款查詢
                        // 1020    1000    0   結案單查詢
                        if($.inArray(vc.editData.status,[0,1]) != -1){
                            setTimeout(function(){
                                setActiveMenu(1000,1010);
                            },500)
                        }
                        else if($.inArray(vc.editData.status,[2,3,4,7,8]) != -1){
                            setTimeout(function(){
                                setActiveMenu(1000,1012);
                            },500)
                        }
                        else if($.inArray(vc.editData.status,[9,10]) != -1){
                            setTimeout(function(){
                                setActiveMenu(1000,1015);
                            },500)
                        }
                        else if($.inArray(vc.editData.status,[11]) != -1){
                            setTimeout(function(){
                                setActiveMenu(1000,1019);
                            },500)
                        }
                        else if($.inArray(vc.editData.status,[12]) != -1){
                            setTimeout(function(){
                                setActiveMenu(1000,1020);
                            },500)
                        }

                        if(vc.editData.sv_items==''){
                            vc.editData.sv_items = vc.sv_item_v1;
                        }
                        $('#pre_work_start_date').val(vc.editData.pre_work_start_date);
                        $('#pre_work_start_date').datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: new Date(),
                            showOnFocus: true,
                        }).on('changeDate', function(e) {
                            $('#pre_work_end_date').datepicker('setStartDate', e.date);
                            vc.editData.pre_work_start_date = moment(e.date).format('YYYY-MM-DD');
                            if (moment(vc.editData.pre_work_end_date).isBefore(moment(e.date))) {
                                $('#pre_work_end_date').datepicker('setDate', e.date);
                                vc.editData.pre_work_end_date = moment(e.date).format('YYYY-MM-DD');
                            }
                            if (vc.editData.pre_work_start_date != '' && vc.editData.pre_work_end_date != '') {
                                var startDate = moment(vc.editData.pre_work_start_date);
                                var endDate = moment(vc.editData.pre_work_end_date);
                                vc.editData.pre_work_days = endDate.diff(startDate, 'days');
                            }
                        });
                        $('#pre_work_end_date').val(vc.editData.pre_work_end_date);
                        $('#pre_work_end_date').datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: new Date(),
                            showOnFocus: true,
                        }).on('changeDate', function(e) {
                            $('#pre_work_start_date').datepicker('setEndDate', e.date);
                            vc.editData.pre_work_end_date = moment(e.date).format('YYYY-MM-DD');
                            if (moment(vc.editData.pre_work_start_date).isAfter(moment(e.date))) {
                                $('#pre_work_start_date').datepicker('setDate', e.date);
                                vc.editData.pre_work_start_date = moment(e.date).format('YYYY-MM-DD');
                            }
                            if (vc.editData.pre_work_start_date != '' && vc.editData.pre_work_end_date != '') {
                                var startDate = moment(vc.editData.pre_work_start_date);
                                var endDate = moment(vc.editData.pre_work_end_date);
                                vc.editData.pre_work_days = endDate.diff(startDate, 'days');
                            }
                        });
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            handleDocumentPathChange(event) {
                vc.new_document = event.target.files[0];
                vc.editData.new_document = event.target.files[0];
            },
            handleFeeReportChange(event) {
                // vc.new_fee_report = event.target.files[0];
                // vc.editData.new_fee_report = event.target.files[0];
                if(vc.editData.status==3){
                    vc.editData.status = 4;
                }
            },
            handleSvPathChange(event) {
                vc.new_sv_path = event.target.files[0];
                vc.editData.new_sv_path = event.target.files[0];
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
            },
            showOriginalImage(path){
                this.originalImagePath = path;
                $('#originalImageModal').modal('show');
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/order' }}";

                if(vc.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/order' }}/"+this.id;
                }

                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";

                let formData = new FormData();
                $.each(vc.editData, function(i, v){
                    if (Array.isArray(v)) {
                        formData.append(i, JSON.stringify(v));
                    } else {
                        formData.append(i, v);
                    }
                })
                if(method=='PUT'){
                    method = 'POST';
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    method: method,
                    url: url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success(data) {
                        if(vc.id == 0){
                            location.href = '{{ route("store.order.list") }}';
                        }
                        if(vc.editData.status==10){
                            location.href = '{{ route("store.order.finish") }}';
                        }
                        sNotify(data.message);
                    },
                    error(xhr, ajaxOptions, thrownError) {
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            checkFinishPass(){
                Swal.fire({
                    title: '{{ __("確認審核通過") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "{{ $baseBtnColor }}",
                    cancelButtonText: '{{ __("取消") }}',
                    confirmButtonText: '{{ __("審核通過") }}',
                    allowOutsideClick: false,
                }).then((result) => {
                    if(result.isConfirmed){
                        vc.editData.status = 10;
                        vc.checkSave();
                    }
                })
            },
        },
        created: function(){
            this.get();
            this.init();
        },
        mounted: function(){
        },
        beforeDestroy() {
        },
    });
</script>
@stop