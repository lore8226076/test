<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ config('services.SITE_NAME') }}</title>
  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <style>
    :root{
      --Page_BrightColor: hsl(240, 15%, 80%, 1.0);
      --Page_DarkColor: hsl(240, 15%, 100%, 1.0);
      /* 選單顏色 */
      --Menu_BrightColor: hsl(240, 10%, 90%, 1.0);
      --Menu_DarkColor: hsl(240, 20%, 30%, 1.0);

      --Font_BrightColor: #BCBBE2;
      --Font_DarkColor: #242436;
      --Font_CommentColor: gray;
    }
    /* 淡藍色背景，可依自己喜好修改；例如 #f8f9fa (bootstrap 灰白)，或其他粉色、淺綠色等 */
    body {
        background: repeating-linear-gradient( 0deg, var(--Page_BrightColor), var(--Page_DarkColor) 30%, var(--Page_BrightColor) 50%);
        font-family: "Roboto Slab","LXGW WenKai Mono TC";
    }

    /* 影片容器，維持 16:9 比例 & 圓角 */
    .video-container {
      position: relative;
      padding-bottom: 56.25%; /* 16:9 */
      height: 0;
      overflow: hidden;
      border-radius: 0.75rem;
    }

    .video-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: 0;
      border-radius: 0.75rem;
    }

    /* 卡片背景色 */
    .my-custom-card {
      background: repeating-linear-gradient( -30deg, var(--Menu_DarkColor), var(--Menu_BrightColor) 30%, var(--Menu_DarkColor) 60%);
      /*border: outset var(--Menu_BrightColor);*/
      border-radius: 0.75rem;    /* 圓角 */
      box-shadow: 0 2px 8px rgba(0,0,0,0.1); /* 淡淡投影 */
    }
  </style>
</head>
<body>
<!-- container-fluid: 手機左右也可滿版 -->
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <!-- 手機佔滿、桌機佔 8 欄 -->
        <div class="col-sm-12 col-lg-10">
            <h2 class="text-center" style="color: var(--Font_DarkColor);">地圖編輯教學 Map Edit Training</h2>
            <!-- 卡片 -->
            <div class="my-custom-card p-2">
              <!-- 影片容器 -->
                <div class="video-container">
                  <iframe
                    id="youtubePlayer"
                    title="地圖編輯器教學"
                    src="https://www.youtube.com/embed/ML_dQpFOQDY?autoplay=1&enablejsapi=1"
                    frameborder="0"
                    allow="autoplay; fullscreen"
                    allowfullscreen>
                  </iframe>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS (可選，用於互動功能) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // 获取 iframe 元素
    const iframe = document.getElementById("youtubePlayer");

    // 监听 iframe 加载完成
    iframe.onload = function () {
      // 请求进入全屏模式
      const requestFullScreen = iframe.requestFullscreen || iframe.mozRequestFullScreen || iframe.webkitRequestFullscreen || iframe.msRequestFullscreen;
      if (requestFullScreen) {
        requestFullScreen.call(iframe);
      }
    };
  });
</script>
