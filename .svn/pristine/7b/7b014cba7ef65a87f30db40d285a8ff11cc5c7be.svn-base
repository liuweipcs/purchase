<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use Yii;
use yii\filters\VerbFilter;

/**
 * This is the model class for table "{{%supplier_update_log}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_name
 * @property string $supplier_name
 * @property string $supplier_code
 * @property string $action
 * @property string $message
 * @property string $create_time
 * @property string $purchase_audit
 * @property string $purchase_note
 * @property string $purchase_time
 * @property string $supply_chain_audit
 * @property string $supply_chain_note
 * @property string $supply_chain_time
 * @property string $finance_audit
 * @property string $finance_note
 * @property string $finance_time
 * @property integer $audit_status
 */
class SupplierUpdateLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_update_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'audit_status'], 'integer'],
            [['message'], 'string'],
            [['create_time', 'purchase_time', 'supply_chain_time', 'finance_time'], 'safe'],
            [['user_name', 'supplier_name', 'supplier_code', 'action', 'purchase_audit', 'purchase_note', 'supply_chain_audit', 'supply_chain_note', 'finance_audit', 'finance_note'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'supplier_name' => 'Supplier Name',
            'supplier_code' => 'Supplier Code',
            'action' => 'Action',
            'message' => 'Message',
            'create_time' => 'Create Time',
            'purchase_audit' => 'Purchase Audit',
            'purchase_note' => 'Purchase Note',
            'purchase_time' => 'Purchase Time',
            'supply_chain_audit' => 'Supply Chain Audit',
            'supply_chain_note' => 'Supply Chain Note',
            'supply_chain_time' => 'Supply Chain Time',
            'finance_audit' => 'Finance Audit',
            'finance_note' => 'Finance Note',
            'finance_time' => 'Finance Time',
            'audit_status' => 'Audit Status',
        ];
    }

    /**
     * 更新供应商 保存信息
     * @param array $supplier_update_log_info
     * @param null $userId ERP链接页面调用才需要设置
     * @return bool|int
     */
    public static  function saveSupplierUpdateLog($supplier_update_log_info,$userId=null)
    {
        $models = self::find()
            ->where(['supplier_code'=>$supplier_update_log_info['supplier_code']])
            ->andWhere(['in','audit_status',['1','3','5']])
            ->one();

        if ($models) {

            return 0;
        } else {
            if(!empty($userId)){
                $addUser = User::find()->where(['id'=>$userId])->one();
            }
            $model = new self;
            $model->user_id = !empty($userId)&&$addUser ? $addUser->id : Yii::$app->user->id;
            $model->user_name = !empty($userId)&&$addUser ? $addUser->username : Yii::$app->user->identity->username;
            $model->supplier_name  = $supplier_update_log_info['supplier_name'];
            $model->supplier_code  = $supplier_update_log_info['supplier_code'];
            $model->action  = $supplier_update_log_info['action'];
            $model->message = $supplier_update_log_info['message'];
            $model->create_time = date('Y-m-d H:i:s',time());

            $message = json_decode($supplier_update_log_info['message'],true);
//            $new_arr = $message['supplier_payment_account_update']['new'];
//            $old_arr = $message['supplier_payment_account_update']['old'];
//            $insert_array = $message['supplier_payment_account_insert'];
//            Vhelper::dump($new_arr,$old_arr);


            //是否修改银行信息
            if (isset($message['supplier_payment_account_update'])) {

                $old = $message['supplier_payment_account_update']['old'];
                $new = $message['supplier_payment_account_update']['new'];
                foreach ($old as $ok => $ov) {
                    foreach ($ov as $k => $v) {
                        //修改
                        if ($new[$ok][$k] != $v) {
                            if ($k == 'account' || $k == 'account_name') {
                                $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                                if (count($roles) == 1) {
                                    //采购组提交修改：待采购审核
                                    if (in_array('采购组-海外', array_keys($roles)) || in_array('FBA采购组', array_keys($roles)) || in_array('采购组-国内', array_keys($roles))) {
                                        $model->audit_status = 1;
                                    } else {
                                        //其它提交修改：待供应链审核
                                        $model->audit_status = 3;
                                    }
                                } else {
                                    //如果提交人属于多个角色：待供应链审核
                                    $model->audit_status = 3;
                                }
                            }
                        }
                    }
                }
                //没有修改银行信息：待供应链审核
                if (empty($model->audit_status)) {
                    $model->audit_status = 3;
                }
            }

            if (isset($message['supplier_payment_account_insert'])) {
                foreach ($message['supplier_payment_account_insert'] as $ik => $iv) {
                    foreach ($iv as $kk => $vv) {
                        if ($kk == 'account' || $kk == 'account_name') {
                            if (!empty($vv)) {
                                //新增
                                $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                                if (count($roles) == 1) {
                                    if (in_array('采购组-海外', array_keys($roles)) || in_array('FBA采购组', array_keys($roles)) || in_array('采购组-国内', array_keys($roles))) {
                                        $model->audit_status = 1;
                                    } else {
                                        $model->audit_status = 3;
                                    }
                                } else {
                                    $model->audit_status = 3;
                                }
                            }
                        }
                    }
                }
                //没有修改银行信息：待供应链审核
                if (empty($model->audit_status)) {
                    $model->audit_status = 3;
                }

                if (!isset($message['supplier_payment_account_insert']) && !isset($message['supplier_payment_account_update'])) {
                    $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                    if (count($roles) == 1) {
                        if (in_array('采购组-海外', array_keys($roles)) || in_array('FBA采购组', array_keys($roles)) || in_array('采购组-国内', array_keys($roles))) {
                            $model->audit_status = 1;
                        } else {
                            $model->audit_status = 3;
                        }
                    } else {
                        $model->audit_status = 3;
                    }
                }
            }

            $status = $model->save(false);

            if($status){
                $result_data = [
                    'res_type' => 2,
                    'res_source' => !empty($userId) ? 1 : 2,
                    'related_id' => $model->id,
                    'supplier_code' => $model->supplier_code,
                    'supplier_name' => $model->supplier_name,
                ];
                SupplierAuditResults::addOneResult($result_data);
            }

            return $status;
        }

    }
    /**
     * 更新审核的值
     */
    public static function updateSupplierUpdateLog($model,$audit_status=1,$audit_note=null,$type = 'purchase')
    {
        $model->audit_status = $audit_status;
        if ($type=='purchase') {
            $model->purchase_note = $audit_note;
            $model->purchase_audit = Yii::$app->user->identity->username;
            $model->purchase_time = date('Y-m-d H:i:s', time());
        } elseif ($type == 'supply_chain') {
            $model->supply_chain_note = $audit_note;
            $model->supply_chain_audit = Yii::$app->user->identity->username;
            $model->supply_chain_time = date('Y-m-d H:i:s', time());
        } elseif ($type == 'finance') {
            $model->finance_note = $audit_note;
            $model->finance_audit = Yii::$app->user->identity->username;
            $model->finance_time = date('Y-m-d H:i:s', time());
        }
        $status = $model->save();
        return $status;
    }

    /**
     * 修改供应商信息
     */
    public static function updateSupplierInfo($supplier_code)
    {
        $model = SupplierUpdateLog::find()
            ->where(['supplier_code'=>$supplier_code])
            ->andWhere(['in','audit_status',['3','5']])
            ->one();
        $message = json_decode($model->message,true);

        //供应商信息 -- 修改  ok ok ok
        if (!empty($message['supplier'])) {
            $supplier_info = $message['supplier']['new'];
            $supplier_count = Yii::$app->db->createCommand()->update('pur_supplier', $supplier_info, 'id = ' . $supplier_info['id'])->execute();
        }

        $flagSupplierModify = false; //表示供应商是否被修改
        //供应商支付帐号表 -- 修改 ok ok
        if (!empty($message['supplier_payment_account_update'])) {
            $supplier_payment_account_info = $message['supplier_payment_account_update']['new'];
            foreach ($supplier_payment_account_info as $k => $v) {
                $supplier_payment_account_model = SupplierPaymentAccount::find()->where(['pay_id'=>$v['pay_id']])->one();
                $supplier_payment_account_model->payment_platform_bank = $v['payment_platform_bank'];
                $supplier_payment_account_model->payment_method = $v['payment_method'];
                $supplier_payment_account_model->payment_platform = empty($v['payment_platform']) ? 0 :$v['payment_platform'];
                $supplier_payment_account_model->payment_platform_branch = $v['payment_platform_branch'];
                $supplier_payment_account_model->account = $v['account'];
                $supplier_payment_account_model->account_name = $v['account_name'];
                $supplier_payment_account_model->account_type = isset($v['account_type']) ? $v['account_type'] :1;
                $supplier_payment_account_model->prov_code = isset($v['prov_code']) ? $v['prov_code'] :'';
                $supplier_payment_account_model->city_code = isset($v['city_code']) ? $v['city_code'] :'';
                $supplier_payment_account_model->id_number = isset($v['id_number']) ? $v['id_number'] :'';
                $supplier_payment_account_model->phone_number = isset($v['phone_number']) ? $v['phone_number'] :'';
                $supplier_payment_account_model->validate();
                $status = $supplier_payment_account_model->save();
                $flagSupplierModify = true;
            }
        }
        //支付账号列表删除
        if (!empty($message['supplier_payment_account_delete'])) {
            foreach ($message['supplier_payment_account_delete'] as $k => $v) {
                $supplier_payment_account_model = SupplierPaymentAccount::find()->where(['pay_id'=>$v['pay_id']])->one();
                $supplier_payment_account_model->status = 2;
                $deletestatus = $supplier_payment_account_model->save();
                $flagSupplierModify = true;
            }
        }
        // 联系方式列表删除
        if (!empty($message['supplier_contact_delete'])){
            $supplier_contact_delete = explode(',',$message['supplier_contact_delete']);
            foreach($supplier_contact_delete as $contact_id){
                $contact_model = SupplierContactInformation::findOne(['contact_id' => $contact_id]);
                //保存旧的数据
                $change_data = [
                    'table_name' => 'pur_supplier_contact_information', //变动的表名称
                    'change_type' => '3', //变动类型(1insert，2update，3delete)
                    'change_content' => json_encode($contact_model->attributes), //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $contact_model->delete();
            }
        }

        //供应商联系方式 -- 修改 ok ok
        if (!empty($message['supplier_contact_information_update'])) {
            $supplier_contact_information_info = $message['supplier_contact_information_update']['new'];
            foreach ($supplier_contact_information_info as $k=>$v) {
                $supplier_contact_information_model = SupplierContactInformation::find()->where(['contact_id'=>$v['contact_id']])->one();
                if(empty($supplier_contact_information_model)){
                    continue;
                }
                $supplier_contact_information_model->supplier_id = $v['supplier_id'];
                $supplier_contact_information_model->contact_person = $v['contact_person'];
                $supplier_contact_information_model->corporate = isset($v['corporate']) ? $v['corporate'] :'';
                $supplier_contact_information_model->contact_number = $v['contact_number'];
                $supplier_contact_information_model->contact_fax = $v['contact_fax'];
                $supplier_contact_information_model->chinese_contact_address = $v['chinese_contact_address'];
                $supplier_contact_information_model->english_address = $v['english_address'];
                $supplier_contact_information_model->contact_zip = $v['contact_zip'];
                $supplier_contact_information_model->qq = $v['qq'];
                $supplier_contact_information_model->micro_letter = $v['micro_letter'];
                $supplier_contact_information_model->email = isset($v['email']) ? $v['email'] : '';
                $supplier_contact_information_model->want_want = $v['want_want'];
                $supplier_contact_information_model->skype = $v['skype'];
                $supplier_contact_information_model->supplier_code = $v['supplier_code'];
                $supplier_contact_information_model->sex = $v['sex'];
                $supplier_contact_information_model->validate();
                $supplier_contact_information_status = $supplier_contact_information_model->save();
            }
        }
        //保存采购员 -- 修改/新增 ok ok ok
        if(!empty($message['supplier_buyer'])) {
            $supplier_buyer_info = $message['supplier_buyer']['new'];

            foreach ($supplier_buyer_info as $v) {
                //SupplierBuyer::updateAll(['status'=>2],['supplier_code'=>$v['supplier_code']]);
                if (!empty($v['id'])) {
                    $supplier_buyer_model = SupplierBuyer::find()->where(['id' => $v['id']])->one();
                } else {
                    $supplier_buyer_model = new SupplierBuyer();
                }
                $supplier_buyer_model->type = $v['type'];
                $supplier_buyer_model->supplier_code = $v['supplier_code'];
                $supplier_buyer_model->buyer = $v['buyer'];
                $supplier_buyer_model->status = $v['status'];
                $supplier_buyer_model->supplier_name = $v['supplier_name'];
                $supplier_buyer_status = $supplier_buyer_model->save(false);
            }
        }

        //产品线 -- 修改/新增 ok ok ok
        if(!empty($message['supplier_product_line'])) {
            $supplier_product_line_info = $message['supplier_product_line']['supplier_product_line'];

            foreach ($supplier_product_line_info as $v) {
                $supplierLine = SupplierProductLine::find()->where(['supplier_code'=>$v['supplier_code']])->all();
                if(!empty($supplierLine)){
                    SupplierProductLine::updateAll(['status'=>2],['supplier_code'=>$v['supplier_code']]);
                }

                if(!isset($v['first_product_line'])||empty($v['first_product_line'])){
                    continue;
                }

                $supplier_product_line_model = new SupplierProductLine();
                $supplier_product_line_model->supplier_code = $v['supplier_code'];
                $supplier_product_line_model->first_product_line = $v['first_product_line'];
                $supplier_product_line_model->second_product_line = empty($v['second_product_line']) ? '' : $v['second_product_line'];
                $supplier_product_line_model->third_product_line = empty($v['third_product_line']) ? '' : $v['third_product_line'];

                $supplier_product_line_status = $supplier_product_line_model->save();
            }
        }

        if(!empty($message['supplier_product_line_insert'])) {
            $supplier_product_line_info = $message['supplier_product_line_insert'];

            foreach ($supplier_product_line_info as $v) {
                if(!isset($v['first_product_line'])||empty($v['first_product_line'])) {
                    continue;
                }

                $supplier_product_line_model = new SupplierProductLine();
                $supplier_product_line_model->supplier_code = $v['supplier_code'];
                $supplier_product_line_model->first_product_line = $v['first_product_line'];
                $supplier_product_line_model->second_product_line = empty($v['second_product_line']) ? '' : $v['second_product_line'];
                $supplier_product_line_model->third_product_line = empty($v['third_product_line']) ? '' : $v['third_product_line'];
                $supplier_product_line_model->status =1;

                $supplier_product_line_status = $supplier_product_line_model->save();
            }
        }
        if(!empty($message['supplier_product_line_delete'])) {
            $supplier_product_line_info = $message['supplier_product_line_delete'];

            foreach ($supplier_product_line_info as $v) {
                if(!isset($v['first_product_line'])||empty($v['first_product_line'])) {
                    continue;
                }
                $supplier_product_line_model = SupplierProductLine::find()->where(
                    [
                        'first_product_line'=>$v['first_product_line'],
                        'second_product_line'=>isset($v['second_product_line']) ? $v['second_product_line'] :'',
                        'third_product_line'=>$v['third_product_line'],
                        'supplier_code'=>$v['supplier_code'],
                        'status'=>1
                    ]
                )->one();
                if(empty($supplier_product_line_model)){
                    continue;
                }
                $supplier_product_line_model->status =2;

                $supplier_product_line_status = $supplier_product_line_model->save();
            }
        }

        //==================================== 新增 =======================
        //供应商联系方式 -- 新增 ok ok ok  !!么有测试数据
        if (!empty($message['supplier_contact_information_insert'])) {
            $supplier_contact_information_info = $message['supplier_contact_information_insert'];
            foreach ($supplier_contact_information_info as $k => $v) {
                $supplier_contact_information_model = new SupplierContactInformation();
                $supplier_contact_information_model->supplier_id = isset($v['supplier_id']) ? $v['supplier_id'] :'';
                $supplier_contact_information_model->supplier_code = isset($v['supplier_code']) ? $v['supplier_code'] :'';
                $supplier_contact_information_model->contact_person = isset($v['contact_person']) ? $v['contact_person'] :'';
                $supplier_contact_information_model->corporate = isset($v['corporate']) ? $v['corporate'] :'';
                $supplier_contact_information_model->contact_number = $v['contact_number'];
                $supplier_contact_information_model->chinese_contact_address = $v['chinese_contact_address'];
                $supplier_contact_information_model->qq = $v['qq'];
                $supplier_contact_information_model->micro_letter = $v['micro_letter'];
                $supplier_contact_information_model->email = isset($v['email']) ? $v['email'] : '';
                $supplier_contact_information_model->want_want = $v['want_want'];
                $supplier_contact_information_model->validate();
                $supplier_contact_information_status = $supplier_contact_information_model->save();
            }
        }

        //结算日志 -- 新增 ok ok ok
        if (!empty($message['supplier_settlement_log'])) {
            $supplier_settlement_log_info = $message['supplier_settlement_log'];
            $supplierSettleModel = new SupplierSettlementLog();
            $supplierSettleModel->supplier_code = $supplier_settlement_log_info['supplier_code'];
            $supplierSettleModel->old_settlement =$supplier_settlement_log_info['old_settlement'];
            $supplierSettleModel->new_settlement = $supplier_settlement_log_info['new_settlement'];
            $supplierSettleModel->create_user_name = $supplier_settlement_log_info['create_user_name'];
            $supplierSettleModel->create_user_id = $supplier_settlement_log_info['create_user_id'];
            $supplierSettleModel->create_time = $supplier_settlement_log_info['create_time'];
            $supplier_settlement_log_status = $supplierSettleModel->save();
        }

        //供应商支付帐号表 -- 新增 ok ok ok
        if (!empty($message['supplier_payment_account_insert'])) {
            $supplier_payment_account_info = $message['supplier_payment_account_insert'];
            foreach ($supplier_payment_account_info as $k => $v) {
                $supplier_payment_account_model = new SupplierPaymentAccount();
                $supplier_payment_account_model->supplier_id = $v['supplier_id'];
                $supplier_payment_account_model->supplier_code = $v['supplier_code'];
                $supplier_payment_account_model->payment_method = $v['payment_method'];
                $supplier_payment_account_model->payment_platform = empty($v['payment_platform']) ? 0 :$v['payment_platform'];
                $supplier_payment_account_model->payment_platform_bank = $v['payment_platform_bank'];
                $supplier_payment_account_model->payment_platform_branch = $v['payment_platform_branch'];
                $supplier_payment_account_model->account = $v['account'];
                $supplier_payment_account_model->account_name = $v['account_name'];
                $supplier_payment_account_model->status = $v['status'];
                $supplier_payment_account_model->account_type = isset($v['account_type'])?$v['account_type']:1;
                $supplier_payment_account_model->prov_code = isset($v['prov_code'])?$v['prov_code']:'';
                $supplier_payment_account_model->city_code = isset($v['city_code'])?$v['city_code']:'';
                $supplier_payment_account_model->id_number = isset($v['id_number'])?$v['id_number']:'';
                $supplier_payment_account_model->phone_number = isset($v['phone_number'])?$v['phone_number']:'';
                $supplier_payment_account_model->validate();
                $supplier_payment_account_status = $supplier_payment_account_model->save();
                $flagSupplierModify = true;
            }
        }

        //批量插入图片 -- 新增 ok ok ok
        if (!empty($message['supplier_images'])) {
            $supplier_images_info = $message['supplier_images'];
            $supplier_images_status = SupplierImages::saveSupplierImageBcc($supplier_images_info);

            /*foreach ($supplier_images_info as $v ) {
                $supplier_images_status = Yii::$app->db->createCommand()->batchInsert(SupplierImages::tableName(), ['supplier_id', 'image_url'], [[$v[0], $v[1]]])->execute();
            }*/
        }

        //SupplierLog日志 -- 新增 ok ok ok
        if (!empty($message['supplier_log'])) {
            $supplier_log_info = $message['supplier_log'];
            $supplier_log_model = new SupplierLog();
            $supplier_log_model->user_id = $supplier_log_info['user_id'];
            $supplier_log_model->user_name = $supplier_log_info['user_name'];
            $supplier_log_model->action  = $supplier_log_info['action'];
            $supplier_log_model->message = $supplier_log_info['message'];
            $supplier_log_model->time    = $supplier_log_info['time'];
            $supplier_log_status = $supplier_log_model->save();
        }
        
        //更新供应商修改时间
        if( $flagSupplierModify && $supplier_code ){
        	$supplier_count = Yii::$app->db->createCommand()->update('pur_supplier', ['modify_time'=>date('Y-m-d H:i:s')], 'supplier_code = "' .$supplier_code.'"' )->execute();
        }

        unset($message['supplier_settlement_log']);
        unset($message['supplier']);
        unset($message['supplier_payment_account_update']);
        unset($message['supplier_contact_information_update']);
        unset($message['supplier_images']);
        unset($message['supplier_buyer']);
        unset($message['supplier_product_line']);
        unset($message['supplier_log']);
        unset($message['supplier_contact_delete']);
//        Vhelper::dump($message);
    }
    /**
     * 供应商审核状态
     */
    public static function getAuditStatus($supplier_code)
    {
        $log_info = SupplierUpdateLog::find()
            ->where(['supplier_code'=>$supplier_code])
            ->andWhere(['in','audit_status',['1','2','3','4','5','6','7']])
            ->orderBy('id desc')
            ->one();
        if (!empty($log_info)) {
            return SupplierServices::getSupplierAuditStatus($log_info->audit_status);
        } else {
            return false;
        }
    }
    /**
     * 供应商审核备注
     */
    public static function getAuditNote($supplier_code)
    {
        $log_info = SupplierUpdateLog::find()
            ->where(['supplier_code'=>$supplier_code])
            ->andWhere(['in','audit_status',['1','2','3','4','5','6','7']])
            ->orderBy('id desc')
            ->one();
        if (empty($log_info)) {
            return false;
        }

        $res_note = '';
        foreach ($log_info as $k => $v) {
            if ($k == 'purchase_note') {
                $res_note .= "采购审核：{$v}<br />";
            } elseif ($k == 'supply_chain_note') {
                $res_note .= "供应链审核：{$v}<br />";
            } elseif ($k == "finance_note") {
                $res_note .= "财务审核：{$v}<br />";
            }
        }
        return $res_note;
    }
    /**
     * 获取修改的信息
     */
    public static function getSupplierUpdateInfo($supplier_code)
    {
        $res_info = [];

        $supplier_update_log_info = SupplierUpdateLog::find()->where(['in','supplier_code',$supplier_code])->orderBy('id desc')->all();
        foreach ($supplier_update_log_info as $sulik=> $suliv) {
            $message = json_decode($suliv->message);
            $supplier_info = '提交修改：<br />';

            //供应商信息 -- 修改 ok ok
            if (!empty($message->supplier)) {
                $old = $message->supplier->old;
                $new = $message->supplier->new;
                foreach ($old as $k => $v) {
                    if ($new->$k != $v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'supplier_level') { //供应商等级
                            $old_v = SupplierServices::getSupplierLevel($v);
                            $new_v = SupplierServices::getSupplierLevel($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'supplier_type') { //供应商类型
                            $old_v = SupplierServices::getSupplierType($v);
                            $new_v = SupplierServices::getSupplierType($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'supplier_settlement') { //结算方式
                            $old_v = SupplierServices::getSettlementMethod($v);
                            $new_v = SupplierServices::getSettlementMethod($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'payment_method') { //支付方式
                            $old_v = SupplierServices::getDefaultPaymentMethod($v);
                            $new_v = SupplierServices::getDefaultPaymentMethod($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'invoice') { //是否开发票
                            $old_v = SupplierServices::getInvoice($v);
                            $new_v = SupplierServices::getInvoice($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'province') { //所在省
                            $province_info = BaseServices::getCityList(1);
                            $province_old = $v;
                            $province_new = $new->$k;
                            $old_v = !empty($province_info[$v]) ? $province_info[$v] :'';
                            $new_v = !empty($province_info[$new->$k]) ? $province_info[$new->$k] : '';
                        } elseif ($k == 'city') { //城市
                            $city_info_old = BaseServices::getCityList($province_old);
                            $city_info_new = BaseServices::getCityList($province_new);
                            $city_old = $v;
                            $city_new = $new->$k;
          
                            $old_v = !empty($city_info_old[$v]) ? $city_info_old[$v] : '';
                            $new_v = !empty($city_info_new[$new->$k]) ? $city_info_new[$new->$k] : '';
                        } elseif ($k == 'area') { //地区
                    
                            $area_info_old = BaseServices::getCityList($city_old);
                            $area_info_new = BaseServices::getCityList($city_new);

                            $old_v = !empty($area_info_old[$v]) ? $area_info_old[$v] : '';
                            $new_v = !empty($area_info_new[$new->$k]) ? $area_info_new[$new->$k] : '';
                        } elseif ($k == 'first_cooperation_time') { //首次合作时间
                            $old_v = $v;
                            $new_v = date('Y-m-d H:i:s',strtotime($new->$k));
                            if ($old_v == $new_v) {
                                continue;
                            }
                        } else {
                            $old_v = $v;
                            $new_v = $new->$k;
                        }
                        $supplier_info .= "{$field_info}：修改前“{$old_v}”；修改后“{$new_v}”<br />";
                    } else {
                        if ($k == 'province') { //所在省
                            $province_old = $v;
                            $province_new = $new->$k;
                        } elseif ($k == 'city') { //城市
                            $city_old = $v;
                            $city_new = $new->$k;
                        }
                    }
                }
            }

            //供应商支付帐号表 -- 修改 ok ok
            if (!empty($message->supplier_payment_account_update)) {
                $old = $message->supplier_payment_account_update->old;
                $new = $message->supplier_payment_account_update->new;
                foreach ($old as $ok => $ov) {
                    foreach ($ov as $k=>$v) {
                        if ($new[$ok]->$k != $v) {
                            $field_info = SupplierServices::publicInfo($k);

                            if ($k == 'payment_method') {
                                $old_v = !empty($v) ? SupplierServices::getPaymentMethod($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? SupplierServices::getPaymentMethod($new[$ok]->$k) : '';
                            }elseif ($k == 'payment_platform') { //支付平台
                                $old_v = !empty($v) ? SupplierServices::getPaymentPlatform($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? SupplierServices::getPaymentPlatform($new[$ok]->$k) : '';
                            }elseif ($k == 'account_type') { //账户类型
                                $old_v = !empty($v) ? SupplierServices::getAccountType($v) : '';
                                $new_v = !empty($new[$ok]->$k)?SupplierServices::getAccountType($new[$ok]->$k):'';
                            }elseif ($k =='prov_code'){
                                $old_v = !empty($v) ? UfxFuiou::getProvInfo($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? UfxFuiou::getProvInfo($new[$ok]->$k) : '';
                            }elseif ($k =='city_code'){
                                $old_v = !empty($v) ? UfxFuiou::getCityInfo($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? UfxFuiou::getCityInfo($new[$ok]->$k) : '';
                            }elseif ($k == 'payment_platform_bank') { //请录入支行名称
                                $old_v = !empty($v) ? UfxFuiou::getMasterBankInfo($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? UfxFuiou::getMasterBankInfo($new[$ok]->$k) : '';
                            } else {
                                $old_v = $v;
                                $new_v = $new[$ok]->$k;
                            }
                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                            $supplier_info .= "{$field_info}：修改前“{$old_v}”；修改后“{$new_v}”<br />";
                        }
                    }
                }
            }
            if (!empty($message->supplier_payment_account_delete)) {
                foreach ($message->supplier_payment_account_delete as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'payment_method') {
                            $res = !empty($v) ? SupplierServices::getPaymentMethod($v):'';
                        }elseif ($k == 'payment_platform') { //支付平台
                            $res = !empty($v) ? SupplierServices::getPaymentPlatform($v):'';
                        } elseif ($k == 'payment_platform_bank') { //请录入支行名称
                            $res = !empty($v) ? UfxFuiou::getMasterBankInfo($v) : '';
                        }elseif ($k =='prov_code'){
                            $res = !empty($v) ? UfxFuiou::getProvInfo($v) : '';
                        }elseif ($k =='city_code'){
                            $res = !empty($v) ? UfxFuiou::getCityInfo($v) : '';
                        }elseif ($k == 'account_type') { //账户类型
                            $res = !empty($v) ? SupplierServices::getAccountType($v) : '';
                        } else {
                            $res = $v;
                        }
                        $res = is_array($res) ? '' : $res;
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $res . "<br />";
                    }
                    $supplier_info .= "删除支付账号：<br />{$info}<br />";
                }
            }

            //供应商支付帐号表 -- 新增 ？？ 未翻译 无数据测试
            if (!empty($message->supplier_payment_account_insert)) {
                foreach ($message->supplier_payment_account_insert as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'payment_method') {
                            $res = SupplierServices::getPaymentMethod($v);
                        }elseif ($k == 'payment_platform') { //支付平台
                            $res = SupplierServices::getPaymentPlatform($v);
                        } elseif ($k == 'payment_platform_bank') { //请录入支行名称
                            $res = !empty($v) ? UfxFuiou::getMasterBankInfo($v) : '';
                        }elseif ($k =='prov_code'){
                            $res = !empty($v) ? UfxFuiou::getProvInfo($v) : '';
                        }elseif ($k =='city_code'){
                            $res = !empty($v) ? UfxFuiou::getCityInfo($v) : '';
                        }elseif ($k == 'account_type') { //账户类型
                            $res = !empty($v) ? SupplierServices::getAccountType($v) : '';
                        } else {
                            $res = $v;
                        }
                        $res = is_array($res) ? '' : $res;
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $res . "<br />";
                    }
                    $supplier_info .= "新增支付帐号：<br />{$info}<br />";
                }
            }

            //供应商联系方式 -- 修改 ok ok

            if (!empty($message->supplier_contact_information_update)) {
                $old = $message->supplier_contact_information_update->old;
                $new = $message->supplier_contact_information_update->new;
                foreach ($old as $ok => $ov) {
                    foreach ($ov as $k=>$v) {
                        if ($new[$ok]->$k != $v) {

                            $field_info = SupplierServices::publicInfo($k);
                            $old_v = $v;
                            $new_v = $new[$ok]->$k;
                            $supplier_info .= "{$field_info}：修改前“{$old_v}”；修改后“{$new_v}”<br />";
                        }
                    }
                }
            }
            if (!empty($message->supplier_contact_delete_list_info)) {
                foreach ($message->supplier_contact_delete_list_info as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $v . "<br />";
                    }
                    $supplier_info .= "删除联系方式：<br />{$info}<br />";
                }
            }

            //供应商联系方式 -- 新增 ？？ 未翻译 无数据测试
            if (!empty($message->supplier_contact_information_insert)) {
                foreach ($message->supplier_contact_information_insert as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $v . "<br />";
                    }
                    $supplier_info .= "新增联系方式：<br />{$info}<br />";
                }
            }

            //供应商附图 -- 新增 ok ok
            if (!empty($message->supplier_images)) {
                $new = $message->supplier_images;
                $supplier_id = !empty($new->supplier_id)?$new->supplier_id: (!empty($new['supplier_id'])?$new['supplier_id']:false);
                if (!empty($supplier_id)) {
                    $old = SupplierImages::find()->where(['supplier_id'=>$supplier_id, 'image_status'=>2])->orderBy('image_id DESC')->asArray()->one();
                }


                if (empty($old)) {
                    foreach ($new as $nk => $nv) {
                        if (is_array($nv)) {
                            foreach($nv as $k222 => $v222){
                                $info = "图片ID：{$nk}<br />图片URL：<img src='{$v222}' class='viewImg2' width='100' height='50'>";
                                $supplier_info .= "新增图片：<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info}<br />";
                            }
                        } elseif (!empty($nv) && $nk != 'supplier_id') {
                            $info = "图片ID：{$nk}<br />图片URL：<img src='{$nv}' class='viewImg2' width='100' height='50'>";
                            $supplier_info .= "新增图片：<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info}<br />";
                        }
                    }
                } else {
                    foreach ($new as $nk => $nv) {
                        if(empty($nv)) continue;
                        if (is_array($nv)) {
                            foreach($nv as $k222 => $v222){
                                $info = "图片ID：{$nk}<br />图片URL：<img src='{$v222}' class='viewImg2' width='100' height='50'>";
                                $supplier_info .= "新增图片：<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info}<br />";
                            }
                        }elseif (!empty($nv) && $nk != 'supplier_id') {
                            $info = "图片ID：{$nk}<br />图片URL：<img src='{$nv}' class='viewImg2' width='100' height='50'>";
                            $supplier_info .= "新增图片：<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info}<br />";
                        }

                        if (!empty($old[$nk])) {// 展示旧图
                            $old_img_list = explode(';',$old[$nk]);
                            foreach($old_img_list as $old_img_list_v){
                                if( (is_array($nv) and  !in_array($old_img_list_v,$nv)) OR (!is_array($nv) and $old_img_list_v != $nv ) ){
                                    $info = "图片ID：{$nk}<br />图片URL：<img src='{$old_img_list_v}' class='viewImg2' width='100' height='50'>";
                                    $supplier_info .= "更新图片：<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info}<br />";
                                }
                            }
                        }

                    }
                }
            }

            //供应商绑定采购员 -- 修改/新增 ok ok
            if (!empty($message->supplier_buyer)) {
                $old = $message->supplier_buyer->old;
                $new = $message->supplier_buyer->new;
                foreach ($new as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        if (!empty($old[$ok])) { //如果存在，代表修改
                            if ($old[$ok]->$k != $v) {
                                if ($k == 'type') { //所属部门 1，国内仓 2，海外仓 3，FBA
                                    $old_v = PurchaseOrderServices::getPurchaseType($old[$ok]->$k);
                                    $new_v = PurchaseOrderServices::getPurchaseType($v);

                                    $old_v = is_array($old_v) ? '' : $old_v;
                                    $new_v = is_array($new_v) ? '' : $new_v;
                                } elseif ($k == 'status') { //状态(1启用2停用3删除)
                                    $old_v =  SupplierServices::getBuyerStatus($old[$ok]->$k);
                                    $new_v = SupplierServices::getBuyerStatus($v);

                                    $old_v = is_array($old_v) ? '' : $old_v;
                                    $new_v = is_array($new_v) ? '' : $new_v;
                                } else {
                                    $old_v = $old[$ok]->$k;
                                    $new_v = $v;
                                }

                                $field_info = SupplierServices::publicInfo($k);

                                $supplier_info .= "{$field_info}：修改前“{$old_v}”；修改后“{$new_v}”<br />";
                            }
                        } else { //新增
                            if ($k == 'id') {
                                continue;
                            }
                            $field_info = SupplierServices::publicInfo($k);
                            if ($k == 'type') { //所属部门 1，国内仓 2，海外仓 3，FBA
                                $res_v = PurchaseOrderServices::getPurchaseType($v);
                            } elseif ($k == 'status') { //状态(1启用2停用3删除)
                                $res_v = SupplierServices::getBuyerStatus($v);
                            } else {
                                $res_v = $v;
                            }
                            $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $res_v . "<br />";
                            $old_v = $info;
                            $new_v = $info;
                        }
                    }
                    if (!empty($info)) {
                        $supplier_info .= "新增采购员：<br />{$info}<br />";
                    }
                }
            }

            //供应商绑定产品线--修改、新增 !!!!翻译合并(??每次都会新增)
            if (!empty($message->supplier_product_line)) {
                $supplier_product_line_info = $message->supplier_product_line->supplier_product_line;
                foreach ($supplier_product_line_info as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'id') { //过滤掉id
                            continue;
                        } elseif ($k == 'first_product_line') { //一级产品线
                            $new_v = BaseServices::getProductLine($v);
                            $first_product_line = $v;
                        } elseif ($k == 'second_product_line') { //二级产品线
                            $new_v = BaseServices::getProductLineList($first_product_line)[$v];
                            $second_product_line = $v;
                        } elseif ($k == 'third_product_line') { //三级产品线
                            $new_v = !empty(BaseServices::getProductLineList($second_product_line)[$v]) ? BaseServices::getProductLineList($second_product_line)[$v] : '';
                        } elseif ($k == 'status') { //状态
                            continue;
                            $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                        } else {
                            $new_v = $v;
                        }
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $new_v . "<br />";
                    }
                    $supplier_info .= "新增产品线：<br />{$info}<br />";
                }
            }

            if (!empty($message->supplier_product_line_insert)) {
                $supplier_product_line_info = $message->supplier_product_line_insert;
                foreach ($supplier_product_line_info as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'id') { //过滤掉id
                            continue;
                        } elseif ($k == 'first_product_line') { //一级产品线
                            $new_v = BaseServices::getProductLine($v);
                            $first_product_line = $v;
                        } elseif ($k == 'second_product_line') { //二级产品线
                            $new_v = BaseServices::getProductLineList($first_product_line)[$v];
                            $second_product_line = $v;
                        } elseif ($k == 'third_product_line') { //三级产品线
                            $new_v = !empty(BaseServices::getProductLineList($second_product_line)[$v]) ? BaseServices::getProductLineList($second_product_line)[$v] : '';
                        } elseif ($k == 'status') { //状态
                            continue;
                            $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                        } else {
                            $new_v = $v;
                        }
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $new_v . "<br />";
                    }
                    $supplier_info .= "新增产品线：<br />{$info}<br />";
                }
            }
            if (!empty($message->supplier_product_line_delete)) {
                $supplier_product_line_info = $message->supplier_product_line_delete;
                foreach ($supplier_product_line_info as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'id') { //过滤掉id
                            continue;
                        } elseif ($k == 'first_product_line') { //一级产品线
                            $new_v = BaseServices::getProductLine($v);
                            $first_product_line = $v;
                        } elseif ($k == 'second_product_line') { //二级产品线
                            $new_v = !empty(BaseServices::getProductLineList($first_product_line)[$v]) ? BaseServices::getProductLineList($first_product_line)[$v] : '';
                            $second_product_line = $v;
                        } elseif ($k == 'third_product_line') { //三级产品线
                            $new_v = !empty(BaseServices::getProductLineList($second_product_line)[$v]) ? BaseServices::getProductLineList($second_product_line)[$v] : '';
                        } elseif ($k == 'status') { //状态
                            continue;
                            $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                        } else {
                            $new_v = $v;
                        }
                        $info .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $field_info . '：' . $new_v . "<br />";
                    }
                    $supplier_info .= "删除产品线：<br />{$info}<br />";
                }
            }
            $res_info[] = [
                'time'=>$suliv->create_time,
                'buyer'=>$suliv->user_name,
                'detail'=>$supplier_info
            ];
        }
        return $res_info;
    }
    /**
     * 审核日志
     */
    public static function getSupplierAuditInfo($supplier_code)
    {
        $res_info = [];

        $supplier_update_log_info = SupplierUpdateLog::find()->where(['in','supplier_code',$supplier_code])->orderBy('id desc')->all();
        foreach ($supplier_update_log_info as $sulik=> $suliv) {
            if (!empty($suliv['finance_audit'])) { //财务
                if ($suliv['audit_status'] == 6) {
                    $supplier_audit_info = "财务审核，审核结果：失败；备注：{$suliv['finance_note']}";
                } else {
                    $supplier_audit_info = "财务审核，审核结果：通过；备注：{$suliv['finance_note']}";
                }
                $res_info[] = [
                    'time'=>$suliv->finance_time,
                    'buyer'=>$suliv->finance_audit,
                    'detail'=>$supplier_audit_info
                ];
            }

            if (!empty($suliv['supply_chain_audit'])) { //供应链
                if ($suliv['audit_status'] == 4) {
                    $supplier_audit_info = "供应链审核，审核结果：失败；备注：{$suliv['supply_chain_note']}";
                } else {
                    $supplier_audit_info = "供应链审核，审核结果：通过；备注：{$suliv['supply_chain_note']}";
                }
                $res_info[] = [
                    'time'=>$suliv->supply_chain_time,
                    'buyer'=>$suliv->supply_chain_audit,
                    'detail'=>$supplier_audit_info
                ];
            }
            
            if (!empty($suliv['purchase_audit'])) { //采购
                if ($suliv['audit_status'] == 2) {
                    $supplier_audit_info = "采购审核，审核结果：失败；备注：{$suliv['purchase_note']}";
                } else {
                    $supplier_audit_info = "采购审核，审核结果：通过；备注：{$suliv['purchase_note']}";
                }
                $res_info[] = [
                    'time'=>$suliv->purchase_time,
                    'buyer'=>$suliv->purchase_audit,
                    'detail'=>$supplier_audit_info
                ];
            }
        }
        return $res_info;
    }
    /**
     * 查看当前审核时的数据
     */
    public static function getCurrentAuditInfo($supplier_code)
    {
        $change_info = [];

        $supplier_update_log_info = SupplierUpdateLog::find()->where(['in','supplier_code',$supplier_code])->andWhere(['in','audit_status',[1,3,5]])->one();

        $audit_status = !empty($supplier_update_log_info->audit_status) ? $supplier_update_log_info->audit_status : -1;

        $message = json_decode($supplier_update_log_info->message);
        $is_update_bank = false;
        //供应商信息 -- 修改 ok ok
        if (!empty($message->supplier)) {
            $old = $message->supplier->old;
            $new = $message->supplier->new;
            foreach ($old as $k => $v) {
                if ($new->$k != $v) {
                    $change_info['supplier'][$k] = $new->$k;
                }
            }
        }

        //供应商支付帐号表 -- 修改 ok ok
        if (!empty($message->supplier_payment_account_update)) {
            $old = $message->supplier_payment_account_update->old;
            $new = $message->supplier_payment_account_update->new;
            foreach ($old as $ok => $ov) {
                foreach ($ov as $k=>$v) {
                    if ($new[$ok]->$k != $v) {

                        if ($k == 'account' || $k == 'account_name' || $k == 'account_type') { //账户、账户名、账户类型
                            $old_v = $v;
                            $new_v = $new[$ok]->$k;
                            if (!empty($old_v) || !empty($new_v)) {
                                $is_update_bank = true;
                            }
                        }

                        $new_v = (!empty($new[$ok]->$k) || !is_array($new[$ok]->$k)) ? $new[$ok]->$k : '';
                        $change_info['supplier_payment_account'][$ov->pay_id][$k] = $new_v;
                    }
                }
            }
        }

        //供应商支付帐号表 -- 新增
        if (!empty($message->supplier_payment_account_insert)) {
            foreach ($message->supplier_payment_account_insert as $ok => $ov) {
                foreach ($ov as $k=>$v) {
                    if ($k == 'account' || $k == 'account_name' || $k == 'account_type') { //账户、账户名、账户类型
                        if (!empty($v)) {
                            $is_update_bank = true;
                        }
                        $res = $v;
                        if ($k == 'account_type') { //账户类型
                            $res = !empty($v) ? SupplierServices::getAccountType($v) : '';
                        }
                    }
                    $change_info['supplier_payment_account'][$ok][$k] = $v;
                }
            }
        }

        //供应商支付帐号表 -- 删除
        /*if (!empty($message->supplier_payment_account_delete)) {
            foreach ($message->supplier_payment_account_delete as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $field_info = SupplierServices::publicInfo($k);
                    if($field_info=='未知'){
                        continue;
                    }
                    if ($k == 'payment_method') {
                        $res = SupplierServices::getPaymentMethod($v);
                    }elseif ($k == 'payment_platform') { //支付平台
                        $res = SupplierServices::getPaymentPlatform($v);
                    } elseif ($k == 'payment_platform_bank') { //请录入支行名称
                        $res = !empty($v) ? \app\models\UfxFuiou::getMasterBankInfo($v) : '';
                    } elseif ($k == 'account' || $k == 'account_name' || $k == 'account_type') { //账户、账户名、账户类型
                        if (!empty($v)) {
                            if ($is_update_bank) {
                                echo '<input type="hidden" name="is_update_bank" value="1">';
                                $is_update_bank = false;
                            }
                        }
                        $res = $v;
                        if ($k == 'account_type') { //账户类型
                            $res = !empty($v) ? SupplierServices::getAccountType($v) : '';
                        }
                    } else {
                        $res = $v;
                    }
                    $res =   is_array($res) ? '' : $res;
                    $info .= $field_info . '：' . $res . "<br />";
                }
            }
        }*/

        //供应商联系方式 -- 修改 ok ok
        if (!empty($message->supplier_contact_information_update)) {
            $old = $message->supplier_contact_information_update->old;
            $new = $message->supplier_contact_information_update->new;
            foreach ($old as $ok => $ov) {
                foreach ($ov as $k=>$v) {
                    if ($new[$ok]->$k != $v) {
                        $field_info = SupplierServices::publicInfo($k);
                        $change_info['supplier_contact_information'][$ov->contact_id][$k] = $new[$ok]->$k;
                    }
                }
            }
        }

        //供应商联系方式 -- 新增 ok ok
        if (!empty($message->supplier_contact_information_insert)) {
            foreach ($message->supplier_contact_information_insert as $ok => $ov) {
                foreach ($ov as $k=>$v) {
                    $change_info['supplier_contact_information_insert'][$ok][$k] = $v;
                }
            }
        }

        //供应商附图 -- 新增 ok
        if (!empty($message->supplier_images)) {
            $new = $message->supplier_images;
            if (!empty($new->supplier_id)) {
                $old = SupplierImages::find()->where(['supplier_id'=>$new->supplier_id, 'image_status'=>1])->asArray()->one();
                if (empty($old)) {
                    foreach ($new as $nk => $nv) {
                        //??
                        if (!empty($nv)) {
                            $change_info['supplier_images'][$nk] = $nv;
                        }
                    }
                } else {
                    foreach ($new as $nk => $nv) {
                        if (!empty($nv)) {
                            $change_info['supplier_images'][$nk] = $nv;
                        }
                    }
                }
            }
            
        }

        //供应商绑定采购员 -- 修改/新增 ok
        if (!empty($message->supplier_buyer)) {
            $old = $message->supplier_buyer->old;
            $new = $message->supplier_buyer->new;
            foreach ($new as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    if (!empty($old[$ok])) { //如果存在，代表修改
                        if ($old[$ok]->$k != $v) {
                            $change_info['supplier_buyer'][$ov->id][$k] = $v;
                        }
                    } else { //新增
                        //??
                        $change_info['supplier_buyer'][$ov->id][$k] = $v;
                    }
                }
            }
        }

        //供应商绑定产品线--修改、新增(??每次都会新增)
        if (!empty($message->supplier_product_line_insert)) {
            $supplier_product_line_insert = $message->supplier_product_line_insert;
            foreach ($supplier_product_line_insert as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $change_info['supplier_product_line'][$k] = $v;
                }
            }
        }

        //供应商 删除联系方式
        if (!empty($message->supplier_contact_delete)) {
            $change_info['supplier_contact_delete'] = $message->supplier_contact_delete;
        }

        /*if (!empty($message->supplier_product_line)) {
            $supplier_product_line_info = $message->supplier_product_line->supplier_product_line;
            foreach ($supplier_product_line_info as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $field_info = SupplierServices::publicInfo($k);

                    if ($k == 'id') { //过滤掉id
                        continue;
                    } elseif ($k == 'first_product_line') { //一级产品线
                        $new_v = BaseServices::getProductLine($v);
                        $first_product_line = $v;
                    } elseif ($k == 'second_product_line') { //二级产品线
                        $new_v = BaseServices::getProductLineList($first_product_line)[$v];
                        $second_product_line = $v;
                    } elseif ($k == 'third_product_line') { //三级产品线
                        $new_v = !empty(BaseServices::getProductLineList($second_product_line)[$v]) ? BaseServices::getProductLineList($second_product_line)[$v] : '';
                    } elseif ($k == 'status') { //状态
                        continue;
                        $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                    } else {
                        $new_v = $v;
                    }
                    // $change_info['supplier_product_line'][$k] = $new_v;

                    $info .= $field_info . '：' . $new_v . "<br />";
                }
                echo "<tr>";
                echo "<td>新增产品线</td>";
                echo "<td></td>";
                echo "<td>{$info}</td>";
                echo "</tr>";
            }
        }
        if (!empty($message->supplier_product_line_delete)) {
            $supplier_product_line_delete = $message->supplier_product_line_delete;
            foreach ($supplier_product_line_delete as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $field_info = SupplierServices::publicInfo($k);

                    if ($k == 'id') { //过滤掉id
                        continue;
                    } elseif ($k == 'first_product_line') { //一级产品线
                        $new_v = \app\models\ProductLine::find()->select('linelist_cn_name')->where(['product_line_id'=>$v])->scalar();
                        $new_v = $new_v ? $new_v : '';
                    } elseif ($k == 'second_product_line') { //二级产品线
                        $v = \app\models\ProductLine::find()->select('linelist_cn_name')->where(['product_line_id'=>$v])->scalar();

                        $new_v = $v ? $v  :'';
                    } elseif ($k == 'third_product_line') { //三级产品线
                        $v = \app\models\ProductLine::find()->select('linelist_cn_name')->where(['product_line_id'=>$v])->scalar();
                        $new_v = $v ? $v  :'';
                    } elseif ($k == 'status') { //状态
                        continue;
                        $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                    } else {
                        $new_v = $v;
                    }
                    $change_info['supplier_product_line_delete'][$k] = $new_v;
                    $info .= $field_info . '：' . $new_v . "<br />";
                }
                echo "<tr>";
                echo "<td>删除产品线</td>";
                echo "<td></td>";
                echo "<td>{$info}</td>";
                echo "</tr>";
            }
        }*/
        $change_info['is_update_bank'] = $is_update_bank;
        $change_info['audit_status'] = $audit_status;
        return $change_info;

    }
}
