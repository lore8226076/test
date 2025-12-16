<div class="menu">
    <div class="main-menu">
        <div class="scroll" id="main-menu-scroll">
            <ul class="list-unstyled">
                @foreach($storeMenus->where('up_id',0) as $storeMenu)
                <li id="menu1-li{{ $storeMenu->id }}">
                    @if($storeMenu->is_dashboard)
                    <a href="{{ Route::has($storeMenu->link)?route($storeMenu->link):'' }}">
                        {!! $storeMenu->icon?$storeMenu->icon:'<i class="iconsminds-arrow-forward-2"></i>' !!}
                        <span>{{ $storeMenu->name }}</span>
                    </a>
                    @else
                    <a href="#menulink{{ $storeMenu->id }}">
                        {!! $storeMenu->icon?$storeMenu->icon:'<i class="iconsminds-arrow-forward-2"></i>' !!}
                        <span>{{ $storeMenu->name }}</span>
                    </a>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="sub-menu">
        <div class="scroll">
            @foreach($storeMenus as $storeMenu)
                @if($storeMenu->up_id==0)
                <ul class="list-unstyled" data-link="menulink{{ $storeMenu->id }}">
                    @foreach($storeMenus->where('up_id',$storeMenu->id) as $d2menu)
                        @if($storeMenus->where('up_id',$d2menu->id)->count())
                        <li id="menu2-li{{ $d2menu->id }}">
                            <a href="#" data-toggle="collapse" data-target="#collapse{{ $d2menu->id }}" aria-expanded="true"
                                aria-controls="collapse{{ $d2menu->id }}" class="rotate-arrow-icon opacity-50">
                                <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">{{ $d2menu->name }}</span>
                            </a>
                            <div id="collapse{{ $d2menu->id }}" class="collapse show">
                                <ul class="list-unstyled inner-level-menu">
                                    @foreach($storeMenus->where('up_id',$d2menu->id) as $d3menu)
                                    <li id="menu3-li{{ $d3menu->id }}">
                                        <a href="{{ Route::has($d3menu->link)?route($d3menu->link):'' }}">
                                            <i class="simple-icon-user-following"></i> <span class="d-inline-block">{{ $d3menu->name }}</span>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                        @else
                        <li id="menu2-li{{ $d2menu->id }}">
                            <a href="{{ Route::has($d2menu->link)?route($d2menu->link):'' }}">
                                {!! $d2menu->icon !!} <span class="d-inline-block">{{ $d2menu->name }}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
                @endif
            @endforeach
        </div>
    </div>
</div>