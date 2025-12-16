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
                            <label>{{ __('編號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.no" placeholder="{{ __('編號') }}" @keyup="get()">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('個人姓名') }}</label>
                            <input type="text" class="form-control" v-model="searchData.name" placeholder="{{ __('個人姓名') }}" @keyup="get()">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('個人帳號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.account" placeholder="{{ __('個人帳號') }}" @keyup="get()">
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
        <div class="col-12 list" data-check-all="checkAll">
            <div class="card d-flex flex-row mb-3" v-for="(list,i) in lists" v-if="list.is_active==searchData.is_active" @click="goEdit(list.id)">
                <div class="d-flex flex-grow-1 min-width-zero">
                    <div class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
                        <p class="list-item-heading mb-0 truncate w-10 w-xs-100" v-html="'#'+list.id"></p>
                        <p class="list-item-heading mb-0 truncate w-20 w-xs-100" v-html="list.no"></p>
                        <p class="list-item-heading mb-0 truncate w-20 w-xs-100" v-html="list.name"></p>
                        <p class="list-item-heading mb-0 truncate w-50 w-xs-100" v-html="list.account"></p>
                        <p class="list-item-heading mb-0 truncate w-40 w-xs-100" v-html="list.city_area_list?list.city_area_list.name:''"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center text-center">
        @include('store.common.api_page')
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            store           : {!! json_encode($store) !!},
            lists           : [],
            pageData        : false,
            store_customer_category : [],
            searchData      : {
                'is_active' : 1,
                'apply_type': 1,
                'apply_pass': 1,
                'no'   : '',
                'name' : '',
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'sort'      : 'id',
                'direction' : 'asc',
            },
            sorts           : [],
            words           : {
                "type" : "{{ __('新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get(type = false, sort = false) {
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
            activeSwitch(is_active){
                vc.searchData.is_active = is_active;
                vc.get();
            },
            checkSave(editData, is_active = 1){
                Swal.fire({
                    title: is_active?'{{ __("確認啟用") }}?':'{{ __("確認停用") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "{{ $baseBtnColor }}",
                    cancelButtonText: '{{ __("取消") }}',
                    confirmButtonText: is_active?'{{ __("啟用") }}':'{{ __("停用") }}',
                    allowOutsideClick: false,
                }).then((result) => {
                    if(result.isConfirmed){
                        editData.is_active = is_active;

                        // method = "PUT";
                        // url = "{{ config('services.API_URL').'/store_customer' }}/"+editData.id;
                        // editData.is_active = is_active;
                        // editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                        // $.ajax({
                        //     method: method,
                        //     url: url,
                        //     data: editData,
                        //     dataType: 'json',
                        //     success(data){
                        //         vc.get();
                        //         sNotify(data.message);
                        //     },
                        //     error:function(xhr, ajaxOptions, thrownError){
                        //         console.log(xhr);
                        //         sNotify(xhr.responseJSON.message, 'danger');
                        //     },
                        // });
                    }
                })
            },
            goEdit(id){
                location.href = '{{ route('store.customer.pedit') }}/'+id
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