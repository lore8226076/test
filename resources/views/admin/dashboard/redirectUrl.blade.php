<!-- resources/views/redirect.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>鏘鏘鏘-創意派對 等你來搞怪</title>
</head>

<body>
    <script>
        const ua = navigator.userAgent || navigator.vendor || window.opera;

        if (/android/i.test(ua)) {
            // call api紀錄裝置
            sendRecordPost('android', ua);
            window.location.href = "https://play.google.com/store/apps/details?id=com.wowlong.clangmol";
        } else if (/iPhone|iPad|iPod/.test(ua)) {
            sendRecordPost('ios', ua);
            window.location.href = "https://apps.apple.com/tw/app/%E9%8F%98%E9%8F%98%E9%8F%98-%E5%89%B5%E6%84%8F%E6%B4%BE%E5%B0%8D/id6745211695";
        } else {
            sendRecordPost('other', ua);
            window.location.href = "https://play.google.com/store/apps/details?id=com.wowlong.clangmol";
        }

        function sendRecordPost(type, userAgent) {
            const url = '{{ route('ads.redirect.post') }}';

            fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        type: type,
                        userAgent: userAgent
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        console.error("Failed to record device info.");
                    }
                })
                .catch(error => {
                    console.error("Error recording device info:", error);
                });
        }
    </script>
    <noscript>
        請開啟 JavaScript，才能正確跳轉。
    </noscript>
</body>

</html>
