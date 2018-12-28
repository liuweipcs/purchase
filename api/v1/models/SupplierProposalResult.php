<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_stock".
 *
 * @property string $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $on_way_stock
 * @property string $available_stock
 * @property string $stock
 * @property integer $left_stock
 * @property string $created_at
 * @property string $update_at
 */
class SupplierProposalResult extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_proposal_result}}';
    }
	
	
	
	 
     public static function SaveProposalresult($data)
    {
        try {
			// $i = 0;
			//if(gettype($data->data)=='object'){
				// echo gettype($data->data);
			// }else{
				// echo "dddddddddddddddddd";
			// }
			
		
			foreach ($data->data->array as $k => $datass) {
				// $i++;
				$models = self::find()->where(['goodsSku' => $datass->goodsSku])->one();
				if ($models) {
					   $models->caculatedate        = $datass->caculateDate;
					   $models->currstockquantity       = $datass->currStockQuantity;
					   $models->proposalquantity       = $datass->proposalQuantity;
					   $models->goodsidkey      = $datass->goodsIdKey;
					   $models->warehouseidkey       = $datass->warehouseIdKey;
					   $models->intransitstockquantity = $datass->intransitStockQuantity;
					   $models->warehousename      = $datass->warehouseName;
					   $models->saleavg7    = $datass->saleAvg7;
					   $models->saleavg15       = $datass->saleAvg15;
					   $models->devliverydays     = $datass->devliveryDays;
					   $models->saleavg30    = $datass->saleAvg30;
					   $models->unpickingquantity    = $datass->unpickingQuantity;
					   $models->dailysales     = $datass->dailySales;
					   $models->update();

				} else {
					$model                      = new self;
					$model->goodsSku            = $datass->goodsSku;
				   $model->caculatedate        = $datass->caculateDate;
				   $model->currstockquantity       = $datass->currStockQuantity;
				   $model->proposalquantity       = $datass->proposalQuantity;
				   $model->goodsidkey      = $datass->goodsIdKey;
				   $model->warehouseidkey       = $datass->warehouseIdKey;
				   $model->intransitstockquantity = $datass->intransitStockQuantity;
				   $model->warehousename      = $datass->warehouseName;
				   $model->saleavg7    = $datass->saleAvg7;
				   $model->saleavg15       = $datass->saleAvg15;
				   $model->devliverydays     = $datass->devliveryDays;
				   $model->saleavg30    = $datass->saleAvg30;
				   $model->unpickingquantity    = $datass->unpickingQuantity;
				   $model->dailysales     = $datass->dailySales;
				   $model->save();


				}
			}

        } catch (Exception $e) {
			echo $e->getMessage();

        }
    }


}
