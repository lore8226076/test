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
            <!-- <img src="/logos/logo.png" style="width: 110px; height: 25px"> -->
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


    <a class="navbar-logo" href="{{ route('store.dashboard.index') }}">
        <!-- <img src="{{ $store->logo_url }}" style="width: 55px; height: 55px"> -->
        <!-- <span class="d-none logo d-xs-block"></span>
        <span class="logo-mobile d-block d-xs-none"></span> -->
    </a>

    <div class="navbar-right">
        <div class="header-icons d-inline-block align-middle">
            <!-- <div class="d-none d-md-inline-block align-text-bottom mr-3">
                <div class="custom-switch custom-switch-primary-inverse custom-switch-small pl-1"
                     data-toggle="tooltip" data-placement="left" title="Dark Mode">
                    <input class="custom-switch-input" id="switchDark" type="checkbox" checked>
                    <label class="custom-switch-btn" for="switchDark"></label>
                </div>
            </div> -->
<!--
            <div class="position-relative d-none d-sm-inline-block">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle mb-1" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: : 10px;">繁體中文</button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="{{ route('store.setLocale','zh_hk') }}"><img src="/img/locale/zh_hk.png"> 繁體中文</a>
                    <a class="dropdown-item" href="{{ route('store.setLocale','en_gb') }}"><img src="/img/locale/en_gb.png"> English</a>
                    <a class="dropdown-item" href="{{ route('store.setLocale','zh_cn') }}"><img src="/img/locale/zh_cn.png"> 簡體中文</a>
                </div>
            </div> -->
<!--
            <div class="position-relative d-none d-sm-inline-block">
                <button class="header-icon btn btn-empty" type="button" id="iconMenuButton" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="simple-icon-grid"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right mt-3  position-absolute" id="iconMenuDropdown" style="height: auto">
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-book-open d-block"></i>
                        <div>{{ __('default.新增預約') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-note d-block"></i>
                        <div>{{ __('default.開立訂單') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-calendar d-block"></i>
                        <div>{{ __('default.預約表') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="iconsminds-coins d-block"></i>
                        <div>{{ __('default.儲值購買') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item">
                        <i class="simple-icon-credit-card d-block"></i>
                        <div>{{ __('default.課程購買') }}</div>
                    </a>
                    <a href="#" class="icon-menu-item" data-toggle="modal" data-target="#speedLinkModal">
                        <i class="simple-icon-link d-block"></i>
                        <div>{{ __('default.快速連結') }}</div>
                    </a>
                </div>
            </div> -->

            <div class="position-relative d-inline-block">
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
                                    <p class="font-weight-medium mb-1">您有一筆個人加盟需要審核</p>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex flex-row mb-3 pb-3 border-bottom">
                            <div class="col-12">
                                <a href="#">
                                    <p class="font-weight-medium mb-1">您有一筆公司加盟需要審核</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <button class="header-icon btn btn-empty d-none d-sm-inline-block" type="button" id="fullScreenButton">
                <i class="simple-icon-size-fullscreen"></i>
                <i class="simple-icon-size-actual"></i>
            </button> -->

        </div>

        <div class="user d-inline-block">
            <button class="btn btn-empty p-0" type="button" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <span class="header-user">{{ Auth::guard('store')->user()->name }}</span>
                <!-- <span>
                    <img alt="Profile Picture" src="img/profiles/l-1.jpg" />
                </span> -->
            </button>

            <div class="dropdown-menu dropdown-menu-right mt-3">
                <!-- <a class="dropdown-item" href="#">{{ __('default.帳號') }}</a> -->
                <!-- <a class="dropdown-item" href="#">Features</a>
                <a class="dropdown-item" href="#">History</a>
                <a class="dropdown-item" href="#">Support</a> -->
                <a class="dropdown-item" href="{{ route('store.store.logout') }}">{{ __('default.登出') }}</a>
            </div>
        </div>
    </div>
</nav>

<div class="modal fade" id="speedLinkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <button class="btn btn-secondary clipboard-modal" type="button" data-clipboard-text="{{ $store->booking_url }}">{{ __('default.客戶預約連結') }}<i class="iconsminds-file-copy"></i></button>
                            </div>
                            <input type="text" class="form-control" value="{{ $store->booking_url }}" aria-label="" aria-describedby="basic-addon1" readonly>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <button class="btn btn-secondary clipboard-modal" type="button" data-clipboard-text="{{ $store->login_url }}">{{ __('default.店家登入連結') }}<i class="iconsminds-file-copy"></i></button>
                            </div>
                            <input type="text" class="form-control" value="{{ $store->login_url }}" aria-label="" aria-describedby="basic-addon1" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">{{ __('default.關閉') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function(){
        locale = '{{ \App::currentLocale() }}';
        if(locale=='zh_hk' || locale=='zh_tw'){
            $('#dropdownMenuButton').html('繁體中文');
        }
        else if(locale=='en_gk'){
            $('#dropdownMenuButton').html('English');
        }
        else if(locale=='zh_cn'){
            $('#dropdownMenuButton').html('簡體中文');
        }
    })
</script>