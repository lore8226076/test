@extends('admin.common.master')

@section('content')
    <div class="container-fluid" id="vc">
        <div class="row">
            @include('admin.common.map')
        </div>
        <div class="container mt-4" id="rewardHelperApp">
            <div class="card pt-4">
                <div class="card-header">
                    <h5>獎勵內容產生小工具</h5>
                </div>
                <div class="card-body">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-6">
                            <label>搜尋道具</label>
                            <input type="text" class="form-control" v-model="searchKeyword" placeholder="輸入道具名稱或ID"
                                @input="getItemName">
                        </div>
                    </div>
                    <div v-if="filteredItems.length > 0" class="mb-3">
                        <label>選擇道具並輸入數量：</label>
                        <div class="list-group" style="max-height: 300px; overflow-y: auto;">
                            <div class="list-group-item d-flex align-items-center" v-for="item in filteredItems"
                                :key="item.id">
                                <div class="flex-grow-1">
                                    <div>
                                        <span class="badge badge-secondary mr-2">Item ID：@{{ item.item_id }}</span>
                                    </div>
                                    <div>
                                        zh_info：@{{ item.zh_info }}
                                    </div>
                                    <div>
                                        LocalizationName：@{{ item.localization_name }}
                                    </div>
                                    <div>
                                        Category：@{{ item.category || '-' }}
                                    </div>
                                    <div>
                                        Type：@{{ item.type || '-' }}
                                    </div>
                                    <div>
                                        ManagerId：@{{ item.manager_id || '-' }}
                                    </div>
                                    <div>
                                        Region：@{{ item.region || '-' }}
                                    </div>
                                </div>
                                <input type="number" class="form-control ml-2" style="width: 100px;" min="1"
                                    v-model.number="selectedItems[item.item_id]" placeholder="數量">
                                <button class="btn btn-sm btn-primary ml-2" @click="addItem(item)">加入</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="rewardList.length > 0" class="mt-4">
                        <label>產生結果</label>
                        <ul class="list-group mb-2">
                            <li v-for="(item, idx) in rewardList"
                                class="list-group-item d-flex justify-content-between align-items-center">
                                <span>@{{ getItemName(item.item_id) }} @{{ item.item_id }} x @{{ item.amount }}</span>
                                <button class="btn btn-sm btn-danger" @click="removeItem(idx)">移除</button>
                            </li>
                        </ul>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model="rewardJson" readonly id="rewardResultInput">
                            <div class="input-group-append">
                                <button class="btn btn-success" type="button" @click="copyResult">複製</button>
                            </div>
                        </div>
                        <small class="form-text text-success" v-if="copySuccess">@{{ copySuccess }}</small>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script>
        let allItems = @json($items);

        new Vue({
            el: '#rewardHelperApp',
            data: {
                searchKeyword: '',
                selectedItems: {},
                rewardList: [],
                copySuccess: ''
            },
            computed: {
                filteredItems() {
                    let kw = this.searchKeyword.trim().toLowerCase();
                    if (!kw) return [];
                    return allItems.filter(item =>
                        (item.item_id && item.item_id.toString().includes(kw))||
                        (item.zh_info && item.zh_info.toLowerCase().includes(kw))
                    );
                },
                rewardJson() {
                    // [{id, amount}] 格式
                    return JSON.stringify(this.rewardList.map(obj => ({
                        [obj.item_id]: obj.amount
                    })));
                }
            },
            methods: {
                addItem(item) {
                    let amount = this.selectedItems[item.item_id];
                    if (!amount || amount <= 0) {
                        alert('請輸入有效的數量');
                        return;
                    }
                    let exist = this.rewardList.find(obj => obj.item_id === item.item_id);
                    if (exist) {
                        exist.amount = amount;
                    } else {
                        this.rewardList.push({
                            item_id: item.item_id,
                            amount
                        });
                    }
                    this.selectedItems[item.item_id] = '';
                },
                removeItem(idx) {
                    this.rewardList.splice(idx, 1);
                },
                getItemName(item_id) {
                    let found = allItems.find(item => String(item.item_id) === String(item_id));
                    return found ? found.name : '';
                },
                async copyResult() {
                    try {
                        await navigator.clipboard.writeText(this.rewardJson);
                        this.copySuccess = '已複製到剪貼簿！';
                    } catch {
                        this.copySuccess = '複製失敗，請手動複製';
                    }
                    setTimeout(() => this.copySuccess = '', 1500);
                }
            }
        });
    </script>
@endsection
