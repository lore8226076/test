@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('store.common.map')
    </div>

    <div class="row mb-4" v-if="store_employee_count >= store_employee_limit">
        <div class="col-12">
            <div class="alert alert-danger rounded" role="alert">{{ __('default.員工數量已經超過上限') }}</div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form ref="myForm" autocomplete="off">
                        <div class="form-row">
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.帳號') }}<span class="red">*</span></label>
                                <input type="number" class="form-control" v-model="editData.account" ref="account" placeholder="{{ __('default.帳號') }}" :readonly="id != 0">
                                <input type="hidden" class="form-control" v-model="editData.store_id" ref="store_id">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.新密碼') }}<span class="red" v-if="id==0">*</span></label>
                                <input type="password" class="form-control" v-model="editData.password" ref="password" placeholder="{{ __('default.新密碼') }}" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.姓名') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.name" ref="name" placeholder="{{ __('default.姓名') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.聯絡電話') }}</label>
                                <input type="text" class="form-control" v-model="editData.cellphone" placeholder="{{ __('default.聯絡電話') }}">
                            </div>
                        </div>
                        <div class="form-row mt-4">
                            <button type="button" class="btn btn-primary d-block col-12" @click="checkSave">{{ __('default.確認儲存') }}</button>
                        </div>

                        <div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('default.圖片裁切') }} <span id="ratio_text"></span></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="croper-box">
                                            <input type="hidden" id="crop_type">
                                            <img id="preview_image">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <!-- <button type="button" class="btn btn-secondary"data-dismiss="modal">Close</button> -->
                                        <button type="button" class="btn btn-primary col-12" @click="cropImage()">{{ __('default.確認') }}</button>
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
    var vc = new Vue({
        el:'#vc',
        data:{
            id              : '{{ $id?:0 }}',
            editData        : {},
            bank_lists      : [],
            store_employee_roles : [],
            store_employee_count : 0,
            store_employee_limit : '{{ $storeSetting->store_employee_limit }}',
            words           : {
                "type" : "{{ $id?__('員工編輯'):__('員工新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store_employee' }}/"+this.id;
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            getStoreEmployeeCount(){
                let url = "{{ config('services.API_URL').'/store_employee' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        store_id    : '{{ $store->id }}',
                        getCount    : true
                    },
                    dataType: 'json',
                    success(data){
                        vc.store_employee_count = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_employee' }}";

                if(vc.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/store_employee' }}/"+this.id;
                }
                vc.editData.store_id = '{{ $store->id }}';
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                vc.editData.store_employee_role_id = 1;
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        if(method=='POST'){
                            location.href = '{{ route("store.employee.list") }}';
                        }
                        vc.get();
                        sNotify(data.message);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if(typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            selectImage(e){
                $('.croper-box').hide();
                $('#crop_type').val('');
                const files = e.target.files;
                const id = e.target.id;
                if(files.length){
                    var file = files[0];
                    var sizem = file.size / 1024;
                    if(sizem > 2000){
                        sNotify('{{ __("default.檔案不可超過 :size MB", ["size" => 2]) }}', 'danger');
                        e.target.value = null;
                        return false;
                    }
                    if(file){
                        let reader = new FileReader();
                        reader.onload = (e)=>{
                            let dataURL = reader.result;
                            $('#preview_image').attr('src',dataURL);
                            $('#crop_type').val(id);
                            $('#cropModal').modal('show');
                        }
                        reader.readAsDataURL(file);
                    }
                }
            },
            cropModalShow(){
                const image = document.getElementById('preview_image');
                var aspectRatio = 1;
                if(typeof $('#'+$('#crop_type').val()).data('ratio') != 'undefined'){
                    aspectRatio = $('#'+$('#crop_type').val()).data('ratio');
                }
                if(typeof $('#'+$('#crop_type').val()).data('ratio-text') != 'undefined'){
                    $('#ratio_text').html($('#'+$('#crop_type').val()).data('ratio-text'));
                }
                cropper = new Cropper(image, {
                    aspectRatio: aspectRatio,
                    autoCropArea:0.9,
                    viewMode:0,
                })
                $('.croper-box').show();
            },
            cropModalHide(){
                cropper.destroy();
                cropper = null;
            },
            cropImage(){
                const buttonElement = event.target;
                buttonElement.disabled = true;

                if (cropper) {
                    canvas = cropper.getCroppedCanvas();
                    var src = canvas.toDataURL();

                    if($('#crop_type').val()=='photo_image'){
                        vc.editData.photo_url = src;
                        vc.editData.new_photo = src;
                    }
                    else if($('#crop_type').val()=='sid_image'){
                        vc.editData.sid_image_url = src;
                        vc.editData.new_sid_image = src;
                    }
                }
                $('#cropModal').modal('hide');
                buttonElement.disabled = false;
            },
            init(){
                let url = "{{ config('services.API_URL').'/store_employee_role' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'per_page' : 0,
                        'store_id' : '{{ $store->id }}',
                        'to_key_value' : 'id-name'
                    },
                    dataType: 'json',
                    success(data){
                        vc.store_employee_roles = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });

                url = "{{ config('services.API_URL').'/bank_list' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'per_page' : 0,
                        'is_active' : 1,
                    },
                    dataType: 'json',
                    success(data){
                        vc.bank_lists = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
        },
        created: function(){
            if(this.id == 0){
                this.getStoreEmployeeCount();
            }
            this.init();
            this.get();

        },
        mounted: function(){
            $('.maptitle').html(this.words.type);
            $('.map2name').html(this.words.type);

            $('#cropModal').on('shown.bs.modal', this.cropModalShow);
            $('#cropModal').on('hidden.bs.modal', this.cropModalHide);
        },
        beforeDestroy() {
            $('#cropModal').off('shown.bs.modal', this.cropModalShow);
            $('#cropModal').off('hidden.bs.modal', this.cropModalHide);
        },
    });
</script>
@stop