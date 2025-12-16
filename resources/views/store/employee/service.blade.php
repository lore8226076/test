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
                    <select v-model="store_employee_id" id="store_employee_id" class="form-control select2-single">
                        <option value="0">{{ __("default.請選擇員工") }}</option>
                        <option v-for="(store_employee, i) in store_employees" :value="store_employee.id">@{{ store_employee.show_data }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row" v-if="store_employee_id>0">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <form autocomplete="off">
                        <div class="form-row mb-2">
                            <button type="button" class="btn btn-primary badge-primary-color mr-2 d-block" data-toggle="modal" data-target="#speedCopyModal">{{ __('default.快速複製') }}</button>
                            <button type="button" class="btn btn-primary badge-primary-color mr-2 d-block" @click="copyStore">{{ __('default.快速預設') }}</button>
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('default.服務分類') }}</th>
                                        <th scope="col">{{ __('default.服務名稱') }}</th>
                                        <th scope="col">{{ __('default.開放預約') }}</th>
                                        <th scope="col">{{ __('default.服務時間(分鐘)') }}</th>
                                        <th scope="col">{{ __('default.費用') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(list,i) in lists">
                                        <td scope="row">@{{ list.store_service_category.name }}</td>
                                        <td scope="row">@{{ list.name }}</td>
                                        <td scope="row">
                                            <div class="custom-switch custom-switch-secondary mb-2">
                                                <input class="custom-switch-input" :id="'week1_work'+i" type="checkbox" checked="">
                                                <label class="custom-switch-btn" :for="'week1_work'+i"></label>
                                            </div>
                                        </td>
                                        <td scope="row">
                                            <div class="form-group">
                                                <select class="form-control">
                                                    @for ($minute = 0; $minute < 360; $minute += 15)
                                                        <option value="">{{ $minute }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </td>
                                        <td scope="row">
                                            <div class="form-group">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">＄</span>
                                                    <input type="number" step="1" class="form-control" v-model="list.price" ref="price">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-row mt-4">
                            <button type="button" class="btn btn-primary d-block col-12" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>

                        <div class="modal fade" id="speedCopyModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('default.快速複製') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="checkbox" class="custom-control-input" id="kk">
                                                    <label class="custom-control-label text-one" for="kk">
                                                        Abby
                                                    </label>
                                                </div>
                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="checkbox" class="custom-control-input" id="kk2">
                                                    <label class="custom-control-label text-one" for="kk2">
                                                        KIKI
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary col-12" data-dismiss="modal">{{ __('default.確認複製') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $('#store_employee_id').on('change', function(){
            vc.store_employee_id = $(this).val();
            vc.get();
        })
    })
    var vc = new Vue({
        el:'#vc',
        data:{
            store_employee_id   : 0,
            store_employees     : [],
            lists               : [
                {
                    category_name:'剪髮',
                    name:'專業造型剪髮',
                    開放預約:'造型剪髮',
                },
                {
                    category_name:'洗髮',
                    name:'紓壓洗髮',
                    開放預約:'造型剪髮',
                },
                {
                    category_name:'染髮',
                    name:'女生染髮XL',
                    開放預約:'造型剪髮',
                },
                {
                    category_name:'燙髮',
                    name:'紳士專業燙',
                    開放預約:'造型剪髮',
                },
                {
                    category_name:'護髮',
                    name:'專業濕滑保水',
                    開放預約:'造型剪髮',
                }
            ],
            words               : {
                "data_warning" : '',
            },
        },
        methods: {
            get(type = false,sort = false) {
                vc = this;
                let url = "{{ config('services.API_URL').'/store_service' }}"
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
            getStoreEmployees(){
                let url = "{{ config('services.API_URL').'/store_employee' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'store_employees__store_id' : '{{ auth()->guard("store")->user()->store_id }}',
                    },
                    dataType: 'json',
                    success(data){
                        vc.store_employees = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    },
                });
            },
            checkSave(){

            },
            copyStore(){

            },
        },
        created : function(){
            this.get();
            this.getStoreEmployees();
        }
    });
</script>
@stop