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
                                <label>{{ __('兌換碼名稱') }}</label>
                                <input type="text" class="form-control" v-model="searchData.name" @keydown.enter="get()"
                                    placeholder="{{ __('兌換碼名稱') }}">
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
                                        <th scope="col">{{ __('兌換碼') }}</th>
                                        <th scope="col">{{ __('兌換碼名稱') }}</th>
                                        <th scope="col">{{ __('開始時間') }}</th>
                                        <th scope="col">{{ __('結束時間') }}</th>
                                        <th scope="col">{{ __('獎勵內容') }}</th>
                                        <th scope="col">{{ __('備註') }}</th>
                                        <th scope="col">{{ __('操作') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(list,i) in lists">
                                        <td scope="row">@{{ i + 1 }}</td>
                                        <td scope="row">@{{ list.id }}</td>
                                        <td scope="row">@{{ list.code }}</td>
                                        <td scope="row">@{{ list.name }}</td>
                                        <td scope="row">@{{ formatDate(list.start_at) }}</td>
                                        <td scope="row">@{{ formatDate(list.end_at) }}</td>
                                        <td scope="row">@{{ list.rewards }}</td>
                                        <td scope="row">@{{ list.memo }}</td>
                                        <td scope="row" class="text-center">
                                            <a href="#" @click.prevent="openEditModal(list.id)"
                                                class="btn btn-primary btn-xs mr-2 mb-2 text-white">{{ __('編輯') }}</a>
                                            <a href="#" @click.prevent="openViewModal(list.id)"
                                                class="btn btn-info btn-xs mr-2 mb-2 text-white">{{ __('查看') }}</a>
                                            <!-- <a href="#"
                                                @click.prevent.stop="deleteRedeem(list.id)"
                                                class="btn btn-danger btn-xs mr-2 mb-2">{{ __('刪除') }}</a> -->
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

        <!-- 新增/編輯 Modal -->
        <div class="modal fade modal-bottom" id="createRedeemModal" tabindex="-1" role="dialog"
            aria-labelledby="createRedeemModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('新增兌換碼') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form ref="myForm" autocomplete="off" @submit.prevent="checkSave(editData.id)">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('兌換碼') }}<span class="red">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" v-model="editData.code"
                                            placeholder="{{ __('兌換碼') }}" required maxlength="255">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-secondary" @click="generateRandomCode">隨機產生</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('兌換碼名稱') }}<span class="red">*</span></label>
                                    <input type="text" class="form-control" v-model="editData.name"
                                        placeholder="{{ __('兌換碼名稱') }}" required maxlength="255">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('開始日期') }}</label>
                                    <input type="date" class="form-control" v-model="editData.start_at">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('結束日期') }}</label>
                                    <input type="date" class="form-control" v-model="editData.end_at">
                                </div>
                                <div class="form-group col-md-12">
                                    <label>
                                        {{ __('獎勵內容') }}<span class="red">*</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-info ml-2"
                                            onclick="event.stopPropagation(); window.open('{{ route('admin.reward.help') }}', '_blank')"
                                            tabindex="-1">
                                            獎勵小工具
                                        </button>
                                    <textarea class="form-control" v-model="editData.rewards" placeholder="請直接輸入獎勵內容" required maxlength="255"></textarea>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>{{ __('備註') }}</label>
                                    <input type="text" class="form-control" v-model="editData.memo" placeholder="{{ __('備註') }}" maxlength="255">
                                </div>
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
                </div>
            </div>
        </div>

        <!-- 查看 Modal -->
        <div class="modal fade modal-bottom" id="viewRedeemModal" tabindex="-1" role="dialog"
            aria-labelledby="viewRedeemModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('查看兌換碼') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form autocomplete="off">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('兌換碼') }}</label>
                                    <input type="text" class="form-control" v-model="viewData.code"
                                        placeholder="{{ __('兌換碼') }}" maxlength="255" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('兌換碼名稱') }}</label>
                                    <input type="text" class="form-control" v-model="viewData.name"
                                        placeholder="{{ __('兌換碼名稱') }}" maxlength="255" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('開始時間') }}</label>
                                    <input type="text" class="form-control" v-model="viewData.start_at"
                                        placeholder="{{ __('開始時間') }}" maxlength="255" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('結束時間') }}</label>
                                    <input type="text" class="form-control" v-model="viewData.end_at"
                                        placeholder="{{ __('結束時間') }}" maxlength="255" disabled>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>
                                        {{ __('獎勵內容') }}
                                    </label>
                                    <textarea class="form-control" v-model="viewData.rewards" placeholder="請直接輸入獎勵內容" maxlength="255" disabled></textarea>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>{{ __('備註') }}</label>
                                    <input type="text" class="form-control" v-model="viewData.memo" placeholder="{{ __('備註') }}" maxlength="255" disabled>
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
            el: '#vc',
            data: {
                lists: [],
                pageData: false,
                searchData: {
                    name: '',
                    sort: '',
                    direction: '',
                },
                editData: {
                    code: '',
                    name: '',
                    start_at: '',
                    end_at: '',
                    rewards: '',
                    memo: '',
                },
                viewData: {
                    code: '',
                    name: '',
                    start_at: '',
                    end_at: '',
                    rewards: '',
                    memo: '',
                },
                words: {
                    "data_success": '',
                    "data_warning": '',
                },
            },
            methods: {
                get(type = false, sort = false) {
                    if (type == 'sort') {
                        this.searchData.sort = sort;
                        this.searchData.direction = this.searchData.direction == 'desc' ? 'asc' : 'desc';
                    }
                    if (type == 'up') this.pageData.current_page--;
                    if (type == 'down') this.pageData.current_page++;
                    this.searchData.current_page = this.pageData.current_page;
                    vc = this;

                    let baseUrl = "{{ config('services.API_URL') . '/redeem' }}";
                    let apiKey = "{{ config('services.API_KEY') }}";
                    let url = baseUrl;

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
                            console.log(data);
                            vc.lists = data.data.data;
                            vc.pageData = data.data;
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            console.log(xhr);
                        },
                    });
                },
                // 檢查儲存
                checkSave(id) {
                    if (!this.editData.code) {
                        this.words.data_warning = "請輸入兌換碼";
                        return;
                    }
                    if (!this.editData.name) {
                        this.words.data_warning = "請輸入兌換碼名稱";
                        return;
                    }
                    if (!this.editData.rewards) {
                        this.words.data_warning = "請輸入獎勵內容";
                        return;
                    }
                    if (this.editData.start_at && this.editData.end_at && this.editData.start_at > this.editData.end_at) {
                        this.words.data_warning = "開始日期不能大於結束日期";
                        return;
                    }

                    this.words.data_warning = "";

                    let postData = {
                        code: this.editData.code,
                        name: this.editData.name,
                        start_at: this.editData.start_at || null,
                        end_at: this.editData.end_at || null,
                        rewards: this.editData.rewards,
                        memo: this.editData.memo || '',
                    };

                    let url = id ?
                        "{{ config('services.API_URL') . '/redeem' }}/" + id :
                        "{{ config('services.API_URL') . '/redeem' }}";

                    let method = id ? 'PUT' : 'POST';

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
                                $('#createRedeemModal').modal('hide');
                                this.get();
                            } else {
                                this.words.data_warning = response.message || "儲存失敗";
                            }
                        },
                        error: (xhr) => {
                            this.words.data_warning = "儲存失敗，請稍後再試";
                        }
                    });
                },
                deleteRedeem(id) {
                    if (confirm("確定要刪除這筆兌換碼嗎？")) {
                        let baseUrl = "{{ config('services.API_URL') . '/redeem' }}";
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
                                    this.get();
                                } else {
                                    sNotify(data.message, 'danger');
                                }
                            })
                            .catch(error => {
                                sNotify('刪除失敗，請稍後再試', 'danger');
                            });
                    }
                },
                openCreateModal() {
                    this.editData = {
                        code: '',
                        name: '',
                        start_at: '',
                        end_at: '',
                        rewards: '',
                        memo: '',
                    };
                    this.words.data_warning = "";
                    this.words.data_success = "";
                    $('#createRedeemModal').modal();
                },
                openViewModal(id) {
                    let item = this.lists.find(item => item.id === id);
                    if (!item) return;
                    this.viewData = {
                        code: item.code || '',
                        name: item.name || '',
                        start_at: item.start_at || '',
                        end_at: item.end_at || '',
                        rewards: item.rewards || '',
                        memo: item.memo || '',
                    };
                    $('#viewRedeemModal').modal();
                },
                openEditModal(id) {
                    let item = this.lists.find(item => item.id === id);
                    if (!item) return;
                    this.editData = {
                        code: item.code || '',
                        name: item.name || '',
                        start_at: item.start_at || '',
                        end_at: item.end_at || '',
                        rewards: item.rewards || '',
                        memo: item.memo || '',
                        id: item.id,
                    };
                    $('#createRedeemModal').modal();
                },
                formatDate(date) {
                    if (!date) return '-';
                    const dateObj = new Date(date);
                    if (isNaN(dateObj.getTime())) {
                        return date.split(' ')[0] || '-';
                    }
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const hour = String(dateObj.getHours()).padStart(2, '0');
                    const min = String(dateObj.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day} ${hour}:${min}`;
                },
                generateRandomCode() {
                    // 產生10碼隨機英數字
                    this.editData.code = Math.random().toString(36).substr(2, 10).toUpperCase();
                },
            },
            created: function() {
                this.get();
            },
        });
    </script>
@endsection
