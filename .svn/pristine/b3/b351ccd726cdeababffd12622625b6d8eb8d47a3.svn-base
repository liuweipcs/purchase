<?php
namespace app\synchcloud\models;
use Yii;
class MidPurchaseIn extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_in}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    // 获取员工编号为null的数据 
    public function getData($limit = 10)
    {
        $models = self::find()->where(['hrms_code' => ''])->limit($limit)->all();
        return $models;
    }

    /**
     * 批量写入MID采购订单主表
     */
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = self::findOne($v['id']);
            if(empty($model)) {
                continue;
            }
            $model->hrms_code = $v['hrms_code'];           // 员工编号
            if($v['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
                $model->org_id = '101';
            } elseif($v['is_drawback'] == 2) {
                $model->org_id = '102';
            }

            $res = $model->save(false);
            if($res) {
                $success[] = $v['id'];
            }
        }
        return $success;
    }
    
    /**
     * @desc 获取待更新开票点的采购入库单据 -- 针对海外仓和东莞仓入库
     */
    public function getSyncInStockTaxData($fields='*',$limit=100,$conditions='')
    {
    	$where = ' a.datasource in(1,2) ';
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
		    	->select($fields)
		    	->from(self::tableName().' as a')
		    	->leftJoin(MidPurchaseInDetail::tableName().' as b', 'a.bill_no = b.bill_no')
		    	->where($where)
		    	->andWhere('b.ticketed_point is null');
		    	
    	
    	if( empty($conditions) ){
    		$obj->andWhere('b.qty_time >="2018-01-01 00:00:00" and b.qty_time < "2018-02-01 00:00:00"');
    	}
    
    	$obj->limit($limit);
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * @desc 获取待更新组织ID的采购入库单据
     */
    public function getSyncInStockOrgData($fields='*',$limit=100,$conditions='')
    {
    	$where = ' 1=1 ';
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.org_id=""')
	    	->limit($limit);
    	 
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新入库单组织id
     */
    public function saveOrgRows($rows)
    {
    	$success = [];
    	foreach($rows as $k => $v) {
    		$model = self::findOne($v['id']);
    		if(empty($model)) {
    			continue;
    		}
    		
    		$model->org_id = $rows['org_id'];
    
    		$res = $model->save(false);
    		if($res) {
    			$success[] = $v['id'];
    		}
    	}
    	return $success;
    }
}