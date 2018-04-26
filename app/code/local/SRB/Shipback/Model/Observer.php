<?php
/*
	A hooked class for Magento events
*/
class SRB_Shipback_Model_Observer{
	// Magento passes a Varien_Event_Observer object as the first parameter of dispatched events.
	public function addProduct(Varien_Event_Observer $observer){
		// Retrieve the product being updated from the event observer
		$product = $observer->getEvent()->getProduct();
		$product_helper = Mage::helper("srb_shipback/product")->syncOneItem($product->getId());

	}// end function

	public function addOrder(Varien_Event_Observer $observer){
		$order = $observer->getEvent()->getOrder();
		$order_helper = Mage::helper("srb_shipback/order")->syncOneItem($order->getId());
        // echo $incrementId = $order->getIncrementId();
        // echo $custName    = $order->getCustomerFirstname();
        // echo $orderPrice  = $order->getGrandTotal();
        //echo $mobile      = trim($order->getShippingAddress()->getData('telephone'));
	}//  end function

}
?>