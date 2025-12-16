@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('store.common.map')
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- <h5 class="mb-4">{{ __('default.店家資料') }}</h5> -->
                    <form autocomplete="off">
                        <div class="form-row">
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>{{ __('default.幾小時前可以取消/更改預約') }}</label>
                                <input type="number" step="1" min="0" class="form-control" v-model="editData.can_cancel_hour" placeholder="{{ __('default.幾小時前可以取消/更改預約') }}" @input="validateCanCancelHour">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>{{ __('default.預約服務介紹') }}</label>
                                <textarea id="service_introduce" class="form-control" rows="8" v-html="editData.service_introduce"></textarea>
                            </div>
                        </div>
                        <div class="form-row mt-4">
                            <button type="button" class="btn btn-primary d-block col-12" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/vendor/ckeditor5-build-classic/ckeditor.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        ClassicEditor
            .create(document.querySelector('#service_introduce'), {
                name: 'service_introduce',
                removePlugins: ['ImageUpload']
            })
            .then(editor => {
                window.ckeditorInstance = editor;
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>
<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            editData        : {},
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store' }}/{{ $store->encode_id }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data.store_setting;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    },
                });
            },
            checkSave(){
                let method = "PUT";
                let url = "{{ config('services.API_URL').'/store_setting' }}/"+vc.editData.id;
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                vc.editData.service_introduce = window.ckeditorInstance.getData();
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        sNotify(data.message);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if(typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            validateCanCancelHour(){
                if(this.editData.can_cancel_hour==''){
                    this.editData.can_cancel_hour = 0;
                }
                else{
                    this.editData.can_cancel_hour=this.editData.can_cancel_hour.replace(/^(0+)|[^\d]+/g,'')
                }
            },
        },
        created: function(){
            this.get();
        },
        mounted: function(){

        },
    });
</script>
@stop