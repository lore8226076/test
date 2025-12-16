@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        <!-- <div class="top-right-button-container mb-2">
            <a href="#" v-if="searchData.is_active==1" @click.prevent="activeSwitch(0)" class="btn btn-primary ml-2 d-block">{{ __('停用區') }}</a>
            <a href="#" v-else @click.prevent="activeSwitch(1)" class="btn btn-primary ml-2 d-block">{{ __('返回') }}</a>
        </div> -->
        @stop
        @include('store.common.map')
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>{{ __('名稱') }}</label>
                            <input type="text" class="form-control" v-model="searchData.store_customers__name" placeholder="{{ __('名稱') }}" @keyup="get()">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('帳號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.store_customers__account" placeholder="{{ __('帳號') }}" @keyup="get()">
                        </div>
                        <!-- <div class="form-group col-md-3 text-right">
                            <button class="btn btn-primary mt-4" @click="get()">{{ __('搜尋') }}</button>
                        </div> -->
                    </div>
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
                                    <th scope="col" @click="get('sort', 'store_customers.apply_at')">{{ __('申請時間') }}</th>
                                    <th scope="col" @click="get('sort', 'store_customers.apply_type')">{{ __('身分') }}</th>
                                    <th scope="col" @click="get('sort', 'store_customers.name')">{{ __('名稱') }}</th>
                                    <th scope="col" @click="get('sort', 'store_customers.account')">{{ __('帳號') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="goView(list)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.apply_at }}</td>
                                    <td scope="row">@{{ list.apply_type_text }}</td>
                                    <td scope="row">@{{ list.name }}</td>
                                    <td scope="row">@{{ list.account }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @include('store.common.api_page')
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-bottom" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" v-show="viewData">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('申請時間') }}：@{{ viewData.apply_at }} @{{ viewData.apply_type_text }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-1">
                        <div class="col-3">
                            <label><b>{{ __('名稱') }}</b></label>：
                            <label>@{{ viewData.name }}</label>
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('帳號') }}</b></label>：
                            <label>@{{ viewData.account }}</label>
                        </div>
                        <template v-if="viewData.apply_type==1">
                            <div class="col-3">
                                <label><b>{{ __('性別') }}</b></label>：
                                <label>@{{ viewData.gender_text }}</label>
                            </div>
                            <div class="col-3">
                                <label><b>{{ __('生日') }}</b></label>：
                                <label>@{{ viewData.birthday }}</label>
                            </div>
                        </template>
                        <template v-else>
                            <div class="col-3">
                                <label><b>BR</b></label>：
                                <label>@{{ viewData.company_br }}</label>
                            </div>
                            <div class="col-3">
                                <label><b>{{ __('勞保') }}</b></label>：
                                <label>@{{ viewData.company_labor }}</label>
                            </div>
                        </template>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <label><b>{{ __('電話') }}</b></label>：
                            <label>@{{ viewData.cellphone }}</label>
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('區域') }}</b></label>：
                            <label>@{{ viewData.city_area_list?viewData.city_area_list.name:'' }}</label>
                        </div>
                        <div class="col-6">
                            <label><b>{{ __('地址') }}</b></label>：
                            <label>@{{ viewData.address }}</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <label><b>{{ __('銀行') }}</b></label>：
                            <label>@{{ viewData.bank_list?viewData.bank_list.show_name:'' }}</label>
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('分行') }}</b></label>：
                            <label>@{{ viewData.bank_sub_code }}</label>
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('銀行戶口號碼') }}</b></label>：
                            <label>@{{ viewData.bank_account }}</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <label><b>{{ __('電訊公司經驗') }}</b></label>：
                            <label>@{{ viewData.telecom_company_experience_text }}</label>
                        </div>
                        <div class="col-3">
                            <label><b>{{ __('電訊工種') }}</b></label>：
                            <label>@{{ viewData.telecom_occupation_text }}</label>
                        </div>
                        <template v-if="viewData.apply_type==1">
                            <div class="col-3">
                                <label><b>{{ __('電訊年資') }}</b></label>：
                                <label>@{{ viewData.telecom_age }}</label>
                            </div>
                        </template>
                        <template v-else>
                            <div class="col-3">
                                <label><b>{{ __('工具') }}</b></label>：
                                <label>@{{ viewData.company_tools_text }}</label>
                            </div>
                        </template>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label><b>{{ __('備註') }}</b></label>：
                            <label>@{{ viewData.memo }}</label>
                        </div>
                    </div>
                    <div class="row"><div class="col-12"><hr></div></div>
                    <div class="row">
                        <template v-if="viewData.apply_type==1">
                            <div class="col-4 mb-1" v-show="viewData.sid_url">
                                <label><b>{{ __('身分證') }}</b></label>：
                                <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="viewData.sid_url">
                            </div>
                        </template>
                        <template v-else>
                            <div class="col-4 mb-1" v-show="viewData.company_ci_report_url">
                                <a :href="viewData.company_ci_report_url" class="btn btn-primary" target="_blank"><b>{{ __('CI以及年報表') }}</b></a>
                            </div>
                        </template>
                        <div class="col-4 mb-1" v-show="viewData.safe_card_url">
                            <label><b>{{ __('平安卡') }}</b></label>：
                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="viewData.safe_card_url">
                        </div>
                        <div class="col-4 mb-1" v-show="viewData.bank_url">
                            <label><b>{{ __('銀行戶口') }}</b></label>：
                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="viewData.bank_url">
                        </div>
                    </div>
                </div>
                <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('取消') }}</button> -->
                    <button type="button" class="btn btn-primary col-12" @click="checkSave()">{{ __('通過申請') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            store           : {!! json_encode($store) !!},
            lists           : [],
            pageData        : false,
            viewData        : false,
            store_customer_category : [],
            searchData      : {
                'is_active' : 1,
                'apply_type':'1_2',
                'apply_pass': 0,
                'store_customers__name' : '',
                'store_customers__account' : '',
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'sort'      : 'apply_at',
                'direction' : 'desc',
            },
            sorts           : [],
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
                if(type=='up') this.pageData.current_page--;
                if(type=='down') this.pageData.current_page++;
                this.searchData.current_page = this.pageData.current_page;
                vc = this;
                let url = "{{ config('services.API_URL').'/store_customer' }}"
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
            init(){
            },
            checkSave(){
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
                        vc.viewData.apply_pass = 1;

                        method = "PUT";
                        url = "{{ config('services.API_URL').'/store_customer' }}/"+vc.viewData.id;
                        vc.viewData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                        $.ajax({
                            method: method,
                            url: url,
                            data: vc.viewData,
                            dataType: 'json',
                            success(data){
                                vc.get();
                                sNotify('{{ __("審核通過") }}');
                                $('#viewModal').modal('hide');
                            },
                            error:function(xhr, ajaxOptions, thrownError){
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                            },
                        });
                    }
                })
            },
            goView(list){
                vc.viewData = list;
                $('#viewModal').modal('show');
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