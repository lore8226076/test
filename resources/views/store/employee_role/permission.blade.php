@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        <div class="top-right-button-container">
            <a href="{{ route('store.employee.role') }}" class="btn btn-primary btn-lg top-right-button mr-1">{{ __('返回') }}</a>
        </div>
        @stop
        @include('store.common.map')
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-4"> {{ __('可使用頁面') }}</h5>
                    <form>
                        @foreach($storeMenus->where('up_id',0) as $storeMenu)
                        <div class="ml-1">
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input menuCheckBox main" id="menuCheck{{ $storeMenu->id }}" v-model="store_menu_ids" value="{{ $storeMenu->id }}" data-id="{{ $storeMenu->id }}">
                                <label class="custom-control-label text-one" for="menuCheck{{ $storeMenu->id }}">
                                    {{ $storeMenu->name }}
                                </label>
                            </div>
                            <div class="mb-1 ml-4 form-inline">
                                @foreach($storeMenu->children as $subMenu)
                                <div class="custom-control custom-checkbox mb-3 mr-3">
                                    <input type="checkbox" class="custom-control-input menuCheckBox sub sub{{ $storeMenu->id }}" id="menuCheck{{ $subMenu->id }}" v-model="store_menu_ids" value="{{ $subMenu->id }}" data-up="{{ $storeMenu->id }}">
                                    <label class="custom-control-label" for="menuCheck{{ $subMenu->id }}">
                                        {{ $subMenu->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <hr>
                        @endforeach
                        <button type="button" class="btn btn-primary mb-0" @click="checkSave">{{ __('確認儲存') }}</button>
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
            store_menu_ids  : [],
            pageData        : [],
            searchData      : [],
            editData        : null,
            words           : {
                "type" : "{{ __('新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get(type = false,sort = false) {
                vc = this;
                $.ajax({
                    method: "GET",
                    url: "{{ route('store.employee.role.permission', $store_employee_role_id) }}",
                    data: {
                        'getData'   : true,
                        '_token'    : '{!! csrf_token() !!}',
                    },
                    dataType: 'json',
                    success(data){
                        if(data.success){
                            if(data.storeEmployeePermission.store_menu_ids != null){
                                vc.store_menu_ids = JSON.parse(data.storeEmployeePermission.store_menu_ids);
                            }
                        }
                        else{
                            vc.words.data_warning = data.message;
                        }
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            checkSave(){
                vc.store_menu_ids = [];
                $('.menuCheckBox').each(function(k,v){
                    if($(v).prop('checked')){
                        vc.store_menu_ids.push($(v).val());
                    }
                })
                $.ajax({
                    method: "POST",
                    url: "{{ route('store.employee.role.update', $store_employee_role_id) }}",
                    data: {
                        'editData'   : {
                            store_menu_ids : vc.store_menu_ids,
                        },
                        '_token'     : '{!! csrf_token() !!}',
                    },
                    dataType: 'json',
                    success(data){
                        if(data.success){
                            vc.get();
                            sNotify(data.message);
                        }
                        else{
                            vc.words.data_warning = data.message;
                        }
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
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

