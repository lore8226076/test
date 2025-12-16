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
                                <label>{{ __('信件標題') }}</label>
                                <input type="text" class="form-control" v-model="searchData.name" @keydown.enter="get()"
                                    placeholder="{{ __('信件標題') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <button class="btn btn-primary" @click="get()">{{ __('default.搜尋') }}</button>
                                <button class="btn btn-success" @click.prevent.stop="openCreateModal()">
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ __('ID') }}</th>
                                        <th scope="col">{{ __('發送類型') }}</th>
                                        <th scope="col">{{ __('接收類型') }}</th>
                                        <th scope="col">{{ __('標題') }}</th>
                                        <th scope="col">{{ __('內容') }}</th>
                                        <!-- <th scope="col">{{ __('開始時間') }}</th>
                                        <th scope="col">{{ __('結束時間') }}</th>
                                        <th scope="col">{{ __('到期時間') }}</th> -->
                                        <th scope="col">{{ __('操作') }}</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(list,i) in lists">
                                        <td scope="row">@{{ i + 1 }}</td>
                                        <td scope="row">@{{ list.id }}</td>
                                        <td scope="row" class="text-center">@{{ formatSenderType(list.sender_type) }}</td>
                                        <td scope="row" class="text-center">@{{ formatTargetType(list.target_type) }}</td>
                                        <td scope="row">@{{ list.title }}</td>
                                        <td scope="row">@{{ list.content }}</td>
                                        <!-- <td scope="row">@{{ list.start_at ? formatDate(list.start_at) : '-' }}</td>
                                        <td scope="row">@{{ list.end_at ? formatDate(list.end_at) : '-' }}</td>
                                        <td scope="row">@{{ list.expire_at ? formatDate(list.expire_at) : '-' }}</td> -->
                                        <td scope="row" class="text-center">
                                            <a href="#" @click.prevent="openEditModal(list.id)"
                                                v-if="list.status === 'pending' || list.sender_type === 'system'"
                                                class="btn btn-primary btn-xs mr-2 mb-2 text-white">{{ __('編輯') }}</a>
                                                
                                            <a href="#" v-if="list.sender_type !== 'system' && list.status === 'pending'"
                                                @click.prevent.stop="deleteInbox(list.id)" 
                                                class="btn btn-danger btn-xs mr-2 mb-2">{{ __('刪除信件') }}</a>
                                            {{-- 發送信件 --}}
                                            <a href="#"
                                                @click.prevent="confirmSendInbox(list.id, list.status, list.sender_type)"
                                                :class="{
                                                    'btn btn-primary btn-xs mr-2 mb-2 text-white': list
                                                        .status === 'pending',
                                                    'btn btn-secondary btn-xs mr-2 mb-2 text-white': list
                                                        .status === 'active' || list.status === 'expired' || list
                                                        .status === 'cancelled'
                                                }"
                                                :disabled="list.status === 'active' || list.status === 'expired' || list
                                                    .status === 'cancelled'">
                                                {{ __('發送信件') }}
                                            </a>
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

        <div class="modal fade modal-bottom" id="createInboxModal" tabindex="-1" role="dialog"
            aria-labelledby="createInboxModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('新增信件') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form ref="myForm" autocomplete="off" @submit.prevent="checkSave(editData.id)">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('發信類型') }}<span class="red">*</span></label>
                                    <select class="form-control" v-model="editData.sender_type" required
                                        :disabled="editData.sender_type === 'system'">
                                        <option value="" selected>{{ __('請選擇') }}</option>
                                        <option value="gm">{{ __('遊戲管理員') }}</option>
                                        <option value="system" disabled>{{ __('系統') }}</option>
                                    </select>
                                    <span v-if="editData.sender_type === 'system'" class="text-danger small">
                                        系統信件無法修改
                                    </span>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('收件類型') }}<span class="red">*</span></label>
                                    <select class="form-control" v-model="editData.target_type" required
                                        :disabled="editData.sender_type === 'system'">
                                        <option value="">{{ __('請選擇') }}</option>
                                        <option value="batch">{{ __('批量') }}</option>
                                        <option value="all">{{ __('全體') }}</option>
                                        <option value="single" disabled>{{ __('單人') }}</option>
                                    </select>
                                </div>
                                <!-- 信件標題 -->
                                <div class="form-group col-md-6">
                                    <label>{{ __('信件標題') }}<span class="red">*</span></label>
                                    <input type="text" class="form-control" v-model="editData.title"
                                        placeholder="{{ __('信件標題') }}" required maxlength="255">
                                </div>

                                <!-- 信件內容 -->
                                <div class="form-group col-md-12">
                                    <label>{{ __('信件內容') }}<span class="red">*</span></label>
                                    <textarea class="form-control" v-model="editData.content" placeholder="{{ __('內容') }}" required
                                        maxlength="255"></textarea>
                                </div>

                                <!-- 批量 填uid -->
                                <div class="form-group col-md-12"
                                    v-if="editData.target_type == 'batch'">
                                    <label>{{ __('收件uid') }}<span class="red">*</span></label>
                                    <textarea class="form-control" v-model="editData.target_uid"
                                        placeholder="單筆輸入UID，批量用逗號分隔 ex: 1788888,1788889,1788890" rows="3" maxlength="1000" required>
                                    </textarea>
                                </div>

                                <!-- 獎勵內容 -->
                                <div class="form-group col-md-12">
                                    <label>
                                        {{ __('獎勵內容') }}<span class="red">*</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-info ml-2"
                                            onclick="event.stopPropagation(); window.open('{{ route('admin.reward.help') }}', '_blank')"
                                            tabindex="-1">
                                            獎勵小工具
                                        </button>
                                    <textarea class="form-control" v-model="editData.reward" placeholder="請直接輸入獎勵內容" required maxlength="255"></textarea>
                                </div>

                                <!-- 開始日期 -->
                                <!-- <div class="form-group col-md-4">
                                    <label>{{ __('開始日期') }}</label>
                                    <input type="date" class="form-control" v-model="editData.start_at"
                                        :min="new Date().toISOString().slice(0, 10)">
                                </div> -->

                                <!-- 結束日期 -->
                                <!-- <div class="form-group col-md-4">
                                    <label>{{ __('結束日期') }}</label>
                                    <input type="date" class="form-control" v-model="editData.end_at"
                                        :min="editData.start_at || new Date().toISOString().slice(0, 10)">
                                </div> -->

                                <!-- 到期日期 -->
                                <!-- <div class="form-group col-md-4">
                                    <label>{{ __('到期日期') }}</label>
                                    <input type="date" class="form-control" v-model="editData.expire_at">
                                </div> -->
                            </div>

                            <div class="form-row mt-4">
                                <button type="submit" class="btn btn-primary col-12">
                                    {{ __('確認儲存') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="alert alert-success" role="alert" v-if="words.data_success">@{{ words.data_success }}
                    </div>
                    <div class="alert alert-danger" role="alert" v-if="words.data_warning">@{{ words.data_warning }}
                    </div>

                    <!-- <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('default.取消') }}</button>
                                    <button type="button" class="btn btn-primary col-12" @click="checkSave()">{{ __('default.確認儲存') }}</button>
                                </div> -->
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
                editData: {
                    sender_type: '',
                    target_type: '',
                    title: '',
                    content: '',
                    target_uid: '',
                    start_at: '',
                    end_at: '',
                    expire_at: '',
                    reward: '',
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
                    let baseUrl = "{{ config('services.API_URL') . '/inbox' }}";
                    let apiKey = "{{ config('services.API_KEY') }}";
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
                        headers: {
                            'x-api-key': apiKey
                        },
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
                getItem() {
                    vc.itemData = false;
                    vc.words.data_success = "";
                    vc.words.data_warning = "";
                    if (vc.giveData.item_id) {
                        vc = this;
                        let url = "{{ config('services.API_URL') . '/data_center/get_item' }}/" + vc.giveData
                            .item_id;
                        fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'x-api-key': '{{ config('services.API_KEY') }}'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (typeof data.data.item.item_id != "undefined") {
                                    vc.itemData = data.data.item;
                                } else {
                                    vc.words.data_warning = "查無資料";
                                }
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
                // 檢查儲存
                checkSave(id) {
                    if (!this.editData.sender_type) {
                        this.words.data_warning = "請選擇發信類型";
                        return;
                    }
                    if (!this.editData.target_type) {
                        this.words.data_warning = "請選擇收件類型";
                        return;
                    }
                    if (!this.editData.title) {
                        this.words.data_warning = "請輸入信件標題";
                        return;
                    }
                    if (!this.editData.content) {
                        this.words.data_warning = "請輸入信件內容";
                        return;
                    }
                    if (!this.editData.reward) {
                        this.words.data_warning = "請輸入獎勵內容";
                        return;
                    }
                    if (this.editData.target_type == 'single' || this.editData.target_type == 'batch') {
                        if (!this.editData.target_uid) {
                            this.words.data_warning = "請輸入收件uid";
                            return;
                        }
                    }

                    // 日期檢查
                    if (this.editData.start_at && this.editData.end_at && this.editData.start_at > this.editData
                        .end_at) {
                        this.words.data_warning = "開始日期不能大於結束日期";
                        return;
                    }
                    if (this.editData.end_at && this.editData.expire_at && this.editData.end_at > this.editData
                        .expire_at) {
                        this.words.data_warning = "結束日期不能大於到期日期";
                        return;
                    }

                    // 清除警告訊息
                    this.words.data_warning = "";

                    // 準備要傳送的資料
                    let postData = {
                        sender_type: this.editData.sender_type,
                        target_type: this.editData.target_type,
                        status: 'pending',
                        title: this.editData.title,
                        content: this.editData.content,
                        target_uid: this.editData.target_uid,
                        // start_at: this.editData.start_at || null,
                        // end_at: this.editData.end_at || null,
                        // expire_at: this.editData.expire_at || null,
                        reward: this.editData.reward || null
                    };

                    // 如果有ID則為編輯，否則為新增
                    let url = id ?
                        "{{ config('services.API_URL') . '/inbox' }}/" + id :
                        "{{ config('services.API_URL') . '/inbox' }}";

                    let method = id ? 'PUT' : 'POST';

                    // 發送請求
                    $.ajax({
                        url: url,
                        type: method,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: postData,
                        dataType: 'json',
                        success: (response) => {
                            if (response.success) {
                                this.words.data_success = response.message || "儲存成功";
                                $('#createInboxModal').modal('hide');
                                this.get(); // 重新載入列表
                            } else {
                                console.log(response);
                                this.words.data_warning = response.message || "儲存失敗";
                            }
                        },
                        error: (xhr) => {
                            console.log(xhr);
                            this.words.data_warning = "儲存失敗，請稍後再試";
                        }
                    });
                },
                // 新增：發送信件前提示
                confirmSendInbox(id, status, sender_type) {
                    // 只有在可發送狀態才彈窗
                    if (status === 'pending') {
                        if (confirm('發送後將無法刪除或編輯此信件，確定要發送嗎？')) {
                            this.sendInbox(id);
                        }
                    } else {
                        // 其他狀態下仍顯示原本的提示
                        alert(this.getSendButtonTooltip(status, sender_type));
                    }
                },
                // 發送信件
                sendInbox(id) {
                    let baseUrl = "{{ config('services.API_URL') . '/inbox' }}";
                    let apiKey = "{{ config('services.API_KEY') }}";
                    let url = baseUrl + '/' + id;

                    fetch(url, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'x-api-key': apiKey
                            },
                            body: JSON.stringify({
                                id: id,
                                status: 'active',
                                is_send: 'true'
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            sNotify(data.message);
                            this.get();
                        })
                },

                getSendButtonTooltip(status, sender_type = 'gm') {
                    if (sender_type === 'system') {
                        return '系統信件發送請由工程師操作';
                    }
                    if (status === 'active') {
                        return '信件已發送';
                    } else if (status === 'expired') {
                        return '信件已過期';
                    } else if (status === 'cancelled') {
                        return '信件已取消';
                    }
                    return '';
                },

                // 開啟新增信件
                openCreateModal() {
                    // 清空資料
                    this.editData = {
                        sender_type: '',
                        target_type: '',
                        title: '',
                        content: '',
                        target_uid: '',
                        reward: '',
                    };

                    // 清空警告訊息
                    this.words.data_warning = "";

                    $('#createInboxModal').modal();
                },

                // 開啟獎勵小工具
                openRewardTool() {

                },

                // 格式化發送者類型
                formatSenderType(type) {
                    if (type == 'system') {
                        return '系統';
                    } else if (type == 'gm') {
                        return '遊戲管理員';
                    }
                },

                // 格式化接收者類型
                formatTargetType(type) {
                    if (type == 'batch') {
                        return '批量';
                    } else if (type == 'all') {
                        return '全體';
                    } else if (type == 'single') {
                        return '單人';
                    }
                },

                formatDate(date) {
                    if (!date) return '-';

                    const dateObj = new Date(date);
                    if (isNaN(dateObj.getTime())) {
                        return date.split(' ')[0] || '-';
                    }

                    // 格式化為 YYYY-MM-DD
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');

                    return `${year}-${month}-${day}`;
                },

                deleteInbox(id) {
                    if (confirm("確定要刪除這封信件嗎？")) {
                        let baseUrl = "{{ config('services.API_URL') . '/inbox' }}";
                        let apiKey = "{{ config('services.API_KEY') }}";
                        let url = baseUrl + '/' + id;
                        fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'x-api-key': apiKey
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    sNotify(data.message);
                                    this.get(); // 重新載入列表
                                } else {
                                    sNotify(data.message, 'danger');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                sNotify('刪除失敗，請稍後再試', 'danger');
                            });
                    }

                },

                openEditModal(id) {
                    this.editData = this.lists.find(item => item.id === id);

                    // 目標
                    if (this.editData.targets && this.editData.targets.length > 0) {
                        this.editData.target_uid = this.editData.targets.map(t => t.target_uid).join(',');
                    } else {
                        this.editData.target_uid = '';
                    }

                    // 將附件轉換為獎勵內容的 JSON 字串，若無則為空
                    if (this.editData.attachments && this.editData.attachments.length > 0) {
                        this.editData.reward = JSON.stringify(
                            this.editData.attachments.map(a => {
                                let obj = {};
                                obj[a.item_id] = a.amount;
                                return obj;
                            })
                        );
                    } else {
                        this.editData.reward = '';
                    }

                    $('#createInboxModal').modal();
                }


            },
            created: function() {
                this.get();
            },
            mounted: function() {
                var self = this;
            }

        });
    </script>
@endsection
