@extends('admin.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @include('admin.common.map')
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form ref="myForm" autocomplete="off">
                        <div class="form-row">
                            <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('確認儲存') }}</button>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('帳號') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.account" ref="account" placeholder="{{ __('帳號') }}" :readonly="id != 0">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('新密碼') }}<span class="red" v-if="id==0">*</span></label>
                                <input type="password" class="form-control" v-model="editData.password" ref="password" placeholder="{{ __('新密碼') }}" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('姓名') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.name" ref="name" placeholder="{{ __('姓名') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('身分權限') }}<span class="red">*</span></label>
                                <select v-model="editData.admin_role_id" id="admin_role_id" class="form-control select2-single" required>
                                    <option value="0">{{ __('請選擇') }}</option>
                                    <option v-for="(role_name, id) in admin_roles" :value="id">@{{ role_name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('性別') }}<span class="red">*</span></label>
                                <select v-model="editData.sex" class="form-control">
                                    <option value="0">{{ __('女') }}</option>
                                    <option value="1">{{ __('男') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('生日') }}</label>
                                <input type="date" class="form-control" id="birthday" v-model="editData.birthday" placeholder="{{ __('生日') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('聯絡電話') }}</label>
                                <input type="text" class="form-control" v-model="editData.cellphone" placeholder="{{ __('聯絡電話') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('Email') }}</label>
                                <input type="text" class="form-control" v-model="editData.email" placeholder="{{ __('Email') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>{{ __('聯絡地址') }}</label>
                                <input type="text" class="form-control" v-model="editData.address" placeholder="{{ __('聯絡地址') }}">
                            </div>
                        </div>
                        <div class="form-row mt-4">
                            <button type="button" class="btn btn-primary d-block col-12" @click="checkSave">{{ __('確認儲存') }}</button>
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
            admin_roles     : [],
            words           : {
                "type" : "{{ $id?__('員工編輯'):__('員工新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/admin' }}/"+this.id;
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
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/admin' }}";

                if(vc.id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/admin' }}/"+this.id;
                }
                vc.editData.updated_name = "{{ auth()->user()->name }}";
                vc.editData.admin_role_id = $('#admin_role_id').val();

                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        if(method=='POST'){
                            location.href = '{{ route("admin.admin.list") }}';
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
            init(){
                let url = "{{ config('services.API_URL').'/admin_role' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'per_page': 0,
                        'is_active' : 1,
                        'to_key_value': 'id-name'
                    },
                    dataType: 'json',
                    success(data) {
                        console.log(data);
                        vc.admin_roles = data.data;
                    },
                    error(xhr, ajaxOptions, thrownError) {
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
        },
        created: function(){
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