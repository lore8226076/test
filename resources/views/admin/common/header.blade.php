<nav class="navbar fixed-top">
    <div class="d-flex align-items-center navbar-left">
        <a href="#" class="menu-button d-none d-md-block">
            <svg class="main" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 17">
                <rect x="0.48" y="0.5" width="7" height="1" />
                <rect x="0.48" y="7.5" width="7" height="1" />
                <rect x="0.48" y="15.5" width="7" height="1" />
            </svg>
            <svg class="sub" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 17">
                <rect x="1.56" y="0.5" width="16" height="1" />
                <rect x="1.56" y="7.5" width="16" height="1" />
                <rect x="1.56" y="15.5" width="16" height="1" />
            </svg>
        </a>

        <a href="#" class="menu-button-mobile d-xs-block d-sm-block d-md-none">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 17">
                <rect x="0.5" y="0.5" width="25" height="1" />
                <rect x="0.5" y="7.5" width="25" height="1" />
                <rect x="0.5" y="15.5" width="25" height="1" />
            </svg>
        </a>

        <!-- <div class="search" data-search-path="Pages.Search.html?q=">
            <input placeholder="Search...">
            <span class="search-icon">
                <i class="simple-icon-magnifier"></i>
            </span>
        </div> -->

        <div class="d-inline-block ml-2">
            <a class="navbar-logo" href="{{ route('admin.dashboard.index') }}">
                {{-- <img src="/img/logo.png" style="width: auto; max-height: 50px"> --}}
            </a>
            <!-- <span style="font-weight: bold;font-size: 15pt;">BooKing!</span> -->
<!--             <select class="form-control rounded" id="locale">
                <option value="zh_hk">繁體中文</option>
                <option value="en_gb"><i class="simple-icon-home"></i> English</option>
                <option value="zh_cn">簡體中文</option>
            </select> -->

        </div>

        <!-- <a class="btn btn-sm btn-outline-primary ml-3 d-none d-md-inline-block"
            href="https://1.envato.market/5kAb">&nbsp;BUY&nbsp;
        </a> -->
    </div>


    <!-- <a class="navbar-logo" href="{{ route('admin.dashboard.index') }}"> -->
        <!-- <img src="/img/logo.png" style="width: auto; max-height: 50px"> -->
        <!-- <span class="d-none logo d-xs-block"></span>
        <span class="logo-mobile d-block d-xs-none"></span> -->
    <!-- </a> -->

    <div class="navbar-right">
        <div class="header-icons d-inline-block align-middle">
            <!-- <div class="d-none d-md-inline-block align-text-bottom mr-3">
                <div class="custom-switch custom-switch-primary-inverse custom-switch-small pl-1"
                     data-toggle="tooltip" data-placement="left" title="Dark Mode">
                    <input class="custom-switch-input" id="switchDark" type="checkbox" checked>
                    <label class="custom-switch-btn" for="switchDark"></label>
                </div>
            </div> -->

          <!--   <div class="position-relative d-none d-sm-inline-block">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle mb-1" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: : 10px;">繁體中文</button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href=""><img src="/img/locale/zh_hk.png"> 繁體中文</a>
                    <a class="dropdown-item" href=""><img src="/img/locale/en_gb.png"> English</a>
                    <a class="dropdown-item" href=""><img src="/img/locale/zh_cn.png"> 簡體中文</a>
                </div>
            </div> -->

            <!-- <div class="position-relative d-none d-sm-inline-block">
                <button class="header-icon btn btn-empty" type="button" id="iconMenuButton" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="simple-icon-grid"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right mt-3  position-absolute" id="iconMenuDropdown" style="height: auto">
                    <a href="" class="icon-menu-item">
                        <i class="simple-icon-book-open d-block"></i>
                        <div>{{ __('新增預約') }}</div>
                    </a>
                    <a href="" class="icon-menu-item">
                        <i class="simple-icon-user-follow d-block"></i>
                        <div>{{ __('新增客戶') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-note d-block"></i>
                        <div>{{ __('開立訂單') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-calendar d-block"></i>
                        <div>{{ __('預約表') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="iconsminds-coins d-block"></i>
                        <div>{{ __('儲值購買') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-credit-card d-block"></i>
                        <div>{{ __('課程購買') }}</div>
                    </a>
                </div>
            </div> -->

<!--             <div class="position-relative d-inline-block">
                <button class="header-icon btn btn-empty" type="button" id="notificationButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="simple-icon-bell"></i>
                    <span class="count">4</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right mt-3 position-absolute" id="notificationDropdown">
                    <div class="scroll pt-2">
                        <div class="d-flex flex-row mb-3 pb-3 border-bottom">
                            <div class="col-12">
                                <a href="#">
                                    <p class="font-weight-medium mb-1">您有一筆新預約: 客戶AAA預約了{員工名稱} MM/DD HH:MM的 {服務項目}服務</p>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-row mb-3 pb-3 border-bottom">
                            <div class="col-12">
                                <a href="#">
                                    <p class="font-weight-medium mb-1">客戶AAA已將{員工名稱} MM/DD HH:MM的{服務項目} 更改至 MM/DD HH:MM</p>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-row mb-3 pb-3 border-bottom">
                            <div class="col-12">
                                <a href="#">
                                    <p class="font-weight-medium mb-1">客戶AAA已取消{員工名稱} MM/DD HH:MM的{服務項目}</p>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-row mb-3 pb-3 border-bottom">
                            <div class="col-12">
                                <a href="#">
                                    <p class="font-weight-medium mb-1">商品BBB的庫存已剩下{目前數量} 已小於安全庫存{安全庫存數量}</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 -->
            <!-- <button class="header-icon btn btn-empty d-none d-sm-inline-block" type="button" id="fullScreenButton">
                <i class="simple-icon-size-fullscreen"></i>
                <i class="simple-icon-size-actual"></i>
            </button> -->

        </div>

        <div class="user d-inline-block">
            <button class="btn btn-empty p-0" type="button" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <span class="header-user">{{ Auth::user()->name }}</span>
                <!-- <span>
                    <img alt="Profile Picture" src="img/profiles/l-1.jpg" />
                </span> -->
            </button>

            <div class="dropdown-menu dropdown-menu-right mt-3">
                <!-- <a class="dropdown-item" href="#">{{ __('帳號') }}</a> -->
                <!-- <a class="dropdown-item" href="#">Features</a>
                <a class="dropdown-item" href="#">History</a>
                <a class="dropdown-item" href="#">Support</a> -->
                <a class="dropdown-item" href="{{ route('admin.admin.logout') }}">{{ __('登出') }}</a>
            </div>
        </div>
    </div>
</nav>



<script type="text/javascript">
    $(function(){
    })
</script>