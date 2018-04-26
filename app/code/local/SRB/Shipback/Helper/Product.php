<?php 
/***
    DAO for working with table "shoprunback_mapper" in CMS (type=product)
 */
class SRB_Shipback_Helper_Product extends SRB_Shipback_Helper_Coredata
{
    public function getConfig()
    {
        $data = array('type' => 'product');
        return $data;
    }

    /**
        Get a collection of product in Magento CMS
    */
    public function getCollection($limit, $currentPage)
    {
        try {
            $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect(array('id', 'sku', 'name', 'description', 'manufacturer'));
            if($limit>0){
                $collection->setPageSize($limit);
            }
            if ($currentPage>0) {
                $collection->setCurPage($currentPage);
            }
            $results = array();
            foreach ($collection as $item) {
                $results[$item->getId()] = array(
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'brand' => $item->getAttributeText('manufacturer')
                );
            }
            return $results;
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
        
    }

    /**
        Get a single object of Product from Magento CMS
    */
    public function getByIdOriginal($id)
    {
        $product = Mage::getModel('catalog/product')->load($id); 
        return $product;
    }

    /**
        Get a single object of Product from Magento and map into SRB object 
    */
    public function getByIdAfterMap($id)
    {
        $product = Mage::getModel('catalog/product')->load($id); 
        $api = Mage::helper('srb_shipback/srbapi');
        return $api->mapProductParam(array("product" => $product, "brand" => ""));
    }

    /**
        1. Try to add product to SRB. If product is already exist, get the product
        2. Map prduct received from SRB to CMS and store
    */
    public function addToSRB($id_cms)
    {
        try {
            $product = $this->getByIdAfterMap($id_cms);
            $api = Mage::helper('srb_shipback/srbapi');
            $result = $api->insertProduct($product);
            if ($result['code'] == 400) {
                $result = $api->getProductById($id_cms);
            }
            if ($result['code'] == 200 || $result['code'] == 201) {
                $cmsDate = Mage::getModel('core/date')->date("Y-m-d H:i:s");
                $arr_data = array(
                    'id_item_srb' => $result['id'], 
                    'id_item' => $id_cms, 
                    'type' => 'product', 
                    'last_sent_at' => $cmsDate
                );
                $this->add($arr_data);
                $result['updated_at'] = $cmsDate;
            }
            $this->logErr(__FUNCTION__, $result['code'], 3);
            return $result;
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
    }

    /**
        Update product to SRB and Magento CMS
    */
    public function updateToSRB($id_cms, $id_srb)
    {
        try {
            // update to SRB
            $product = $this->getByIdAfterMap($id_cms);
            $api = Mage::helper('srb_shipback/srbapi');
            $result = $api->updateProduct($product, $id_cms);

            // update to Magento CMS
            $cmsDate = Mage::getModel('core/date')->date("Y-m-d H:i:s");
            $arr_data  = array('last_sent_at' => $cmsDate);
            $this->update($id_cms, $arr_data);
            $result['updated_at'] = $cmsDate;
            $this->logErr(__FUNCTION__, $result['code'], 3);
            return $result;
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
        
    }

    public function syncOneItem($id)
    {
        try {
            $arr = array(
                'id_item' => $id,
                'type' => 'product'
            );
            $item = $this->getByManyFields($arr);
            if ($item->getId_item()) {
                return $this->updateToSRB($id);
            } else {
                return $this->addToSRB($id);
            }
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
        
    }

    public function syncAll()
    {
        try {
            $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect(array('id'));
            $all_product_map = $this->getAllItemOri();
            $arr_hash_product = array();
            foreach ($all_product_map as $product) {
                $arr_hash_product[$product['id_item']] = 1;
            }
            foreach ($collection as $item) {
                $id = $item->getId();
                if (array_key_exists($id, $arr_hash_product)) {
                    $this->updateToSRB($id);
                } else {
                    $this->addToSRB($id);
                }
            }
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
            
    }
    
    public function syncNew()
    {
        try {
            $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect(array('id'));
            $all_product_map = $this->getAllItemOri();
            $arr_hash_product = array();
            foreach ($all_product_map as $product) {
                $arr_hash_product[$product['id_item']] = 1;
            }
            foreach ($collection as $item) {
                if (!array_key_exists($item->getId(), $arr_hash_product)) {
                    $this->addToSRB($item->getId());
                }
            }
        } catch (Exception $err) {
            $this->logException($err, 7);
        }
            
    }

    /**
        Declare current table name to main class in order to use common functions
    */
    public function getTableConfig()
    {
        return "srb_shipback/srbmapper";
    }

    public function getClassName()
    {
        return "Product";
    }
}
