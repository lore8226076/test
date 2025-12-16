@extends('admin.common.master')
@section('content')
    <div class="container-fluid" id="vc">
        <div class="row">
            @include('admin.common.map')
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-3">
                                <label>{{ __('扭蛋機名稱') }}</label>
                                <input type="text" class="form-control" v-model="searchData.name" @keydown.enter="get()"
                                    placeholder="{{ __('扭蛋機名稱') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <button class="btn btn-primary" @click="get()">{{ __('default.搜尋') }}</button>
                                <button class="btn btn-success" @click="openCreateModal()">
                                    {{ __('default.新增') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        {{-- <div class="row justify-content-end mb-2">
                            <a :href="goEdit()" class="btn btn-primary ml-2 d-block">{{ __('更新價格') }}</a>
                        </div> --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ __('扭蛋機 ID') }}</th>
                                        <th scope="col">{{ __('扭蛋機名稱') }}</th>
                                        <th scope="col">{{ __('扭蛋機活動時間') }}</th>
                                        <th scope="col">{{ __('是否啟用') }}</th>
                                        <th scope="col">{{ __('其他功能') }}</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(list,i) in lists">
                                        <td scope="row">@{{ i + 1 }}</td>
                                        <td scope="row">@{{ 10000 + list.id }}</td>
                                        <td scope="row">@{{ list.name }}</td>
                                        <td scope="row">@{{ list.start_time ? formatDate(list.start_time) + ' ~ ' + formatDate(list.end_time) : '常駐' }}</td>
                                        <td scope="row">
                                            <label class="switch">
                                                <input type="checkbox" :checked="list.is_active"
                                                    @change="toggleStatus(list)">
                                                <span class="slider"></span>
                                            </label>
                                        </td>
                                        <td scope="row" class="text-left">
                                            <a :href="goEdit(list.id)"
                                                class="btn btn-primary btn-xs mr-2 mb-2">{{ __('編輯') }}</a>
                                            <a href="#" @click.prevent.stop="showGiveItem(list)"
                                                class="btn btn-primary btn-xs mr-2 mb-2">{{ __('新增道具') }}</a>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @include('store.common.api_page')
                    </div>
                </div>
            </div>
        </div>

        {{-- 新增道具 - 扭蛋機資訊 --}}
        <div class="modal fade modal-bottom" id="giveItemModal" tabindex="-1" role="dialog"
            aria-labelledby="giveItemModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('新增道具') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div><b>轉蛋機名稱：</b>@{{ giveGacha.name }}</div>
                            </div>
                            <div class="col-md-4">
                                <div><b>類型：</b>@{{ giveGacha.status_text }}</div>
                            </div>
                            <div class="col-md-4" v-show="!giveGacha.is_permanent">
                                <div><b>開放時間：</b>@{{ giveGacha.start_time }} ~ @{{ giveGacha.end_time }}</div>
                            </div>

                            <div class="col-md-4">
                                <div><b>使用貨幣ID：</b>@{{ giveGacha.currency_item_id }}</div>
                            </div>
                            <div class="col-md-4">
                                <div><b>單次價格：</b>@{{ giveGacha.one_price }}</div>
                            </div>
                            <div class="col-md-4">
                                <div><b>十連價格：</b>@{{ giveGacha.ten_price }}</div>
                            </div>

                            <div class="col-md-4">
                                <div><b>保底次數：</b>@{{ giveGacha.max_times }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <b>當前總機率：</b>
                                @{{ giveGacha.total_percent }} %
                            </div>
                        </div>
                        <hr>
                        <div class="row d-flex justify-content-between">
                            <div class="col-3">
                                <label>Item ID</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" v-model="giveData.item_id"
                                        placeholder="輸入item_id">
                                    <button class="btn btn-primary" type="button" :disabled="findItemBtnDisabled"
                                        @click="getItem()">查找</button>
                                    <button type="button" class="btn btn-primary" @click="triggerAndImport(giveGacha)">
                                        匯入檔案
                                    </button>

                                    <input type="file" ref="fileInput" class="form-control" hidden
                                        @change="handleFileChange" />
                                </div>

                            </div>
                            <div class="col-3">
                                <label>範例檔案</label>
                                <div class="input-group">
                                    <!-- 範例檔案 -->
                                    <a href="{{ asset('storage/gacha/gacha_items.xlsx') }}" class="btn btn-primary"
                                        target="_blank" download>
                                        檔案下載
                                    </a>
                                </div>
                            </div>

                        </div>

                        {{-- 設定扭蛋資訊 --}}
                        <template v-if="itemData">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="mt-2">Item ID</label>
                                    <input type="text" class="form-control" v-model="itemData.item_id" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="mt-2">中獎機率 (%)</label>
                                    <input type="number" class="form-control" v-model="itemData.percent" min="0.01"
                                        step="0.01" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="mt-2">稀有度</label>
                                    <input type="text" class="form-control" v-model="itemData.rarity" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="mt-2">是否為大獎</label>
                                    <select class="form-control" v-model="itemData.guaranteed">
                                        <option disabled value="">請選擇</option>
                                        <option :value="1">是</option>
                                        <option :value="0">否</option>
                                    </select>
                                </div>

                                <!-- 道具數量 -->
                                <div class="col-md-4">
                                    <label class="mt-2">道具數量</label>
                                    <input type="number" class="form-control" v-model="itemData.qty" min="1" step="1">
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary w-100 mt-2" type="button"
                                        @click="giveItem()">確認新增</button>
                                </div>
                            </div>
                            <hr>
                        </template>

                        {{-- 扭蛋列表 --}}
                        <template v-if="gachaDetailItem && gachaDetailItem.length">
                            <table class="table table-bordered table-hover table-sm mt-1">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item ID</th>
                                        <th>機率 (%)</th>
                                        <th>稀有度</th>
                                        <th>是否為大獎</th>
                                        <th>道具數量</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in gachaDetailItem" :key="index">
                                        <td>@{{ index + 1 }}</td>
                                        <td>@{{ item.item_id }}</td>

                                        <!-- 機率 -->
                                        <td v-if="editIndex === index">
                                            <input type="number" class="form-control" v-model="editItem.percent"
                                                min="0.01" step="0.01">
                                        </td>
                                        <td v-else>@{{ formatPercent(item.percent) }}%</td>

                                        <!-- 稀有度 -->
                                        <td>@{{ item.item_detail?.rarity || item.rarity || 'N/A' }}</td>

                                        <!-- 是否大獎 -->
                                        <td v-if="editIndex === index">
                                            <select class="form-control" v-model="editItem.guaranteed">
                                                <option :value="1">是</option>
                                                <option :value="0">否</option>
                                            </select>
                                        </td>
                                        <td v-else>@{{ item.guaranteed == 1 ? '是' : '否' }}</td>

                                        <!-- 道具數量 -->
                                         <td v-if="editIndex === index">
                                            <input type="number"  class="form-control" v-model="editItem.qty"
                                                min="1" step="1"
                                            >
                                         </td>
                                         <td v-else>
                                            @{{ item.qty }}
                                         </td>


                                        <!-- 操作 -->
                                        <td>
                                            <template v-if="editIndex === index">
                                                <button class="btn btn-primary btn-sm mr-1"
                                                    @click="saveEditGachaDetail(index)">儲存</button>
                                                <button class="btn btn-warning btn-sm"
                                                    @click="cancelEditGachaDetail()">取消</button>

                                                <button class="btn btn-danger btn-sm"
                                                    @click="deleteGachaDetail(index)">刪除</button>
                                            </template>
                                            <template v-else>
                                                <button class="btn btn-primary btn-sm"
                                                    @click="editGachaDetail(index)">修改資料</button>
                                            </template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>


                    </div>
                    <div class="alert alert-success" role="alert" v-if="words.data_success">@{{ words.data_success }}
                    </div>
                    <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}
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
                    <div class="modal-body" style="max-height: 300px; overflow-y: auto;">
                        <div class="row">
                            <div class="col-12">
                                <ul class="list-unstyled" id="sorts">
                                    <li v-for="(obj, i) in sorts">
                                        <p>
                                            <span class="badge badge-pill badge-secondary handle">
                                                <i class="simple-icon-cursor-move"></i>
                                            </span>
                                            <span>
                                                <label>@{{ obj.name }}</label>
                                            </span>
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary col-12"
                            @click="checkSortSave()">{{ __('default.儲存') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        var vc = new Vue({
            el: '#vc',
            data: {
                lists: [],
                pageData: false,
                searchData: {
                    'is_active': 1,
                    'uid': '',
                    'sort': '',
                    'direction': '',
                },
                sorts: [],
                itemData: null,
                giveGacha: false,
                giveData: {
                    'item_id': '',
                    'qty': 1,
                },
                words: {
                    "type": "",
                    "data_success": '',
                    "data_warning": '',
                },
                findItemBtnDisabled: false,
                gachaDetailItem: null,

                // 扭蛋列表修改
                editIndex: null,
                editItem: {
                    percent: '',
                    guaranteed: '',
                },
                currentGacha: null,
            },
            methods: {
                // 取得扭蛋機列表
                get(type = false, sort = false) {
                    if (type == 'sort') {
                        this.searchData.sort = sort;
                        this.searchData.direction = this.searchData.direction == 'desc' ? 'asc' : 'desc';
                    }
                    if (type == 'up') this.pageData.current_page--;
                    if (type == 'down') this.pageData.current_page++;
                    this.searchData.current_page = this.pageData.current_page;
                    vc = this;

                    // 基礎url
                    let baseUrl = "{{ config('services.API_URL') . '/gacha' }}";
                    let url = baseUrl;

                    // 過濾空值的參數
                    let params = {};
                    if (this.searchData.name !== '') {
                        params.name = this.searchData.name;
                    }
                    if (this.searchData.sort) {
                        params.sort = this.searchData.sort;
                        params.direction = this.searchData.direction;
                    }
                    if (this.searchData.current_page) {
                        params.current_page = this.searchData.current_page;
                    }

                    $.ajax({
                        method: "GET",
                        url: url,
                        data: params,
                        dataType: 'json',
                        success(data) {
                            vc.lists = data.data.data;
                            vc.pageData = data.data;
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            console.log(xhr);
                        },
                    });
                },

                // 取得所有道具
                getItem() {
                    vc.itemData = false;
                    vc.words.data_success = "";
                    vc.words.data_warning = "";
                    if (vc.giveData.item_id) {
                        vc = this;
                        let url = "{{ config('services.API_URL') . '/data_center/get_item' }}/" + vc.giveData.item_id;
                        $.ajax({
                            method: "GET",
                            url: url,
                            data: {},
                            dataType: 'json',
                            success(data) {
                                console.log(data.data.item.item_id);
                                if (typeof data.data.item.item_id != "undefined") {
                                    vc.itemData = data.data.item;
                                    vc.itemData.guaranteed = '';
                                    vc.itemData.percent = 0;
                                    vc.itemData.qty = 1;
                                } else {
                                    vc.words.data_warning = "查無資料";
                                }

                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                console.log(xhr);
                            },
                        });
                    }else{
                        vc.itemData = null;
                        vc.words.data_warning = "請先查找物品";
                    }
                },

                // 扭蛋機新增道具
                giveItem() {
                    if (!this.itemData.item_id) {
                        this.words.data_warning = "請先查找物品";
                        return;
                    }

                    if (!this.itemData.percent || this.itemData.percent <= 0) {
                        this.words.data_warning = '請輸入有效的中獎機率';
                        return;
                    }

                    if (this.itemData.guaranteed !== 1 && this.itemData.guaranteed !== 0) {
                        this.words.data_warning = '請選擇是否為大獎';
                        return;
                    }

                    axios.post(`{{ config('services.API_URL') }}/gacha_items/`, {
                            item_id: this.itemData.item_id,
                            percent: this.itemData.percent,
                            guaranteed: this.itemData.guaranteed,
                            gacha_id: this.giveGacha.id,
                            qty: this.itemData.qty,
                        })
                        .then(response => {
                            this.words.data_success = "新增成功";
                            this.itemData = null;
                            this.giveData.item_id = '';

                            const newItem = response.data.data || response.data;
                            console.log(newItem);
                            console.log(newItem.item_detail.rarity);
                            console.log(this.gachaDetailItem);

                            // 推入新資料到 gachaDetailItem 陣列
                            this.gachaDetailItem.push({
                                item_id: newItem.item_id,
                                percent: newItem.percent,
                                guaranteed: newItem.guaranteed,
                                rarity: newItem.item_detail.rarity,
                                qty: newItem.qty,
                            });

                            // 重新計算
                            let totalPercent = this.calculateTotalPercent(this.gachaDetailItem);
                            this.giveGacha.total_percent = totalPercent;

                        })
                        .catch(error => {
                            console.error(error);

                            if (error.response && error.response.status === 422) {
                                this.words.data_warning = error.response.data.message || "資料驗證錯誤";
                            } else {
                                this.words.data_warning = "新增失敗，請稍後再試";
                            }
                        });

                },

                // 顯示扭蛋機道具資訊
                showGiveItem(gacha) {
                    gacha.is_permanent = gacha.start_time === null;
                    gacha.status_text = gacha.is_permanent ? '常駐池' : '非常駐池';
                    vc.giveGacha = gacha;
                    this.gachaDetailItem = Array.isArray(gacha.gacha_details) ? gacha.gacha_details : [];

                    // 如果沒有gacha.total_percent 或 gacha.total_percent 為0，則計算
                    if (!gacha.total_percent || gacha.total_percent == 0) {
                        let totalPercent = this.calculateTotalPercent(this.gachaDetailItem);
                        this.giveGacha.total_percent = totalPercent ;
                    }

                    $('#giveItemModal').modal();
                },
                // 編輯扭蛋機
                goEdit(id) {
                    return '{{ route('admin.gacha.edit') }}/' + id
                },

                // 新增扭蛋機
                openCreateModal() {
                    window.location.href = `/admin/gachas/add`;
                },

                // 編輯扭蛋道具資料
                editGachaDetail(index) {
                    this.editIndex = index;
                    this.editItem = JSON.parse(JSON.stringify(this.gachaDetailItem[index]));
                },
                cancelEditGachaDetail() {
                    this.editIndex = null;
                    this.editItem = {};
                },
                // 更新扭蛋道具列表
                saveEditGachaDetail(index) {
                    const item = this.gachaDetailItem[index];

                    axios.put(`{{ config('services.API_URL') }}/gacha_items/${item.id}`, {
                            gacha_id: item.gacha_id,
                            item_id: item.item_id,
                            percent: this.editItem.percent,
                            guaranteed: this.editItem.guaranteed,
                            qty: this.editItem.qty,
                        })
                        .then(res => {
                            this.words.data_success = '新增成功';
                            const newItem = res.data.data || res.data;

                            // 更新當前扭蛋道具列表
                            this.gachaDetailItem[index] = newItem;
                            let totalPercent = this.calculateTotalPercent(this.gachaDetailItem);
                            this.giveGacha.total_percent = totalPercent;

                            // 稀有度
                            this.gachaDetailItem[index].rarity = newItem.item_detail.rarity;

                            this.cancelEditGachaDetail();
                        })
                        .catch(err => {
                            console.log(err);
                            this.words.data_success = '';
                            this.words.data_warning = '更新失敗';
                        })
                },

                // 刪除扭蛋道具
                deleteGachaDetail(index) {
                    if (confirm('確定要刪除嗎？')) {
                        axios.delete(`{{ config('services.API_URL') }}/gacha_items/${this.gachaDetailItem[index].id}`)
                            .then(res => {
                                if (res.status == 200) {
                                    // 關閉編輯
                                    this.cancelEditGachaDetail();
                                    this.gachaDetailItem.splice(index, 1);
                                    // 重新計算 code
                                    let totalPercent = this.calculateTotalPercent(this.gachaDetailItem);
                                    this.giveGacha.total_percent = totalPercent;
                                    this.words.data_success = '刪除成功';
                                }else{
                                    this.words.data_warning = '刪除失敗';
                                }
                            })
                            .catch(err => {
                                this.words.data_warning = '刪除失敗';
                            })
                    }
                },

                // 匯入扭蛋 - 觸發檔案選擇
                triggerAndImport(gacha) {
                    this.currentGacha = gacha;
                    this.$refs.fileInput.click(); // 點下去後觸發檔案選擇
                },
                // 匯入扭蛋 - 選擇檔案
                handleFileChange(event) {
                    const file = event.target.files[0];
                    const gacha = this.currentGacha;

                    if (!file) {
                        alert('未選擇檔案');
                        return;
                    }

                    if (!gacha || !gacha.id) {
                        alert('gacha_id 不存在');
                        return;
                    }
                    if (confirm('確定要匯入扭蛋嗎？')) {
                        const formData = new FormData();
                        formData.append('gacha_id', gacha.id);
                        formData.append('file', file);

                        axios.post("{{ route('admin.gacha_items.import') }}", formData, {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            })
                            .then(res => {
                                if (res.data.success) {
                                    this.words.data_success = '匯入成功';
                                    this.showGiveItem(res.data.data);
                                    this.$refs.fileInput.value = ''; // 重設檔案欄位
                                } else {
                                    this.words.data_warning = res.data.message || '匯入失敗';
                                }
                            })
                    }
                },

                // 轉換日期
                formatDate(datetime) {
                    return datetime.slice(0, 10)
                },
                // 轉換機率
                formatPercent(percent) {
                    return Number(percent).toFixed(2);
                },

                // 扭蛋機 - 啟用狀態變更
                async toggleStatus(item) {
                    item.is_active = item.is_active ? 0 : 1;

                    try {
                        let response = await axios.put(`{{ config('services.API_URL') }}/gacha/${item.id}`, {
                            is_active: item.is_active
                        });

                        console.log(response.data); // 顯示更新結果
                    } catch (error) {
                        console.error('更新失敗', error);
                        alert('更新失敗，請稍後再試');

                        // 如果更新失敗，恢復原始狀態
                        item.is_active = item.is_active ? 0 : 1;
                    }
                },

                // 計算扭蛋池總機率
                calculateTotalPercent(gachaItems) {
                    let totalPercent = 0;
                    gachaItems.forEach(item => {
                        totalPercent += parseFloat(item.percent) || 0;
                    });
                    totalPercent = parseFloat(totalPercent.toFixed(2));

                    return totalPercent;
                    },
            },
            created: function() {
                this.get();
            },
            mounted: function() {
                var self = this;
            }
        });
    </script>
@stop
