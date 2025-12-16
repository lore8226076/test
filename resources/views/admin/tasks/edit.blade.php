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
                                <div class="form-group col-md-6">
                                    <label>{{ __('名稱') }}<span class="red">*</span></label>
                                    <input type="text" class="form-control" v-model="editData.name"
                                        placeholder="{{ __('名稱') }}" required maxlength="255">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('本地化名稱') }}</label>
                                    <input type="text" class="form-control" v-model="editData.localization_name"
                                        placeholder="{{ __('本地化名稱') }}" maxlength="255">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('類型') }}<span class="red">*</span></label>
                                <select v-model="editData.is_permanent" class="form-control">
                                    <option :value="1">{{ __('常駐池') }}</option>
                                    <option :value="0">{{ __('非常駐池') }}</option>
                                </select>
                            </div>

                            <!-- 當選擇 "常駐池" (1) 時，顯示開始/結束時間 -->
                            <div class="form-row" v-if="editData.is_permanent == 0">
                                <div class="form-group col-md-6">
                                    <label>{{ __('開始時間') }}<span class="red">*</span></label>
                                    <input type="datetime-local" class="form-control" v-model="editData.start_time"
                                        :required="editData.is_permanent !== 1">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('結束時間') }}<span class="red">*</span></label>
                                    <input type="datetime-local" class="form-control" v-model="editData.end_time"
                                        :required="editData.is_permanent !== 1" :min="editData.start_time">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('貨幣 ID') }}</label>
                                    <input type="number" class="form-control" v-model="editData.currency_item_id"
                                        placeholder="{{ __('貨幣 ID') }}" min="100">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('單次價格') }}</label>
                                    <input type="number" class="form-control" v-model="editData.one_price"
                                        placeholder="{{ __('單次價格') }}" min="1" step="0.01">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('十連價格') }}</label>
                                    <input type="number" class="form-control" v-model="editData.ten_price"
                                        placeholder="{{ __('十連價格') }}" min="10" step="0.01">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('保底次數 (沒有保底請填-1)') }}</label>
                                    <input type="number" class="form-control" v-model="editData.max_times"
                                        placeholder="{{ __('保底次數') }}" min="-1">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('是否啟用') }}<span class="red">*</span></label>
                                    <select class="form-control" v-model="editData.is_active" required>
                                        <option :value="1">{{ __('啟用') }}</option>
                                        <option :value="0">{{ __('停用') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mt-4">
                                <button type="button" class="btn btn-primary col-12" @click="checkSave">
                                    {{ __('確認儲存') }}
                                </button>
                            </div>
                        </form>



                    </div>
                </div>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        var vc = new Vue({
            el: '#vc',
            data: {
                id: '{{ $id ?: 0 }}',
                editData: {
                    is_permanent: 1,
                    start_time: "",
                    end_time: "",
                    is_active: 1,
                    ten_price: 10,
                    one_price: 1,
                    max_times: 1,
                    currency_item_id: 100
                },
                admin_roles: [],
                words: {
                    "type": "{{ $id ? __('扭蛋機編輯') : __('扭蛋機新增') }}",
                    "data_warning": '',
                },
            },
            methods: {
                get() {
                    let apiUrl = "{{ config('services.API_URL') . '/gacha/' }}" + this.id;

                    $.ajax({
                        method: "GET",
                        url: apiUrl,
                        dataType: 'json',
                        success: (data) => {
                            this.editData = Object.assign({}, this.editData, data.data);

                            if (!this.editData.start_time && !this.editData.end_time) {
                                this.editData.is_permanent = 1;
                            } else {
                                this.editData.is_permanent = 0;
                            }

                        },
                        error: (xhr) => {
                            console.log(xhr);
                        },
                    });
                },
                checkSave() {
                    if (this.editData.is_permanent !== 1) {
                        if (!this.editData.start_time || !this.editData.end_time) {
                            sNotify("請選擇開始時間和結束時間", "danger");
                            return;
                        }
                    }

                    let method = "POST";
                    let url = "{{ config('services.API_URL') . '/gacha' }}";

                    if (vc.id >= 1) {
                        method = "PUT";
                        url = "{{ config('services.API_URL') . '/gacha' }}/" + this.id;
                    }
                    vc.editData.updated_name = "{{ auth()->user()->name }}";

                    $.ajax({
                        method: method,
                        url: url,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: vc.editData,
                        dataType: 'json',
                        success(data) {
                            if (method == 'POST') {
                                location.href = '{{ route('admin.gacha.list') }}';
                            }
                            vc.get();
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
                init() {}
            },

            created: function() {
                this.init();
                this.get();

            },
            mounted: function() {
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
