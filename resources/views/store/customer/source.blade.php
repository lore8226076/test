@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        <div class="top-right-button-container mb-2">
            <a href="#" class="btn btn-primary badge-primary-color top-right-button mr-1" @click.prevent="openDataModal()">{{ __('default.新增') }}</a>
        </div>
        <div class="modal fade modal-bottom" id="dataModal" tabindex="-1" role="dialog"
            aria-labelledby="dataModal" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@{{ words.type }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" v-if="editData">
                        <div class="form-group">
                            <label>{{ __('default.來源') }}</label>
                            <input type="text" class="form-control" v-model="editData.name" placeholder="">
                        </div>
                    </div>
                    <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                    <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('default.取消') }}</button> -->
                        <button type="button" class="btn btn-primary col-12" @click="checkSave()">{{ __('default.確認儲存') }}</button>
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
                    <div class="row justify-content-end mb-2">
                        <button type="button" class="btn btn-primary ml-2 d-block" v-if="searchData.is_active==1" data-toggle="modal" data-target="#sortModal">{{ __('default.排序') }}</button>

                        <a href="#" v-if="searchData.is_active==1" @click.prevent="activeSwitch(0)" class="btn btn-primary ml-2 d-block">{{ __('default.停用區') }}</a>
                        <a href="#" v-else @click.prevent="activeSwitch(1)" class="btn btn-primary ml-2 d-block">{{ __('default.返回') }}</a>
                    </div>


                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" @click="get('sort','store_customer_source.name')">{{ __('default.來源') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="openDataModal(list)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.name }}</td>
                                    <td scope="row" class="text-right">
                                        <template v-if="list.is_active==1">
                                            <a href="#" @click.prevent.stop="switchActive(list, 0)" class="btn btn-outline-danger mr-2 mb-2">{{ __('default.停用') }}</a>
                                        </template>
                                        <template v-else>
                                            <a href="#" @click.prevent.stop="switchActive(list, 1)" class="btn btn-primary mr-2 mb-2">{{ __('default.啟用') }}</a>
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
            lists           : [],
            pageData        : false,
            searchData      : {
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'is_active' : 1,
                'sort'      : 'sort',
                'direction' : 'asc',
            },
            editData        : null,
            sorts           : [],
            words           : {
                "type"          : '10132',
                "data_warning"  : '',
            },
        },
        methods: {
            activeSwitch(is_active){
                vc.searchData.is_active = is_active;
                vc.get();
            },
            get(type = false, sort = false) {
                this.lists = [];
                if(type == 'sort'){
                    this.searchData.sort = sort;
                    this.searchData.direction = this.searchData.direction=='desc'?'asc':'desc';
                }
                if(type=='up') this.pageData.current_page--;
                if(type=='down') this.pageData.current_page++;
                this.searchData.current_page = this.pageData.current_page;
                vc = this;
                let url = "{{ config('services.API_URL').'/store_customer_source' }}"
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
            openDataModal(obj = false){
                if(obj===false){
                    vc.words.type = "{{ __('default.新增') }}";
                    vc.editData = {'id':0, 'name':'', 'is_active': 1};
                }
                else{
                    vc.words.type = "{{ __('default.編輯') }}";
                    vc.editData =  JSON.parse(JSON.stringify(obj));
                }
                $('#dataModal').modal('show');
            },
            switchActive(editData, is_active){
                Swal.fire({
                    title: is_active?'{{ __("default.確認啟用") }}?':'{{ __("default.確認停用") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: '{{ __("default.取消") }}',
                    confirmButtonColor: "#008ecc",
                    confirmButtonText: is_active?'{{ __("default.啟用") }}':'{{ __("default.停用") }}',
                }).then((result) => {
                    if(result.isConfirmed){
                        editData.is_active = is_active;
                        vc.editData = editData;
                        vc.checkSave();
                    }
                })
            },
            checkSave(){
                if(vc.editData.name==''){
                    vc.words.data_warning = "{{ __('default.請輸入分類') }}";
                    return false;
                }
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_customer_source' }}";

                if(vc.editData.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/store_customer_source' }}/"+vc.editData.id;
                }
                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        vc.get();
                        sNotify(data.message);
                        $('#dataModal').modal('hide');
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        xhr.responseText = JSON.parse(xhr.responseText);
                        console.log(xhr.responseText);
                        vc.words.data_warning = xhr.responseText.message;
                    },
                });
            },
            sortModalShow(){
                this.sorts = JSON.parse(JSON.stringify(this.lists));
                $('ol.vertical').each(function(){
                    Sortable.create($(this)[0], {animation: 50});
                })
            },
            checkSortSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_customer_source/update_sort' }}";

                $.ajax({
                    method: method,
                    url: url,
                    data: {
                        sorts : this.sorts,
                        updated_name : "{{ auth()->guard('store')->user()->name }}"
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

