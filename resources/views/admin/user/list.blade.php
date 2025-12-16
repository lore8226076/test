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
                            <label>{{ __('遊戲名稱') }}</label>
                            <input type="text" class="form-control" v-model="searchData.name" @keydown.enter="get()" placeholder="{{ __('遊戲名稱') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('帳號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.account" @keydown.enter="get()" placeholder="{{ __('帳號') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('UID') }}</label>
                            <input type="text" class="form-control" v-model="searchData.uid" @keydown.enter="get()" placeholder="{{ __('UID') }}">
                        </div>
                      <!--   <div class="form-group col-md-3">
                            <label>{{ __('default.服務名稱') }}</label>
                            <input type="text" class="form-control" v-model="searchData.store_services__name" placeholder="{{ __('default.服務名稱') }}" @keyup.enter="get()">
                        </div> -->

                        <!-- <div class="form-group offset-md-3 col-md-3 text-right"> -->

                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
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
                                    <th scope="col">{{ __('遊戲名稱') }}</th>
                                    <th scope="col">{{ __('帳號') }}</th>
                                    <th scope="col">{{ __('UID') }}</th>
                                    <th scope="col">{{ __('地圖') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.id }}</td>
                                    <td scope="row">@{{ list.name }}</td>
                                    <td scope="row">@{{ list.account }}</td>
                                    <td scope="row">@{{ list.uid }}</td>
                                    <td scope="row">
                                        <a href="#" v-for="(user_map, mi) in list.user_maps" class="btn btn-danger btn-xs mr-2 mb-2">@{{ user_map.id + ' ' +user_map.map_name }}</a>
                                    </td>
                                    <td scope="col" class="text-right">
                                        <a :href="goEdit(list.id)" class="btn btn-primary btn-xs mr-2 mb-2">{{ __('編輯') }}</a>
                                        <a href="#" @click.prevent.stop="showGiveItem(list)" class="btn btn-primary btn-xs mr-2 mb-2">{{ __('發道具') }}</a>
                                        <!-- <template v-if="list.is_active==1">
                                            <a href="#" @click.prevent.stop="checkSave(list, 0)" class="btn btn-outline-danger mr-2 mb-2">{{ __('停用') }}</a>
                                        </template>
                                        <template v-else>
                                            <a href="#" @click.prevent.stop="checkSave(list, 1)" class="btn btn-primary mr-2 mb-2">{{ __('啟用') }}</a>
                                        </template> -->
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
    <div class="modal fade modal-bottom" id="mapDataModal" tabindex="-1" role="dialog" aria-labelledby="mapDataModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ words.type }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        @{{ mapData }}
                    </div>
                </div>
                <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('default.取消') }}</button>
                    <button type="button" class="btn btn-primary col-12" @click="checkSave()">{{ __('default.確認儲存') }}</button>
                </div> -->
            </div>
        </div>
    </div>
    <div class="modal fade modal-bottom" id="giveItemModal" tabindex="-1" role="dialog" aria-labelledby="giveItemModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('發道具') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <label>UID：@{{ giveUser.uid }}</label>
                        </div>
                        <div class="col-12">
                            <label>帳號：@{{ giveUser.account }}</label>
                        </div>
                        <div class="col-12">
                            <label>遊戲ID：@{{ giveUser.name }}</label>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-3">
                            <label>Item ID</label>
                            <div class="input-group">
                                <input type="number" class="form-control" v-model="giveData.item_id" placeholder="輸入item_id">
                                <button class="btn btn-primary" type="button" @click="getItem()">查找</button>
                            </div>
                        </div>

                    </div>
                    <template v-if="itemData">
                        <div class="row">
                            <div class="col-3">
                                <div>Item ID： @{{ itemData.item_id }} </div>
                                <div>LocalizationName： @{{ itemData.localization_name }} </div>
                                <div>Category： @{{ itemData.category }} </div>
                                <div>Type： @{{ itemData.type }} </div>
                                <div>ManagerId： @{{ itemData.manager_id }} </div>
                                <div>Region： @{{ itemData.region }} </div>
                            </div>
                            <div class="col-3">
                                <label>數量</label>
                                <input type="number" class="form-control" v-model="giveData.qty" placeholder="輸入數量">
                                <div class="mt-2">
                                    <button class="btn btn-primary" type="button" @click="giveItem()">確認發放</button>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </template>

                </div>
                <div class="alert alert-success" role="alert" v-if="words.data_success">@{{ words.data_success }}</div>
                <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>

                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('default.取消') }}</button>
                    <button type="button" class="btn btn-primary col-12" @click="checkSave()">{{ __('default.確認儲存') }}</button>
                </div> -->
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
            mapData         : false,
            searchData      : {
                'is_active' : 1,
                'uid'       : '',
                'sort'      : '',
                'direction' : '',
            },
            sorts           : [],
            itemData        : false,
            giveUser        : false,
            giveData        : {
                'item_id'   : '',
                'qty'       : 1,
            },
            words           : {
                "type" : "",
                "data_success" : '',
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
            },
            getItem(){
                vc.itemData = false;
                vc.words.data_success = "";
                vc.words.data_warning = "";
                if(vc.giveData.item_id){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/data_center/get_item' }}/"+vc.giveData.item_id;
                    $.ajax({
                        method: "GET",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            console.log(data.data.item.item_id);
                            if(typeof data.data.item.item_id != "undefined"){
                                vc.itemData = data.data.item;
                            }
                            else{
                                vc.words.data_warning = "查無資料";
                            }

                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                }
            },
            giveItem(){
                let url = "{{ config('services.API_URL').'/user_item/give_item' }}";
                $.ajax({
                    method: "POST",
                    url: url,
                    data: {
                        uid : vc.giveUser.uid,
                        item_id : vc.itemData.item_id,
                        qty : vc.giveData.qty
                    },
                    dataType: 'json',
                    success(data){
console.log(data);
                        vc.itemData = false;
                        vc.words.data_success = "發放成功";
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            showMapData(mapData){
                vc.mapData = mapData;
                $('#mapDataModal').modal();
            },
            showGiveItem(user){
                vc.giveUser = user;
                $('#giveItemModal').modal();
            },
            activeSwitch(is_active){
                vc.searchData.is_active = is_active;
                vc.get();
            },
            checkSave(editData, is_active = 1){

            },
            sortModalShow(){
                this.sorts = JSON.parse(JSON.stringify(this.lists));
                $('ol.vertical').each(function(){
                    Sortable.create($(this)[0], {animation: 50});
                })
            },
            goEdit(id){
                return '{{ route('admin.user.edit') }}/'+id
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