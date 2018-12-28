<?php

namespace app\models;
use Yii;
use app\config\Vhelper;

class PurchasePayForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%purchase_pay_form}}';
    }

    public function rules()
    {
        return [
            [[
                'compact_number',
                'pay_id',
                'pay_price',
                'fk_name',
                'supplier_name',
                'account',
                'payment_platform_branch',
                'tpl_id'
            ], 'required'],
            [[
                'status',
                'create_time',
                'payment_reason'
            ], 'safe']
        ];
    }
    public function attributeLabels()
    {
        return [
            'payment_platform_branch' => Yii::t('app', '收款方账户支行'),
            'account'=>Yii::t('app','收款方账户'),
        ];
    }

    // Get Payform Content As Html
    public static function getPayformHtml($control, $model)
    {
        $data = self::find()->where(['compact_number' => $model->compact_number])->all();
        if(empty($data)) {
            return null;
        }
        $cnt = [];
        foreach($data as $v) {
            $tpl = Template::findOne($v->tpl_id);
            $tplPath = $tpl->style_code;
            $cnt[$v->id] = $control->renderPartial("//template/tpls/{$tplPath}", ['model' => $v, 'print' => true]);
        }
        return $cnt;
    }

    public static function getPayForm($pay_id)
    {
        $id = self::find()->select('id')->where(['pay_id' => $pay_id, 'status' => 1])->scalar();
        if($id) {
            return $id;
        } else {
            return null;
        }
    }







}
