@extends('admin.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('admin.common.map')
    </div>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs separator-tabs ml-0 mb-5" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="loing-msg-tab" data-toggle="tab" href="#loing-msg" role="tab" aria-controls="loing-msg" aria-selected="true">
                        {{ __('登入頁公告訊息') }}
                    </a>
                </li>
              <!--   <li class="nav-item">
                    <a class="nav-link" id="booking-tab" data-toggle="tab" href="#booking" role="tab" aria-controls="booking" aria-selected="true">
                        {{ __('default.首頁預約介紹') }}
                    </a>
                </li> -->
            </ul>
            <div class="tab-content">
                <div class="tab-pane show active" id="loing-msg" role="tabpanel" aria-labelledby="loing-msg-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="form-row">
                                        <button type="button" class="btn btn-primary d-block ml-auto" @click="checkLoginMsgSave">{{ __('default.確認儲存') }}</button>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>{{ __('登入頁公告訊息') }}</label>
                                            <textarea id="value" class="form-control" rows="8" v-model="login_msg_board.value"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-row mt-4">
                                        <button type="button" class="btn btn-primary d-block col-12" @click="checkLoginMsgSave">{{ __('default.確認儲存') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropper/3.1.3/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/3.1.3/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/0.8.0/cropper.min.js"></script>

<script src="/js/vendor/ckeditor5-build-classic/ckeditor.js"></script> -->

<script type="text/javascript">
    // document.addEventListener('DOMContentLoaded', () => {
    //     ClassicEditor
    //         .create(document.querySelector('#introduce'), {
    //             name: 'introduce',
    //             removePlugins: ['ImageUpload']
    //         })
    //         .then(editor => {
    //             window.ckeditor_introduce = editor;
    //         })
    //         .catch(error => {
    //             console.error(error);
    //         });
    // });
</script>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            login_msg_board : {},
            words       : {
            },
        },
        methods: {
            loginMsgGet() {
                let url = "{{ config('services.API_URL').'/setting/byname/login_msg_board' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                    },
                    dataType: 'json',
                    success(data){
                        vc.login_msg_board = data.data;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
            checkLoginMsgSave(){
                let method = "PUT";
                let url = "{{ config('services.API_URL').'/setting' }}/"+vc.login_msg_board.id;
                vc.login_msg_board.updated_name = "{{ auth()->guard('admin')->user()->name }}";
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.login_msg_board,
                    dataType: 'json',
                    success(data){
                        vc.loginMsgGet();
                        sNotify("儲存成功");
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
            this.loginMsgGet();
        },
        mounted: function(){
            var self = this;
        },
        beforeDestroy() {
        },
    });
</script>
@stop