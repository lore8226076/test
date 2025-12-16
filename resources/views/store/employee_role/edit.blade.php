@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        <!-- <div class="top-right-button-container mb-2">
            <a href="{{ route('store.employee.role.list') }}" class="btn btn-primary badge-primary-color top-right-button mr-1">{{ __('default.返回') }}</a>
        </div> -->
        @stop
        @include('store.common.map')
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form>
                        <div class="form-row">
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.身分') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.name" ref="name" placeholder="{{ __('default.身分') }}">
                            </div>
                        </div>
                        <h5 class="mb-4">
                            {{ __('default.可使用頁面') }}
                        </h5>
                        @foreach($storeMenus->where('up_id',0)->where('id','!=',100) as $storeMenu)
                        <div class="ml-1">
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input menuCheckBox main" id="menuCheck{{ $storeMenu->id }}" v-model="editData.store_menu_ids" value="{{ $storeMenu->id }}" data-id="{{ $storeMenu->id }}">
                                <label class="custom-control-label text-one" for="menuCheck{{ $storeMenu->id }}">
                                    {{ __('menu.'.$storeMenu->name) }}
                                </label>
                            </div>
                            <div class="mb-1 ml-4 form-inline">
                                @foreach($storeMenu->children as $subMenu)
                                <div class="custom-control custom-checkbox mb-3 mr-3">
                                    <input type="checkbox" class="custom-control-input menuCheckBox sub sub{{ $storeMenu->id }}" id="menuCheck{{ $subMenu->id }}" v-model="editData.store_menu_ids" value="{{ $subMenu->id }}"  data-id="{{ $subMenu->id }}" data-up="{{ $storeMenu->id }}">
                                    <label class="custom-control-label" for="menuCheck{{ $subMenu->id }}">
                                        {{ __('menu.'.$subMenu->name) }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <hr>
                        @endforeach
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
            storeMenus      : [],
            pageData        : [],
            searchData      : [],
            editData        : {},
            words           : {
                "type" : "{{ __('default.新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get(type = false,sort = false) {
                let url = "{{ config('services.API_URL').'/store_employee_role' }}/"+this.id;
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;

                        if(vc.editData.store_menu_ids==null){
                            vc.editData.store_menu_ids = [];
                        }
                        else if(vc.editData.store_menu_ids){
                            vc.editData.store_menu_ids = JSON.parse(vc.editData.store_menu_ids);
                        }
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_employee_role' }}";

                if(vc.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/store_employee_role' }}/"+this.id;
                }
                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                vc.updateStoreMenuIds();

                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        if(method=='POST'){
                            location.href = '{{ route("store.employee.role.list") }}';
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
            updateStoreMenuIds() {
                vc.editData.store_menu_ids = [];
                $('.menuCheckBox').each(function(k,v){
                    if($(v).prop('checked')){
                        vc.editData.store_menu_ids.push($(v).data('id'));
                    }
                });
                if(vc.editData.store_menu_ids.length==0){
                    vc.editData.store_menu_ids = null;
                }
            },
        },
        created : function(){
            this.get();
        }
    });
</script>
<script type="text/javascript">
    $(function(){
        $('.main').on('change', function(){
            var checked = $(this).prop('checked');
            $('.sub'+$(this).data('id')).each(function(k,v){
                $(v).attr('id');
                $(v).prop('checked', checked);
            })
        });
        $('.sub').on('change', function(){
            var checked = false;
            $('.sub'+$(this).data('up')).each(function(k,v){
                if($(this).prop('checked')){
                    checked = true;
                }
            });
            $('#menuCheck'+$(this).data('up')).prop('checked', checked);
        });
    });
</script>
@stop

