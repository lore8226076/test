@extends('admin.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('admin.common.map')
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>{{ __('default.姓名') }}</label>
                            <input type="text" class="form-control" v-model="searchData.name" placeholder="{{ __('default.姓名') }}" @keyup="get()">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('default.身分') }}</label>
                            <select v-model="searchData.admin_role_id" id="admin_role_id" class="form-control select2-single" onChange="vc.get()">
                                <option value="0">{{ __('default.請選擇') }}</option>
                                <option v-for="(admin_role, i) in admin_roles" :value="admin_role.id">@{{ admin_role.name }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('default.帳號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.account" placeholder="{{ __('default.帳號') }}" @keyup="get()">
                        </div>
                        <div class="form-group col-md-3 text-right"> <!-- Use text-right to align the button to the right -->
                            <button class="btn btn-primary mt-4" @click="get()">{{ __('default.搜尋') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end mb-2">
                        <button type="button" class="btn btn-primary ml-2 d-block" data-toggle="modal" data-target="#sortModal">{{ __('default.排序') }}</button>

                        <a href="#" v-if="searchData.is_active==1" @click.prevent="activeSwitch(0)" class="btn btn-primary ml-2 d-block">{{ __('default.停用區') }}</a>
                        <a href="#" v-else @click.prevent="activeSwitch(1)" class="btn btn-primary ml-2 d-block">{{ __('default.返回') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" @click="get('sort', 'name')">{{ __('default.姓名') }}</th>
                                    <th scope="col" @click="get('sort', 'admin_role_id')">{{ __('default.身分') }}</th>
                                    <th scope="col" @click="get('sort', 'account')">{{ __('default.帳號') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="goEdit(list.id)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.name }}</td>
                                    <td scope="row">@{{ list.admin_role?list.admin_role.name:'' }}</td>
                                    <td scope="row">@{{ list.account }}</td>
                                    <td scope="col" class="text-right">
                                        <template v-if="list.is_active==1">
                                            <a href="#" @click.prevent.stop="checkSave(list, 0)" v-if="list.id != auth_id" class="btn btn-outline-danger mr-2 mb-2">{{ __('default.停用') }}</a>
                                        </template>
                                        <template v-else>
                                            <a href="#" @click.prevent.stop="checkSave(list, 1)" class="btn btn-primary mr-2 mb-2">{{ __('default.啟用') }}</a>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="sortModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('default.排序') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <ul class="list-unstyled" id="sorts">
                                <li v-for="(obj, i) in sorts">
                                    <p>
                                        <span class="badge badge-pill badge-secondary handle">
                                            <i class="simple-icon-cursor-move"></i>
                                        </span>
                                        <span>
                                            <label>@{{ obj.name }}</label>
                                        </span>
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary col-12" @click="checkSortSave()">{{ __('default.儲存') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            admin_roles : [],
            auth_id     : '{{ auth()->guard("admin")->user()->id }}',
            lists       : [],
            searchData  : {
                'is_active' : 1,
                'admin_role_id' : 0,
                'account' : '',
                'name' : '',
                'sort'      : 'sort',
                'direction' : 'asc',
            },
            sorts       : [],
            words       : {
                "type" : "{{ __('default.新增') }}",
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
                let url = "{{ config('services.API_URL').'/admin' }}"

                this.searchData.admin_role_id = $('#admin_role_id').val();
console.log(this.searchData.admin_role_id);
                $.ajax({
                    method: "GET",
                    url: url,
                    data: this.searchData,
                    dataType: 'json',
                    success(data){
                        vc.lists = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });

            },
            getAdminRoles(){
                let url = "{{ config('services.API_URL').'/admin_role' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'is_active' : 1,
                    },
                    dataType: 'json',
                    success(data){
                        vc.admin_roles = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    },
                });
            },
            activeSwitch(is_active){
                vc.searchData.is_active = is_active;
                vc.get();
            },
            checkSave(editData, is_active = 1){
                Swal.fire({
                    title: is_active?'{{ __("default.確認啟用") }}?':'{{ __("default.確認停用") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "{{ $baseBtnColor }}",
                    cancelButtonText: '{{ __("default.取消") }}',
                    confirmButtonText: is_active?'{{ __("default.啟用") }}':'{{ __("default.停用") }}',
                }).then((result) => {
                    if(result.isConfirmed){
                        method = "PUT";
                        url = "{{ config('services.API_URL').'/admin' }}/"+editData.id;
                        editData.is_active = is_active;
                        editData.updated_name = "{{ auth()->guard('admin')->user()->name }}";
                        $.ajax({
                            method: method,
                            url: url,
                            data: editData,
                            dataType: 'json',
                            success(data){
                                vc.get();
                                sNotify(data.message);
                            },
                            error:function(xhr, ajaxOptions, thrownError){
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                            },
                        });
                    }
                })
            },
            sortModalShow(){
                this.sorts = JSON.parse(JSON.stringify(this.lists));
                $('ol.vertical').each(function(){
                    Sortable.create($(this)[0], {animation: 50});
                })
            },
            checkSortSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/admin/update_sort' }}";

                $.ajax({
                    method: method,
                    url: url,
                    data: {
                        sorts : this.sorts,
                        updated_name : "{{ auth()->guard('admin')->user()->name }}"
                    },
                    dataType: 'json',
                    success(data){
                        vc.searchData.sort = 'sort';
                        vc.searchData.direction = 'asc';
                        vc.get();
                        $('#sortModal').modal('hide');
                        sNotify(data.message);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if(typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            goEdit(id){
                location.href = '{{ route('admin.account.edit') }}/'+id
            }
        },
        created : function(){
            this.get();
            this.getAdminRoles();
        },
        mounted: function(){
            var self = this;
            self.$nextTick(function() {
                var modalElement = document.getElementById('sortModal');
                var sortable = new Sortable(document.getElementById('sorts'), {
                    onEnd: function(e) {
                        var clonedItems = self.sorts.filter(function(item) {
                            return item;
                        });
                        clonedItems.splice(e.newIndex, 0, clonedItems.splice(e.oldIndex, 1)[0]);
                        self.sorts = [];
                        self.$nextTick(function() {
                            self.sorts = clonedItems;
                        });

                        modalElement.style.overflow = '';
                    },
                    onStart: function(e) {
                        modalElement.style.overflow = 'hidden';
                    },
                    touchStartThreshold: 3,
                });
            });
            $('#sortModal').on('shown.bs.modal', this.sortModalShow);
        }
    });
</script>
@stop