@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        <div class="top-right-button-container">
            <a href="#" class="btn btn-primary btn-lg top-right-button mr-1" @click.prevent="openDataModal()">{{ __('新增') }}</a>
        </div>
        <div class="modal fade modal-right" id="dataModal" tabindex="-1" role="dialog"
            aria-labelledby="dataModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@{{ words.type }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" v-if="editData">
                        <div class="form-group">
                            <label>{{ __('身分') }}</label>
                            <input type="text" class="form-control" v-model="editData.name" placeholder="">
                        </div>
                        <div class="form-group">
                            <label>{{ __('狀態') }}</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" v-model="editData.is_active" id="role_is_active" :true-value="1" :false-value="0">
                                <label class="custom-control-label" for="role_is_active">{{ __('啟用') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('取消') }}</button>
                        <button type="button" class="btn btn-primary" @click="checkSave()">{{ __('確認儲存') }}</button>
                    </div>
                </div>
            </div>
        </div>
        @stop
        @include('store.common.map')
    </div>
    <div class="row mb-4">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" @click="get('sort','store_employee_roles.name')">{{ __('身分') }}</th>
                                    <th scope="col" @click="get('sort','store_employee_roles.is_active')">{{ __('狀態') }}</th>
                                    <th scope="col" @click="get('sort','store_employee_roles.updated_at')">{{ __('更新時間') }}</th>
                                    <th scope="col">{{ __('功能') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists">
                                    <th scope="row">@{{ i+1 }}</th>
                                    <th scope="row">@{{ list.name }}</th>
                                    <th scope="row">@{{ list.is_active_text }}</th>
                                    <th scope="row">@{{ list.updated_at }}</th>
                                    <th scope="row">
                                        <a href="#" class="btn btn-primary mr-2 mb-2" @click.prevent="openDataModal(list)">{{ __('修改') }}</a>
                                        <a :href="'{{ route('store.employee.role.permission') }}/'+list.id" class="btn btn-primary mr-2 mb-2">{{ __('權限') }}</a>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @include('store.common.page')
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
            pageData        : [],
            searchData      : [],
            editData        : null,
            words           : {
                "type"          : '10132',
                "data_warning"  : '',
            },
        },
        methods: {
            get(type = false,sort = false) {
                main_get('{{ \URL::current() }}', this.pageData, this.searchData, type, sort);
            },
            openDataModal(obj = false){
                if(obj===false){
                    vc.words.type = "{{ __('新增') }}";
                    vc.editData = {'id':0, 'name':'', 'is_active': 1};
                }
                else{
                    vc.words.type = "{{ __('修改') }}";
                    vc.editData =  JSON.parse(JSON.stringify(obj));
                }
                $('#dataModal').modal('show');
            },
            checkSave(){
                if(vc.editData.name==''){
                    vc.words.data_warning = "{{ __('請輸入身分') }}";
                    return false;
                }
                let url = "{{ route('store.employee.role.update') }}/"+vc.editData.id;
                $.ajax({
                    method: "POST",
                    url: url,
                    data: {
                        'editData'   : vc.editData,
                        '_token'     : '{!! csrf_token() !!}',
                    },
                    dataType: 'json',
                    success(data){
console.log(data);
                        if(data.success){
                            vc.get();
                            sNotify(data.message);
                            $('#dataModal').modal('hide');
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
@stop

