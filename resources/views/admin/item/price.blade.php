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
                            <label>{{ __('Item ID') }}</label>
                            <input type="text" class="form-control" v-model="searchData.item_id" @keydown.enter="get()" placeholder="{{ __('Item ID') }}">
                        </div>
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
                        <a :href="goEdit()" class="btn btn-primary ml-2 d-block">{{ __('更新價格') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" @click="get('sort', 'item_id')">{{ __('Item ID') }} <i class="fas fa-sort"></i></th>
                                    <th scope="col" @click="get('sort', 'tag')">{{ __('Tag') }} <i class="fas fa-sort"></i></th>
                                    <th scope="col" @click="get('sort', 'currency_item_id')">{{ __('貨幣Item ID') }} <i class="fas fa-sort"></i></th>
                                    <th scope="col" @click="get('sort', 'price')">{{ __('價格') }} <i class="fas fa-sort"></i></th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.item_id }}</td>
                                    <td scope="row">@{{ list.tag }}</td>
                                    <td scope="row">@{{ list.currency_item_id }}</td>
                                    <td scope="row">@{{ list.price }}</td>
                                    <td scope="col" class="text-right">
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

</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            lists           : [],
            pageData        : false,
            searchData      : {
                'item_id' : '',
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
                let url = "{{ config('services.API_URL').'/item_price' }}"
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
            goEdit(){
                return '{{ route('admin.item.price_upload') }}';
            },
        },
        created : function(){
            this.get();

        },
        mounted: function(){
            var self = this;
        }
    });
</script>
@stop