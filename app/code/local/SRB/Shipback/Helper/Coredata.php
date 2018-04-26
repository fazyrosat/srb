<?php 
/***
    DAO class for common function that work with shoprunback_mapper (type=order/product)
 */
class SRB_Shipback_Helper_Coredata extends SRB_Shipback_Helper_Dao
{
    public function getAllItemAndMap($limit, $currentPage)
    {
        $config = $this->getConfig();
        $collection = $this->getCollection($limit, $currentPage);
        $qresult = $this->getAllItemOri();
        $results = array();
        foreach ($qresult as $item) {
            if (isset($collection[$item['id_item']])) {
                $collection[$item['id_item']]['data'] = $item;
            }
        }
        return $collection;
    }

    public function getAllItemOri()
    {
        $config = $this->getConfig();
        $table_ref = $this->getTableConfig();
        $model = Mage::getModel($table_ref);
        return $model->getCollection()->addFieldToFilter('type',trim($config['type']));
    }
    
    public function update($id, $data)
    {
        $config = $this->getConfig();
        $table_ref = $this->getTableConfig();
        if($config['type'] == 'product') {
            $models = Mage::getModel($table_ref)
                ->getCollection()
                ->addFieldToFilter('id_item', $id)
                ->addFieldToFilter('type', 'product');
            foreach ($models as $model) {
                foreach ($data as $key => $value) {
                    $model->setData($key, $value);
                }
                $model->save();
            }
        } else {
            $model = Mage::getModel($table_ref)->load($id, 'id_item');
            foreach ($data as $key => $value) {
                $model->setData($key, $value);
            }
            $model->save();
        }
    }
}
