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
                            <label>{{ __('狀態') }}</label>
                            <select class="form-control select2-single" id="status" v-model="searchData.status" onChange="vc.get()">
                                <option value="0">{{ __('待審核') }}</option>
                                <option value="1">{{ __('同意') }}</option>
                                <option value="2">{{ __('不同意') }}</option>
                            </select>
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
                                    <th scope="col" @click="get('sort', 'status')">狀態</th>
                                    <th scope="col" @click="get('sort', 'created_at')">申請日</th>
                                    <th scope="col">身分</th>
                                    <th scope="col">名稱</th>
                                    <th scope="col">帳號</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists" @click="showCheckModal(list)">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.status_text }}</td>
                                    <td scope="row">@{{ list.created_at }}</td>
                                    <td scope="row">@{{ list.store_customer.apply_type_text }}</td>
                                    <td scope="row">@{{ list.store_customer.name }}</td>
                                    <td scope="row">@{{ list.store_customer.account }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @include('store.common.api_page')
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-bottom" id="checkModal" tabindex="-1" role="dialog" aria-labelledby="checkModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" v-if="editData">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('名稱') }}:@{{ editData.store_customer.name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label><h4><b>原銀行資料</b></h4></label>
                    <div class="row">
                        <div class="col-4">
                            <label><b>{{ __('銀行') }}</b></label>：@{{ editData.store_customer.bank_list.show_name }}
                        </div>
                        <div class="col-4">
                            <label><b>{{ __('銀行分行號碼') }}</b></label>：@{{ editData.store_customer.bank_sub_code }}
                        </div>
                        <div class="col-4">
                            <label><b>{{ __('銀行帳號') }}</b></label>：@{{ editData.store_customer.bank_account }}
                        </div>
                        <div class="col-12">
                            <label><b>{{ __('銀行存摺') }}</b></label>：<br>
                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="editData.store_customer.bank_url" @click="showOriginalImage(editData.store_customer.bank_url)">
                        </div>
                    </div>
                    <hr>
                    <label><h4><b>申請更改銀行資料</b></h4></label>
                    <div class="row">
                        <div class="col-4">
                            <label><b>{{ __('銀行') }}</b></label>：@{{ editData.bank_list.show_name }}
                        </div>
                        <div class="col-4">
                            <label><b>{{ __('銀行分行號碼') }}</b></label>：@{{ editData.bank_sub_code }}
                        </div>
                        <div class="col-4">
                            <label><b>{{ __('銀行帳號') }}</b></label>：@{{ editData.bank_account }}
                        </div>
                        <div class="col-12">
                            <label><b>{{ __('銀行存摺') }}</b></label>：<br>
                            <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="editData.bank_url" @click="showOriginalImage(editData.bank_url)">
                        </div>
                    </div>
                </div>
                <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary col-6" @click.prevent="checkSave()">{{ __('同意修改') }}</a>
                    <a href="#" class="btn btn-primary badge-primary-color col-6" @click.prevent="checkReject()">{{ __('不同意修改') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="originalImageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img v-if="originalImagePath" :src="originalImagePath" style="max-width: 100%">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('關閉') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            lists               : [],
            pageData            : false,
            searchData  : {
                'status'        : 0,
                'sort'          : 'updated_at',
                'direction'     : 'desc',
            },
            sorts               : [],
            editData            : false,
            originalImagePath   : '',
            words               : {
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
console.log(vc.searchData);
                let url = "{{ config('services.API_URL').'/store_customer_apply_update' }}"
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
            showCheckModal(list){
                if(list.status==0){
                    vc.editData = list;
                    $('#checkModal').modal('show');
                }
            },
            showOriginalImage(path){
                this.originalImagePath = path;
                $('#originalImageModal').modal('show');
            },
            init(){
            },
            checkSave(){
                Swal.fire({
                    title: '{{ __("確認同意修改銀行") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "{{ $baseBtnColor }}",
                    cancelButtonText: '{{ __("取消") }}',
                    confirmButtonText: '{{ __("確認同意") }}',
                    allowOutsideClick: false,
                }).then((result) => {
                    if(result.isConfirmed){

                        var url = "{{ config('services.API_URL').'/store_customer_apply_update' }}/"+vc.editData.id;
                        $.ajax({
                            method: "PUT",
                            url: url,
                            data: {
                                status : 1,
                                updated_name : "{{ auth()->guard('store')->user()->name }}",
                            },
                            dataType: 'json',
                            success(data) {
                                vc.get();
                                $('#checkModal').modal('hide');
                                sNotify(data.message);
                            },
                            error(xhr, ajaxOptions, thrownError) {
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                                if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                            },
                        });
                    }
                })
            },
            checkReject(){
                Swal.fire({
                    title: '{{ __("確認不同意修改銀行") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "#F89D26",
                    cancelButtonText: '{{ __("取消") }}',
                    confirmButtonText: '{{ __("確認不同意") }}',
                    allowOutsideClick: false,
                }).then((result) => {
                    if(result.isConfirmed){

                        var url = "{{ config('services.API_URL').'/store_customer_apply_update' }}/"+vc.editData.id;
                        $.ajax({
                            method: "PUT",
                            url: url,
                            data: {
                                status : 2,
                                updated_name : "{{ auth()->guard('store')->user()->name }}",
                            },
                            dataType: 'json',
                            success(data) {
                                vc.get();
                                $('#checkModal').modal('hide');
                                sNotify(data.message);
                            },
                            error(xhr, ajaxOptions, thrownError) {
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                                if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                            },
                        });
                    }
                })
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