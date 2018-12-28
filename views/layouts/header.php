<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header" <?php if(Yii::$app->user->isGuest){ echo "style=display:none";}?>>

    <?= Html::a('<span class="logo-mini">易</span><span class="logo-lg">' . "易佰采购云系统" . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">



                <!-- Messages: style can be found in dropdown.less-->
                <li class="dropdown messages-menu" style="display: none;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-envelope-o"></i>
                        <span class="label label-success">1</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">你有1条消息</li>
                        <li>
                            <a href="#">你今天还没有吃饭吧?</a>
                        </li>
                        <li class="footer"><a href="#">查看所有</a></li>
                    </ul>
                </li>






                <li class="dropdown notifications-menu" style="display: none;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning">10</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">您有10个通知</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 今天有5名新成员参加
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>












                <!-- Tasks: style can be found in dropdown.less -->
                <li class="dropdown tasks-menu" style="display: none;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-flag-o"></i>
                        <span class="label label-danger">9</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">你有9个任务</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">

                                <!-- end task item -->
                                <li><!-- Task item -->
                                    <a href="#">
                                        <h3>
                                            我每天要采购
                                            <small class="pull-right">80%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-yellow" style="width: 80%"
                                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">80% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end task item -->
                            </ul>
                        </li>
                        <li class="footer">
                            <a href="#">View all tasks</a>
                        </li>
                    </ul>
                </li>




                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle">
                        <span>V18007</span>
                    </a>
                </li>
                <li class="dropdown user user-menu" <?php if(Yii::$app->user->isGuest){ echo "style=display:none";}?>>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                        <span class="hidden-xs"><?=Yii::$app->user->identity->username?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                 alt="User Image"/>

                            <p>
                                <?=Yii::$app->user->identity->username?>
                                <small>注册时间</small>
                            </p>
                        </li>
                        <!-- Menu Body -->
                      <!--  <li class="user-body">
                            <div class="col-xs-4 text-center">
                                <a href="#">关注人</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">销售</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">朋友</a>
                            </div>
                        </li>-->
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <?= Html::a(
                                    Yii::t('app','修改密码'),
                                    ['/site/change-password'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    Yii::t('app','退出'),
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- User Account: style can be found in dropdown.less -->
                <!--<li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>-->
            </ul>
        </div>
    </nav>
</header>

<?php
$js = <<<EOF

$(function() {

    function setCookie(cname, cvalue, exdays)
    {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }
    
    function getCookie(cname)
    {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i].trim();
            if(c.indexOf(name) == 0) { 
                return c.substring(name.length, c.length); 
            }
        }
        return "";
    }
    
    if(getCookie('offMessage') == '') {
        $.get('/site/messages', function(data) {
            if(data['msg']) {
                var html = '';
                for(var i = 0; i < data['msg'].length; i++) {
                    html += '<p>'+data['msg'][i]+'</p>';
                }
                layer.open({
                    type: 0,
                    skin: 'layui-layer-molv',
                    shade: 0,
                    offset: 'rb',
                    area: '450px',
                    content: html,
                    btn: ['我知道了', '不再提示'],
                    yes: function(index, layero) {
                        // 回执
                        $.post('/site/messages', {tab: data['tab']}, function(e) {
                            layer.close(index);
                        });
                    },
                    btn2: function(index, layero) {
                        if(getCookie('offMessage') == '') {
                            setCookie('offMessage', 'yes');
                            layer.close(index);
                        }
                    }
                });
            }
        }, 'json');
    }

});

EOF;

$this->registerJs($js);
?>
