<?php
class SRB_Shipback_ReturnController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Return Item'));
        $this->renderLayout();
    }
}
