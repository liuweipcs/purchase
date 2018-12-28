<?php

namespace app\config\components\Log;

use Yii;
use yii\helpers\Url;
use app\models\PurchaseLog;
class PurchasesLog
{

    /**
     * 采购单日志
     * @param $event
     */
    public static function write($event)
    {
        // 具体要记录什么东西，自己来优化$description
        if(!empty($event->changedAttributes)) {
            $desc = '';
            foreach($event->changedAttributes as $name => $value) {
                $desc .= $name . ' : ' . $value . '=>' . $event->sender->getAttribute($name) . ',';
            }
            $desc = substr($desc, 0, -1);
            $description = Yii::$app->user->identity->username . '修改了' . $event->sender->className() . 'id:' . $event->sender->id . '的' . $desc;
            $route = Url::to();
            $userId = Yii::$app->user->id;
            $data = [
                'route' => $route,
                'description' => $description,
                'user_id' => $userId
            ];
            $model = new PurchaseLog();
            $model->setAttributes($data);
            $model->save();
        }
    }

}