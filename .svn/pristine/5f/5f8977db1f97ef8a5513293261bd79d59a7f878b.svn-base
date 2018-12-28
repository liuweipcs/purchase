<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%amazon_orders}}".
 *
 * @property string $id
 * @property string $sdate
 * @property string $stime
 * @property string $ship_name
 * @property integer $rs_state
 * @property string $sku
 * @property integer $qty
 * @property integer $pro_weight
 * @property string $platform
 * @property string $account
 * @property string $sales_site
 * @property string $warehouse
 * @property string $parcel_number
 * @property string $mailing_way
 * @property integer $total_weight
 * @property integer $total_freight
 * @property string $tracking_number
 * @property string $order_number
 * @property integer $item_id
 * @property string $item_title
 * @property string $buyer_id
 * @property string $buyer_name
 * @property string $country
 * @property string $shipping_address1
 * @property string $shipping_address2
 * @property string $city
 * @property string $province
 * @property string $zip_code
 * @property string $phone
 * @property string $complete_address
 * @property string $payment_date
 * @property string $payment_time
 * @property string $sales_date
 * @property string $sales_time
 * @property string $receipt_paypal
 * @property string $payment_paypal
 * @property string $merchandiser
 * @property string $product_developer
 * @property string $inquirer
 * @property string $buyer
 * @property string $receiving_currency
 * @property string $order_total_price
 * @property string $rmb_order_total_price
 * @property string $price
 * @property string $rmb_price
 * @property string $commodity_cost
 * @property string $channel_transaction_currency
 * @property string $channel_payment_fee
 * @property string $rmb_channel_payment_fee
 * @property string $paypal_rate
 * @property string $paypal_fee
 * @property string $rmb_paypal_fee
 * @property string $channel_costs
 * @property string $first_way_of_transport
 * @property string $first_time_freight
 * @property string $headage_declaration_fee
 * @property string $packaging_materials
 * @property string $packaging_costs
 * @property string $freight
 * @property string $profit
 * @property string $profit_margins
 */
class AmazonOrders extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%amazon_orders}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sdate', 'stime', 'payment_date', 'payment_time', 'sales_date', 'sales_time'], 'safe'],
            [['rs_state', 'qty', 'pro_weight', 'total_weight', 'total_freight', 'item_id'], 'integer'],
            [['order_total_price', 'rmb_order_total_price', 'price', 'rmb_price', 'commodity_cost', 'channel_payment_fee', 'rmb_channel_payment_fee', 'paypal_fee', 'rmb_paypal_fee', 'channel_costs', 'first_time_freight', 'headage_declaration_fee', 'packaging_costs', 'freight', 'profit'], 'number'],
            [['ship_name', 'sku', 'warehouse', 'parcel_number', 'mailing_way', 'tracking_number', 'order_number', 'buyer_id', 'buyer_name', 'city', 'province', 'zip_code', 'phone', 'merchandiser', 'product_developer', 'inquirer', 'buyer', 'packaging_materials'], 'string', 'max' => 50],
            [['platform', 'account', 'sales_site'], 'string', 'max' => 30],
            [['item_title', 'shipping_address1', 'shipping_address2', 'complete_address', 'receipt_paypal', 'payment_paypal', 'first_way_of_transport'], 'string', 'max' => 100],
            [['country', 'receiving_currency', 'channel_transaction_currency', 'profit_margins'], 'string', 'max' => 10],
            [['paypal_rate'], 'string', 'max' => 12],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sdate' => Yii::t('app', 'Sdate'),
            'stime' => Yii::t('app', 'Stime'),
            'ship_name' => Yii::t('app', 'Ship Name'),
            'rs_state' => Yii::t('app', 'Rs State'),
            'sku' => Yii::t('app', 'Sku'),
            'qty' => Yii::t('app', 'Qty'),
            'pro_weight' => Yii::t('app', 'Pro Weight'),
            'platform' => Yii::t('app', 'Platform'),
            'account' => Yii::t('app', 'Account'),
            'sales_site' => Yii::t('app', 'Sales Site'),
            'warehouse' => Yii::t('app', 'Warehouse'),
            'parcel_number' => Yii::t('app', 'Parcel Number'),
            'mailing_way' => Yii::t('app', 'Mailing Way'),
            'total_weight' => Yii::t('app', 'Total Weight'),
            'total_freight' => Yii::t('app', 'Total Freight'),
            'tracking_number' => Yii::t('app', 'Tracking Number'),
            'order_number' => Yii::t('app', 'Order Number'),
            'item_id' => Yii::t('app', 'Item ID'),
            'item_title' => Yii::t('app', 'Item Title'),
            'buyer_id' => Yii::t('app', 'Buyer ID'),
            'buyer_name' => Yii::t('app', 'Buyer Name'),
            'country' => Yii::t('app', 'Country'),
            'shipping_address1' => Yii::t('app', 'Shipping Address1'),
            'shipping_address2' => Yii::t('app', 'Shipping Address2'),
            'city' => Yii::t('app', 'City'),
            'province' => Yii::t('app', 'Province'),
            'zip_code' => Yii::t('app', 'Zip Code'),
            'phone' => Yii::t('app', 'Phone'),
            'complete_address' => Yii::t('app', 'Complete Address'),
            'payment_date' => Yii::t('app', 'Payment Date'),
            'payment_time' => Yii::t('app', 'Payment Time'),
            'sales_date' => Yii::t('app', 'Sales Date'),
            'sales_time' => Yii::t('app', 'Sales Time'),
            'receipt_paypal' => Yii::t('app', 'Receipt Paypal'),
            'payment_paypal' => Yii::t('app', 'Payment Paypal'),
            'merchandiser' => Yii::t('app', 'Merchandiser'),
            'product_developer' => Yii::t('app', 'Product Developer'),
            'inquirer' => Yii::t('app', 'Inquirer'),
            'buyer' => Yii::t('app', 'Buyer'),
            'receiving_currency' => Yii::t('app', 'Receiving Currency'),
            'order_total_price' => Yii::t('app', 'Order Total Price'),
            'rmb_order_total_price' => Yii::t('app', 'Rmb Order Total Price'),
            'price' => Yii::t('app', 'Price'),
            'rmb_price' => Yii::t('app', 'Rmb Price'),
            'commodity_cost' => Yii::t('app', 'Commodity Cost'),
            'channel_transaction_currency' => Yii::t('app', 'Channel Transaction Currency'),
            'channel_payment_fee' => Yii::t('app', 'Channel Payment Fee'),
            'rmb_channel_payment_fee' => Yii::t('app', 'Rmb Channel Payment Fee'),
            'paypal_rate' => Yii::t('app', 'Paypal Rate'),
            'paypal_fee' => Yii::t('app', 'Paypal Fee'),
            'rmb_paypal_fee' => Yii::t('app', 'Rmb Paypal Fee'),
            'channel_costs' => Yii::t('app', 'Channel Costs'),
            'first_way_of_transport' => Yii::t('app', 'First Way Of Transport'),
            'first_time_freight' => Yii::t('app', 'First Time Freight'),
            'headage_declaration_fee' => Yii::t('app', 'Headage Declaration Fee'),
            'packaging_materials' => Yii::t('app', 'Packaging Materials'),
            'packaging_costs' => Yii::t('app', 'Packaging Costs'),
            'freight' => Yii::t('app', 'Freight'),
            'profit' => Yii::t('app', 'Profit'),
            'profit_margins' => Yii::t('app', 'Profit Margins'),
        ];
    }
}
