@extends('admin.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        <div class="top-right-button-container mb-2">
            <a href="{{ route('admin.account.admin.role.edit',0) }}" class="btn btn-primary badge-primary-color top-right-button mr-1">{{ __('default.新增') }}</a>
        </div>
        @stop
        @include('admin.common.map')
    </div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end mb-2">
                        <a href="#" v-if="searchData.is_active==1" @click.prevent="activeSwitch(0)" class="btn btn-primary d-block">{{ __('default.停用區') }}</a>
                        <a href="#" v-else @click.prevent="activeSwitch(1)" class="btn btn-primary d-block">{{ __('default.返回') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" @click="get('sort', 'name')">{{ __('default.身分') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="goEdit(list.id)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.name }}</td>
                                    <td scope="col" class="text-right">
                                        <template v-if="list.is_active==1">
                                            <a href="#" @click.prevent.stop="checkSave(list, 0)" class="btn btn-outline-danger mr-2 mb-2">{{ __('default.停用') }}</a>
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
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            lists       : [],
            searchData  : {
                'is_active' : 1,
                'sort'      : '',
                'direction' : '',
            },
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
                let url = "{{ config('services.API_URL').'/admin_role' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: vc.searchData,
                    dataType: 'json',
                    success(data){
                        vc.lists = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
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
                    cancelButtonText: '{{ __("default.取消") }}',
                    confirmButtonColor: "{{ $baseBtnColor }}",
                    confirmButtonText: is_active?'{{ __("default.啟用") }}':'{{ __("default.停用") }}',
                }).then((result) => {
                    if(result.isConfirmed){
                        method = "PUT";
                        url = "{{ config('services.API_URL').'/admin_role' }}/"+editData.id;
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
                                if(xhr.responseJSON.field == 'is_active_back1'){
                                    editData.is_active = 1;
                                }
                                sNotify(xhr.responseJSON.message, 'danger');
                            },
                        });
                    }
                })
            },
            goEdit(id){
                location.href = '{{ route('admin.account.role.edit') }}/'+id
            }
        },
        created : function(){
            this.get();
        }
    });
</script>
@stop