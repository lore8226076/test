<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ __('default.店家登入') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link rel="stylesheet" href="/font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="/font/simple-line-icons/css/simple-line-icons.css" />

    <link rel="stylesheet" href="/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-float-label.min.css" />
    <link rel="stylesheet" href="/css/main.css" />
</head>

<style type="text/css">
    body{
    }
</style>
<body class="background show-spinner no-footer">
    <main>
        <div class="container">
            <div class="row h-100">
                <div class="col-12 col-md-10 mx-auto my-auto">
                    <div class="card auth-card">
                        <div class="position-relative image-side ">
                            <p class=" text-white h2">{{ __('default.派工管理系統') }}</p>
                            <p class="white mb-0">
                                <!-- Please use your credentials to login.
                                <br>If you are not a member, please
                                <a href="#" class="white">register</a>. -->
                            </p>
                        </div>
                        <div class="form-side">
                            <!-- <a href="Dashboard.Default.html">
                                <span class="logo-single"></span>
                            </a> -->
                            <h6 class="mb-4">{{ __('default.登入') }}</h6>
                            <form method="POST" action="{{ route('store.store.login') }}">
                                @csrf
                                <label class="form-group has-float-label mb-4">
                                    <input name="account" class="form-control" required value="0987654322">
                                    <span>{{ __('default.帳號') }}</span>
                                </label>

                                <label class="form-group has-float-label mb-4">
                                    <input name="password" class="form-control" type="password" placeholder="" required value="123456">
                                    <span>{{ __('default.密碼') }}</span>
                                </label>

                                @if(session()->has('message'))
                                <div class="alert alert-warning alert-dismissible fade show rounded mb-1" role="alert">
                                    <strong>{{ session()->get('message')['message'] }}</strong>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#"></a>
                                    <button class="btn btn-primary btn-lg btn-shadow" type="submit">{{ __('default.登入') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="/js/vendor/jquery-3.3.1.min.js"></script>
    <script src="/js/vendor/bootstrap-notify.min.js"></script>
    <script src="/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="/js/dore.script.js"></script>
    <script src="/js/scripts.js"></script>

    <script type="text/javascript">
        @if(session()->has('message.message'))
            $.notify({
                message: "{{ session()->get('message')['message'] }}",
            }, {
                placement: {
                    from: "top",
                    align: "center"
                },
                type: "success"
            });

        @endif
    </script>
</body>
</html>
