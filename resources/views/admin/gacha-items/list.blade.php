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
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-3">
                                <label>{{ __('Item ID') }}</label>
                                <input type="text" class="form-control" v-model="searchData.item_id"
                                    @keydown.enter="get()" placeholder="{{ __('Item ID') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>{{ __('扭蛋機') }}</label>
                                <select class="form-control" v-model="searchData.gacha_id">
                                    <option value="">{{ __('全部') }}</option>
                                    <option v-for="gacha in gachas" :key="gacha.id" :value="gacha.id">
                                        @{{ gacha.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <button class="btn btn-primary" @click="get()">{{ __('default.搜尋') }}</button>
                                <button class="btn btn-success" @click="openCreateModal()">
                                    {{ __('default.新增') }}
                                </button>
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ __('Item ID') }}</th>
                                        <th scope="col">{{ __('抽獎機率') }}</th>
                                        <th scope="col">{{ __('扭蛋機名稱') }}</th>
                                        <th scope="col">{{ __('操作') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(list,i) in lists">
                                        <td scope="row">@{{ i + 1 }}</td>
                                        <td scope="row">@{{ list.item_id }}</td>
                                        <td scope="row">@{{ list.percent }} %</td>
                                        <td scope="row">@{{ list.gacha.name }}</td>
                                        <td scope="row" class="text-left ">
                                            <button class="btn btn-sm btn-primary nohide"
                                                @click="goEdit(list.id)">修改</button>
                                        </td>
                                </tbody>
                            </table>
                        </div>
                        @include('store.common.api_page')
                    </div>
                </div>
            </div>
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script type="text/javascript">
        var vc = new Vue({
            el: '#vc',
            data: {
                lists: [],
                pageData: false,
                searchData: {
                    'item_id': '',
                    'gacha_id': '',
                },
                gachas: [],
                words: {
                    "type": "",
                    "data_success": '',
                    "data_warning": '',
                },
            },
            methods: {
                get(type = false, sort = false) {
                    if (type == 'sort') {
                        this.searchData.sort = sort;
                        this.searchData.direction = this.searchData.direction == 'desc' ? 'asc' : 'desc';
                    }
                    if (type == 'up') this.pageData.current_page--;
                    if (type == 'down') this.pageData.current_page++;
                    this.searchData.current_page = this.pageData.current_page;
                    vc = this;
                    let baseUrl = "{{ config('services.API_URL') . '/gacha_items' }}";
                    let url = baseUrl;
                    let params = {};
                    if (this.searchData.item_id !== '') {
                        params.item_id = this.searchData.item_id;
                    }
                    if (this.searchData.gacha_id !== '') {
                        params.gacha_id = this.searchData.gacha_id;
                    }
                    $.ajax({
                        method: "GET",
                        url: url,
                        data: params,
                        dataType: 'json',
                        success(data) {
                            vc.lists = data.data.data;

                            vc.pageData = data.data;
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            console.log(xhr);
                        },
                    });
                },
                goEdit(id) {
                    window.location.href = `/admin/gacha-items/edit/${id}`;
                },
                openCreateModal() {
                    window.location.href = `/admin/gacha-items/add`;
                },
                async fetchGachas() {
                    let url = "{{ config('services.API_URL') . '/gacha' }}";
                    let response = await axios.get(url);
                    this.gachas = response.data.data.data;
                }
            },
            created: function() {
                this.get();
                this.fetchGachas();
            },
        });
    </script>
@stop
