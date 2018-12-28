<?php

/* @var $this yii\web\View */

$this->title = Yii::t('app','易佰采购云系统');
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\BulletinBoard;
use app\models\PurchaseAbnormals;
$Bull=BulletinBoard::find()->limit(4)->orderBy('id desc')->all();
$Abnormal=PurchaseAbnormals::find()->where(['status'=>1])->limit(4)->orderBy('id desc')->all();
?>

<div class="site-index">



    <div class="body-content">

        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><span class="glyphicon glyphicon-heart" aria-hidden="true"></span></h3>

                        <p>我们一心一意的采购</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><span class="glyphicon glyphicon-globe" aria-hidden="true"></span></h3>

                        <p>我们的客户遍布全球</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><span class="glyphicon glyphicon-plane" aria-hidden="true"></span></h3>

                        <p>我们的物流风驰电掣</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><span class="glyphicon glyphicon-yen" aria-hidden="true"></span></h3>

                        <p>每天进账易佰亿</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>

    </div>
   <div class="panel panel-primary pull-left" style="width: 100%">
                <h3>温馨提示:为了区分采购单号,即国内采购以<b style="color: red">PO</b>打头,海外以<b style="color: red">ABD</b>打头,FBA以<b style="color: red">FBA</b>打头</h3>
    </div>
    <div class="panel panel-primary pull-left" style="width: 100%; color: red;">
        <?php $headlines = BulletinBoard::getBulletinBoardHeadlines();
            if (!empty($headlines)) {
        ?>
                <h3><b>【头条】<a href="<?= Url::toRoute(['bulletin-board/view', 'id' =>$headlines->id])?>" style="color: red"><?=$headlines->title ?></a></b></h3>
        <?php }?>
    </div>
    <div class="panel panel-primary pull-left" style="width: 49%;margin-right: 2%">
        <div class="panel-heading">我的个人信息</div>
        <div class="panel-body">
            <ul class="list-group">
                <li class="list-group-item"> <h3 style="display: inline"><?=Yii::$app->user->identity->username?>,欢迎您登录,推荐使用<b style="color: red">google</b>浏览器,点击<b style="color: red">右上角名字</b>可以修改密码</h3></li>
                <li class="list-group-item"><?php

                    $s =Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);
                    foreach($s as $v){
                        echo '所属角色:'.'['.$v->name.']';
                    }
                    ?></li>
                <li class="list-group-item"><?=Yii::t('app','当前时间:')?><?=date('Y-m-d H:i:s',time())?></li>
                <li class="list-group-item"><?=Yii::t('app','当前IP:')?><?=Yii::$app->request->userIP?></li>

            </ul>

        </div>
    </div>
    <div class="panel panel-primary pull-left" style="width: 49%">
        <div class="panel-heading">系统开发团队</div>
        <div class="panel-body">
            <ul class="list-group">
                <li class="list-group-item">版权所有：深圳市易佰网络科技有限公司</li>
                <li class="list-group-item">团队成员：以前团队成员:<?=Yii::$app->params['before']?>;现任团队成员:<?=Yii::$app->params['just_now']?></li>
                <li class="list-group-item">UI:感谢Bootstrap提供前端驱动</li>
                <li class="list-group-item">官方网站:http://www.yibainetwork.com/ </li>

            </ul>
        </div>
    </div>
    <div class="panel panel-primary pull-left" style="width: 49%;margin-right: 2%">
        <div class="panel-heading">公告栏<span class="label label-default pull-right"><a href="<?= Url::toRoute(['bulletin-board/index'])?>">更多</a></span></div>
        <div class="panel-body">
            <ul class="list-group">
                <?php foreach($Bull as $k=>$v){
                    ?>
                <li class="list-group-item"><a href="<?= Url::toRoute(['bulletin-board/view', 'id' =>$v->id])?>"><span class="badge btn-success"><?=$k+1?></span><?=$v->title?></a></li>

                <?php }?>
            </ul>
        </div>
    </div>
    <div class="panel panel-primary pull-left" style="width: 49%;">
        <div class="panel-heading">采购单异常</div>
        <div class="panel-body">
            <ul class="list-group">
                <?php foreach($Abnormal as $k=>$v){
                    if($v->type==1)
                    {
                        $url = Url::toRoute(['purchase-receive/index']);
                    } elseif($v->type==2){
                        $url = Url::toRoute(['purchase-qc/index']);
                    } else {
                        $url = Url::toRoute(['purchase-abnomal/index']);
                    }
                    ?>
                    <li class="list-group-item"><a href="<?=$url?>"><span class="badge btn-danger"><?=$k+1?></span><?=$v->title?></a></li>

                <?php }?>

            </ul>
        </div>
    </div>

</div>
<div style="clear: both;"></div>

