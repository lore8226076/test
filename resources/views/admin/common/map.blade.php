<div class="col-12">
    <!-- <h1 class="maptitle">{!! $activeMenu->name??'' !!}</h1> -->
    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
        <ol class="breadcrumb pt-0">
            @if(!empty($activeMenu->parent))
            <li class="breadcrumb-item map1name">
                <b>
                    @if(Route::has($activeMenu->parent->link))
                        <a href="{!! route($activeMenu->parent->link) !!}">{{ $activeMenu->parent->name }}</a>
                    @else
                        {{ $activeMenu->parent->name }}
                    @endif
                </b>
            </li>
            <li class="breadcrumb-item active map2name" aria-current="page">
                <b>{{ $activeMenu->name }}</b>
            </li>
            @elseif(!empty($activeMenu))
            <li class="breadcrumb-item map1name">
                <b>{{ $activeMenu->name }}</b>
            </li>
            @endif
            <!-- <li class="breadcrumb-item active" aria-current="page">Data</li> -->
        </ol>
    </nav>

    @yield('top_right')
    <!-- <div class="separator mb-5"></div> -->
</div>
