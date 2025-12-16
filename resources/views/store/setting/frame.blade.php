@extends('store.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('store.common.map')
    </div>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs separator-tabs ml-0 mb-5" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="worktime-tab" data-toggle="tab" href="#worktime" role="tab" aria-controls="worktime" aria-selected="true">
                        {{ __('default.店家營業時間') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="booking-tab" data-toggle="tab" href="#booking" role="tab" aria-controls="booking" aria-selected="true">
                        {{ __('default.首頁預約介紹') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="banner-tab" data-toggle="tab" href="#banner" role="tab" aria-controls="banner" aria-selected="true">
                        {{ __('default.首頁輪播圖') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="booking_bg-tab" data-toggle="tab" href="#booking_bg" role="tab" aria-controls="booking_bg" aria-selected="true">
                        {{ __('default.預約背景圖') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane show active" id="worktime" role="tabpanel" aria-labelledby="worktime-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form>
                                        <div class="form-row mb-4">
                                            <button type="button" class="btn btn-primary mr-2 d-block" data-toggle="modal" data-target="#speedCopyModal">{{ __('default.快速設定') }}</button>
                                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                                        </div>
                                        <h5>{{ __('default.店家營業時間') }}</h5>
                                        <div class="form-row mb-2" v-for="(open, k) in editData.openings">
                                            <input type="hidden" v-model="open.week">
                                            <div class="col-md-1 col-xs-5 pt-2">
                                                <div class="custom-switch custom-switch-secondary mb-2">
                                                    <input class="custom-switch-input" :id="k" v-model="open.is_active" type="checkbox" true-value="1" false-value="0">
                                                    <label class="custom-switch-btn" :for="k"></label>
                                                </div>
                                            </div>
                                            <div class="col-md-1 col-xs-5"><label class="col-form-label">@{{ words[k] }}</label></div>
                                            <div class="col-4">
                                                <select class="form-control" v-model="open.start" @change="checkOpen('start')">
                                                    <option value="">{{ __('default.請選擇') }}</option>
                                                    <option v-for="(time, i) in times" v-if="open.end=='' || time < open.end" :value="time">@{{ time }}</option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-control" v-model="open.end" @change="checkOpen('end')">
                                                    <option value="">{{ __('default.請選擇') }}</option>
                                                    <option v-for="(time, i) in times" v-if="open.start=='' || time > open.start" :value="time">@{{ time }}</option>
                                                </select>
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
                    <div class="modal fade" id="speedCopyModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('default.快速設定') }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <select class="form-control" v-model="speed.start" @change="checkSpeedOpen('start')">
                                                <option value="">{{ __('default.請選擇') }}</option>
                                                <option v-for="(time, i) in times" v-if="speed.end=='' || time < speed.end" :value="time">@{{ time }}</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select class="form-control" v-model="speed.end" @change="checkSpeedOpen('end')">
                                                <option value="">{{ __('default.請選擇') }}</option>
                                                <option v-for="(time, i) in times" v-if="speed.start=='' || time > speed.start" :value="time">@{{ time }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div v-if="speed.warning" class="red">{{ __('default.結束時間必須大於開始時間') }}</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary col-12" @click="openCopy">{{ __('default.確認') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="booking" role="tabpanel" aria-labelledby="booking-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form>
                                        <div class="form-row">
                                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>{{ __('default.公司網址') }}</label>
                                                <input type="text" id="company_url" class="form-control" v-model="editData.company_url">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>{{ __('default.IG') }}</label>
                                                <input type="text" id="ig_url" class="form-control" v-model="editData.ig_url">
                                            </div>
                                            <div class="form-group col-md-12">
                                                <label>{{ __('default.首頁預約介紹') }}</label>
                                                <textarea id="introduce" class="form-control" rows="4" v-model="editData.introduce"></textarea>
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
                <div class="tab-pane" id="banner" role="tabpanel" aria-labelledby="banner-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <!-- <h5 class="mb-4">Dropzone</h5> -->
                                    <form>

                                        <label class="btn btn-primary btn-upload" v-if="listBanner.length < 5">
                                            <input type="file" id="banner_image" class="sr-only" @change="selectImage" data-ratio="1.5" data-ratio-text="{{ __('default.建議尺寸') }}(900*600)" name="file" accept=".jpg,.jpeg,.png">
                                            {{ __('default.新增圖片') }}
                                        </label>

                                        <!-- <button type="button" class="btn btn-primary mb-3 ml-2 d-block float-right" @click="checkSaveStoreFile('banner')">{{ __('default.確認儲存') }}</button> -->
                                        <button type="button" class="btn btn-primary mb-3 ml-2 d-block float-right" data-toggle="modal" data-target="#sortModal">{{ __('default.排序') }}</button>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">{{ __('default.圖片') }} {{ __('default.建議尺寸') }}(900*600)</th>
                                                        <th scope="col">{{ __('default.開始時間') }}</th>
                                                        <th scope="col">{{ __('default.結束時間') }}</th>
                                                        <th scope="col"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(list,i) in listBanner">
                                                        <td scope="row">@{{ i+1 }}</td>
                                                        <td class="col-3" scope="row"><img class="img-fluid td-img" :src="list.path_url" @click="showOriginalImage(list.path_url)"></td>
                                                        <td scope="row" class="align-middle">
                                                            <div class="d-flex align-items-center">
                                                                <input type="date" class="form-control" v-model="list.start_date">
                                                            </div>
                                                        </td>
                                                        <td scope="row" class="align-middle">
                                                            <div class="d-flex align-items-center">
                                                                <input type="date" class="form-control" v-model="list.end_date">
                                                            </div>
                                                        </td>
                                                        <td scope="row" class="align-middle text-right">
                                                            <a href="#" v-if="list.id" @click.prevent="storeFileDelete(list.id)" class="btn btn-outline-danger mr-2 mb-2">{{ __('default.刪除') }}</a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- <button type="button" class="btn btn-primary d-block mt-3 col-12" @click="checkSaveStoreFile('banner')">{{ __('default.確認儲存') }}</button> -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="booking_bg" role="tabpanel" aria-labelledby="booking_bg-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <!-- <h5 class="mb-4">Dropzone</h5> -->
                                    <form>
                                        <label class="btn btn-primary btn-upload" v-if="bookingBg.length < 1">
                                            <input type="file" id="booking_bg_image" class="sr-only" @change="selectImage" data-ratio="1.5" data-ratio-text="{{ __('default.建議尺寸') }}(900*600)" name="file" accept=".jpg,.jpeg,.png">
                                            {{ __('default.新增圖片') }}
                                        </label>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">{{ __('default.圖片') }}{{ __('default.建議尺寸') }}(900*600)</th>
                                                        <th scope="col"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(list,i) in bookingBg">
                                                        <td scope="row">@{{ i+1 }}</td>
                                                        <td class="col-3" scope="row"><img class="img-fluid" :src="list.path_url" @click="showOriginalImage(list.path_url)"></td>
                                                        <td scope="row" class="align-middle text-right">
                                                            <a href="#" v-if="list.id" @click.prevent="storeFileDelete(list.id)" class="btn btn-outline-danger mr-2 mb-2">{{ __('default.刪除') }}</a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('default.圖片裁切') }}  <span id="ratio_text"></span></h5>
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
                                <button type="button" class="btn btn-primary" @click="cropImage()">{{ __('default.確認') }}</button>
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
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('default.關閉') }}</button>
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
                                                        <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="obj.path_url">
                                                    </span>
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary col-12" @click="checkBatchSaveStoreFile()">{{ __('default.儲存') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropper/3.1.3/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/3.1.3/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/0.8.0/cropper.min.js"></script>

<script src="/js/vendor/ckeditor5-build-classic/ckeditor.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        ClassicEditor
            .create(document.querySelector('#introduce'), {
                name: 'introduce',
                removePlugins: ['ImageUpload']
            })
            .then(editor => {
                window.ckeditor_introduce = editor;
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
            listBanner      : [],
            bookingBg       : [],
            originalImagePath : '',
            sorts           : [],
            speed           : {start:'', end:'', warning:false},
            times           : [],
            words       : {
                "week1" : "{{ __('default.星期一') }}",
                "week2" : "{{ __('default.星期二') }}",
                "week3" : "{{ __('default.星期三') }}",
                "week4" : "{{ __('default.星期四') }}",
                "week5" : "{{ __('default.星期五') }}",
                "week6" : "{{ __('default.星期六') }}",
                "week7" : "{{ __('default.星期日') }}",
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/store' }}/{{ $store->encode_id }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                    },
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
            getBanner() {
                let url = "{{ config('services.API_URL').'/store_file' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        store_id    : "{{ $store->id }}",
                        type        : "banner",
                        sort        : 'sort',
                    },
                    dataType: 'json',
                    success(data){
                        vc.listBanner = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
            getBookingBg() {
                let url = "{{ config('services.API_URL').'/store_file' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        store_id    : "{{ $store->id }}",
                        type        : "booking_bg",
                    },
                    dataType: 'json',
                    success(data){
                        vc.bookingBg = data.data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
            checkOpen(type){
                $.each(vc.editData.openings ,function(k, open){
                    if(open.start != '' && open.end != '' && open.start > open.end){
                        if(type=='start')
                            open.start = '';
                        else
                            open.end = '';
                        sNotify('{{ __('default.結束時間必須大於開始時間') }}', 'danger');
                    }
                })
            },
            checkSpeedOpen(type){
                vc.speed.warning = false;
                if(vc.speed.start != '' && vc.speed.end != '' && vc.speed.start > vc.speed.end){
                    if(type=='start')
                        vc.speed.start = '';
                    else
                        vc.speed.end = '';
                    vc.speed.warning = true;
                }
            },
            openCopy(){
                $.each(vc.editData.openings ,function(k, open){
                    open.start = vc.speed.start;
                    open.end = vc.speed.end;
                })
                $('#speedCopyModal').modal('hide')
            },
            storeFileDelete(id) {
                Swal.fire({
                    title: '{{ __("default.確認刪除") }}?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "#008ecc",
                    cancelButtonText: '{{ __("default.取消") }}',
                    confirmButtonText: '{{ __("default.刪除") }}'
                }).then((result) => {
                    if(result.isConfirmed){
                        let url = "{{ config('services.API_URL').'/store_file' }}/"+id;
                        $.ajax({
                            method: "DELETE",
                            url: url,
                            data: {},
                            dataType: 'json',
                            success(data){
                                vc.getBanner();
                                vc.getBookingBg();
                                sNotify(data.message);
                            },
                            error:function(xhr, ajaxOptions, thrownError){
                                console.log(xhr);
                                sNotify(xhr.responseJSON.message, 'danger');
                            },
                        });
                    }
                })
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
                var aspectRatio = 3/2;
                if(typeof $('#'+$('#crop_type').val()).data('ratio') != 'undefined'){
                    aspectRatio = $('#'+$('#crop_type').val()).data('ratio');
                }
                if(typeof $('#'+$('#crop_type').val()).data('ratio-text') != 'undefined'){
                    $('#ratio_text').html($('#'+$('#crop_type').val()).data('ratio-text'));
                }
                cropper = new Cropper(image, {
                    aspectRatio: aspectRatio,
                    autoCropArea:1,
                    viewMode:0,
                })
                $('.croper-box').show();
            },
            cropModalHide(){
                $('#banner_image').val('');
                $('#booking_bg_image').val('');
                cropper.destroy();
                cropper = null;
            },
            cropImage(){
                const buttonElement = event.target;
                buttonElement.disabled = true;

                if (cropper) {
                    canvas = cropper.getCroppedCanvas();
                    var src = canvas.toDataURL();

                    if($('#crop_type').val()=='banner_image'){
                        vc.listBanner.push({
                            id : 0,
                            file_path : src,
                            path_url : src,
                            start_date : null,
                            end_date : null,
                        });
                        vc.checkSaveStoreFile('banner')
                    }
                    else if($('#crop_type').val()=='booking_bg_image'){
                        vc.bookingBg.push({
                            id : 0,
                            file_path : src,
                            path_url : src,
                            start_date : null,
                            end_date : null,
                        });
                        vc.checkSaveStoreFile('booking_bg')
                    }
                }
                $('#cropModal').modal('hide');
                buttonElement.disabled = false;
            },
            showOriginalImage(path){
                this.originalImagePath = path;
                $('#originalImageModal').modal('show');
            },
            checkSave(){
                let method = "PUT";
                let url = "{{ config('services.API_URL').'/store' }}/{{ $store->encode_id }}";
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";
                vc.editData.introduce = window.ckeditor_introduce.getData();
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
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
            checkSaveStoreFile(type){
                const formData = new FormData();

                var upArray = [];
                if(type=='banner'){
                    upArray = vc.listBanner;
                }
                else if(type=='booking_bg'){
                    upArray = vc.bookingBg;
                }

                $.each(upArray, function(i,v){
                    if(v.id == 0){
                        formData.append('new_image'+i, v.file_path);
                    }
                    formData.append('id'+i, v.id);
                    formData.append('store_id'+i, '{{ $store->id }}');
                    formData.append('type'+i, type);

                    formData.append('start_date'+i, v.start_date);
                    formData.append('end_date'+i, v.end_date);

                    formData.append('updated_name'+i, "{{ auth()->guard('store')->user()->name }}");
                })

                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_file/batch' }}";
                $.ajax({
                    method: method,
                    url: url,
                    processData: false,
                    contentType: false,
                    data: formData,
                    dataType: 'json',
                    success(data){
                        vc.getBanner();
                        vc.getBookingBg();
                        sNotify(data.message);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if(typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            sortModalShow(){
                this.sorts = JSON.parse(JSON.stringify(this.listBanner));
                $('ol.vertical').each(function(){
                    Sortable.create($(this)[0], {animation: 50});
                })
            },
            checkBatchSaveStoreFile(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/store_file/update_sort' }}";

                $.ajax({
                    method: method,
                    url: url,
                    data: {
                        sorts : this.sorts,
                        updated_name : "{{ auth()->guard('store')->user()->name }}"
                    },
                    dataType: 'json',
                    success(data){
                        vc.getBanner();
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
        created: function(){
            this.get();
            this.getBanner();
            this.getBookingBg();
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

            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 15) {
                    const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                    self.times.push(time);
                }
            }

            $('#cropModal').on('shown.bs.modal', this.cropModalShow);
            $('#cropModal').on('hidden.bs.modal', this.cropModalHide);

            $('#sortModal').on('shown.bs.modal', this.sortModalShow);
        },
        beforeDestroy() {
            $('#cropModal').off('shown.bs.modal', this.cropModalShow);
            $('#cropModal').off('hidden.bs.modal', this.cropModalHide);
        },
    });
</script>
@stop