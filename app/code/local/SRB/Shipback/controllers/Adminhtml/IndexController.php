<?php
/***
    Shoprunback Admin Controller 
    Controller for all admin pages of SRB plugin
    Rules and Terminoloties: 
        - $this->__("Captions") is used for i18N
        - pageAction: is a page action (eg. productAction, orderAction, returnAction, configAction,..)
        - classname_dao: a DAO class for manipulating CMS data (via ORM) and SRB data (via API)
        - srbapi: shoprunback api
        - Factory: a mechanism that decides the result (which function to call, which data to return, ...)
 */
class SRB_Shipback_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

    //BlockName Factory
    public function getBlockName($page_name)
    {
        $block_mapping = array(
            'product' => "shoprunback.product", 
            'order' => "shoprunback.order", 
            'return' => "shoprunback.return", 
            'config' => "shoprunback.config", 
            'brand' => "shoprunback.brand"
        );
        return $block_mapping[$page_name];
    }

    // DAO Factory
    public function getDao($keyword)
    {
        $dao_mapping  = array(
            'product' => "srb_shipback/product", 
            'order' => "srb_shipback/order", 
            'return' => "srb_shipback/shipback", 
            'config' => "srb_shipback/config", 
            'brand' => "srb_shipback/brand", 
            'srbapi' => "srb_shipback/srbapi"
        );
        return Mage::helper($dao_mapping[$keyword]);
    }

    // Declare menu items and their captions 
    public function getMenuItems()
    {
        $menu_items  = array(
            "return"=> array('caption' => $this->__("My returns")),
            "product"=> array('caption' => $this->__("My products")), 
            "order"=> array('caption' => $this->__("My orders")), 
            "brand"=> array('caption' => $this->__("My brands")),
            "config"=> array('caption' => $this->__("Configuration"))
        );
        return $menu_items;
    }

    /**
        1. Provide base url for links in menu
        2. Generate magento secrete key for each menu link
        3. Hightligh active menu
    */
    public function setupMenu($block, $block_name)
    {
        $base_url = Mage::getBaseUrl()."shoprunback/adminhtml_index/";
        $menu_items = $this->getMenuItems();
        forEach ($menu_items as $key => $item) {
            $menu_items[$key]['url'] = $base_url.$key."/key/".$this->getSecretKey("adminhtml_index", $key);
        }
        $pages = explode(".", $block_name);
        $page = $pages[1];
        $menu_items[$page]['active'] = "srb-active";
        $block->getChild("menu")->setMenuItemList($menu_items);
    }

    public function setupPagination($block, $page)
    {
    	$numItem = 0;
    	$config_dao = $this->getDao("config");
    	switch($page){
    		case 'product':
    			$numItem = Mage::getResourceModel('catalog/product_collection')->getSize();
    			break;
    		case 'order':
    			$numItem = Mage::getResourceModel('sales/order_collection')->getSize();
    			break;
    		case 'return':
    			$numItem = $this->getDao("return")->getAllItem()->getSize();
    			break;
    	}
        $block->getChild("pagination")->setLimitItem($config_dao->getItemLimitPagination());
        $block->getChild("pagination")->setNumItem($numItem);
    }

    /**
        1. Map Template and Controller
        2. Send data to template
        3. Render Page
    */
    public function setupPage($page, $data)
    {
        $this->loadLayout();
        $layout = $this->getLayout();
        $block_name = $this->getBlockName($page);
        $block = $layout->getBlock($block_name);
        $data["limit_item"] = $this->getDao("config")->getItemLimitPagination();
        $key_array = array();
        if ($block) {
            $this->setupPagination($block, $page);
            $this->setupMenu($block, $block_name);
            $block->setData($data);
        }
        $this->renderLayout();
    }

    /**
        Generate secrete key for our custom url
        Without magento secrete key, the custom link we have created will be rejected
    */
    public function getSecretKey($controller = null, $action = null)
    {
        $salt = Mage::getSingleton('core/session')->getFormKey();
        $p = explode('/', trim($this->getRequest()->getOriginalPathInfo(), '/'));
        if (!$controller) {
            $controller = !empty($p[1]) ? $p[1] : $this->getRequest()->getControllerName();
        }
        if (!$action) {
            $action = !empty($p[2]) ? $p[2] : $this->getRequest()->getActionName();
        }
        $secret = $controller.$action.$salt;
        return Mage::helper('core')->getHash($secret);
    }

    // If the request is ajax, don't need to render the page
    public function isAjaxCAll()
    {
        if (isset($_GET['isAjax']) && $_GET['isAjax'] == 'true') {
            exit;
        }
    }

    // Sync Factory
    public function requestSync($dao, $sync_type)
    {
        switch ($sync_type) {
            case 'sync_new':
                $dao->syncNew();
                break;
            case 'sync_all':
                $dao->syncAll();
                break;
            default:
                break;
        }
    }

    // PAGE BRAND
    public function brandAction()
    {
        //$block_name = $this->getBlockName("brand");
        $page = "brand";
        $array_brands = $this->getDao("srbapi")->getAllBrands();
        $config_dao = $this->getDao("config")->getPublicURL();
        $data = array(
            "brandList" => $array_brands,
            "srb_product_url" => $config_dao
        );
        $this->setupPage(
            $page,
            $data
        );
    }

    // PAGE PRODUCT
    public function productAction()
    {
        $page = "product";
        $cpage = 1;
        $product_dao = $this->getDao($page);
        $config_dao = $this->getDao("config");
        /* REQUEST SYNC */
        if (isset($_POST['id_item'])) {
            echo json_encode($product_dao->syncOneItem($_POST['id_item']));
            exit();
        } else if (isset($_POST['sync_type'])) {
            $this->requestSync($product_dao, $_POST['sync_type']);
        }
        $this->isAjaxCAll();
        if (isset($_GET['page'])) {
            $cpage = $_GET['page']; 
        }
        $array_product = $product_dao
            ->getAllItemAndMap(
                $config_dao->getItemLimitPagination(), 
                $cpage
            );

        $data = array(
            'productList' => $array_product,
            'srb_product_url' => $config_dao->getPublicURL()
        );
        $this->setupPage(
            $page,
            $data
        );
    }

    // PAGE ORDER
    public function orderAction()
    {
        $page = "order";
        $cpage = 1;
        $config_dao = $this->getDao("config");
        $order_dao = $this->getDao($page);
        // SYNC PRODUCT   
        if (isset($_POST['id_item'])) {
            echo json_encode($order_dao->syncOneItem($_POST['id_item']));
            exit();
        } else if (isset($_POST['sync_type'])) {
            $this->requestSync($order_dao, $_POST['sync_type']);
        }
        $this->isAjaxCAll();
        // RENDER PAGE
        // pagination 
        if (isset($_GET['page'])) {
            $cpage = $_GET['page']; 
        }
        $array_data = $order_dao
            ->getAllItemAndMap(
                $config_dao->getItemLimitPagination(), 
                $cpage
            );
        $data = array(
            'orderList' => $array_data,
            'srb_order_url' => $config_dao->getPublicURL()
        );
        $this->setupPage(
            $page,
            $data
        );
    }

    //PAGE RETURN
    public function returnAction()
    {
        $page = "return";
        $shipback_list = array();
        $array_data = array();
        $cpage = 1;
        $config_dao = $this->getDao("config");
        $order_dao = $this->getDao("order");
        
        // AJAX Sync
        if (isset($_POST['sync_type'])) {
            $shipback = Mage::Helper("srb_shipback/shipback");
            $shipback->syncAll();
            exit();
        }
        $this->isAjaxCAll();

        // RENDER PAGE
        // pagination
        if (isset($_GET['page'])) {
            $cpage = $_GET['page']; 
        }
        $array_data = $this->getDao($page)
            ->getLimitItems(
                $config_dao->getItemLimitPagination(),
                $cpage
            );

        // render data
        forEach ($array_data as $item) {
            $order = $order_dao->getByIdOriginal($item['id_order']);
            $item['customer'] = $order->getCustomerFirstname();
            $item['order_increment_id'] = $order->getIncrementId();
            array_push($shipback_list, $item);
        }
        $data  = array(
            'returnList' => $shipback_list,
            'public_url' => $config_dao->getPublicURL()
        );
        $this->setupPage(
            $page, 
            $data
        );
    }
    
    // PAGE CONFIGURATION
    public function configAction()
    {
        $page = "config";
        $config_dao = $this->getDao($page);
        if (isset($_POST['input_token'])) {
            echo $config_dao->setToken($_POST['input_token']);
            exit();
        } else if (isset($_POST['api_status'])) {
            $config_dao->updateAPIstatus($_POST['api_status']);
        } else if (isset($_POST['weight_unit'])) {
            $weight_unit = $_POST['weight_unit'];
            $config_dao->updateWeightUnit($weight_unit);
        }

        $this->isAjaxCAll();
        $data  = array (
            'srb_public_url' => $config_dao->getPublicURL(), 
            "token" => $config_dao->getToken(),
            'api_production' => $config_dao->getAPIstatus(),
            'weight_unit' => $config_dao->getWeightUnit()
        );
        $this->setupPage(
            $page,
            $data
        );
    }
}
