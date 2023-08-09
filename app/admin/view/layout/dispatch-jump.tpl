<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>跳转提示</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="/assets-admin/media/favicons/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets-admin/media/favicons/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets-admin/media/favicons/apple-touch-icon-180x180.png">
    <!-- END Icons -->

    <!-- Stylesheets -->
    <!-- Fonts and OneUI framework -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700">
    <link rel="stylesheet" id="css-main" href="/assets-admin/css/oneui.min.css">
</head>
<body>
<div id="page-container">
    <!-- Main Container -->
    <main id="main-container">
        <!-- Page Content -->
        <div class="hero">
            <div class="hero-inner text-center">
                <div class="content content-full bg-white overflow-hidden">
                    <div class="py-4">
                        <h1 class="font-w300 {$code? 'text-success' : 'text-city'} push-10 animated flipInX"><i class="fa fa-{$code? 'check' : 'times'}-circle"></i> <?php echo(strip_tags($msg));?></h1>
                        <p class="font-w300 push-20 animated fadeInUp">页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>秒</p>
                        <div class="push-50">
                            <a class="btn btn-minw btn-rounded btn-success" href="<?php echo($url);?>"><i class="fa fa-external-link-square"></i> 立即跳转</a>
                            <button class="btn btn-minw btn-rounded btn-warning" type="button" onclick="stop()"><i class="fa fa-ban"></i> 禁止跳转</button>
                        </div>
                    </div>
                </div>
                <div class="content content-full font-size-sm text-muted">
                    <!-- Error Footer -->
                    <p class="mb-1">
                        CooYum2019
                    </p>
                    <a class="link-fx" href="{$Request.baseFile}">返回首页</a>
                </div>
            </div>
        </div>
    </main>
    <!-- END Main Container -->
</div>
<script>

    (function(){
        var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if (time <= 0) {
                location.href = href;
                clearInterval(interval);
            }
        }, 1000);

        // 禁止跳转
        window.stop = function (){
            clearInterval(interval);
        }
    })();
</script>
</body>
</html>
