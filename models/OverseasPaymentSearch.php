<?php

namespace app\models;

use app\models\base\BaseModel;
use yii\data\ActiveDataProvider;

use Yii;
use app\models\PlatformSummary;
use app\models\Product;
use app\models\ProductProvider;
use yii\data\Pagination;
use app\synchcloud\models\PurchaseOrderPayType;

class OverseasPaymentSearch extends PurchaseOrderPay
{
    public $audit_level;
    public $pay_number;
    public $supplier_special_flag;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[], 'required'],
            [['requisition_number','pay_status','settlement_method','pay_type','applicant','audit_level','start_time','end_time','supplier_code','supplier_special_flag'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
        ];
    }

    public function search($params)
    {
        $this->load($params);
        $query = purchaseOrderPay::find()->alias('pay')
            ->leftJoin(SupplierContactInformation::tableName()." contact","contact.supplier_code = pay.supplier_code")
            ->leftJoin(PurchaseCompactItems::tableName()." pci", "pci.compact_number=pay.pur_number AND pci.bind=1")
            ->leftJoin(PurchaseOrder::tableName()." po", "po.pur_number=pay.pur_number OR po.pur_number=pci.pur_number")
            //->leftJoin(SupplierPaymentAccount::tableName()." account","account.supplier_code = pay.supplier_code")
            ->where("pay.pur_number like 'ABD%'")
            ->andWhere(['=', 'po.is_new', 1])  //是否是新系统采购单
            ->andWhere(['not in', 'pay.pay_status', [0]]);
        $query->andWhere("contact.contact_id=(SELECT MIN(contact_id) FROM pur_supplier_contact_information AS tmp WHERE tmp.supplier_code=contact.supplier_code)");// 只查询最后一条 供应商联系方式表（避免记录重复）
        $query->groupBy(['pay.pur_number','pay.requisition_number']);
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 10;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        //account.account,account.account_name,account.payment_platform,account.payment_platform_branch
        $query->select('pay.*,contact.contact_person,contact.contact_number');
        if (empty($this->start_time)) {
            //$this->start_time = date('Y-m-d', time() - 86400*180).' 00:00:00';
            $this->start_time = '2018-08-29 10:00:00';
            $this->end_time = date('Y-m-d', time() + 86400).' 00:00:00';
        }
        $query->andFilterWhere(['between', 'pay.application_time', $this->start_time, $this->end_time]);
        
        if ($this->audit_level) {//弃用
            $checkprice = OverseasCheckPriv::getOverseasCheckPirce(3);
            if ($this->audit_level == 1) {
            }
        }
        
        if (empty($params['sort'])) {
            $query->orderBy('pay.id desc');
        }
        //echo $this->supplier_code;exit;
        $query->andFilterWhere([
            'pay.requisition_number' => $this->requisition_number,
            'pay.pay_status' => $this->pay_status,
            'pay.settlement_method' => $this->settlement_method,
            'pay.pay_type' => $this->pay_type,
            'pay.applicant' => $this->applicant,
            'pay.supplier_code' => $this->supplier_code,
        ]);

        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }

        return $dataProvider;
    }
    
    public static function getOrderInfo($pur_number, $field)
    {
        if (strpos($pur_number,'HT') !== false) {
            $pur_number = PurchaseCompactItems::find()->where(['bind'=>1,'compact_number'=>$pur_number])->select('pur_number')->scalar();
        }
        return PurchaseOrder::find()->where(['pur_number'=>$pur_number])->select($field)->scalar();
    }
    
    public static function getOrderPayTypeInfo($pur_number, $field)
    {
        if (strpos($pur_number,'HT') !== false) {
            $pur_number = PurchaseCompactItems::find()->where(['bind'=>1,'compact_number'=>$pur_number])->select('pur_number')->scalar();
        }
        return PurchaseOrderPayType::find()->where(['pur_number'=>$pur_number])->select($field)->scalar();
    }
}
