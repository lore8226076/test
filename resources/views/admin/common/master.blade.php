<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ config('services.SITE_NAME') }} admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link rel="stylesheet" href="/font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="/font/simple-line-icons/css/simple-line-icons.css" />

    <link rel="stylesheet" href="/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="/css/vendor/select2.min.css" />
    <link rel="stylesheet" href="/css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/css/vendor/glide.core.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-stars.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-datepicker3.min.css" />
    <link rel="stylesheet" href="/css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="/css/vendor/cropper.min.css" />
    <link rel="stylesheet" href="/css/main.css" />

    <script src="/js/fontawesome5.js"></script>
</head>

<body id="app-container" class="menu-default show-spinner">
    <script src="/js/vendor/jquery-3.3.1.min.js"></script>
    <script src="/js/vendor/bootstrap-notify.min.js"></script>
    <script src="/js/jquery.loading.min.js"></script>
    <script src="/js/moment.js"></script>
    <script src="/js/vue2.7.14.js"></script>
    <script src="https://unpkg.com/vuejs-datepicker@1.6.2/dist/vuejs-datepicker.min.js"></script>

    <script src="/js/sortable.js"></script>
    <script src="/js/vendor/Chart.bundle.min.js"></script>
    <script src="/js/vendor/chartjs-plugin-datalabels.js"></script>
    <script src="/js/vendor/ckeditor5-build-classic/ckeditor.js"></script>
    <!-- <script src="/js/vendor/ckeditor5-upload.js"></script> -->
    <script src="/js/vendor/bootstrap-datepicker.js"></script>
    <script src="/js/vendor/select2.full.js"></script>
    <script src="/js/sweetalert2@11.js"></script>
    <script src="/js/clipboard.min.js"></script>
    <script src="/js/vendor/cropper.min.js"></script>

    @include('admin.common.css')
    {{-- 頁面自訂css --}}
    @yield('css')


    @include('admin.common.js')
    @include('admin.common.header')
    @include('admin.common.menu')

    <main>
        @yield('content')
    </main>

    <script src="/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="/js/vendor/perfect-scrollbar.min.js"></script>
    <script src="/js/vendor/mousetrap.min.js"></script>
    <script src="/js/vendor/glide.min.js"></script>
    <script src="/js/dore.script.js?20231101"></script>
    <script src="/js/scripts.js"></script>

    {{-- 引入axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>


    <script type="text/javascript">
        function sNotify(message = '', type = 'success') {
            $.notify({
                message: message,
            }, {
                placement: {
                    from: "top",
                    align: "center"
                },
                type: type
            });
        }
        @if (session()->has('message.message'))
            sNotify("{{ session()->get('message.message') }}", "{{ session()->get('message.type', 'success') }}");
        @endif

        $(function() {
            // window.onbeforeunload = function(){
            //     $("body").loading();
            // };
            // $.ajaxSetup({
            //     beforeSend  : function(jqXHR, settings){
            //         if (settings.url.indexOf('get') === -1 && settings.type !== 'GET'){
            //             $("body").loading();
            //         }
            //     },
            //     complete       : function(xhr, textStatus, errorThrown){
            //         $("body").loading('stop');
            //     }
            // });
        })
    </script>

    {{-- 頁面自訂js --}}
    @yield('js')

</body>

</html>
