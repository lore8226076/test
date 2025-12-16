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
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>{{ __('UID') }}</label>
                            <input type="text" class="form-control" v-model="searchData.uid" placeholder="{{ __('UID') }}" @input="get()">
                        </div>
                      <!--   <div class="form-group col-md-3">
                            <label>{{ __('default.服務名稱') }}</label>
                            <input type="text" class="form-control" v-model="searchData.store_services__name" placeholder="{{ __('default.服務名稱') }}" @keyup.enter="get()">
                        </div> -->
<!--
                        <div class="form-group offset-md-3 col-md-3 text-right">
                            <button class="btn btn-primary mt-4" @click="get()">{{ __('default.搜尋') }}</button>
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
                    <div class="row justify-content-end mb-2">
                        <!-- <button type="button" class="btn btn-primary ml-2 d-block" data-toggle="modal" data-target="#sortModal">{{ __('default.排序') }}</button>

                        <a href="#" v-if="searchData.is_active==1" @click.prevent="activeSwitch(0)" class="btn btn-primary ml-2 d-block">{{ __('default.停用區') }}</a>
                        <a href="#" v-else @click.prevent="activeSwitch(1)" class="btn btn-primary ml-2 d-block">{{ __('default.返回') }}</a> -->
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('id') }}</th>
                                    <th scope="col">{{ __('UID') }}</th>
                                    <th scope="col">{{ __('地圖') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="goEdit(list.id)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.id }}</td>
                                    <td scope="row">@{{ list.uid }}</td>
                                    <td scope="row">
                                        <a href="#" @click.prevent.stop="shopMapData(user_map, mi)" v-for="list.user_maps" class="btn btn-primary btn-xs mr-2 mb-2">{{ __('地圖') }}@{{ mi+1 }}</a>
                                    </td>
                                    <td scope="col" class="text-right">
                                        <template v-if="list.is_active==1">
                                            <a href="#" @click.prevent.stop="checkSave(list, 0)" class="btn btn-outline-danger mr-2 mb-2">{{ __('停用') }}</a>
                                        </template>
                                        <template v-else>
                                            <a href="#" @click.prevent.stop="checkSave(list, 1)" class="btn btn-primary mr-2 mb-2">{{ __('啟用') }}</a>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @include('store.common.api_page')
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
                <div class="modal-body" style="max-height: 300px; overflow-y: auto;">
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
            lists           : [],
            pageData        : false,
            store_service_category : [],
            searchData      : {
                'is_active' : 1,
                'store_services__store_service_category_id' : '',
                'store_services__name' : '',
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'sort'      : '',
                'direction' : '',
            },
            sorts           : [],
            words           : {
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
                if(type=='up') this.pageData.current_page--;
                if(type=='down') this.pageData.current_page++;
                this.searchData.current_page = this.pageData.current_page;
                vc = this;
                let url = "{{ config('services.API_URL').'/user' }}"
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

                url = "{{ config('services.API_URL').'/store_service_category' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        store_id : '{{ $store->id }}',
                        is_active : 1,
                    },
                    dataType: 'json',
                    success(data){
                        vc.store_service_category = data.data.data;
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
                    confirmButtonColor: "#008ecc",
                    cancelButtonText: '{{ __("default.取消") }}',
                    confirmButtonText: is_active?'{{ __("default.啟用") }}':'{{ __("default.停用") }}',
                }).then((result) => {
                    if(result.isConfirmed){
                        method = "PUT";
                        url = "{{ config('services.API_URL').'/store_service' }}/"+editData.id;
                        editData.is_active = is_active;
                        editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
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
            goEdit(id){
                // location.href = '{{ route('store.user.edit') }}/'+id
            }
        },
        created : function(){
            this.get();
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