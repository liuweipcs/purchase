<?php
namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * PurchaseTacticsSearch represents the model behind the search form about `app\models\PurchaseTactics`.
 */
class PurchaseTacticsSearch extends PurchaseTactics
{
    public $warehouse_code;
    public $warehouse_list;
    public $percent_start;
    public $percent_end;
    public $stockup_days;
    public $service_coefficient;
    public $maximum;
    public $minimum;
    public $daily_sales;
    public $daily_sales_value;
    public $daily_sales_day;
    public $warehouse_list_select;
    public $incr_days;
    public $file_execl;
    public $type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tactics_name','warehouse_list','sku','tactics_type','warehouse_code','status','type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
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
        $query = PurchaseTactics::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        empty($this->tactics_type) AND $this->tactics_type = 1;// 默认展示 MRP补货策略

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'tactics_type' => $this->tactics_type,
            'sku' => $this->sku,
            'status' => $this->status,
        ]);

        if($this->warehouse_code){
            $subQuery = PurchaseTacticsWarehouse::find()
                ->select('tactics_id')
                ->where(['warehouse_code' => $this->warehouse_code]);

            $query->andWhere(['in','id',$subQuery]);
        }

        if($this->type){
            $subQuery = PurchaseTacticsSuggest::find()
                ->select('tactics_id')
                ->where(['type' => $this->type]);

            $query->andWhere(['in','id',$subQuery]);

        }

        $query->orderBy('id desc');

        return $dataProvider;
    }

    /**
     * 获取仓库列表
     * @return array
     */
    public static function getWarehouseList(){

        $warehouseList = Warehouse::find()->select('id,warehouse_name,warehouse_code')
            ->where('use_status=1')
            ->orderBy("convert(warehouse_name USING GB2312) ASC")
            ->createCommand()
            ->queryAll();
        $warehouseList = array_column($warehouseList,'warehouse_name','warehouse_code');

        return $warehouseList;
    }

}
