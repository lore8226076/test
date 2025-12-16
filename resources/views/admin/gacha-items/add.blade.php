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
                        <form ref="myForm" autocomplete="off" @submit.prevent="checkSave">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('所屬扭蛋') }}<span class="red">*</span></label>
                                    <select class="form-control" v-model="editData.gacha_id" required>
                                        <option :value="null" disabled selected>{{ __('請選擇扭蛋') }}</option>
                                        <option v-for="gacha in gachas" :key="gacha.id" :value="gacha.id">
                                            @{{ gacha.name }}
                                        </option>
                                    </select>
                                </div>

                                {{-- 扭蛋item_id --}}
                                <div class="form-group col-md-6">
                                    <label>{{ __('扭蛋item_id') }}<span class="red">*</span></label>
                                    <input type="number" class="form-control" v-model="editData.item_id"
                                        placeholder="{{ __('扭蛋item_id') }}" min="1" max="9999999" required>
                                </div>

                            </div>
                            <div class="form-row">

                                {{-- 扭蛋中獎機率 --}}
                                <div class="form-group col-md-6">
                                    <label>{{ __('中獎機率') }}</label>
                                    <input type="number" class="form-control" v-model="editData.percent"
                                        placeholder="{{ __('中獎機率') }}" min="0.01" step="0.01">
                                </div>

                                {{-- 是否為保底 --}}
                                <div class="form-group col-md-6">
                                    <label>{{ __('是否為保底') }}</label>
                                    <select class="form-control" v-model="editData.guaranteed" required>
                                        <option :value="1">{{ __('是') }}</option>
                                        <option :value="0">{{ __('否') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mt-4">
                                <button type="submit" class="btn btn-primary col-12">
                                    {{ __('確認新增') }}
                                </button>
                            </div>
                        </form>



                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- 引入axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script type="text/javascript">
        var vc = new Vue({
            el: '#vc',
            data: {
                id: '',
                editData: {
                    gacha_id: null,
                    item_id: null,
                    percent: 0.01,
                    guaranteed: 0,
                },
                gachas: [],
                admin_roles: [],
                words: {
                    "type": "扭蛋獎品新增",
                    "data_warning": '',
                },
            },
            methods: {
                checkSave() {
                    if (this.editData.gacha_id == null) {
                        sNotify("請選擇扭蛋", "danger");
                        return;
                    }
                    if (this.editData.item_id == null) {
                        sNotify("請輸入扭蛋item_id", "danger");
                        return;
                    }

                    if (this.editData.percent == null) {
                        sNotify("請輸入中獎機率", "danger");
                        return;
                    }

                    let method = "POST";
                    let url = "{{ config('services.API_URL') . '/gacha_items' }}";
                    vc.editData.updated_name = "{{ auth()->user()->name }}";

                    $.ajax({
                        method: method,
                        url: url,
                        data: vc.editData,
                        dataType: 'json',
                        success(data) {
                            if (method == 'POST') {
                                location.href = '{{ route('admin.gacha_items.list') }}';
                            }
                            sNotify(data.message);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            console.log(xhr);
                            sNotify(xhr.responseJSON.message, 'danger');
                            if (typeof vc.$refs[xhr.responseJSON.field] != 'undefined') vc.$refs[xhr
                                .responseJSON.field].focus();
                        },
                    });
                },
                // 取得扭蛋列表
                async fetchGachas() {
                    try {
                        let url = "{{ config('services.API_URL') . '/gacha' }}";
                        let response = await axios.get(url);
                        this.gachas = response.data.data.data;
                    } catch (error) {
                        console.error('獲取 Gacha 資料失敗', error);
                    }

                }
            },
            mounted: function() {
                // 取得扭蛋機列表
                this.fetchGachas();
            },

        });
    </script>
@stop
