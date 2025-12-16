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
                            <div class="form-group col-md-6">
                                <label>{{ __('default.公司名稱') }}</label>
                                <input type="text" class="form-control" v-model="editData.name" placeholder="{{ __('default.公司名稱') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.公司電話') }}</label>
                                <input type="text" class="form-control" v-model="editData.tel" placeholder="{{ __('default.店家電話') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.Email') }}</label>
                                <input type="text" class="form-control" v-model="editData.email" placeholder="{{ __('default.Email') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.公司地址') }}</label>
                                <input type="text" class="form-control" v-model="editData.address" placeholder="{{ __('default.店家地址') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.LOGO') }}(300*93)</label>
                                <label class="badge badge-pill badge-primary mr-2 mb-2 btn-upload">
                                    <input type="file" id="logo_image" class="sr-only" @change="selectImage" data-ratio="3.23" data-ratio-text="{{ __('default.建議尺寸') }}300*93" name="file" accept=".jpg,.jpeg,.png">
                                    {{ __('default.上傳圖片') }}
                                </label>
                                <img class="img-thumbnail border-0 list-thumbnail align-self-center" :src="editData.logo_url">
                            </div>
                        </div>
                        <h5>{{ __('default.辦公時間') }}</h5>
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
            editData        : {},
            speed           : {start:'', end:'', warning:false},
            times           : [],
            words       : {
                "data_warning" : '',
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
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    },
                });
            },
            checkSave(){
                let method = "PUT";
                let url = "{{ config('services.API_URL').'/store' }}/{{ $store->encode_id }}";
                vc.editData.updated_name = "{{ auth()->guard('store')->user()->name }}";

                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        $('#logo_image').val('');
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
                    outputType: 'png',
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

                    if($('#crop_type').val()=='logo_image'){
                        vc.editData.logo_url = src;
                        vc.editData.new_logo = src;

                        vc.checkSave();
                    }
                }
                $('#cropModal').modal('hide');
                buttonElement.disabled = false;
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
            openCopy(){
                $.each(vc.editData.openings ,function(k, open){
                    open.start = vc.speed.start;
                    open.end = vc.speed.end;
                })
                $('#speedCopyModal').modal('hide')
            },
        },
        created: function(){
            this.get();

            var self = this;
            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 30) {
                    const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                    self.times.push(time);
                }
            }
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