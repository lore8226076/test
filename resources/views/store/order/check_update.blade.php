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
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>{{ __('單號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.no" @keyup="get">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('狀態') }}</label>
                            <select class="form-control select2-single" id="status" v-model="searchData.status" onChange="vc.get()">
                                <option value="6">{{ __('申請改期') }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('安裝服務類型') }}</label>
                            <select class="form-control select2-single" id="repair_type" v-model="searchData.repair_type" onChange="vc.get()">
                                <option value="0">{{ __('全部') }}</option>
                                @foreach(\App\Models\Orders::getRepairTypes() as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('地點類型') }}</label>
                            <select class="form-control select2-single" id="place_type" v-model="searchData.place_type" onChange="vc.get()">
                                <option value="0">{{ __('全部') }}</option>
                                @foreach(\App\Models\Orders::getPlaceTypes() as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>{{ __('區域') }}</label>
                            <select class="form-control select2-single" id="city_area_list_id" v-model="searchData.city_area_list_id" onChange="vc.get()">
                                <option value="0">{{ __('全部') }}</option>
                                <template v-for="(citylist, i) in city_area_list">
                                    <option v-for="(area, i) in citylist.areas" :value="area.id">@{{ area.name }}</option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <!-- <div class="form-row justify-content-end">
                        <button class="btn btn-primary mr-2 mb-2" @click="get()">{{ __('搜尋') }}</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" @click="get('sort', 'status')">狀態</th>
                                    <th scope="col" @click="get('sort', 'created_at')">開單日</th>
                                    <th scope="col" @click="get('sort', 'no')">單號</th>
                                    <th scope="col" @click="get('sort', 'repair_type')">{{ __('安裝') }}</th>
                                    <th scope="col" @click="get('sort', 'place_type')">{{ __('地點') }}</th>
                                    <th scope="col" @click="get('sort', 'city_area_list_id')">{{ __('地區') }}</th>
                                    <th scope="col" @click="get('sort', 'store_customer_id')">{{ __('認領人') }}</th>
                                    <th scope="col" @click="get('sort', 'pre_work_start_date')">{{ __('預計施工') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="goEdit(list.id)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row"><a href="#" @click.prevent.stop="showCheckModal(list)" class="btn btn-primary">@{{ list.status_text }}</a></td>
                                    <td scope="row">@{{ list.created_at }}</td>
                                    <td scope="row">@{{ list.no }}</td>
                                    <td scope="row">@{{ list.repair_type_text }}</td>
                                    <td scope="row">@{{ list.place_type_text }}</td>
                                    <td scope="row">@{{ list.city_area_list?list.city_area_list.name:'' }}</td>
                                    <td scope="row">@{{ list.store_customer?list.store_customer.name + '('+list.store_customer.cellphone+')':'' }}</td>
                                    <td scope="row">@{{ list.pre_work_start_date }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @include('store.common.api_page')
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-bottom" id="checkModal" tabindex="-1" role="dialog" aria-labelledby="checkModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" v-if="editData && editData.order_apply_update">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('工單') }}:@{{ editData.no }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label><h4><b>原施工日期資料</b></h4></label>
                    <div class="row">
                        <div class="col-3">
                            <label><b>{{ __('預計開工日') }}</b></label>：@{{ editData.pre_work_start_date }}
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('預計完工日') }}</b></label>：@{{ editData.pre_work_end_date }}
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('預計需工日') }}</b></label>：@{{ editData.pre_work_days }}
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('預計需工時') }}</b></label>：@{{ editData.pre_work_hours }}
                        </div>
                    </div>
                    <hr>
                    <label><h4><b>申請更改施工日期資料</b></h4></label>
                    <div class="row">
                        <div class="col-3">
                            <label><b>{{ __('預計開工日') }}</b></label>：@{{ editData.order_apply_update.redate_start }}
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('預計完工日') }}</b></label>：@{{ editData.order_apply_update.redate_end }}
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('預計需工日') }}</b></label>：@{{ editData.order_apply_update.redate_days }}
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('預計需工時') }}</b></label>：@{{ editData.order_apply_update.redate_hours }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label><b>{{ __('原因') }}</b></label>
                            <div v-html="editData.order_apply_update.reason"></div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary col-6" @click.prevent="checkSave()">{{ __('同意改期') }}</a>
                    <a href="#" class="btn btn-primary badge-primary-color col-6" @click.prevent="checkReject()">{{ __('不同意改期') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            lists           : [],
            pageData        : false,
            city_area_list  : [],
            searchData  : {
                'no'            : '',
                'status'        : 6,
                'repair_type'   : 0,
                'place_type'    : 0,
                'city_area_list_id' : 0,
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'sort'      : 'updated_at',
                'direction' : 'desc',
            },
            sorts           : [],
            editData         : false,
            words           : {
                "data_warning" : '',
            },
        },
        methods: {
            get(type = false, sort = false) {
                if(type == 'sort'){
                    this.searchData.sort = sort;
                    this.searchData.direction = this.searchData.direction=='desc'?'asc':'desc';
                }
                vc = this;
                vc.searchData.status= $('#status').val()
                vc.searchData.repair_type= $('#repair_type').val()
                vc.searchData.place_type= $('#place_type').val()
                vc.searchData.city_area_list_id= $('#city_area_list_id').val()
                let url = "{{ config('services.API_URL').'/order' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: vc.searchData,
                    dataType: 'json',
                    success(data){
                        vc.lists = data.data.data;
                        vc.pageData = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            showCheckModal(list){
                vc.editData = list;
                $('#checkModal').modal('show');
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
            checkSave(){
                Swal.fire({
                    title: '{{ __("確認同意改期") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "{{ $baseBtnColor }}",
                    cancelButtonText: '{{ __("取消") }}',
                    confirmButtonText: '{{ __("確認同意") }}',
                    allowOutsideClick: false,
                }).then((result) => {
                    if(result.isConfirmed){

                        var url = "{{ config('services.API_URL').'/order' }}/"+vc.editData.id;
                        $.ajax({
                            method: "PUT",
                            url: url,
                            data: {
                                status : 7,
                                redate_status : 1,
                                pre_work_start_date : vc.editData.order_apply_update.redate_start,
                                pre_work_end_date : vc.editData.order_apply_update.redate_end,
                                pre_work_days : vc.editData.order_apply_update.redate_days,
                                pre_work_hours : vc.editData.order_apply_update.redate_hours,
                                updated_name : "{{ auth()->guard('store')->user()->name }}",
                            },
                            dataType: 'json',
                            success(data) {
                                vc.get();
                                $('#checkModal').modal('hide');
                                sNotify(data.message);
                            },
                            error(xhr, ajaxOptions, thrownError) {
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                                if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                            },
                        });
                    }
                })
            },
            checkReject(){
                Swal.fire({
                    title: '{{ __("確認不同意改期") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "#F89D26",
                    cancelButtonText: '{{ __("取消") }}',
                    confirmButtonText: '{{ __("確認不同意") }}',
                    allowOutsideClick: false,
                }).then((result) => {
                    if(result.isConfirmed){

                        var url = "{{ config('services.API_URL').'/order' }}/"+vc.editData.id;
                        $.ajax({
                            method: "PUT",
                            url: url,
                            data: {
                                status : 7,
                                redate_status : 2,
                                updated_name : "{{ auth()->guard('store')->user()->name }}",
                            },
                            dataType: 'json',
                            success(data) {
                                vc.get();
                                $('#checkModal').modal('hide');
                                sNotify(data.message);
                            },
                            error(xhr, ajaxOptions, thrownError) {
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                                if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                            },
                        });
                    }
                })
            },
            goEdit(id){
                location.href = '{{ route('store.order.edit') }}/'+id;
            },
        },
        created : function(){
            this.get();
            this.init();
        },
        mounted: function(){
        }
    });
</script>
@stop