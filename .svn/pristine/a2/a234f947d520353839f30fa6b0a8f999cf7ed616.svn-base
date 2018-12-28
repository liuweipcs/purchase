<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%product_tax_rate}}".
 *
 * @property string $id
 * @property string $sku
 * @property double $tax_rate
 * @property double $ticketed_point
 * @property string $update_user
 * @property string $create_time
 * @property string $update_time
 */
class ProductTaxRate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_tax_rate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tax_rate', 'ticketed_point'], 'number'],
            [['create_time', 'update_time'], 'safe'],
            [['sku'], 'string', 'max' => 100],
            [['update_user'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'tax_rate' => 'Rebate Tax Rate',
            'ticketed_point' => 'Ticketed Point',
            'update_user' => 'Update User',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
    /**
     * 获取出口退税税率
     */
    public static function getRebateTaxRate($sku)
    {
        $tax_rate =  self::find()->select('tax_rate')->where(['sku'=>$sku])->scalar();
        return (!empty($tax_rate) ? $tax_rate : '') . '%';
    }
    /**
     * 获取采购开票点（税点）
     */
    public static function getTicketedPoint($sku)
    {
        $ticketed_point =  self::find()->select('ticketed_point')->where(['sku'=>$sku])->scalar();
        return !empty($ticketed_point)?$ticketed_point:0;
    }
    /**
     * 新增和修改数据
     */
    public static function insertUpdateProductTaxTate($datas=null)
    {
//        $datas = Yii::$app->request->post()['results'];
        if (isset($datas) && !empty($datas)) {
//            $datas = Json::decode($datas);
            foreach($datas as $k=>$v){
                $model = self::find()->where(['sku'=>$v['sku']])->one();

                //如果存在税率，就更新
                if ($model) {
                    $status = self::saveProductTaxTate($model,$v,1);
                } else { //如果不存在税率，就新增
                    $model =new self;
                    $status = self::saveProductTaxTate($model,$v);
                }
            }
            return $status;
        } else {
            return '没有任何的数据传输过来！';
        }
    }
    /**
     * 新增和修改数据
     */
    public static function saveProductTaxTate($model, $v,$exist=null)
    {
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            //如果系统中存在
            if (!empty($exist)) {
                $model->ticketed_point = isset($v['ticketed_point'])?$v['ticketed_point']:null; //采购开票点（税点）
                $model->update_user = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:null; //修改税点人（最新的）
                $model->update_time = date('Y-m-d H:i:s',time());  //修改时间
            } else {
                $model->sku = $v['sku'];
                $model->ticketed_point = isset($v['ticketed_point'])?$v['ticketed_point']:null; //采购开票点（税点）
                $model->create_time = date('Y-m-d H:i:s',time()); //开发时间
            }
            $status =$model->save(false);

            $transaction->commit();
            return $status;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }
}
