@extends('admin.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        <!-- <div class="form-group col-md-3">
            <button class="btn btn-primary mt-4" @click="delete_g8pad()">{{ __('刪除兩台平板帳號所有地圖') }}</button>
        </div> -->
        <div class="form-group col-md-3">
            <button class="btn btn-primary mt-4" @click="change_train1()">{{ __('外平板name5帳號刷新教學') }}</button>
        </div>
        <div class="form-group col-md-3">
            <button class="btn btn-primary mt-4" @click="change_train2()">{{ __('內平板我是誰帳號刷新教學') }}</button>
        </div>
        <div class="form-group col-md-3">
            <button class="btn btn-primary mt-4" @click="change_train3()">{{ __('重置GDData & surgame資料') }}</button>
        </div>
        <div class="form-group col-md-3">
            <button class="btn btn-danger mt-4" @click="resetSurgameData()">{!! __('重置 Surgame 資料') !!}</button>
        </div>
    </div>

</div>

<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        data:{
            lists           : [],
            pageData        : false,
        },
        methods: {
            get(type = false, sort = false) {

            },
            delete_g8pad(){
                if(confirm("確認刪除?")){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/user/delete_maps/94' }}"
                    $.ajax({
                        method: "POST",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                    url = "{{ config('services.API_URL').'/user/delete_maps/85' }}"
                    $.ajax({
                        method: "POST",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                    url = "{{ config('services.API_URL').'/user/delete_maps/98' }}"
                    $.ajax({
                        method: "POST",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                }
            },
            change_g8pad(){
                if(confirm("確認更新?")){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/user/change_g8pad' }}"
                    $.ajax({
                        method: "POST",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                }
            },
            change_train1(){
                if(confirm("確認更新?")){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/user/128' }}"
                    $.ajax({
                        method: "PUT",
                        url: url,
                        data: {
                            teaching_square:0,
                            teaching_level:0
                        },
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                }
            },
            change_train2(){
                if(confirm("確認更新?")){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/user/135' }}"
                    $.ajax({
                        method: "PUT",
                        url: url,
                        data: {
                            teaching_square:0,
                            teaching_level:0
                        },
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        },
                    });
                }
            },
            change_train3()
            {
                if(confirm("確認更新?")){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/refresh_gddata_items' }}";
                    $.ajax({
                        method: "POST",
                        url: url,
                        data: {},
                        dataType: 'json',
                        success(data){
                            sNotify(data.message);
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                        }
                    });
                }
            },
            resetSurgameData()
            {
                let uid = prompt("請輸入玩家 UID:");
                if(!uid || uid.trim() === ''){
                    sNotify('請輸入玩家 UID');
                    return;
                }
                if(confirm("確認重置該玩家 UID: " + uid + " 的所有 Surgame 資料？此操作不可逆！")){
                    vc = this;
                    let url = "{{ config('services.API_URL').'/reset_user_surgame_data' }}";
                    $.ajax({
                        method: "POST",
                        url: url,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: {
                            uid: uid.trim(),
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success(data){
                            if(data.status == 1){
                                sNotify('重置 Surgame 資料成功！');
                            } else {
                                sNotify(data.message || '重置失敗');
                            }
                        },
                        error:function(xhr, ajaxOptions, thrownError){
                            console.log(xhr);
                            sNotify('操作失敗，請查看控制台');
                        }
                    });
                }
            }
        },
        created : function(){
            this.get();
        },
        mounted: function(){

        }
    });
</script>
@stop
