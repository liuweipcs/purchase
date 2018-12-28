<?php
namespace app\models;
use yii\data\ActiveDataProvider;

use Yii;
use app\models\PlatformSummary;
use app\models\Product;
use app\models\ProductProvider;
use yii\data\Pagination;
use m35\thecsv\theCsv;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\config\Vhelper;

class OverseasPurchaseOrderSearch extends PlatformSummary
{
    public $order_status;
    public $compact_number;
    public $buyer;
    public $bh_type;
    public $supplier_code;
    public $arrival_status;
    public $is_drawback;
    public $pay_status;
    public $overseas_transport;
    public $order_source;
    public $is_sku_destroy;
    public $supplier_special_flag;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[], 'required'],
            [['pur_number','demand_number','start_time','end_time','product_is_new','order_status','compact_number','sku','buyer','bh_type',
                'supplier_code','is_drawback','transport_style','order_source','audit_level','pay_number','pay_status','product_name','platform_number','arrival_status','supplier_special_flag'], 'safe'],
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
        $query = PlatformSummary::find()->alias("summary")
        ->leftJoin(PurchaseDemand::tableName()." d", "summary.demand_number = d.demand_number")
        ->leftJoin(PurchaseOrder::tableName()." o", "o.pur_number = d.pur_number")
        ->leftJoin(PurchaseOrderItems::tableName()." item", "item.pur_number = o.pur_number and item.sku = summary.sku")
        ->leftJoin(Product::tableName()." p", "p.sku = summary.sku")
        ->leftJoin("(select * from pur_purchase_compact_items where bind = 1) as compactb", "o.pur_number = compactb.pur_number")
        //->leftJoin(PurchaseCompact::tableName()." compact", "compact.compact_number = compactb.compact_number")
        ->leftJoin(PurchaseOrderPayType::tableName()." paytype", "paytype.pur_number = o.pur_number")
        ->where(['summary.purchase_type'=>2])
        ->andwhere(['in','summary.level_audit_status',[1,8]]);
        
        $pageSize = 10;
        if (!empty($params['pageSize'])) $pageSize = $params['pageSize'];
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->select('
            summary.*,
            o.pur_number,o.currency_code,o.create_type,o.date_eta,o.account_type,o.pay_type,o.shipping_method,o.buyer,o.is_drawback,o.source,o.transit_warehouse,o.audit_time,
            item.price,item.base_price,item.product_link,item.product_img,item.pur_ticketed_point,
            p.product_is_new,p.tax_rate,p.export_cname,p.declare_unit,
            paytype.settlement_ratio,paytype.freight_formula_mode,paytype.purchase_acccount,paytype.platform_order_number,paytype.freight_payer,
            compactb.compact_number
        ');
        //强制限制只显示2018-08-29 10点之后的需求
        $query->andWhere(['>','summary.agree_time','2018-08-29 10:00:00']);
        $query->andWhere(['in', 'o.is_new', 1]); //区分是否是新系统的单
        if (!empty($this->order_status) && $this->order_status[0]) {
            $query->andwhere(['demand_status'=>$this->order_status]);
        }
        if (!empty($this->pay_status)) {
            $query->andwhere(['summary.pay_status'=>$this->pay_status]);
        }
        if (!empty($this->audit_level)) {
            $query->andwhere(['audit_level'=>$this->audit_level]);
        }
        if (!empty($this->demand_number)) {
            $query->andWhere(['in', 'summary.demand_number', getStringToArray($this->demand_number)]);
        }
        if (!empty($this->pur_number)) {
            $query->andWhere(['in', 'o.pur_number', getStringToArray($this->pur_number)]);
        }
        if (!empty($this->compact_number)) {
            $pos = PurchaseCompact::getPurNumbers(getStringToArray($this->compact_number));
            $query->andWhere(['in', 'o.pur_number', $pos]);
        }
        if (!empty($this->sku)) {
            $skus = getStringToArray($this->sku);
            $skuwheres = [];
            foreach ($skus as $_skuwhere) {
                $skuwheres[] = "summary.sku like '%{$_skuwhere}%'";
            }
            $query->andWhere(implode(" or ",$skuwheres));
        }
        if (!empty($this->product_name)) {
            $query->andWhere("summary.product_name like '%{$this->product_name}%'");
        }
        if (!empty($this->buyer)){
            if ($this->buyer == 'admin') {
                $query->andwhere(['in','o.buyer',['','admin']]);
            } else {
                $query->andFilterWhere(['o.buyer'=> Vhelper::chunkBuyerByNumeric($this->buyer)]);
            }
        }
        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }

        //1 => '等待采购询价',2 => '信息变更等待审核',3 => '待采购审核',5 => '待销售审核',6 => '等待生成进货单',
        //在等待到货前，任何一个状态，他们都要查看到自己的。
        $userRoleName = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        $username = Yii::$app->user->identity->username;
        $allowUser = ['龙菁','肖发红','胡不为','尹小婷','李新','史家松','范晶晶','蒙朝侣','张建蓉','史家松','徐善德','黄欣欣','龚海燕','岳霞 ','蒋瑾语 ','岳霞','袁哲','曹为艳','朱之沁','李江南 ','雷颖','杨春红','刘楚雯']; //允许查看所有的订单的名单
        if (!array_key_exists('超级管理员组',$userRoleName) && !in_array($username,$allowUser) && !in_array(Yii::$app->user->identity->id,[124,393,151,126,292,47])) {
            $query->andwhere("((demand_status < 7 and o.buyer = '$username') or (demand_status > 6))");
        }
        /*
         if (empty($this->buyer)){
         $userIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
         $user = User::find()->select('username')->andFilterWhere(['id'=>$userIds])->asArray()->all();
         $users = array_column($user,'username');
         $users[] = '';
         //$query->andWhere(['in','o.buyer',$users]);
         } else {
         $query->andwhere(['o.buyer'=>$this->buyer]);
         }
         */
        if (!empty($this->platform_number)) {
            $query->andwhere(['summary.platform_number'=>$this->platform_number]);
        }
        if (!empty($this->bh_type)) {
            $query->andwhere(['summary.bh_type'=>$this->bh_type]);
        }

        if (!empty($this->supplier_code)) {
            if (isset($_GET['export'])) {
                $this->supplier_code = Supplier::getSupplierCode($this->supplier_code);
            }
            $query->andwhere(['summary.supplier_code'=>$this->supplier_code]);
        }
        if (!empty($this->is_drawback)) {
            $query->andwhere(['o.is_drawback'=>$this->is_drawback]);
        }
        if (!empty($this->transport_style)) {
            $query->andwhere(['summary.transport_style'=>$this->transport_style]);
        }
        if (!empty($this->order_source)) {
            $query->andwhere(['o.source'=>$this->order_source]);
        }
        if (!empty($this->product_is_new)) {
            $query->andwhere(['p.product_is_new'=>$this->product_is_new == 1 ? 1 : 0]);
        }
        if (empty($this->start_time)) {
            $this->start_time = max(date('Y-m-d', time() - 86400*180).' 00:00:00','2018-08-29 10:00:00');
            $this->end_time = date('Y-m-d', time() + 86400).' 00:00:00';
        }
        $query->andFilterWhere(['between', 'summary.agree_time', $this->start_time, $this->end_time]);
        if (isset($params['requisition_number'])) {
            $requisition_number = $params['requisition_number'];
            $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$requisition_number])->column();
            if (empty($demand_numbers)) {
                $query->andwhere("1 = 0");
            } else {
                $query->andwhere(['in','summary.demand_number',$demand_numbers]);
            }
        }

        if (isset($_GET['export'])) {
            $datalist = $query->all();
            $this->exportData($datalist);
        }
        
        $query2 = clone $query;
        $totalprice = $query2->select("sum(summary.purchase_quantity*item.price)")->scalar();
        
        if (empty($params['sort'])) {
            $query->orderBy('summary.id desc');
        }

        $dataProvider->setSort([
            'attributes' => [
                'product_name' => [
                    'desc' => ['summary.product_name' => SORT_DESC],
                    'asc' => ['summary.product_name' => SORT_ASC],
                    'label' => 'product_name',
                ],
                'pur_number' => [
                    'desc' => ['o.pur_number' => SORT_DESC],
                    'asc' => ['o.pur_number' => SORT_ASC],
                    'label' => 'pur_number',
                ],
                'compact_number' => [
                    'desc' => ['compactb.compact_number' => SORT_DESC],
                    'asc' => ['compactb.compact_number' => SORT_ASC],
                    'label' => 'compact_number',
                ],
                'buyer' => [
                    'desc' => ['o.buyer' => SORT_DESC],
                    'asc' => ['o.buyer' => SORT_ASC],
                    'label' => 'buyer',
                ],
                'purchase_acccount' => [
                    'desc' => ['paytype.purchase_acccount' => SORT_DESC],
                    'asc' => ['paytype.purchase_acccount' => SORT_ASC],
                    'label' => 'purchase_acccount',
                ],
                'platform_order_number' => [
                    'desc' => ['paytype.platform_order_number' => SORT_DESC],
                    'asc' => ['paytype.platform_order_number' => SORT_ASC],
                    'label' => 'platform_order_number',
                ],
            ]
        ]);
        return ['dataProvider'=>$dataProvider,'totalprice'=>$totalprice];
    }

    /**
     * 海外仓-采购单-NEW 导出数据
     * @param $datalist
     */
    public function exportData($datalist){
        set_time_limit(0); //用来限制页面执行时间的,以秒为单位
        ini_set('memory_limit', '1024M');

        // 设置表头
        $title = [
            '需求单号','订单状态','SKU','图片','产品名称','PO号','合同号','采购员','采购主体','供应商','数量','是否新品','含税单价','未税单价',
            '开票点','出口退税税率','开票品名','开票单位','采购金额','中转数','海外物流类型','平台','采购仓库','加急采购单','物流单号',
            '补货类型','币种','结算方式','支付方式','结算比例','供应商运输','是否中转','中转仓库','是否退税','创建日期','预计到货日期',
            '采购来源','运费','运费计算方式','运费支付','优惠额','账号','拍单号','采购到货日期','仓库到货日期','到货数量','入库日期',
            '入库数量','入库金额','已付金额','是否逾期','是否核销','退款金额','付款状态','付款时间','请款单号','已开票数量','未开票数量',
            '发票编号','采购异常回复','销售反馈','备注'
        ];
        $data = [];
        if ($datalist) {
            foreach($datalist as $model) {
                if ($model->demand_status ==14 ) continue;

                $set = [];
                $set[] = $model->demand_number;
                $set[] = PurchaseOrderServices::getOverseasOrderStatus($model->demand_status);
                $set[] = $model->sku;
                $set[] = $model->product_img;
                $set[] = $model->product_name;
                $set[] = $model->pur_number;
                $set[] = $model->compact_number;
                $set[] = $model->buyer;
                $set[] = BaseServices::getBuyerCompany($model->is_drawback,'name');
                $set[] = empty($model->supplier2) ? '' : $model->supplier2->supplier_name;
                $set[] = $model->purchase_quantity;
                $set[] = $model->product_is_new == 1 ? '是' : '否';
                $set[] = $model->is_drawback == 1 ? '' : ($model->demand_status == 1 ? self::getSkuQuoteValue($model->sku, 'base_price') : $model->price);
                $set[] = $model->demand_status == 1 ? self::getSkuQuoteValue($model->sku, 'base_price') : $model->base_price;
                $set[] = $model->demand_status == 1 ? self::getSkuQuoteValue($model->sku, 'pur_ticketed_point') : $model->pur_ticketed_point;
                $set[] = $model->tax_rate;
                $set[] = $model->demand_status == 1 ? ($model->demand_export_cname ? $model->demand_export_cname : $model->export_cname) : $model->demand_export_cname;
                $set[] = $model->demand_status == 1 ? ($model->demand_declare_unit ? $model->demand_declare_unit : $model->declare_unit) : $model->demand_declare_unit;
                if ($model->demand_status == 1) {
                    $price_type = $model->is_drawback == 2 ? 'price' : 'base_price';
                    $price = self::getSkuQuoteValue($model->sku, $price_type);
                } else {
                    $price = $model->price;
                }
                $set[] = round($price * $model->purchase_quantity,4);
                $set[] = $model->transit_number;
                $set[] = $model->transport_style ? PurchaseOrderServices::getTransport($model->transport_style) : '';
                $set[] = $model->platform_number;
                $set[] = BaseServices::getWarehouseCode($model->purchase_warehouse);
                $set[] = $model->demand_is_expedited == 1 ? '是' : '否';
                $set[] = $model->demand_status > 6 ? OverseasPurchaseOrderSearch::getShipExpressNo($model->pur_number, $model->demand_number) : '';
                $set[] = $model->bh_type ? PurchaseOrderServices::getBhTypes($model->bh_type) : '';
                $set[] = $model->currency_code;
                $account_type = $model->demand_status == 1 ? (empty($model->supplier2) ? '' : $model->supplier2->supplier_settlement) : $model->account_type;
                $set[] = !empty($account_type) ? SupplierServices::getSettlementMethod($account_type) : '';
                $pay_type = $model->demand_status == 1 ? (empty($model->supplier2) ? '' : $model->supplier2->payment_method) : $model->pay_type;
                $set[] = !empty($pay_type) ? SupplierServices::getDefaultPaymentMethod($pay_type) : '';
                $set[] = $model->settlement_ratio;
                $set[] = $model->shipping_method ? PurchaseOrderServices::getShippingMethod($model->shipping_method) : '';
                $set[] = $model->is_transit == 1 ? '直发' : '是';
                $set[] = PurchaseOrderServices::getTransitWarehouse(strval($model->transit_warehouse));
                $set[] = $model->is_drawback == 2 ? '是' : '否';
                $set[] = $model->agree_time;
                $set[] = $model->date_eta;
                $set[] = $model->source == 1 ? '合同' : '网采';
                $set[] = self::getDemandPayInfo($model->demand_number, 'freight');
                $set[] = !empty($model->freight_formula_mode) ? ( $model->freight_formula_mode == 'weight' ? '重量' : '体积' ) : '';
                $set[] = !empty($model->freight_payer) ? ( $model->freight_payer == 1 ? '甲方支付' : '乙方支付' ) : '';
                $set[] = self::getDemandPayInfo($model->demand_number, 'discount');
                $set[] = $model->purchase_acccount;
                $set[] = $model->platform_order_number;
                $set[] = $model->purchase_arrival_date;
                $set[] = '';//Todo入库日期
                $set[] = $model->rqy;
                $set[] = $model->instock_date;
                $set[] = $model->cty;
                $set[] = $model->cty * $model->price;
                $set[] = self::getDemandPayInfo($model->demand_number, 'pay_amount');
                $set[] = '';//Todo是否逾期
                $set[] = '';//Todo是否核销
                $set[] = '';//Todo退款金额
                $set[] = PurchaseOrderServices::getPayStatus($model->pay_status);
                $set[] = implode("\r\n",self::getDemandPayInfo($model->demand_number, 'payer_time'));
                $set[] = implode("\r\n",self::getDemandPayInfo($model->demand_number, 'requisition_number'));
                $set[] = self::getInvoiceQty($model->demand_number, 'qty');
                $set[] = $model->purchase_quantity - self::getInvoiceQty($model->demand_number, 'qty');
                $set[] = implode("\r\n",self::getInvoiceQty($model->demand_number, 'invoice_code'));
                $replys = PurchaseReply::find()->where(['demand_number'=>$model->demand_number,'replay_type'=>1])->orderBy('id asc')->asArray()->all();
                $string = '';
                if ($replys) {
                    foreach ($replys as $v) {
                        $string .= $v['create_time'].'(by '.$v['create_user'].")\r\n".$v['note']."\r\n";
                    }
                }
                $set[] = $string;
                $replys = PurchaseReply::find()->where(['demand_number'=>$model->demand_number,'replay_type'=>2])->orderBy('id asc')->asArray()->all();
                $string = '';
                if ($replys) {
                    foreach ($replys as $v) {
                        $string .= $v['create_time'].'(by '.$v['create_user'].")\r\n".$v['note']."\r\n";
                    }
                        
                }
                $set[] = $string;
                $notes = PurchaseNote::find()->where(['demand_number'=>$model->demand_number])->orderBy('id asc')->asArray()->all();
                $string = '';
                if ($notes) {
                    foreach ($notes as $v) {
                        $string .= $v['create_time'].'(by '.$v['create_user'].")\r\n".$v['note']."\r\n";
                    }
                }
                $set[] = $string;
                $data[] = $set;
            }
        } else {
            echo '<font color="red">没有符合条件的数据！</font>';
            die;
        }

        if($data){
            $objectPHPExcel = new \PHPExcel();
            $objectPHPExcel->setActiveSheetIndex(0);

            // 设置表头
            foreach ($title as $k => $v) {
                // 自增 列名称
                if($k < 26){
                    $char = chr($k + 65);
                }elseif($k >= 26 AND $k < 52){
                    $char = 'A'.chr($k + 65 - 26);
                }elseif($k >= 52 AND $k < 78){
                    $char = 'B'.chr($k + 65 - 52);
                }elseif($k >= 78){
                    $char = 'C'.chr($k + 65 - 78);
                }else{
                    echo '<font color="red">超过最大列数了，请联系IT处理！</font>';
                    die;
                }
                $objectPHPExcel->getActiveSheet()->setCellValue( $char.'1',$v);
            }

            // 设置列宽
            $objectPHPExcel->getActiveSheet()->getColumnDimension( 'C' )->setWidth(20);
            $objectPHPExcel->getActiveSheet()->getColumnDimension( 'D' )->setWidth(20);

            foreach($data as $key => $v_data){
                $num = $key + 2;

                foreach ($title as $k => $v) {
                    // 自增 列名称
                    if($k < 26){
                        $char = chr($k + 65);
                    }elseif($k >= 26 AND $k < 52){
                        $char = 'A'.chr($k + 65 - 26);
                    }elseif($k >= 52 AND $k < 78){
                        $char = 'B'.chr($k + 65 - 52);
                    }elseif($k >= 78){
                        $char = 'C'.chr($k + 65 - 78);
                    }else{
                        $char = 'A';
                    }

                    if($k == 3){// 第三列展示SKU图片
                        $sku            = $v_data[2];// SKU
                        $product_img    = $v_data[3];// SKU图片地址
                        try {
                            $imgUrl = ProductImgDownload::find()->where(['sku' => $sku,'status' => 1])->one();
                            $url = !empty($imgUrl) ? $imgUrl->image_url : Vhelper::downloadImg($sku,$product_img);
                            if(!file_exists($url)){
                                $url = Vhelper::downloadImg($sku,$product_img);
                            }
                            if($url ){
                                $img = new \PHPExcel_Worksheet_Drawing();
                                $img->setPath($url);//写入图片路径
                                $img->setHeight(80);//写入图片高度
                                $img->setWidth(80);//写入图片宽度
                                $img->setOffsetX(2);//写入图片在指定格中的X坐标值
                                $img->setOffsetY(2);//写入图片在指定格中的Y坐标值
                                $img->setRotation(1);//设置旋转角度
                                $img->getShadow()->setDirection(50);//
                                $img->setCoordinates($char.$num);//设置图片所在表格位置
                                $objectPHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(50);
                                $img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
                            }
                        } catch (\Exception $e) {
                            $objectPHPExcel->getActiveSheet()->setCellValue($char.$num ,' ');
                        }

                    }else{
                        if(isset($v_data[$k]) AND $v_data[$k]){
                            if(is_array($v_data[$k])){
                                $v_data[$k] = current($v_data[$k]);
                            }
                            $objectPHPExcel->getActiveSheet()->setCellValue($char.$num ,$v_data[$k]);
                        }
                    }
                }

            }

            header("Content-type:application/vnd.ms-excel;charset=UTF-8");
            header('Content-Type : application/vnd.ms-excel');
            header('Content-Disposition:attachment;filename="'.'海外仓-采购单-NEW-导出-'.date("Y年m月j日").'.xls"');
            $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
            $objWriter->save('php://output');
            die;

        }else{
            echo '<font color="red">未匹配到目标数据！</font>';
            die;
        }
    }
    
    
    public static function getShipExpressNo($pur_number, $demand_number)
    {
        $data = PurchaseOrderShip::find()->select('id,express_no,cargo_company_id,demand_number')->where(['pur_number'=>$pur_number])->all();
        $string = '';
        if ($data) {
            foreach ($data as $k=>$v) {
                if ($v->demand_number && $v->demand_number != $demand_number) {
                    continue;
                }
                $string .= '<div>'.$v['cargo_company_id'];
                if ($v->demand_number) {
                    $string .= '<a class="delete-express" data-id="'.$v->id.'" href="javascript:;"><i class="fa fa-fw fa-close"></i></a>';
                }
                $string .= "<br><a title='".$v['cargo_company_id']."' href='https://www.kuaidi100.com/chaxun?com&nu=".$v['express_no']."' target='_blank'>".$v['express_no']."</a><br>";
                $string .= '</div>';
            }
        }
        return $string;
    }
    
    public static function getSkuQuoteValue($sku, $name)
    {
        static $_demand_data_sku = [];
        $sku = strtoupper($sku);
        if (isset($_demand_data_sku[$sku])) {
            return $_demand_data_sku[$sku][$name];
        }
        $quoteid = ProductProvider::find()->where(['sku'=>$sku,'is_supplier'=>1])->select('quotes_id')->scalar();
        $product_quote = SupplierQuotes::find()->where(['id'=>$quoteid])->one();
        $_demand_data_sku[$sku]['pur_ticketed_point'] = $product_quote->pur_ticketed_point;
        $_demand_data_sku[$sku]['is_back_tax'] = $product_quote->is_back_tax;
        $_demand_data_sku[$sku]['base_price'] = $product_quote->supplierprice;
        if ($product_quote->is_back_tax == 1) {
            $pur_ticketed_point = is_null($product_quote->pur_ticketed_point)?0:$product_quote->pur_ticketed_point;// 税点为NULL时其值设置为0
            $_demand_data_sku[$sku]['price'] = round($product_quote->supplierprice + $product_quote->supplierprice*$pur_ticketed_point/100, 4);
        } else {
            $_demand_data_sku[$sku]['price'] = $product_quote->supplierprice;
        }
        return $_demand_data_sku[$sku][$name];
    }

    /**
     * @param $demand_number
     * @param $name
     * @param $is_current 是否取最新一次的请款信息
     * @return mixed
     */
    public static function getDemandPayInfo($demand_number, $name,$is_current=null)
    {
        static $_demand_pay_info = [];
        if (!isset($_demand_pay_info[$demand_number])) {
            $paid_models = OrderPayDemandMap::find()
            ->from(OrderPayDemandMap::tableName().' as a')
            ->leftJoin(PurchaseOrderPay::tableName().' as b', 'a.requisition_number = b.requisition_number')
            ->where(['a.demand_number'=>$demand_number])
            //->andWhere(['b.pay_status'=>5])
            ->select('pay_amount,freight,discount,a.requisition_number,b.payer_time,b.pay_status')
            ->orderBy('b.id desc')
            ->all();
            $set = [];
            $set['pay_amount'] = $set['freight'] = $set['discount'] = 0;
            $set['requisition_number'] = $set['payer_time'] = [];
            if ($paid_models) {
                foreach ($paid_models as $v) {
                    $set['requisition_number'][] = $v->requisition_number;
                    if ($v->pay_status == 5) { //财务付款后才显示出来
                        $set['pay_amount'] += $v->pay_amount;
                        $set['payer_time'][] = $v->payer_time;
                    }
                    $set['freight'] += $v->freight;
                    $set['discount'] += $v->discount;
                    if ($is_current) break;
                }
            }
            $_demand_pay_info[$demand_number] = $set;
        }
        
        return $_demand_pay_info[$demand_number][$name];
    }
    /**
     * 获取合同请款的请款单号
     */
    public static  function getCompactPayInfo($compact_number, $name,$is_current=null)
    {
        $agreeStatus = [2, 4, 5, 6, 10, 13]; //付款状态：待处理和已付款状态
        static $_compact_pay_info = [];
        if (!isset($_compact_pay_info[$compact_number])) {
            $paid_models = PurchaseOrderPay::find()
                ->where(['pur_number'=>$compact_number])
                ->select('pur_number,pay_price,requisition_number,payer_time,pay_status')
                ->orderBy('id desc')
                ->all();

            $set = [];
            $set['pay_amount'] = $set['freight'] = $set['discount'] = 0;
            $set['requisition_number'] = $set['payer_time'] = [];
            if ($paid_models) {
                foreach ($paid_models as $v) {
                    if (!in_array($v->pay_status, $agreeStatus)) continue;
                    $set['requisition_number'][] = $v->requisition_number;
//                    $set['freight'] += $v->freight;
//                    $set['discount'] += $v->discount;
                    if ($is_current) break;
                }
            }
            $_compact_pay_info[$compact_number] = $set;
        }
        return $_compact_pay_info[$compact_number][$name];
    }


    /**
     * 获取发票中数量
     * @param $demand_number
     * @param $name
     * @return mixed
     */
    public static function getInvoiceQty($demand_number, $name)
    {
        static $_demand_invoice_qty = [];
        if (!isset($_demand_invoice_qty[$demand_number])) {
            $data = DemandInvoice::findAll(['demand_number'=>$demand_number]);
            $set = ['qty'=>0, 'invoice_code'=>[]];
            if ($data) {
                foreach ($data as $v) {
                    $set['qty'] += $v->qty;
                    $set['invoice_code'][] = $v->invoice_code;
                }
            }
            $_demand_invoice_qty[$demand_number] = $set;
        }
        
        return $_demand_invoice_qty[$demand_number][$name];
    }


}
