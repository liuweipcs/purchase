<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\ArrayDataProvider;
use yii\web\NotFoundHttpException;

/**
 * WlClientRecieveInvoicesSearch represents the model behind the search form about `app\models\WlClientRecieveInvoices`.
 */
class StockSearch extends Stock
{
    public $buyer;
    public $product_line;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['sku','warehouse_code', 'state', 'purchas_status', 'sourcing_status', 'warn_status', 'suggest_note','buyer','product_line'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
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
        $query = Stock::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
               'pageSize' => 20,//要多少写多少吧
            ],
        ]);

        $query->where(['not in','warehouse_code',['DG','SZ_AA']]);
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
      $query->andFilterWhere([
            'sku' => trim($this->sku),
            'warehouse_code' => trim($this->warehouse_code),
        ]);


        return $dataProvider;
    }

    /**实时sku查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function search2($params)
    {
        $query = Stock::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
               'pageSize' => 20,//要多少写多少吧
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
       $query->andFilterWhere([
            'sku' => trim($this->sku),
            'warehouse_code' => $this->warehouse_code,
       ]);

        return $dataProvider;
    }
    /**
     * 海外仓-实时sku查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function search3($params)
    {
        $query = Stock::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
               'pageSize' => 20,//要多少写多少吧
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
       $query->andFilterWhere([
            'sku' => trim($this->sku),
            'warehouse_code' => $this->warehouse_code,
       ]);
        return $dataProvider;
    }
    /**
     * 国内仓-实时sku 查询（新版）
     */
    public function search5($params){
        if(empty($params) || (empty($params['StockSearch']['sku']) && empty($params['sku']))){
            $model           = new self;
            $model->scenario = 'timesku';
            $list            = [];
            $pages           = new Pagination(['totalCount' => count($list)]);
            return [
                'params'     => $params,
                'model'      => $model,
                'data'       => $list,
                'pagination' => $pages
            ];
        }
        if(!empty($params['sku'])){
            $sku = $params['sku'];
        }else{
            $sku = $params['StockSearch']['sku'];
        }
        $skus       = explode(' ', $sku);
        $skus       = array_filter($skus);
        $sku_string = Vhelper::getSqlArrayString($skus);
        $sql        = "SELECT
              poi.sku AS sku,
              poi.pur_number, -- 采购单号
              poi.name, -- 商品名称
              s.on_way_stock AS on_way_stock, -- 在途数量
              s.available_stock AS available_stock, -- 可用数量
              `s`.warehouse_code AS warehouse_code, -- 仓库编码
              w.warehouse_name AS warehouse_name,
              po.shipping_method AS shipping_method,
              po.created_at AS created_at,
              iadt.avg_delivery_time AS avg_delivery_time, -- sku的权限交期
            
              poi.qty AS qty, -- 建议采购数量
              poi.ctq AS ctq, -- 实际采购数量
              po.account_type, -- 结算方式（从供应商拉）
              op.pay_status, -- 付款状态
              po.creator AS creator, -- 创建人
              po.buyer AS buyer, -- 采购员
            
              ps.state AS state, -- 采购建议状态
              purchas_status, -- 到货状态
              sourcing_status, -- 货源状态
              warn_status, -- 预警状态
              po.audit_time AS audit_time, -- 采购单生成时间
              payer_time -- 付款时间
            FROM
                pur_purchase_order_items AS poi
            LEFT JOIN pur_purchase_order AS po ON (po.pur_number = poi.pur_number)
            LEFT JOIN pur_stock AS s on (s.sku=poi.sku AND s.warehouse_code=po.warehouse_code)
            LEFT JOIN pur_product_source_status AS pss on (`pss`.`sku` = `poi`.`sku`)
            LEFT JOIN pur_purchase_warning_status AS pws on (`pws`.`sku` = `poi`.`sku` AND pws.pur_number=poi.pur_number)
            LEFT JOIN pur_purchase_suggest ps ON (`ps`.`sku` = `poi`.`sku` AND ps.warehouse_code=s.warehouse_code)
            LEFT JOIN pur_purchase_order_pay op ON op.pur_number=poi.pur_number
            LEFT JOIN pur_warehouse w ON (w.warehouse_code=po.warehouse_code)
            LEFT JOIN pur_inland_avg_deliery_time iadt ON (`iadt`.`sku` = `poi`.`sku`)";
        if(!empty($params['StockSearch']['suggest_note'])){
            $sql .= " LEFT JOIN pur_purchase_suggest_note psn ON (`psn`.`sku` = `poi`.`sku` AND psn.warehouse_code=po.warehouse_code)";
        }

        //到货状态：针对sku
        if(!empty($params['StockSearch']['purchas_status'])){
            if($params['StockSearch']['purchas_status'] == 6){
                # 全到货
                $sql .= " LEFT JOIN pur_warehouse_results wr ON (`wr`.`sku` = `poi`.`sku` AND wr.pur_number=poi.pur_number AND wr.arrival_quantity=poi.ctq)";
            }elseif($params['StockSearch']['purchas_status'] == 5){
                # 部分到货
                $sql .= " LEFT JOIN pur_warehouse_results wr ON (`wr`.`sku` = `poi`.`sku` AND wr.pur_number=poi.pur_number AND wr.arrival_quantity<poi.ctq AND wr.arrival_quantity>0)";
            }else{
                # 等待到货
                $sql .= " LEFT JOIN pur_warehouse_results wr ON (`wr`.`sku` = `poi`.`sku` AND wr.pur_number=poi.pur_number)";
            }
        }
        $sql .= " WHERE (pss.`status`=1 OR ISNULL(pss.id)) and po.purchas_status not in (4,10)";

        //=======================================================
        //sku
        $sql .= " AND poi.sku in(".$sku_string.")";
        //采购类型
        if(!empty($params['StockSearch']['purchase_type'])){
            $sql .= " AND po.purchase_type={$params['StockSearch']['purchase_type']}";
        }
        //采购建议状态
        if(isset($params['StockSearch']['state'])){
            if(!empty($params['StockSearch']['state']) || $params['StockSearch']['state'] === '0'){
                $sql .= " AND ps.state={$params['StockSearch']['state']}";
            }
        }

        if(!empty($params['StockSearch']['purchas_status'])){
            if($params['StockSearch']['purchas_status'] == 6){
                # 全到货
                $sql .= " AND wr.id is not null AND wr.arrival_quantity=poi.ctq";
            }elseif($params['StockSearch']['purchas_status'] == 5){
                # 部分到货
                $sql .= " AND wr.id is not null";
            }else{
                # 等待到货
                $sql .= " AND (ISNULL(wr.id) OR wr.arrival_quantity=0)";
            }
        }

        //货源状态
        if(!empty($params['StockSearch']['sourcing_status']))
            $sql .= " AND sourcing_status={$params['StockSearch']['sourcing_status']}";
        //预警状态
        if(!empty($params['StockSearch']['warn_status']))
            $sql .= " AND warn_status={$params['StockSearch']['warn_status']}";

        //采购单生成时间
        if(!empty($params['StockSearch']['audit_time'])){
            $timeArray  = explode(' ~ ', $params['StockSearch']['audit_time']);
            $start_time = date('Y-m-d 00:00:00', strtotime(trim($timeArray[0])));
            $end_time   = date('Y-m-d 23:59:59', strtotime(trim($timeArray[1])));
            $sql        .= " AND (po.created_at BETWEEN '{$start_time}' AND '{$end_time}')";
        }
        //付款时间
        if(!empty($params['StockSearch']['payer_time'])){
            $timeArray  = explode(' ~ ', $params['StockSearch']['payer_time']);
            $start_time = date('Y-m-d 00:00:00', strtotime(trim($timeArray[0])));
            $end_time   = date('Y-m-d 23:59:59', strtotime(trim($timeArray[1])));
            $sql        .= " AND (op.payer_time BETWEEN '{$start_time}' AND '{$end_time}')";
        }
        //权均到货时间
        if(!empty($params['StockSearch']['date_eta'])){
            $timeArray  = explode(' ~ ', $params['StockSearch']['date_eta']);
            $start_time = date('Y-m-d 00:00:00', strtotime(trim($timeArray[0])));
            $end_time   = date('Y-m-d 23:59:59', strtotime(trim($timeArray[1])));
            $sql        .= " AND (FROM_UNIXTIME( IFNULL(UNIX_TIMESTAMP(po.audit_time), 0) + IFNULL(round(iadt.avg_delivery_time,0), 0) ,'%Y-%m-%d %H:%i:%s') BETWEEN '{$start_time}' AND '{$end_time}' )";
        }

        //是否超过权限交期
        if(!empty($params['StockSearch']['is_pass']) && $params['StockSearch']['is_pass'] == 1){
            //是
            $sql .= " AND ( (po.audit_time IS NOT NULL) AND ( (IFNULL(UNIX_TIMESTAMP(po.audit_time), 0)+IFNULL(round(iadt.avg_delivery_time,0), 0)) < IFNULL(UNIX_TIMESTAMP(wr.instock_date), unix_timestamp(now())) ) )";
        }elseif(!empty($params['StockSearch']['is_pass']) && $params['StockSearch']['is_pass'] == 2){
            //否
            $sql .= " AND ( (po.audit_time IS NULL) OR ( (po.audit_time IS NOT NULL) AND ( (IFNULL(UNIX_TIMESTAMP(po.audit_time), 0)+IFNULL(round(iadt.avg_delivery_time,0), 0)) >= IFNULL(UNIX_TIMESTAMP(wr.instock_date), unix_timestamp(now()) ) ) ) )";
        }
        //备注
        if(!empty($params['StockSearch']['suggest_note'])){

            $sql .= " AND suggest_note like '%".trim($params['StockSearch']['suggest_note'])."%' AND psn.status=1";
        }
        //仓库
        if(!empty($params['StockSearch']['warehouse_code']))
            $sql .= " AND s.warehouse_code IN(".Vhelper::getSqlArrayString($params['StockSearch']['warehouse_code']).')';

        //采购员
        if(isset($params['StockSearch']['buyer']) AND $params['StockSearch']['buyer']){
            $sql .= " AND po.buyer='{$params['StockSearch']['buyer']}'";
        }

        $sql .= ' ORDER BY po.created_at DESC';
        //===========================================================================

        $model           = new self();
        $model->scenario = 'timesku';
        $q               = Yii::$app->db->createCommand($sql)->queryAll();

        $pageSize = 20;
        if(!empty($params['pageSize']))
            $pageSize = $params['pageSize'];
        $pages = new Pagination(['totalCount' => count($q), 'pageSize' => $pageSize]);

        $list         = Yii::$app->db->createCommand($sql." limit ".$pages->limit." offset ".$pages->offset."")->queryAll();
        $dataprovider = new ArrayDataProvider(['allModels' => $list,]);

        return [
            'params'     => $params,
            'model'      => $model,
            'data'       => $list,
            'pagination' => $pages,
        ];
    }

}
