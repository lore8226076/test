<script type="text/javascript">
    let ajaxRequestsInProgress = 0;
    $(document).ajaxStart(function() {
        if (ajaxRequestsInProgress === 0) {
            showLoading();
        }
        ajaxRequestsInProgress++;
    });
    $(document).ajaxStop(function() {
        ajaxRequestsInProgress--;
        if (ajaxRequestsInProgress === 0) {
            hideLoading();
        }
        hideLoading();
    });

    function showLoading() {
        $('button:not(.nohide)').attr('disabled', true);
        // Swal.showLoading();
    }

    function hideLoading() {
        $('button').attr('disabled', false);
        // Swal.close();
    }

    $(function() {

        @if (isset($activeMenu) && empty($activeMenu->parent) && $activeMenu)
            setActiveMenu('{{ !empty($activeMenu) ? $activeMenu->id : 0 }}', 0);
        @else
            setActiveMenu('{{ !empty($activeMenu->parent) ? $activeMenu->parent->id : 0 }}',
                '{{ !empty($activeMenu) ? $activeMenu->id : 0 }}');
        @endif

        var client = new ClipboardJS('.clipboard');
        client.on('success', function(event) {
            sNotify('{{ __('default.複製成功') }}');
        });
        var speedLinkCopy = new ClipboardJS('.clipboard-modal', {
            container: document.getElementById('speedLinkModal')
        });
        speedLinkCopy.on('success', function(event) {
            $(event.trigger).tooltip({
                placement: "bottom",
                title: '{{ __('default.複製成功') }}',
            });
            $(event.trigger).tooltip('show');
            setTimeout(function() {
                $(event.trigger).tooltip('hide').tooltip('dispose');
            }, 2000)
        });
    })

    function setActiveMenu(menu1_id = 0, menu2_id = 0) {
        $('#menu1-li' + menu1_id).addClass('active');
        $('#menu2-li' + menu2_id).addClass('active');

        if (menu1_id) {
            var elementToScroll = $("#menu1-li" + menu1_id);
            var scrollContainer = $("#main-menu-scroll");
            if (elementToScroll.length && scrollContainer.length) {
                scrollContainer.animate({
                    scrollTop: elementToScroll.offset().top - scrollContainer.offset().top
                }, 500); // 1000毫秒内平滑滚动
            }
        }
    }

    Number.prototype.numberFormat = function(c, d, t) {
        var n = this,
            c = isNaN(c = Math.abs(c)) ? 0 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math
            .abs(n - i).toFixed(c).slice(2) : "");
    };

    function isString(x) {
        return typeof x === "string";
    }

    function isInteger(x) {
        return typeof x === "number" && isFinite(x) && Math.floor(x) === x;
    }

    function isFloat(x) {
        return !!(x % 1);
    }

    function isEmail(email) {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    }
</script>
