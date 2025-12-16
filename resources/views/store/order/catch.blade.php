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
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>{{ __('單號') }}</label>
                            <input type="text" class="form-control" v-model="searchData.no" @keyup="get">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('狀態') }}</label>
                            <select class="form-control select2-single" id="status" v-model="searchData.status" onChange="vc.get()">
                                <option value="2_3_4_7_8">{{ __('全部') }}</option>
                                <option value="2">{{ __('待SV') }}</option>
                                <option value="3">{{ __('待報價') }}</option>
                                <option value="4">{{ __('待師傅確認報價') }}</option>
                                <option value="7">{{ __('待施工') }}</option>
                                <option value="8">{{ __('施工中') }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('安裝服務類型') }}</label>
                            <select class="form-control select2-single" id="repair_type" v-model="searchData.repair_type" onChange="vc.get()">
                                <option value="0">{{ __('全部') }}</option>
                                @foreach(\App\Models\Orders::getRepairTypes() as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('地點類型') }}</label>
                            <select class="form-control select2-single" id="place_type" v-model="searchData.place_type" onChange="vc.get()">
                                <option value="0">{{ __('全部') }}</option>
                                @foreach(\App\Models\Orders::getPlaceTypes() as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>{{ __('區域') }}</label>
                            <select class="form-control select2-single" id="city_area_list_id" v-model="searchData.city_area_list_id" onChange="vc.get()">
                                <option value="0">{{ __('全部') }}</option>
                                <template v-for="(citylist, i) in city_area_list">
                                    <option v-for="(area, i) in citylist.areas" :value="area.id">@{{ area.name }}</option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <!-- <div class="form-row justify-content-end">
                        <button class="btn btn-primary mr-2 mb-2" @click="get()">{{ __('搜尋') }}</button>
                    </div> -->
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
                                    <th scope="col" @click="get('sort', 'status')">狀態</th>
                                    <th scope="col" @click="get('sort', 'created_at')">開單日</th>
                                    <th scope="col" @click="get('sort', 'no')">單號</th>
                                    <th scope="col" @click="get('sort', 'repair_type')">{{ __('安裝') }}</th>
                                    <th scope="col" @click="get('sort', 'place_type')">{{ __('地點') }}</th>
                                    <th scope="col" @click="get('sort', 'city_area_list_id')">{{ __('地區') }}</th>
                                    <th scope="col" @click="get('sort', 'store_customer_id')">{{ __('認領人') }}</th>
                                    <th scope="col" @click="get('sort', 'pre_work_start_date')">{{ __('預計施工') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="goEdit(list.id)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.status_text }}</td>
                                    <td scope="row">@{{ list.created_at }}</td>
                                    <td scope="row">@{{ list.no }}</td>
                                    <td scope="row">@{{ list.repair_type_text }}</td>
                                    <td scope="row">@{{ list.place_type_text }}</td>
                                    <td scope="row">@{{ list.city_area_list?list.city_area_list.name:'' }}</td>
                                    <td scope="row">@{{ list.store_customer?list.store_customer.name + '('+list.store_customer.cellphone+')':'' }}</td>
                                    <td scope="row">@{{ list.pre_work_start_date }}</td>
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
            city_area_list  : [],
            searchData  : {
                'no'            : '',
                'status'        : '2_3_4_7_8',
                'repair_type'   : 0,
                'place_type'    : 0,
                'city_area_list_id' : 0,
                'store_id'  : '{{ auth()->guard("store")->user()->store_id }}',
                'sort'      : 'updated_at',
                'direction' : 'desc',
            },
            sorts           : [],
            words           : {
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
                vc.searchData.status= $('#status').val()
                vc.searchData.repair_type= $('#repair_type').val()
                vc.searchData.place_type= $('#place_type').val()
                vc.searchData.city_area_list_id= $('#city_area_list_id').val()
                let url = "{{ config('services.API_URL').'/order' }}"
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
            init(){
                url = "{{ config('services.API_URL').'/city_area_list/list' }}"
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                    },
                    dataType: 'json',
                    success(data){
                        vc.city_area_list = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            goEdit(id){
                location.href = '{{ route('store.order.edit') }}/'+id;
            },
        },
        created : function(){
            this.get();
            this.init();
        },
        mounted: function(){
        }
    });
</script>
@stop