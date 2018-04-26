<?php
class SRB_Shipback_Helper_Srblog extends Mage_Core_Helper_Abstract
{
	public function setLog($message, $level)
	{
		Mage::log($message, $level, 'shoprunback.log');
	}
}