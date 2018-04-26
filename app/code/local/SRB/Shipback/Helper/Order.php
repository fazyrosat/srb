<?php 
/***
    DAO for shoprunback_mapper (type=order)
 */
class SRB_Shipback_Helper_Order extends SRB_Shipback_Helper_Coredata
{
    public function getConfig()
    {
        $data = array('type' => 'order');
        return $data;
    }
 
    public function getCollection($limit, $currentPage)
    {
        try {
            $collection = Mage::getModel('sales/order')->getCollection();
            if($limit>0){
                $collection->setPageSize($limit);
            }
            if ($currentPage>0) {
                $collection->setCurPage($currentPage);
            }
            $results  = array();
            $all_returns = Mage::helper("srb_shipback/shipback")->getAllItem();
            $hash_returns = array();
            foreach ($all_returns as $return) {
                $hash_returns[$return['id_order']] = $return;
            }
            $results  = array();
            foreach ($collection as $item) {
               $results[$item->getId()] = array(
                    'id' => $item->getId(),
                    'client' => $item->getCustomerName(),
                    'created_at' => $item->getCreatedAt(),
                    'order' => $item->getIncrementId(),
                    'return_status' => $hash_returns[$item->getId()]['state']
                );
            }
            return $results;
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
            
    }

    public function getByIdOriginal($id)
    {
        $order = Mage::getModel('sales/order')->load($id); 
        return $order;
    }

    public function getByIdAfterMap($id)
    {
        $item = Mage::getModel('sales/order')->load($id);
        $api = Mage::helper('srb_shipback/srbapi');
        return $api->mapOrderParam($item);
    }

    public function addToSRB($id_cms)
    {
        try {
            $table_ref = "srb_shipback/srbmapper";
            $item = $this->getByIdAfterMap($id_cms);
            $api = Mage::helper('srb_shipback/srbapi');
            $result = $api->insertOrder($item);
            if ($result['code'] == 400) {
                $item = json_decode($item, true); 
                $result = $api->getOrderById($item['order_number']);
            }
            if ($result['code'] == 200 || $result['code'] == 201) {
                $cmsDate = Mage::getModel('core/date')->date("Y-m-d H:i:s");
                $arr_data = array(
                    'id_item_srb' => $result['id'], 
                    'id_item' => $id_cms, 
                    'type' => 'order', 
                    'last_sent_at' => $cmsDate
                );
                $this->add($arr_data);
                $result['last_sent'] = $cmsDate;
            }
            $this->logErr(__FUNCTION__, $result['code'], 3);
        } catch (Exception $err) {
            $result = 0;
            $this->logException($err, 7);
        }
        return $result;
    }

    public function syncOneItem($id)
    {
        return $this->addToSRB($id);
    }

    public function syncAll()
    {
        $collection = Mage::getModel('sales/order')->getCollection();
        foreach ($collection as $item) {
            $this->addToSRB($item->getId());
        }
    }

    public function syncNew()
    {
        try {
            $collection = Mage::getModel('sales/order')->getCollection();
            $all_item_map = $this->getAllItemOri();
            $arr_hash_item = array();
            foreach ($all_item_map as $map_item) {
                $arr_hash_item[$map_item['id_item']] = $map_item;
            }
            foreach ($collection as $item) {
                $id = $item->getId();
                if (!array_key_exists($id, $arr_hash_item)) {
                    $this->addToSRB($id);
                }
            }
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
            
    }

    public function getTableConfig()
    {
        return "srb_shipback/srbmapper";
    }

    public function getClassName()
    {
        return "Order";
    }
}
