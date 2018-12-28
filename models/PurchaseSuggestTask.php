<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_suggest_task".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $purchase_num
 * @property string $create_time
 */
class PurchaseSuggestTask extends BaseModel
{
    
    const TASK_GET_SKU_SALES        = 'getSkuSales';        //获取销量
    const TASK_GET_SKU_OUTOFSTOCK   = 'getSkuOutofstock';   //获取缺货
    
    const TASK_STATUS_INIT              = 0;          //初始化
    const TASK_STATUS_RUNNING           = 1;          //运行中
    const TASK_STATUS_FAILED            = -1;         //运行失败
    const TASK_STATUS_SUCCESS           = 2;          //运行成功
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_suggest_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_name', 'task_status', 'execute_time', 'end_time', 'create_time', 'page', 
                'group_id', 'thread_number','total_page', 'error_message'], 'safe'],
        ];
    }
    
    /**
     * @desc检查任务是否可以运行
     * @param unknown $taskName
     * @param unknown $page
     * @param unknown $startTime
     * @param unknown $endTime
     * @param number $maxRunTime
     * @return boolean|string
     */
    public static function checkRunable($taskName, $page, $startTime, $endTime, $maxRunTime = 90)
    {
        $taskModel = self::find()->select("*")
            ->where(['task_name' => $taskName])
            ->andWhere(['page' => $page])
            ->andWhere(['between', 'create_time', $startTime, $endTime])
            ->orderBy("create_time desc")
            ->limit(1)
            ->one();
        if (!empty($taskModel))
        {
            //如果运行时间超过最大运行时间，把任务重置为失败
            if ($taskModel->task_status == self::TASK_STATUS_RUNNING)
            {
                if ((time() - strtotime($taskModel->execute_time)) >= $maxRunTime)
                {
                    $taskModel->task_status = self::TASK_STATUS_FAILED;
                    $taskModel->error_message = 'Run Time Exceed ' . $maxRunTime;
                    if ($taskModel->save())
                        return true;
                    else
                        return fasle;
                }
                return false;
            }
            if ($taskModel->task_status == self::TASK_STATUS_SUCCESS)
                return false;
        }
        return true;
    }
    
    /**
     * @desc检查任务是否可以运行
     * @param unknown $taskName
     * @param unknown $startTime
     * @param unknown $endTime
     * @return boolean|string
     */
    public static function checkTaskDone($taskName, $startTime, $endTime)
    {
        $taskInfos = PurchaseSuggestTask::find()->select('page, total_page')
            ->where(['task_name' => PurchaseSuggestTask::TASK_GET_SKU_SALES])
            ->andWhere(['between', 'create_time', $startTime, $endTime])
            ->andWhere(['task_status' => PurchaseSuggestTask::TASK_STATUS_SUCCESS])
            ->groupBy('page')
            ->all();
        $pages = [];
        if (empty($taskInfos))
            return false;
        $pageCount = 0;
        $totalPage = $taskInfos[0]->total_page;
        if (sizeof($taskInfos) >= $totalPage)
            return true;
        return false;
    }
}
