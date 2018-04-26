<?php
class SRB_Shipback_Model_Resource_Srbmapper_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('srb_shipback/srbmapper');
    }
}