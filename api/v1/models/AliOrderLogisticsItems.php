<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use app\models\PurchaseOrderPayType;
use Yii;

/**
 * This is the model class for table "pur_ali_order_logistics_items".
 *
 * @property integer $id
 * @property string $delivered_time
 * @property string $logistics_code
 * @property integer $type
 * @property integer $logistics_id
 * @property string $status
 * @property string $gmt_modified
 * @property string $gmt_create
 * @property string $carriage
 * @property string $from_province
 * @property string $from_city
 * @property string $from_area
 * @property string $from_address
 * @property string $from_phone
 * @property string $from_mobile
 * @property string $from_post
 * @property string $logistics_company_id
 * @property string $logistics_company_no
 * @property string $logistics_company_name
 * @property string $logistics_bill_no
 * @property string $sub_item_ids
 * @property string $to_province
 * @property string $to_city
 * @property string $to_area
 * @property string $to_address
 * @property string $to_phone
 * @property string $to_mobile
 * @property string $to_post
 * @property string $pur_number
 * @property string $order_number
 */
class AliOrderLogisticsItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_logistics_items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'logistics_id'], 'integer'],
            [['carriage'], 'number'],
            [['sub_item_ids'], 'string'],
            [['delivered_time', 'logistics_code', 'gmt_modified', 'gmt_create', 'from_post', 'logistics_company_no', 'logistics_company_name', 'logistics_bill_no', 'to_post', 'order_number'], 'string', 'max' => 100],
            [['status', 'from_province', 'from_city', 'from_area', 'from_phone', 'from_mobile', 'logistics_company_id', 'to_province', 'to_city', 'to_area', 'to_phone', 'to_mobile', 'pur_number'], 'string', 'max' => 50],
            [['from_address', 'to_address'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'delivered_time' => 'Delivered Time',
            'logistics_code' => 'Logistics Code',
            'type' => 'Type',
            'logistics_id' => 'Logistics ID',
            'status' => 'Status',
            'gmt_modified' => 'Gmt Modified',
            'gmt_create' => 'Gmt Create',
            'carriage' => 'Carriage',
            'from_province' => 'From Province',
            'from_city' => 'From City',
            'from_area' => 'From Area',
            'from_address' => 'From Address',
            'from_phone' => 'From Phone',
            'from_mobile' => 'From Mobile',
            'from_post' => 'From Post',
            'logistics_company_id' => 'Logistics Company ID',
            'logistics_company_no' => 'Logistics Company No',
            'logistics_company_name' => 'Logistics Company Name',
            'logistics_bill_no' => 'Logistics Bill No',
            'sub_item_ids' => 'Sub Item Ids',
            'to_province' => 'To Province',
            'to_city' => 'To City',
            'to_area' => 'To Area',
            'to_address' => 'To Address',
            'to_phone' => 'To Phone',
            'to_mobile' => 'To Mobile',
            'to_post' => 'To Post',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
        ];
    }

    public static function saveData($pur_number,$order_number,$data){
        self::updateAll(['items_status'=>0],['pur_number'=>$pur_number,'order_number'=>$order_number]);
        $insertData=[];
        foreach ($data as $k=>$v){
            $insertData[$k][] = isset($v['deliveredTime'])        ? Vhelper::getAliDateTime($v['deliveredTime'])        : '';
            $insertData[$k][] = isset($v['logisticsCode'])        ? $v['logisticsCode']        : '';
            $insertData[$k][] = isset($v['type'])                 ? $v['type']                 : '';
            $insertData[$k][] = isset($v['id'])                   ? $v['id']                   : '';
            $insertData[$k][] = isset($v['status'])               ? $v['status']               : '';
            $insertData[$k][] = isset($v['gmtModified'])          ? Vhelper::getAliDateTime($v['gmtModified'])          : '';
            $insertData[$k][] = isset($v['gmtCreate'])            ? Vhelper::getAliDateTime($v['gmtCreate'])            : '';
            $insertData[$k][] = isset($v['carriage'])             ? $v['carriage']             : '';
            $insertData[$k][] = isset($v['fromProvince'])         ? $v['fromProvince']         : '';
            $insertData[$k][] = isset($v['fromCity'])             ? $v['fromCity']             : '';
            $insertData[$k][] = isset($v['fromArea'])             ? $v['fromArea']             : '';
            $insertData[$k][] = isset($v['fromAddress'])          ? $v['fromAddress']          : '';
            $insertData[$k][] = isset($v['fromPhone'])            ? $v['fromPhone']            : '';
            $insertData[$k][] = isset($v['fromMobile'])           ? $v['fromMobile']           : '';
            $insertData[$k][] = isset($v['fromPost'])             ? $v['fromPost']             : '';
            $insertData[$k][] = isset($v['logisticsCompanyId'])   ? $v['logisticsCompanyId']   : '';
            $insertData[$k][] = isset($v['logisticsCompanyNo'])   ? $v['logisticsCompanyNo']   : '';
            $insertData[$k][] = isset($v['logisticsCompanyName']) ? $v['logisticsCompanyName'] : '';
            $insertData[$k][] = isset($v['logisticsBillNo'])      ? $v['logisticsBillNo']      : '';
            $insertData[$k][] = isset($v['subItemIds'])           ? $v['subItemIds']           : '';
            $insertData[$k][] = isset($v['toProvince'])           ? $v['toProvince']           : '';
            $insertData[$k][] = isset($v['toCity'])               ? $v['toCity']               : '';
            $insertData[$k][] = isset($v['toArea'])               ? $v['toArea']               : '';
            $insertData[$k][] = isset($v['toAddress'])            ? $v['toAddress']            : '';
            $insertData[$k][] = isset($v['toPhone'])              ? $v['toPhone']              : '';
            $insertData[$k][] = isset($v['toMobile'])             ? $v['toMobile']             : '';
            $insertData[$k][] = isset($v['toPost'])               ? $v['toPost']               : '';
            $insertData[$k][] = $pur_number;
            $insertData[$k][] = $order_number;
            if(!empty($v['logisticsCompanyName'])&&!empty($v['logisticsBillNo'])){
                $saveResponse = PurchaseOrderShip::saveData($pur_number,$v['logisticsCompanyName'],$v['logisticsBillNo']);
                if($saveResponse==false){
                    return false;
                }else{
                    Yii::$app->db->createCommand()->update(PurchaseOrderPayType::tableName(),['is_success'=>1,'check_date'=>date('Y-m-d H:i:s',time())],
                        ['is_success'=>0,'pur_number'=>$pur_number])->execute();
                    //记录标记成功日志
                    $logDatas = [
                        'pur_number'=>$pur_number,
                        'order_number'=>$order_number,
                        'message'=>'物流信息保存成功，标记完成',
                        'error_code'=>'logistics_save_success',
                    ];
                    AliOrderLog::saveSuccessLog($logDatas);
                }
            }
        }

       Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            ['delivered_time','logistics_code','type', 'logistics_id', 'status', 'gmt_modified',
            'gmt_create','carriage','from_province','from_city','from_area','from_address','from_phone','from_mobile','from_post',
            'logistics_company_id','logistics_company_no','logistics_company_name','logistics_bill_no','sub_item_ids','to_province',
            'to_city','to_area','to_address','to_phone','to_mobile','to_post','pur_number','order_number'
            ],$insertData)->execute();
    }
}
