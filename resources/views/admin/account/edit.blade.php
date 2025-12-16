@extends('admin.common.master')
@section('content')
<div class="container-fluid" id="vc">
    <div class="row">
        @section('top_right')
        @stop
        @include('admin.common.map')
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form ref="myForm" autocomplete="off">
                        <div class="form-row">
                            <!-- <button type="button" class="btn btn-primary d-block ml-auto" @click="checkSave">{{ __('default.確認儲存') }}</button> -->
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.帳號') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.account" ref="account" placeholder="{{ __('default.帳號') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.新密碼') }}<span class="red" v-if="admin_id==0">*</span></label>
                                <input type="password" class="form-control" v-model="editData.password" ref="password" placeholder="{{ __('default.新密碼') }}" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.姓名') }}<span class="red">*</span></label>
                                <input type="text" class="form-control" v-model="editData.name" ref="name" placeholder="{{ __('default.姓名') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.性別') }}<span class="red">*</span></label>
                                <select v-model="editData.gender" class="form-control">
                                    <option value="0">{{ __('default.女') }}</option>
                                    <option value="1">{{ __('default.男') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.聯絡電話') }}</label>
                                <input type="number" class="form-control" v-model="editData.cellphone" @keydown="validateKey(event)" placeholder="{{ __('default.聯絡電話') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.身分權限') }}<span class="red">*</span></label>
                                <select v-model="editData.admin_role_id" id="admin_role_id" class="form-control select2-single">
                                    <option value="0">{{ __('default.請選擇') }}</option>
                                    <option v-for="(role_name, id) in admin_roles" :value="id">@{{ role_name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ __('default.Email') }}</label>
                                <input type="text" class="form-control" v-model="editData.email" placeholder="{{ __('default.Email') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ __('default.聯絡地址') }}</label>
                                <input type="text" class="form-control" v-model="editData.address" placeholder="{{ __('default.聯絡地址') }}">
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
<script type="text/javascript">
    $(function(){
        $('#admin_role_id').on('change', function(){
            vc.editData.admin_role_id = $(this).val();
        })
    })
</script>
<script type="text/javascript">
    var vc = new Vue({
        el:'#vc',
        components: {
        },
        data:{
            admin_id : '{{ $id?:0 }}',
            editData        : {},
            admin_roles : [],
            words           : {
                "type" : "{{ $id?__('員工編輯'):__('員工新增') }}",
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/admin' }}/"+this.admin_id;
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;

                        vc.init();
console.log(vc.editData);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            checkSave(){
                let method = "POST";
                let url = "{{ config('services.API_URL').'/admin' }}";

                if(vc.admin_id >= 1){
                    method = "PUT";
                    url = "{{ config('services.API_URL').'/admin' }}/"+this.admin_id;
                }
                vc.editData.updated_name = "{{ auth()->guard('admin')->user()->name }}";
console.log($('#admin_role_id').val());
                vc.editData.admin_role_id = $('#admin_role_id').val();
                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        if(method=='POST'){
                            location.href = '{{ route("admin.account.admin.edit") }}/'+data.data.id;
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
                vc = this;
                let url = "{{ config('services.API_URL').'/admin_role' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        'per_page' : 0,
                        'is_active': 1,
                        'to_key_value' : 'id-name'
                    },
                    dataType: 'json',
                    success(data){
                        vc.admin_roles = data.data;
console.log(vc.admin_roles);
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                    },
                });
            },
            validateKey(event){
                const allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete', 'Enter', 'Home', 'End'];
                const isControlKey = event.ctrlKey;
                // 允许 Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z 以及其他控制键
                const controlKeys = ['a', 'c', 'v', 'x', 'z'];
                const controlKeyAllowed = isControlKey && controlKeys.includes(event.key.toLowerCase());
                if (!allowedKeys.includes(event.key) && !controlKeyAllowed) {
                    event.preventDefault();
                }
            },
        },
        created: function(){
        },
        mounted: function(){
            this.get();

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