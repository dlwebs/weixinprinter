<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>





{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>微信打印机</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
      <link id="bs-css" href="/css/bootstrap-cerulean.min.css" rel="stylesheet">
    <link href="/css/signin.css" rel="stylesheet">
      <link href="/css/charisma-app.css" rel="stylesheet">
  </head>
  <body>
  <div class="container">
      <div class="row">


          <div class="row">
              <div class="well col-md-5 center login-box">
              <h1>聚优客微信打印机平台</h1>
                  <div class="alert alert-info">
                   <div class="system-message">
                   <?php if(isset($message)) {?>
                   <h1>恭喜：</h1>
                   <p class="success"><?php echo($message); ?></p>
                   <?php }else{?>
                   <h1>糟糕:(</h1>
                   <p class="error"><?php echo($error); ?></p>
                   <?php }?>
                   <p class="detail"></p>
                   <p class="jump">
                   页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b>
                   </p>
                   </div>

                  </div>

              </div>
              <!--/span-->
          </div><!--/row-->
      </div><!--/fluid-row-->

  </div><!--/.fluid-container-->
             <script type="text/javascript">
             (function(){
             var wait = document.getElementById('wait'),href = document.getElementById('href').href;
             var interval = setInterval(function(){
             	var time = --wait.innerHTML;
             	if(time <= 0) {
             		location.href = href;
             		clearInterval(interval);
             	};
             }, 1000);
             })();
             </script>
  </body>
</html>












