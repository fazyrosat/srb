<?php
class SRB_Shipback_WebhookController extends Mage_Core_Controller_Front_Action
{
    public function updateshipbackAction()
    {
        $product_helper = Mage::helper("srb_shipback/product");
        $id = $_GET['id'];
        $product_helper->syncOneItem($id);
    }
    /**
        @description: receive shipback json object from SRB and update to CMS
    */
    public function indexAction()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $event = explode(".",$data['event']);
        if (strpos($event[0], "shipback") > -1) {
            $shipback = array(
                'id_srb_shipback' => $data['data']['id'],
                'mode' => $data['data']['mode'], 
                'state' => $event[1], 
                'update_at' => $data['data']['updated_at'],
                'public_url' => $data['data']['public_url']
            );
            $dao = Mage::helper("srb_shipback/shipback");
            $model = $dao->getByField("id_srb_shipback",$data['id']);
            $dao->update($model, $shipback);
        }// end if
    }// end function
    
}// end class
