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
                            <button class="btn btn-primary mt-4" @click="download_ex()">{{ __('最新道具價格表下載') }}</button>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>{{ __('道具價格檔案上傳') }}</label>
                            <label class="btn btn-warning ml-2 mr-2 mb-2 btn-upload">
                                <input type="file" id="document_path" class="sr-only " name="file" @change="handleDocumentPathChange" accept=".xlsx">{{ __('選擇檔案') }}
                            </label>
                            <template v-if="new_document">
                                @{{ new_document?new_document.name:'' }}
                                <button type="button" class="btn btn-primary ml-2 mr-2 mb-2" @click="checkSave()">{{ __('確認上傳') }}</button>
                            </template>

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
                                    <th scope="col" @click="get('sort', 'created_at')">上傳日期 <i class="fas fa-sort"></i></th>
                                    <th scope="col" @click="get('sort', 'success')">成功 <i class="fas fa-sort"></i></th>
                                    <th scope="col" @click="get('sort', 'fail')">失敗 <i class="fas fa-sort"></i></th>
                                    <th scope="col">功能</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list,i) in lists">
                                    <td scope="row">@{{ i+1 }}</td>
                                    <td scope="row">@{{ list.created_at }}</td>
                                    <td scope="row">@{{ list.success }}</td>
                                    <td scope="row">@{{ list.fail }}</td>
                                    <td scope="row">
                                        <a href="#" @click.prevent.stop="viewModalShow(list)" class="btn btn-primary">{{ __('查看明細') }}</a>
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
    <div class="modal fade modal-bottom" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" v-if="viewData">
                    <h5 class="modal-title">@{{ viewData.created_at }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" v-if="viewData">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">no.</th>
                                        <th scope="col">item_id</th>
                                        <th scope="col">tag</th>
                                        <th scope="col">currency_item_id</th>
                                        <th scope="col">pirce</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(list,i) in viewData.item_price_upload_details">
                                        <td scope="row">@{{ i+1 }}</td>
                                        <td scope="row">@{{ list.item_id }}</td>
                                        <td scope="row">@{{ list.tag }}</td>
                                        <td scope="row">@{{ list.currency_item_id }}</td>
                                        <td scope="row">@{{ list.price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}</div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('default.取消') }}</button>
                </div> -->
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            new_document    : false,
            lists           : [],
            pageData        : false,
            searchData  : {
                'sort'      : 'created_at',
                'direction' : 'desc',
            },
            editData        : {},
            viewData        : false,
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

                let url = "{{ config('services.API_URL').'/item_price_upload' }}"
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
            handleDocumentPathChange(event) {
                vc.new_document = event.target.files[0];
                vc.editData.new_document = event.target.files[0];
            },
            viewModalShow(list){
                vc.viewData = list;
                $('#viewModal').modal('show');
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/item_price_upload/new' }}";

                vc.editData.updated_name = "{{ auth()->guard('admin')->user()->name }}";

                let formData = new FormData();
                $.each(vc.editData, function(i, v){
                    if (Array.isArray(v)) {
                        formData.append(i, JSON.stringify(v));
                    } else {
                        formData.append(i, v);
                    }
                })

                $.ajax({
                    method: method,
                    url: url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success(data) {
                        $('#document_path').val('');
                        vc.get();
                        sNotify(data.message);

                        vc.new_document = false;
                        vc.editData = {};
                    },
                    error(xhr, ajaxOptions, thrownError) {
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            download_ex() {
                vc = this;

                let url = "{{ config('services.API_URL').'/item_price_upload/download_price' }}"
                $.ajax({
                    method: "POST",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        location.href = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
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