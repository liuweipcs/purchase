<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/30
 * Time: 9:15
 */

namespace app\api\v1\controllers;

use app\config\Vhelper;
use app\api\v1\models\DevelopTrack;
use app\api\v1\models\PlatformSummary;
use app\api\v1\models\PurchaseOrderItems;
use yii;
use linslin\yii2\curl;
use yii\helpers\Json;
use app\services\BaseServices;


class DevelopTrackController extends BaseController
{
    public $cg_model;
    //=================================== 开发节点 ==============================================
    /**
     * 开发节点总接口
     * 
     */
    public function actionGetDevelopNode()
    {
        set_time_limit(0);
        //ini_set('memory_limit', '1024M');
        $time = \yii::$app->request->get('time');
        $res_info = [];

        if (empty($time)) {
            $date_start = "2016-07";
            $date_end = date('Y-m', time());
            $status = false;
            do{
                $date_start = date('Y-m',strtotime($date_start . " +1 month"));
                $status = ($date_start < $date_end);
                $res_info = $this->beginRun($date_start,$res_info);
                sleep(60);
            }while($status);
        } else {
            $res_info = $this->beginRun($time,$res_info,$show_s=true);
        }
        Vhelper::dump($res_info);
    }
    /**
     * 数据开始运行
     */
    public function beginRun($time=null,$res=array(),$show_s=false)
    {
        if (empty($time)) {
            $url     = yii::$app->params['SKU_ERP_URL'] . '/services/api/product/skuinfo/method/getskudevpinfo';
        } else {
            $url     = yii::$app->params['SKU_ERP_URL'] . '/services/api/product/skuinfo/method/getskudevpinfo/time/' . $time;
        }
        $curl    = new curl\Curl();
        $s       = $curl->post($url);
        
        //验证json
        $sb = Vhelper::is_json($s);
        if (!$sb) {
            if ($show_s) {
                exit($s);
            } else {
                $res[1][] = '请检查json'. $time; 
            }

        } else {
            $_result = Json::decode($s);
            if (!empty($_result['datas']) && $_result['datas']) {
                foreach ($_result['datas'] as $key => $value) {
                    //if ($value['sku'] != 'US-QC07791') {
                    //    continue;
                    //}
                    $this->kfCreate($value);
                }
                $res[2][] = '完成' . $time; //exit($s);
            } else {
                if ($show_s) {
                    exit($s);
                } else {
                    $res[3][] = '无数据' . $time;
                }
                
            }
        }
        return $res;
    }
    /**
     * 产品开发日期：抓取开发人员 从erp录取sku的时间（产品管理—常规新品开发）
     */
    public function kfCreate($data)
    {
        $kf_model = DevelopTrack::find()
            ->where(['in', 'sku', $data['sku']])
            ->orderBy('id DESC')
            ->one();

        // Vhelper::dump($kf_model);

        if (!empty($kf_model)) {
            if ($kf_model->jiedian_status == 1) {
                //完成
                return false;
            } elseif ($kf_model->jiedian_status == -1) {
                //截断
                if ($kf_model->kf_audit_status == 2) {
                    if ($data['kf_audit_status'] == 2) {
                        //状态没有更新
                        return false;
                    }
                    //如果开发的审核状态为驳回，erp的审核状态为：通过，则新增数据
                    $kf_model = DevelopTrack::saveOldInfo($kf_model);
                    $kf_model->kf_create_time = !empty($data['kf_create_time']) ? $data['kf_create_time'] : null;
                    $kf_model->kf_user = !empty($data['kf_user']) ? $data['kf_user'] : '';
                    $kf_model->kf_audit_status = $data['kf_audit_status'];
                    $kf_model->kf_audit_time = !empty($data['kf_audit_time']) ? $data['kf_audit_time'] : null;
                    $kf_model->kf_audit_user = $data['kf_audit_user'];
                    $status = $kf_model->save();
                    if (empty($data['kf_zhijian_status'])) {
                        return false;
                    }

                    //如果质检，截断，则跳出
                    $kf_model->kf_zhijian_status = !empty($data['kf_zhijian_status']) ? $data['kf_zhijian_status'] : '';
                    $kf_model->kf_zhijian_user   = !empty($data['kf_zhijian_user']) ? $data['kf_zhijian_user'] : '';
                    $kf_model->kf_zhijian_time   = !empty($data['kf_zhijian_time']) ? $data['kf_zhijian_time'] : null;
                    $kf_model->jiedian_status = $this->jiedianStatus($data['kf_zhijian_status']);
                    $status = $kf_model->save();
                    if ($kf_model->kf_zhijian_status == -1) {
                        return false;
                    }

                    $this->cgXiaoshou($kf_model, $data);
                } elseif ($kf_model->kf_zhijian_status == 2) {
                    if ($data['kf_zhijian_status'] == 2) {
                        //状态没更新
                        return false;
                    }
                    //如果开发的质检状态为驳回，erp的审核状态为：通过，则新增数据
                    $kf_model = DevelopTrack::saveOldInfo($kf_model);
                    $kf_model->kf_zhijian_status = !empty($data['kf_zhijian_status']) ? $data['kf_zhijian_status'] : '';
                    $kf_model->kf_zhijian_user   = !empty($data['kf_zhijian_user']) ? $data['kf_zhijian_user'] : '';
                    $kf_model->kf_zhijian_time   = !empty($data['kf_zhijian_time']) ? $data['kf_zhijian_time'] : null;
                    $status = $kf_model->save();
                    $this->cgXiaoshou($kf_model, $data);
                } else {
                    //如果有采购的数据，就下一步
                    $cg_model = DevelopTrack::saveOldInfo($kf_model);
                    // Vhelper::dump($cg_model);
                    $this->cgXiaoshou($cg_model, $data, $kf_model->demand_number);
                }
            } else {
                //继续
                if (empty($data['kf_audit_status'])) {
                    //如果审核数据为空
                    return false;
                }
                //如果继续，就更新走下一步
                $kf_model->kf_audit_status = $data['kf_audit_status'];
                $kf_model->kf_audit_time = !empty($data['kf_audit_time']) ? $data['kf_audit_time'] : null;
                $kf_model->kf_audit_user = $data['kf_audit_user'];
                // Vhelper::dump($kf_model);

                if ($data['kf_audit_status'] == 2) {
                    //驳回
                    $kf_model->jiedian_status = -1;
                    $status = $kf_model->save();
                    return false;
                }

                if (empty($data['kf_zhijian_status'])) {
                    //如果质检数据为空
                    $status = $kf_model->save();
                    return false;
                }

                $kf_model->kf_zhijian_status = !empty($data['kf_zhijian_status']) ? $data['kf_zhijian_status'] : '';
                $kf_model->kf_zhijian_user   = !empty($data['kf_zhijian_user']) ? $data['kf_zhijian_user'] : '';
                $kf_model->kf_zhijian_time   = !empty($data['kf_zhijian_time']) ? $data['kf_zhijian_time'] : null;

                if ($data['kf_zhijian_status'] == 2) {
                    //驳回
                    $kf_model->jiedian_status = -1;
                    $status = $kf_model->save();
                    return false;
                } else {

                    $status = $kf_model->save();
                    $this->cgXiaoshou($kf_model, $data);
                }
            }
        } else {
            //如果不存在，就新增
            $kf_model = new DevelopTrack();
            $kf_model->sku = $data['sku'];
            $kf_model->kf_create_time = !empty($data['kf_create_time']) ? $data['kf_create_time'] : null;
            $kf_model->kf_user = !empty($data['kf_user']) ? $data['kf_user'] : '';
            if (empty($data['kf_audit_status'])) {
                $status = $kf_model->save(false);
                return false;
            }

            $kf_model->kf_audit_status = $data['kf_audit_status'];
            $kf_model->kf_audit_time = !empty($data['kf_audit_time']) ? $data['kf_audit_time'] : null;
            $kf_model->kf_audit_user = $data['kf_audit_user'];
            $kf_model->jiedian_status = $this->jiedianStatus($data['kf_audit_status']);

            if ($kf_model->jiedian_status == -1 || empty($data['kf_zhijian_status'])) {
                $status = $kf_model->save(false);
                return false;
            }

            $kf_model->kf_zhijian_status = !empty($data['kf_zhijian_status']) ? $data['kf_zhijian_status'] : '';
            $kf_model->kf_zhijian_user   = !empty($data['kf_zhijian_user']) ? $data['kf_zhijian_user'] : '';
            $kf_model->kf_zhijian_time   = !empty($data['kf_zhijian_time']) ? $data['kf_zhijian_time'] : null;
            $kf_model->jiedian_status = $this->jiedianStatus($data['kf_zhijian_status']);
            if ($kf_model->jiedian_status == -1) {
                $status = $kf_model->save(false);
                return false;
            }
            // Vhelper::dump($data,$kf_model);
            $this->cgXiaoshou($kf_model,$data);
        }
    }
    //=================================== 采购节点 =============================================
    /**
     * 销售建单日期：获取采购系统【海外仓】—【采购需求汇总】的需求时间
     */
    public function cgXiaoshou($cg_model,$data,$demand_number=null)
    {
        $xiaoshou_info = PlatformSummary::getXiaoshouInfo($data['sku']);
        if (empty($xiaoshou_info)) {
            return false;
        }

        $demand_number_arr = array_column($xiaoshou_info, 'demand_number');
        $demand_number_str = implode("','", $demand_number_arr);
        //Vhelper::dump($demand_number_str);


        $sql="SELECT ps.demand_number, pd.pur_number, ps.sku, ps.create_time as cg_xiaoshou_time,ps.create_id as cg_xiaoshou_user,ps.level_audit_status as cg_xiaoshou_audit_status,ps.purchase_time as cg_xiaoshou_audit_time,ps.buyer as cg_xiaoshou_audit_user,po.buyer as cg_suggest_user,po.created_at as cg_suggest_time,po.auditor as cg_audit_user,po.audit_time as cg_audit_time,po.purchas_status as cg_audit_status, pop.applicant as cg_shenqing_pay_user, pop.application_time as cg_shenqing_pay_time,pop.payer as cg_caiwu_pay_user,pop.payer_time as cg_caiwu_pay_time,pop.pay_status as cg_caiwu_pay_status,pop.processing_time as cg_caiwu_audit_time, pop.approver as cg_caiwu_audit_user
            FROM pur_purchase_demand pd
            LEFT JOIN pur_platform_summary ps ON ps.demand_number=pd.demand_number
            LEFT JOIN pur_purchase_order po ON po.pur_number=pd.pur_number 
            LEFT JOIN pur_purchase_order_pay pop ON pop.pur_number=pd.pur_number
            WHERE po.purchase_type=2 AND pd.demand_number IN ('" . $demand_number_str . "') GROUP BY pd.pur_number";

        // LEFT JOIN pur_purchase_order_items poi ON poi.pur_number=pd.pur_number
        $order_info=Yii::$app->db->createCommand($sql)->queryAll();

        if (empty($order_info)) {
            //如果没有找到数据，就返回
            return false;
        }
         // Vhelper::dump($order_info);
        // Vhelper::dump($cg_model);
        $flag = true;
        foreach ($order_info as $key => $value) {
            if (!empty($demand_number)) {
                if ($flag) {
                    //如果$demand_number不为空，则为新复制的数据
                    if ($demand_number == $value['demand_number']) {
                        if (count($order_info) == $key+1) {
                            //如果总数据，等于当前的键值，则说明是最后一个，就直接退出
                            $cg_model->delete();
                            break;
                        } else {
                            $flag = false;
                        }
                    }
                    continue;
                }
            }

            if ($cg_model->cg_xiaoshou_time || $cg_model->cg_suggest_time) {
                //如果有这个两个时间，就说明是要更新数据
                if ($cg_model->demand_number == $value['demand_number']) {
                    
                } else {
                    continue;
                }
            }
            
            //Vhelper::dump($cg_model->demand_number,$value['demand_number'],$order_info);

            $cg_model->pur_number = $value['pur_number'];
            $cg_model->demand_number = $value['demand_number'];

            //销售建单日期
            $cg_model->cg_xiaoshou_time = !empty($value['cg_xiaoshou_time']) ? $value['cg_xiaoshou_time'] : null; //创建日期
            $cg_model->cg_xiaoshou_user = $value['cg_xiaoshou_user']; //创建人

            //销售审核通过日期：驳回和通过只有一条数据
            $cg_model->cg_xiaoshou_audit_status = $value['cg_xiaoshou_audit_status']; //审核状态：4驳回，其他：同意
            $cg_model->cg_xiaoshou_audit_time = !empty($value['cg_xiaoshou_audit_time']) ? $value['cg_xiaoshou_audit_time'] : null; //确认日期
            $cg_model->cg_xiaoshou_audit_user = $value['cg_xiaoshou_audit_user']; //操作人


            if ($value['cg_xiaoshou_audit_status'] == 3 || $value['cg_xiaoshou_audit_status']==5) {
                //3撤销，5删除
                $cg_model->jiedian_status = -1;
                $cg_status = $cg_model->save();
                break;
            }
            if (empty($value['cg_suggest_time'])) {
                $cg_status = $cg_model->save();
                break;
            }

            //采购建议生成日期:采购计划单，??是采购几乎单生成时间，还是提交时间
            $cg_model->cg_suggest_time = !empty($value['cg_suggest_time']) ? $value['cg_suggest_time'] : null; //确认日期
            $cg_model->cg_suggest_user = $value['cg_suggest_user']; //确认人
            if (empty($value['cg_audit_time'])) {
                $cg_status = $cg_model->save();
                break;
            }

            //采购审核通过日期：驳回和同意都是同一时间，：submit_time:提交时间??
            $cg_model->cg_audit_status = $value['cg_audit_status']; //审核状态
            $cg_model->cg_audit_time = !empty($value['cg_audit_time']) ? $value['cg_audit_time'] : null; //审核日期
            $cg_model->cg_audit_user = $value['cg_audit_user']; //审核人
            if ($value['cg_audit_status']==4 || $value['cg_audit_status']==10) {
                //4取消，10作废
                $cg_model->jiedian_status = -1;
                $cg_status = $cg_model->save();
                break;
            }
            if (empty($value['cg_shenqing_pay_time'])) {
                $cg_status = $cg_model->save();
                break;
            }

            //申请付款日期
            $cg_model->cg_shenqing_pay_time = $value['cg_shenqing_pay_time']; //日期
            $cg_model->cg_shenqing_pay_user = !empty($value['cg_caiwu_pay_user']) ? BaseServices::getEveryOne($value['cg_shenqing_pay_user']) : ''; //申请人
            if (empty($value['cg_caiwu_pay_status'])) {
                $cg_status = $cg_model->save();
                break;
            }
            //财务付款日期 
            $cg_model->cg_caiwu_pay_status = $value['cg_caiwu_pay_status']; //审核状态
            $cg_model->cg_caiwu_audit_time = $value['cg_caiwu_audit_time']; //财务审批时间??
            $cg_model->cg_caiwu_audit_user = $value['cg_caiwu_audit_user']; //财务审批人??
            $cg_model->cg_caiwu_pay_time = $value['cg_caiwu_pay_time']; //财务付款时间
            $cg_model->cg_caiwu_pay_user = !empty($value['cg_caiwu_pay_user']) ? BaseServices::getEveryOne($value['cg_caiwu_pay_user']) : ''; //付款人
            if ($value['cg_caiwu_pay_status']==0) {
                # 作废
                $cg_model->jiedian_status = -1;
                $cg_status = $cg_model->save();
                break;
            } else {
                //否则就走仓库数据
                $cg_status = $cg_model->save();
                $this->wmsDaohuo($cg_model, $value['pur_number'], $value['sku']);
                break;
            }
        }
    }

    //=============================== 仓库节点 ==============================================
    /**
     * 采购到货日期：获取【海外仓】-【采购单】的采购到货日期
     */
    public function wmsDaohuo($cg_model,$pur_number, $sku)
    {
        $url     = yii::$app->params['wms_abd'] . '/api/purStock/pushOrderstatus';
        $curl    = new curl\Curl();

        $data[$pur_number]  = [$sku];
        $s  = $curl->setGetParams(['purStatus'  => Json::encode($data)])->post($url);
        //验证json
        $sb = Vhelper::is_json($s);
        //Vhelper::dump($s);

        if (!$sb) {
            return '请检查json'."\r\n";
        } else {
            $_result = Json::decode($s);
            $wms_info = $_result[$sku];
            //Vhelper::dump($wms_info);

            //采购到货日期
            if (!empty($wms_info['pur'])) {
                /**
                 *  no-one：未到货
                 *  part：部分到货
                 *  excep：异常
                 *  all：全部到货
                 */
               
                if ($wms_info['pur']['hwc_daohuo_status'] == 'no-one') {
                    $daohuo_status = 1;
                } elseif ($wms_info['pur']['hwc_daohuo_status'] == 'part') {
                    $daohuo_status = 2;
                } elseif ($wms_info['pur']['hwc_daohuo_status'] == 'excep') {
                    $daohuo_status = 3;
                } elseif ($wms_info['pur']['hwc_daohuo_status'] == 'all') {
                    $daohuo_status = 4;
                } else {
                    $daohuo_status = 0;
                }
                $cg_model->wms_daohuo_status = $daohuo_status;
                $cg_model->wms_daohuo_time = !empty($wms_info['pur']['hwc_daohuo_time']) ? $wms_info['pur']['hwc_daohuo_time'] : null;
                $cg_model->wms_daohuo_user = !empty($wms_info['pur']['hwc_daohuo_user']) ? $wms_info['pur']['hwc_daohuo_user'] : null;
                $cg_model->save();

            } 

            //仓库拆包质检日期
            if (!empty($wms_info['quality'])) {
                $cg_model->wms_zhijian_time = !empty($wms_info['quality']['hwc_zhijian_time']) ? $wms_info['quality']['hwc_zhijian_time'] : null;
                $cg_model->wms_zhijian_user = !empty($wms_info['quality']['hwc_zhijian_user']) ? $wms_info['quality']['hwc_zhijian_user'] : null;
                $cg_model->save();
            }

            //仓库入库日期
            if (!empty($wms_info['putaway'])) {
                $cg_model->wms_ruku_time = !empty($wms_info['putaway']['hwc_ruku_time']) ? $wms_info['putaway']['hwc_ruku_time'] : null;
                $cg_model->wms_ruku_user = !empty($wms_info['putaway']['hwc_ruku_user']) ? $wms_info['putaway']['hwc_ruku_user'] : null;
                $cg_model->save();

            }

            //仓库发货日期
            if (!empty($wms_info['shipPick'])) {
                $cg_model->wms_fahuo_time = !empty($wms_info['shipPick']['hwc_fahuo_time']) ? $wms_info['shipPick']['hwc_fahuo_time'] : null;
                $cg_model->wms_fahuo_user = !empty($wms_info['shipPick']['hwc_fahuo_user']) ? $wms_info['shipPick']['hwc_fahuo_user'] : null;
                $cg_model->save();

            }

            //创建备货单日期
            if (!empty($wms_info['createPick'])) {
                $cg_model->wms_beihuo_time = !empty($wms_info['createPick']['hwc_beihuo_time']) ? $wms_info['createPick']['hwc_beihuo_time'] : null;
                $cg_model->wms_beihuo_user = !empty($wms_info['createPick']['hwc_beihuo_user']) ? $wms_info['createPick']['hwc_beihuo_user'] : null;
                $cg_model->save();

            }
            //物流组审核日期
            if (!empty($wms_info['logisticPick'])) {
                $cg_model->wms_audit_time = !empty($wms_info['logisticPick']['hwc_audit_time']) ? $wms_info['logisticPick']['hwc_audit_time'] : null;
                $cg_model->wms_audit_user = !empty($wms_info['logisticPick']['hwc_audit_user']) ? $wms_info['logisticPick']['hwc_audit_user'] : null;
                $cg_model->save();

            }
            //仓库拣货日期
            if (!empty($wms_info['actualPick'])) {
                $cg_model->wms_jianhuo_time = !empty($wms_info['actualPick']['hwc_jianhuo_time']) ? $wms_info['actualPick']['hwc_jianhuo_time'] : null;
                $cg_model->wms_jianhuo_user = !empty($wms_info['actualPick']['hwc_jianhuo_user']) ? $wms_info['actualPick']['hwc_jianhuo_user'] : null;
                $cg_model->save();

            }
            //物流
            if (!empty($wms_info['acceptOversea'])) {
                if (!empty($wms_info['acceptOversea']['wl_yanhuo_time'])) {
                    $cg_model->wl_yanhuo_time = $wms_info['acceptOversea']['wl_yanhuo_time'];
                }
                if (!empty($wms_info['acceptOversea']['wl_shangjia_time'])) {
                    $cg_model->wl_shangjia_time = $wms_info['acceptOversea']['wl_shangjia_time'];
                    $cg_model->jiedian_status = 1;
                }
                $cg_model->save();
            }
        }
    }
    /**
     * 仓库拆包质检日期：获取中转仓库系统【质检任务列表】-【质检】的完成时间
     */
    /**
     * 仓库到货日期：获取中转仓库系统【仓库入库列表】-【上架】的完成上架时间
     */
    /**
     * 仓库发货日期：获取中转仓库系统【发货列表】-【发货】的发货时间
     */
    /**
     * 创建备货单日期：获取物流系统【发货管理】-【可用发货列表】生成拣货单的时间
     */
    /**
     * 物流审核日期：获取物流系统【发货管理】-【拣货单物流审核】通过/驳回的时间
     */
    /**
     * 仓库拣货日期：获取物流系统【发货管理】-【发货】点击拣货的时间
     */
    /**
     * 物流商验货时间：获取物流系统【物流管理】-【入库清单】-验货完成时间
     */
    /**
     * 海外仓上架时间：获取物流系统【物流管理】-【入库清单】-海外仓上架时间
     */

    /**
     * 根据传过来的状态，来判断节点状态
     */
    public function jiedianStatus($status)
    {
            //传过来的数据状态      节点状态
            //$status = 1 通过       0  继续
            //$status = 2 驳回       -1 驳回
            //$status = 3 等待中     2   等待中
            //$status = ($status == 1) ? 0 : ( ($status == 2) ? -1 : 2 );
            return ($status === 1) ? 0 : ( ($status === 2) ? -1 : 2 );
    }
    function req_curl($url, &$status = null, $options = array())
    {
        $res = '';
        $options = array_merge(array(
            'follow_local' => true,
            'timeout' => 30,
            'max_redirects' => 4,
            'binary_transfer' => false,
            'include_header' => false,
            'no_body' => false,
            'cookie_location' => dirname(__FILE__) . '/cookie',
            'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1',
            'post' => array() ,
            'referer' => null,
            'ssl_verifypeer' => 0,
            'ssl_verifyhost' => 0,
            'headers' => array(
                'Expect:'
            ) ,
            'auth_name' => '',
            'auth_pass' => '',
            'session' => false
        ) , $options);
        $options['url'] = $url;

    $s = curl_init();

    if (!$s) return false;

    curl_setopt($s, CURLOPT_URL, $options['url']);
        curl_setopt($s, CURLOPT_HTTPHEADER, $options['headers']);
        curl_setopt($s, CURLOPT_SSL_VERIFYPEER, $options['ssl_verifypeer']);
        curl_setopt($s, CURLOPT_SSL_VERIFYHOST, $options['ssl_verifyhost']);
        curl_setopt($s, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($s, CURLOPT_MAXREDIRS, $options['max_redirects']);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_FOLLOWLOCATION, $options['follow_local']);
        curl_setopt($s, CURLOPT_COOKIEJAR, $options['cookie_location']);
        curl_setopt($s, CURLOPT_COOKIEFILE, $options['cookie_location']);
        if (!empty($options['auth_name']) && is_string($options['auth_name']))
        {
            curl_setopt($s, CURLOPT_USERPWD, $options['auth_name'] . ':' . $options['auth_pass']);
        }
        if (!empty($options['post']))
        {
            curl_setopt($s, CURLOPT_POST, true);
            curl_setopt($s, CURLOPT_POSTFIELDS, $options['post']);
            //curl_setopt($s, CURLOPT_POSTFIELDS, array('username' => 'aeon', 'password' => '111111'));
        }
        if ($options['include_header'])
        {
            curl_setopt($s, CURLOPT_HEADER, true);
        }
        if ($options['no_body'])
        {

    curl_setopt($s, CURLOPT_NOBODY, true);
        }
        if ($options['session'])
        {
            curl_setopt($s, CURLOPT_COOKIESESSION, true);
            curl_setopt($s, CURLOPT_COOKIE, $options['session']);
        }
        curl_setopt($s, CURLOPT_USERAGENT, $options['useragent']);
        curl_setopt($s, CURLOPT_REFERER, $options['referer']);
        $res = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        curl_close($s);
        return $res;
    }
}