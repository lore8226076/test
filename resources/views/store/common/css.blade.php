<style type="text/css">
    body{
        font-size: 1rem;
    }
    img{
        max-width: 100%;
        max-height: 100%;
    }
    .red{
        color: red;
    }
    .btn{
        font-size: .9rem;
        white-space: nowrap;
    }

    .bg-grayed{
        background-color: #cccccc;
    }

    .baseColor{
        color: {{ $baseBtnColor }};
    }
    .btn-primary{
        background-color: {{ $baseBtnColor }};
        border-color: {{ $baseBtnColor  }};
    }
    .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active{
        background-color: {{ $baseBtnColor }}DD !important;
        border-color: {{ $baseBtnColor  }}DD !important;
    }
    .btn-primary:disabled{
        background-color: {{ $baseBtnColor }}77;
        border-color: {{ $baseBtnColor  }}77;
    }

    .badge-primary{
        background-color: #F89D26 !important;
        border-color: #F89D26 !important;
    }
    .badge-primary:hover, .badge-primary:focus, .badge-primary:active, .badge-primary.active{
        background-color: #F89D26DD !important;
        border-color: #F89D26DD !important;
    }
    .badge-primary:disabled{
        background-color: #F89D2677;
        border-color: #F89D2677;
    }
    .badge-primary-color{
        background-color: #F89D26 !important;
        border-color: #F89D26 !important;
    }
    .badge-primary-color:hover, .badge-primary-color:focus, .badge-primary-color:active, .badge-primary-color.active{
        background-color: #F89D26DD !important;
        border-color: #F89D26DD !important;
    }
    .badge-primary-color:disabled{
        background-color: #F89D2677;
        border-color: #F89D2677;
    }
    .badge-danger{
        background-color: #DC433E !important;
        border-color: #DC433E !important;
    }
    .badge-danger:hover, .badge-danger:focus, .badge-danger:active, .badge-danger.active{
        background-color: #DC433EDD !important;
        border-color: #DC433EDD !important;
    }
    .badge-danger:disabled{
        background-color: #DC433E77;
        border-color: #DC433E77;
    }
    .badge-success{
        background-color: #31AF91 !important;
        border-color: #31AF91 !important;
    }
    .badge-success:hover, .badge-success:focus, .badge-success:active, .badge-success.active{
        background-color: #31AF91DD !important;
        border-color: #31AF91DD !important;
    }
    .badge-success:disabled{
        background-color: #31AF9177;
        border-color: #31AF9177;
    }

    .menu .sub-menu ul li {
        position: static;
    }
    .menu .sub-menu ul li a {
        font-size: 16px;
    }
    .menu .sub-menu ul li a span {
        height: 20px;
    }
    .menu .sub-menu ul li i {
        font-size: 20px;
    }
    .navbar #notificationDropdown {
        padding: 0.5rem;
    }
    .dashboard_gli{
        height: 200px;
    }
    .page-item .page-link.prev {
        /*background: #77bada;*/
        /*border: 1px solid #77bada;*/
    }
    .page-item .page-link.next {
        /*background: #77bada;*/
        /*border: 1px solid #77bada;*/
    }
    .icon-shadow{
        text-shadow: 0.1px 0.1px 0.1px #000;
    }
    .navbar-logo{
        font-size: 30px;
    }
    .navbar .header-icon {
        font-size: 20px;
        /*color: #77bada;*/
    }
    .navbar .header-icon#notificationButton .count {
        /*color: #77bada;*/
        /*border: 1px solid #77bada;*/
    }
    .navbar .icon-menu-item div {
        text-align: center;
        padding: 0 10px;
        line-height: 18px;
    }
    .header-user{
        /*font-weight: bolder;*/
        font-size: 20px;
    }

    .dropdown-item {
        padding: 0.75rem 1.25rem;
    }
    .dropdown-item img{

        width: 20px;
        height: : 20px;
    }

    .simple-icon-grid,.simple-icon-bell{
        font-weight: bolder;
    }

    .croper-box{
        width: 100%;
        height: auto;
    }
    .croper-box img{
        max-width: 100%;
        max-width: 350px;
    }

    .modal-bottom .modal-dialog {
        max-width: 100%;
        width:100%;
        height: 85%;
        position:fixed;
        bottom:0px;
        right:0px;
        margin:0px;
    }
    .modal-bottom .modal-content {
        height: 100%;
    }
    .modal-body {
        margin-bottom: 56px; /* 底部工具列的高度，根據實際情況調整 */
        padding-bottom: 56px;
        overflow-y: auto; /* 如果內容超出屏幕，添加滾動條 */
    }

    .datepicker table tr td.disabled, .datepicker table tr td.disabled:hover {
        color: #ddd;
    }

/*    .fix-thead thead th {
        position: sticky;
        top: 0;
        background-color: #fff;
        z-index: 1;
    }
*/
    .td-img{
        min-width: 200px;
        max-width: 200px;
        height: auto;
    }
    td div input{
        min-width: 120px;
    }
    td div select{
        min-width: 120px;
    }

    .select2-container {
        width: 100% !important;
    }
    .select2-results__options {
        height: 250px;
        overflow-y: auto;
    }

    .vdp-datepicker{
        width: 100%;
    }

    .input-group-text:hover {
        cursor: pointer;
    }

    /* 對所有的數字輸入框隱藏上下箭頭 */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* 對Firefox隱藏上下箭頭 */
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>