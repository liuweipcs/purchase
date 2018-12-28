<?php
use backend\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
if (class_exists('backend\assets\AppAsset')) {
    app\assets\AppAsset::register($this);
} else {
    app\assets\AppAsset::register($this);
}
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
dmstr\web\AdminLteAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php
if( Yii::$app->getSession()->hasFlash('success') ) {
    echo \yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-success', //这里是提示框的class
        ],
        'body' => Yii::$app->getSession()->getFlash('success'), //消息体
    ]);
}
if( Yii::$app->getSession()->hasFlash('error') ) {
    echo \yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-error',
        ],
        'body' => Yii::$app->getSession()->getFlash('error'),
    ]);
}?>

<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
