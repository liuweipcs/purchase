<?php

namespace app\models;

use app\models\base\BaseModel;

use yii\helpers\ArrayHelper;

class LargeWarehouse extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%large_warehouse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id','name'], 'required'],
            [['id','category_id','create_by','create_by','modify_by','is_delete','is_push'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => '分类id',
            'name' => '大仓名称',
            'description' => '描述',
            'create_by' => '创建人ID',
            'create_time' => '创建时间',
            'modify_by' => '修改人ID',
            'modify_time' => '修改时间',
            'is_delete' => '是否删除',
            'is_push' => '是否推送'
        ];
    }

    public  static  function  getWarehouseCode($id=null)
    {
        $Warehouse   = self::find()->select('category_id,name');

        if (!empty($id))
        {
            $Warehouse->andWhere(['id'=>$id]);
            $result = $Warehouse->asArray()->one();
            return $result['warehouse_name'];
        } else {

            $Warehouse= $Warehouse->asArray()->all();
            $result = ArrayHelper::map($Warehouse,'category_id','name');
            return $result;
        }
    }
}
