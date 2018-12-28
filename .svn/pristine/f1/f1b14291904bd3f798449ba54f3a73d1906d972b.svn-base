<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * PurchaseTacticsAbnormalSearch represents the model behind the search form about `app\models\PurchaseTactics`.
 */
class PurchaseTacticsAbnormalSearch extends PurchaseTacticsAbnormal
{
    /*
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_type'], 'integer'],
            [['sku', 'name', 'warehouse_code', 'warehouse_name', 'warehouse_type', 'supplier_name', 'supplier_code', 'buyer', 'reason', 'date_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
  /*  public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tactics_name' => '备货名称',
            'warehouse_list' => '适用仓库',
            'single_price' => '单价',
            'inventory_holdings' => '库存持有量',
            'reserved_max' => '保留最大值',
            'daily_days' => '销量平均值',
            'daily_value' => '比值',
            'creator' => '创建人',
            'created_at' => '创建时间',
            'updateor' => '更新人',
            'update_at' => '更新时间',
            'status' => '是否启用',
            'operation' => '操作',
        ];
    }
*/

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PurchaseTacticsAbnormal::find();
        //echo "<pre>";var_dump($params);exit;
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            //'id' => $this->id
            'warehouse_type' => $this->warehouse_type,
            'warehouse_code' => $this->warehouse_code,
            'supplier_code' => $this->supplier_code,
            'sku' => $this->sku,
        ]);



        $sql = $query->createCommand()->getSql();
       // var_dump($sql);die();


        return $dataProvider;
    }

    /**
     * 获取用户类型名称
     * @return array
     */
    public static function getWarehouseName($params){

        switch ($params) {
            case '1':
                $type = "国内仓";
                break;
            case '2':
                $type = "海外仓";
                break;
            case '3':
                $type = "第三方仓";
                break;    
            default:
                $type = '';
                break;
        }

        return $type;
    }

}
