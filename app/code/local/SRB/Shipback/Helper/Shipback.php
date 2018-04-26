<?php 
/***
    DAO for working with table "shprunback_shipback" on CMS
    @note: Need to use ORM instead of SQL and centralize common function into main class (Dao)
 */
class SRB_Shipback_Helper_Shipback extends SRB_Shipback_Helper_Dao
{

    public function addShipback($order_cms_id, $customer_id)
    {
        $api = Mage::helper('srb_shipback/srbapi');
        $order = Mage::getModel('sales/order')->load($order_cms_id);
        $dao = Mage::helper('srb_shipback/order');
        $arr = array('id_item' => $order_cms_id, 'type' => 'order');
        $order_map = $dao->getByManyFields($arr);
        $data = $api->mapReturnParam($order, $order_map['id_item_srb']);
        $response = $customer_id."|".$order->getCustomerId();
        //check order ID in customer
        if ($customer_id == $order->getCustomerId()) {
            $response = $api->insertReturn($data);
            if ($response['code'] == 201 || $response['code'] == 200) {
                $binds = $this->_mapParamShipbackFromSRBtoCMS($response, $order_cms_id);
                $this->add($binds);
            } elseif ($response['code'] == 400) {
                $map_return = $this->getByField("id_order", $order_cms_id);
                if (isset($map_return['public_url'])) {
                    $response = array(
                        "code" => 200,
                        "public_url" => $map_return['public_url'],
                    );
                } else {
                    $code = "s400";
                    $this->logErr(__FUNCTION__, $code, 3);
                }
            }
            $this->logErr(__FUNCTION__, $response['code'], 3);
        } else {
            $code = "s01";
            $this->logErr(__FUNCTION__, $code, 3);
        }
        return $response;
    }// end function

    // Sync All Return from SRB
    public function syncAll()
    {
        $api = Mage::helper('srb_shipback/srbapi');
        $shipbacks = $api->getAllReturn();
        // create hash dictionary for checking if the shipback is already exist in db 
        $hash_shipback_list = $this->covertFromNormalToAssociatedArray($this->getAllItem(), "id_order");
        foreach ($shipbacks as $shipback) {
            $order_number = $shipback['order']['order_number'];
            $order = Mage::getModel('sales/order')->load($order_number, 'increment_id');
            // sync only the order that exist in database
            if($order->getId()){
                $binds = $this->_mapParamShipbackFromSRBtoCMS($shipback, $order->getId());
                try {
                    // do not sync when the shipback is already sync
                    if(!array_key_exists($order->getId(), $hash_shipback_list)){
                        $this->add($binds); 
                    }
                } catch (Exception $e) {
                    $this->logException($err, 7);
                }
            }
        } 
    }// end function

    function _mapParamShipbackFromSRBtoCMS($shipback, $cms_order_id)
    {
        $result = array(
            'id_srb_shipback'=>$shipback['id'],
            'id_order' => $cms_order_id,
            'state' => $shipback['state'],
            'mode' => $shipback['mode'],
            'created_at' => $this->convertUtcDate($shipback['created_at']),
            'public_url' => $shipback['public_url']
        );
        return $result;
    }// end function

    public function convertUtcDate($UtcDate)
    {
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $date = new DateTime($UtcDate);
        $date->setTimezone(new DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
    }// end function

    protected function getTableConfig()
    {
        return "srb_shipback/srbshipback";
    }

    public function getClassName()
    {
        return "Shipback";
    }
}// end class
