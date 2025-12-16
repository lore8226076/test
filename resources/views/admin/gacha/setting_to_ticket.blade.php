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
                        <template v-if="id>0">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('SSR') }}</label>
                                    <input type="text" class="form-control" v-model="editData.value.SSR" placeholder="{{ __('SSR') }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('SR') }}</label>
                                    <input type="text" class="form-control" v-model="editData.value.SR" placeholder="{{ __('SR') }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('R') }}</label>
                                    <input type="text" class="form-control" v-model="editData.value.R" placeholder="{{ __('R') }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('N') }}</label>
                                    <input type="text" class="form-control" v-model="editData.value.N" placeholder="{{ __('N') }}">
                                </div>
                            </div>
                        </template>
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
            id              : 0,
            editData        : {},
            words           : {
                "data_warning" : '',
            },
        },
        methods: {
            get() {
                let url = "{{ config('services.API_URL').'/setting/byname/avatar_to_ticket' }}";
                $.ajax({
                    method: "GET",
                    url: url,
                    data: {},
                    dataType: 'json',
                    success(data){
                        vc.editData = data.data;
                        vc.id = vc.editData.id;
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                    },
                });
            },
            checkSave(){
                let method = "PUT";
                let url = "{{ config('services.API_URL').'/setting' }}/"+vc.editData.id;

                $.ajax({
                    method: method,
                    url: url,
                    data: vc.editData,
                    dataType: 'json',
                    success(data){
                        vc.get();
                        sNotify("儲存成功");
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log(xhr);
                        sNotify(xhr.responseJSON.message, 'danger');
                        if(typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr.responseJSON.field].focus();
                    },
                });
            },
            init(){
            },
        },
        created: function(){
            this.init();
            this.get();

        },
        mounted: function(){

        },
        beforeDestroy() {
        },
    });
</script>
@stop