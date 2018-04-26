<?php 
/***
    Top level DAO for storing common functions
 */
class SRB_Shipback_Helper_Dao extends Mage_Core_Helper_Abstract
{
    public function getAllItem()
    {
        $table_ref = $this->getTableConfig();
        $model = Mage::getModel($table_ref);
        return $model->getCollection();
    }

    public function getById()
    {
        // todo
        // method body
    }

    public function getByField($key, $value)
    {
        $table_ref = $this->getTableConfig();
        $model = Mage::getModel($table_ref);
        $model->load($value, $key);
        return $model;
    }

    public function getByManyFields($arr_key_value)
    {
        $table_ref = $this->getTableConfig();
        $model = Mage::getModel($table_ref)->getCollection();
        foreach ($arr_key_value as $key => $value) {
            $model->addFieldToFilter($key, $value);
        }
        return $model->getFirstItem();
    }

    public function add($arr_data)
    {
        Mage::getModel($this->getTableConfig())->setData($arr_data)->save();
    }

    public function delete()
    {
        // todo
        // method body
    }

    public function covertFromNormalToAssociatedArray($arr_list, $field_as_key)
    {
        $result  = array();
        foreach ($arr_list as $key => $value) {
            $result[$value[$field_as_key]] = $value;
        }
        return $result;
    }

    public function update($model, $data)
    {
        foreach ($data as $key => $value) {
            $model->setData($key, $value);
        }
        $model->save();
    }

    public function getLimitItems($limit, $currentPage){
        $table_ref = $this->getTableConfig();
        $model = Mage::getModel($table_ref);
        return $model->getCollection()
            ->setPageSize($limit)
            ->setCurPage($currentPage);
    }
    
    public function logErr($funName, $code, $level) 
    {
        $className = $this->getClassName();
        $messages = array(
            "401" => $className.", ".$funName.": API key is incorrect check in config page",
            "0" => $className.", ".$funName.": No Internet connection cannot request to server",
            "500" => $className.", ".$funName.": Server Error cannot request",
            "503" => $className.", ".$funName.": Server Error cannot request",
            "s400" => $className.", ".$funName.": Need Synchronize Order before shipback",
            "s01" => $className.", ".$funName.": Order not belong to user"
        );
        if (isset($messages[$code])) {
            $log = Mage::helper('srb_shipback/srblog');
            $log->setLog($messages[$code], $level);
        }
        
    }
    
    public function logException($message, $level)
    {
        $log = Mage::helper('srb_shipback/srblog');
        $log->setLog($message, $level);
    }

}
