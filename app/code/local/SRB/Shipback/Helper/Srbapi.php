<?php 
/***
    API class for working with SRB
 */
class SRB_Shipback_Helper_Srbapi extends Mage_Core_Helper_Abstract
{
    public function sendPostData($url, $method, $data)
    {
        $config_helper = Mage::helper("srb_shipback/config");
        $api_url = $config_helper->getApiUrl();
        $token = $config_helper->getToken();
        $headers = array(
            "accept : application/json",
            "Authorization : Token token=".$token,
            "Content-Type : application/json"
        );
        $url = trim($api_url).trim($url);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT': 
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($response, true);
        $response['code'] = $code;
        return $response;
    }

    // UPDAGE WEBHOOK 
    public function updateWebhook()
    {
        // Temporary use fix URL, need to work on how to make URL flexible 
        $webhook_url = Mage::getBaseUrl()."shoprunback/webhook/";
        $webhook = array("webhook_url" => $webhook_url);
        $url = "company";
        $method = "PUT";
        $data = json_encode($webhook);
        return $this->sendPostData($url, $method, $data);
    }

    // GET ALL BRAND
    public function getAllBrands()
    {
        $url = "brands";
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // GET BRAND BY ID
    public function getAllBrandById($reference)
    {
        $url = "brands/".$reference;
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    /* -------------------------------- 
    UPDATE BRAND
    $brand id Array
    -----------------------------------*/
    public function updateBrand($brand)
    {
        $reference = $brand['reference'];
        $url = "brands/".$reference;
        $method = "PUT";
        $data = json_encode($brand, JSON_UNESCAPED_UNICODE);
        return $this->sendPostData($url, $method, $data);
    }

    /* -------------------------------- 
    INSERT BRAND
    $brand is Array
    -----------------------------------*/
    public function insertBrand($brand)
    {
        $url = "brands";
        $method = "POST";
        $data = json_encode($brand, JSON_UNESCAPED_UNICODE);
        return $this->sendPostData($url, $method, $data);
    }

    /*------------------------------------------------
    DELETE BRAND
    default = true , cannot delete
    Brand have product cannot delete
    ------------------------------------------------*/
    public function deleteBrand($reference)
    {
        $url = 'brands/'.$reference;
        $method = "DELETE";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // GET ALL PRODUCT
    public function getAllProducts()
    {
        $url = "products";
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // GET PRODUCT BY ID
    public function getProductById($reference)
    {
        $url = "products/".$reference;
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    /*--------------------------------------------
        UPDATE PRODUCT
        $product is Array
        $reference from $product
    ----------------------------------------------*/
    public function updateProduct($product, $reference)
    {
        $url = "products/".$reference;
        $method = "PUT";
        // $data = json_encode($product, JSON_UNESCAPED_UNICODE);
        $data = $product;
        return $this->sendPostData($url, $method, $data);
        // return $reference;
    }

    // INSERT PRODUCT
    public function insertProduct($product)
    {
        $url = "products";
        $method = "POST";
        // $data = json_encode($product, JSON_UNESCAPED_UNICODE);
        $data = $product;
        return $this->sendPostData($url, $method, $data);
    }

    // DELETE PRODUCT
    public function deleteProduct($reference)
    {
        $url = "products/".$reference;
        $method = "DELETE";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // GET ALL ORDER
    public function getAllOrder()
    {
        $url = "orders";
        $method = 'GET';
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // GET ORDER BY ORDER NUMBER
    public function getOrderById($orderNumber)
    {
        $url = "orders/".$orderNumber;
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // UPDATE ORDER
    public function updateOrder($order)
    {
        // No API
    }

    /*---------------------------------------------------
    INSERT ORDER
    $order is array
    ----------------------------------------------------*/
    public function insertOrder($order)
    {
        $url = "orders";
        $method = "POST";
        // $data = json_encode($order, JSON_UNESCAPED_UNICODE);
        $data = $order;
        return $this->sendPostData($url, $method, $data);
    }

    // DELETE ORDER
    public function deleteOrder($orderNumber)
    {
        $url = "orders/".$orderNumber;
        $method = "DELETE";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // GET COMPANY INFO
    public function getCompany()
    {
        $url = "company";
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }

    // UPDATE COMPANY
    public function updateCompany($company)
    {
        // API return = GET Company data
    }

    // INSERT RETURN
    public function insertReturn($data)
    {
        $data = json_encode($data);
        $url = "shipbacks";
        $method = "POST";
        return $this->sendPostData($url, $method, $data);
    }

    // GET RETURN BY ID
    public function getReturnById($return_id)
    {
        $url = "shipbacks/".$return_id;
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }
    
    // GET ALL RETURN
    public function getAllReturn()
    {
        $url = "shipbacks";
        $method = "GET";
        $data = "";
        $response = $this->sendPostData($url, $method, $data);
        $last_page = $response['pagination']['last_page'];
        $shipbacks = $response['shipbacks'];
        for ($i=2; $i <= $last_page ; $i++) { 
            $response2 = $this->getReturnPerPage($i);
            $shipbacks = array_merge($shipbacks, $response2['shipbacks']);
        }
        return $shipbacks;
    }

    // GET RETURN PER PAGE
    public function getReturnPerPage($page)
    {
        $url = "shipbacks?page=".$page;
        $method = "GET";
        $data = "";
        return $this->sendPostData($url, $method, $data);
    }
    /*
        Description: Map product in magento CMS to product of SRB-api
    */
    public function mapProductParam($params)
    {
        $product_pr = $params["product"];
        $brand_pr = $params["brand"];
        $picture_file_base64 = "";
        if ($product_pr->getImage() && $product_pr->getImage() != "no_selection") {
            $media = "media/catalog/product";
            $image_url = Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL).$media.$product_pr->getImage();
            $b64image = base64_encode(file_get_contents($image_url));
            $picture_file_base64 = "data:image/png;base64,".$b64image;
        }
        $weight = $this->convertWeightUnit($product_pr->getWeight());
        $product = '{
            "label": "'.$product_pr->getName().'",
            "reference": "'.$product_pr->getId().'",
            "weight_grams": '.$weight.',
            "brand": {
            "name": "No Brand",
            "reference": "001",
            "default": true
            },
            "picture_file_base64": "'.$picture_file_base64.'",
            "picture_file_url": "string",
            "metadata": {
                "entity_id" : "'.$product_pr->getId().'",
                "attribute_set_id" : "'.$product_pr->getAttributeSetId().'",
                "type_id" : "simple",
                "sku" : "'.$product_pr->getSku().'",
                "has_options" : "0",
                "required_options" : "0",
                "created_at" : "'.$product_pr->getCreatedAt().'",
                "updated_at" : "'.$product_pr->getUpdatedAt().'",
                "name" : "'.$product_pr->getName().'",
                "image" : "'.$image_url.'",
                "price" : "'.$product_pr->getPrice().'"
            }
        }';
        return $product;
    }

    public function mapReturnParam($cms_order, $srb_order_id)
    {
        $shipback = array("order_id" => $srb_order_id);
        return $shipback;
    }

    public function mapOrderParam($params)
    {
        $itemsCollection = $params->getItemsCollection()->getData();
        $items = "";
        foreach ($itemsCollection as $value) {
            $weight = $this->convertWeightUnit($value['weight']);
            $item = ',{
                "reference" : "'.$value['item_id'].'",
                "barcode" : "00000000",
                "price_cents" : "'.$value['price'].'",
                "currency" : "'.$params->getBaseCurrencyCode().'",
                "product_id" : "",
                "product" : {
                    "label" : "'.$value['name'].'",
                    "reference" : "'.$value['item_id'].'",
                    "weight_grams" : "'.$weight.'",
                    "brand_id" : "",
                    "brand" : {
                        "name" : "No Brand",
                        "reference" : "001",
                        "default" : true
                    },
                    "picture_file_base64" : "",
                    "picture_file_url" : "string",
                    "metadata" : {
                        "foo" : "bar"
                    }
                }
            }';
            $items = $items . $item ;
        }
        $items = substr($items, 1);
        $items = "[".$items."]";
        $order = array();
        $addr_obj = $params->getShippingAddress();
        if ($addr_obj != null) {
            $street = $params->getShippingAddress()->getStreet();
            $order = '{
                "ordered_at" : "'.$params->getCreatedAt().'",
                "order_number" : "'.$params->getIncrementId().'",
                "customer" : {
                    "first_name" : "'.$params->getCustomerFirstname().'",
                    "last_name" : "'.$params->getCustomerLastname().'",
                    "email" : "'.$params->getCustomerEmail().'",
                    "phone" : "'.$params->getShippingAddress()->getTelephone().'",
                    "locale" : "'.$params->getShippingAddress()->getCountryId().'",
                    "address" : {
                        "line1" : "'.$street[0].'",
                        "line2" : "'.$street[1].'",
                        "zipcode" : "'.$params->getShippingAddress()->getPostcode().'",
                        "country_code" : "'.$params->getShippingAddress()->getCountryId().'",
                        "city" : "'.$params->getShippingAddress()->getCity().'",
                        "state" : "'.$params->getShippingAddress()->getState().'"
                    }
                },
                "metadata" : {
                    "foo" : "bar"
                },
                "items" : '.$items.'
            }';
        }
        return $order;
    }

    public function convertWeightUnit($weight)
    {
        $unit = Mage::helper("srb_shipback/config")->getWeightUnit();
        $weighUnit = array(
            'kilogram' => 1000,
            'gram' => 1,
            'pound' => 453.592,
            'ounce' => 28.35
        );
        return $weight * $weighUnit[$unit];
    }
}
