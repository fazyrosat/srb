<?php
 
class SRB_Shipback_Model_Resource_Srbshipback extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('srb_shipback/srbshipback', 'id');
    }
}