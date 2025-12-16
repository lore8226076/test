<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>活動登入頁</title>

<style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(180deg, #7ed0ff, #b5e3ff 40%, #ffffff 100%);
        color: #333;
    }

    .page {
        max-width: 480px;
        margin: auto;
        padding: 12px;
        min-height: 100vh;
    }

    /* ===== 上方標題列（亮藍面板） ===== */
    .top-bar {
        display:flex;
        justify-content:space-between;
        padding:10px 14px;
        border-radius:16px;
        background: linear-gradient(180deg, #55c3ff, #2fa8ff);
        border:2px solid #ffffff;
        box-shadow:0 0 10px rgba(0,120,255,0.6);
        margin-bottom:10px;
        color:white;
        font-weight:700;
    }

    .top-bar-badge {
        padding:3px 12px;
        background:linear-gradient(180deg, #ffe66b, #ffb800);
        border-radius:12px;
        color:#4e3500;
        font-size:12px;
        box-shadow:0 0 8px rgba(255,180,0,0.8);
    }

    /* ===== Tabs：亮色 UI 按鈕 ===== */
    .tab-list {
        display:flex;
        overflow-x:auto;
        gap:8px;
        margin-bottom:10px;
        padding-bottom:4px;
    }

    .tab-button {
        flex:0 0 auto;
        padding:8px 16px;
        border-radius:12px;
        font-weight:500;
        color:white;
        border:2px solid white;
        text-shadow:0 1px 0 rgba(0,0,0,0.3);
        cursor:pointer;
        transition:0.15s;
        box-shadow:0 3px 6px rgba(0,0,0,0.2);
    }

    .tab-button:nth-child(1) { background:linear-gradient(180deg,#40ffcf,#0fd8b5); }
    .tab-button:nth-child(2) { background:linear-gradient(180deg,#6aa5ff,#2d75ff); }
    .tab-button:nth-child(3) { background:linear-gradient(180deg,#ff9f4a,#ff6a00); }
    .tab-button:nth-child(4) { background:linear-gradient(180deg,#40ffcf,#0fd8b5); }
    .tab-button:nth-child(5) { background:linear-gradient(180deg,#6aa5ff,#2d75ff); }
    .tab-button:nth-child(6) { background:linear-gradient(180deg,#ff9f4a,#ff6a00); }
    .tab-button:nth-child(7) { background:linear-gradient(180deg,#40ffcf,#0fd8b5); }
    .tab-button:nth-child(8) { background:linear-gradient(180deg,#6aa5ff,#2d75ff); }
    .tab-button:nth-child(9) { background:linear-gradient(180deg,#ff9f4a,#ff6a00); }

    .tab-button.active {
        transform:scale(1.05);
        box-shadow:0 0 10px rgba(255,255,255,0.8);
        border-color:#fff;
    }

    /* ===== 主內容卡片（亮藍毛玻璃感） ===== */
    .card {
        position:relative;
        padding-bottom:50px;
        border-radius:20px;
        background:rgba(255,255,255,0.7);
        backdrop-filter:blur(10px);
        border:2px solid #ffffff;
        box-shadow:0 0 15px rgba(0,150,255,0.4);
    }

    .event-page {
        display:none;
        padding:14px;
    }
    .event-page.active { display:block; }

    .event-title {
        font-size:20px;
        font-weight:900;
        margin-bottom:6px;
        color:#2266cc;
        text-shadow:0 1px 0 #fff;
    }

    .event-subtitle {
        color:#666;
        font-size:12px;
        margin-bottom:10px;
    }

    .event-banner {
        width:100%;
        border-radius:14px;
        overflow:hidden;
        border:2px solid #ffffff;
        box-shadow:0 0 10px rgba(255,255,255,0.6);
        margin-bottom:10px;
    }

    .event-banner img { width:100%; display:block; }

    .event-desc {
        background:white;
        border-radius:12px;
        border:1px solid #bcdcff;
        padding:12px;
        font-size:16px;
        line-height:1.6;
        color:#444;
        box-shadow:0 0 6px rgba(0,90,180,0.2);
    }

    .event-desc b { color:#ff6a00; }

    /* ===== 左右切換按鈕（亮色圓形） ===== */
    .nav-arrows {
        position:absolute;
        bottom:8px;
        left:0; right:0;
        display:flex;
        justify-content:center;
        gap:16px;
    }

    .arrow-btn {
        width:40px; height:40px;
        display:flex; align-items:center; justify-content:center;
        border-radius:50%;
        border:2px solid #ffffff;
        background:linear-gradient(180deg,#ffffff,#d8f0ff);
        box-shadow:0 0 10px rgba(150,200,255,0.9);
        font-size:20px;
        font-weight:900;
        color:#2d75ff;
        cursor:pointer;
        transition:0.1s;
    }

    .arrow-btn:active {
        transform:scale(0.92);
    }

    /* ===== 分頁點 ===== */
    .hint-bar {
        margin-top:8px;
        text-align:center;
        font-size:11px;
        color:#444;
    }

    .hint-dot {
        width:7px; height:7px;
        display:inline-block;
        border-radius:50%;
        background:#aac7ff;
        margin:0 3px;
    }

    .hint-dot.active {
        background:#2d75ff;
        box-shadow:0 0 6px rgba(45,117,255,0.8);
    }
</style>
</head>

<body>
<div class="page">

    <!-- 上方條 -->
    <!-- <div class="top-bar">
        <div>LOGIN EVENT</div>
        <div class="top-bar-badge">活動中心</div>
    </div> -->

    <!-- Tabs -->
    <div class="tab-list" id="tabList">
        <button class="tab-button active" data-index="0">IP市集</button>
        <button class="tab-button" data-index="1">儲值返利</button>
        <button class="tab-button" data-index="2">搶先體驗</button>
        <button class="tab-button" data-index="3">開局送百抽</button>
        <button class="tab-button" data-index="4">無廣</button>
    </div>

    <!-- 內容卡片 -->
    <div class="card">
        <div class="event-page active" data-index="0">
            <!-- <div class="event-title">多元IP大串聯，收藏快感最大化</div>
            <div class="event-subtitle">跨界聯名不間斷，人氣IP大解放</div>
            <div class="event-subtitle">跨界合作上線，造型主題任你挑！</div> -->
            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_ip_01.png">
            </div>
            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_ip_02.png">
            </div>
            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_ip_03.png">
            </div>
            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_ip_04.png">
            </div>
            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_ip_05.png">
            </div>

            <div class="event-desc">
                多組人氣 IP 入駐 「IP 藝術市集」！<br>
                主題造型、限定家具、跨界聯名同步登場！<br>
                隨時逛、自由fun，打造屬於你的混搭藝術。<br>
                靈感大爆發——讓你每次踏進市集，都有新發現！
            </div>
        </div>
        <!-- Page 1 -->
        <div class="event-page" data-index="1">
            <!-- <div class="event-title">官方大放送！儲值100%返利！</div>
            <div class="event-subtitle">儲值翻倍回饋，開服限定！</div> -->

            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_stored_01.png">
            </div>
            <div class="event-desc">
                <!-- 每天登入即可獲得 <b>家具</b>、<b>頭像框</b>、<b>寵物糖果</b>！<br><br> -->
                活動期間累積儲值，即享 100% 靈感返饋！<br>
                即日起至 1 月底，所有儲值金額將全額返還！<br><br>

                <b>儲多少</b>、<b>返多少</b>——助你更快培養理想隊伍，<br>
                創作靈感永不斷電！<br><br>

                📅 儲值加倍期間：開服後 ~ 2026/01/31<br>
                📅 返還時間：2026/02/28 前陸續發送<br>
            </div>
        </div>
        <!-- Page 2 -->
        <div class="event-page" data-index="2">
            <!-- <div class="event-title">搶先體驗！專屬福利先拿先享！</div>
            <div class="event-subtitle">開跑先行版，讓你福利滿滿滿</div> -->

            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_open_01.png">
            </div>

            <div class="event-desc">
                <b>《鏘鏘鏘—藝術派對》</b>搶先體驗正式開放！<br>
                率先探索全新玩法、佈置你的專屬家園，<br>
                揭開藝術宇宙的第一層神秘色彩。<br><br>

                你的每一次點擊，<br>
                都將成為這個世界最初、最珍貴的筆觸。<br>
            </div>
        </div>

        <!-- Page 3 -->
        <div class="event-page" data-index="3">
            <!-- <div class="event-title">百連開局！SSR 一路爆！</div>
            <div class="event-subtitle">登入送百抽，0元爽爽免費玩！</div> -->

            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_100_01.png">
            </div>

            <div class="event-desc">
                開局送百抽！大藝術家集合啦！<br>
                即日起 ～ 2026/02/28 前，<br>
                所有的新晉藝術家都能獲得 連續 10 日的<b>星環幣豪禮</b>！<br>
                無任務、無條件，創角即刻送。<br><br>

                天天登入、天天抽，輕鬆打造你的夢幻開局卡池！<br>
                機會難得——快來感受抽到手軟的幸福吧！<br>
            </div>
        </div>

        <!-- Page 4 -->
        <div class="event-page" data-index="4">
            <!-- <div class="event-title">無強迫・無跳轉・全程零廣告！</div>
            <div class="event-subtitle">純淨遊戲體驗・0 廣告好自在！</div> -->

            <div class="event-banner">
                <img src="https://clang-party.wow-dragon.com.tw/files/webview/ad_ads_01.png">
            </div>

            <div class="event-desc">
                純粹創作，不被打斷。<br><br>

                我們相信，每一段靈感都值得被好好珍惜。<br>
                在這裡，再也沒有惱人的廣告干擾，<br>
                你能安心把每一刻專注力留給創作與冒險。<br><br>

                探索吧，用你的方式。其他的——交給我們就好。😸
            </div>
        </div>

        <!-- 左右切換 -->
        <div class="nav-arrows">
            <button class="arrow-btn" id="prevBtn">‹</button>
            <button class="arrow-btn" id="nextBtn">›</button>
        </div>
    </div>

    <!-- 下方點點 -->
    <div class="hint-bar">
        <span class="hint-dot active"></span>
        <span class="hint-dot"></span>
        <span class="hint-dot"></span>
        <span class="hint-dot"></span>
        <span class="hint-dot"></span>
    </div>

</div>

<script>

    (function(){
        const tabs = [...document.querySelectorAll(".tab-button")];
        const pages = [...document.querySelectorAll(".event-page")];
        const dots = [...document.querySelectorAll(".hint-dot")];
        const prev = document.getElementById("prevBtn");
        const next = document.getElementById("nextBtn");

        let index = 0;

        function show(i){
            if(i < 0) i = pages.length - 1;
            if(i >= pages.length) i = 0;
            index = i;

            tabs.forEach((t,idx)=>t.classList.toggle("active", idx===i));
            pages.forEach((p,idx)=>p.classList.toggle("active", idx===i));
            dots.forEach((d,idx)=>d.classList.toggle("active", idx===i));
        }

        tabs.forEach(t => t.onclick = ()=> show(+t.dataset.index));
        prev.onclick = ()=> show(index-1);
        next.onclick = ()=> show(index+1);

        /* ======== 手機滑動支援 ======== */
        let startX = 0;
        let endX = 0;

        const swipeArea = document.querySelector(".card");

        swipeArea.addEventListener("touchstart", (e)=>{
            startX = e.changedTouches[0].clientX;
        });

        swipeArea.addEventListener("touchmove", (e)=>{
            endX = e.changedTouches[0].clientX;
        });

        swipeArea.addEventListener("touchend", ()=>{
            let diff = endX - startX;

            if(Math.abs(diff) > 50){
                if(diff < 0){
                    show(index + 1); // 左滑 → 下一頁
                } else {
                    show(index - 1); // 右滑 → 上一頁
                }
            }
        });

        show(0);
    })();
</script>

</body>
</html>
