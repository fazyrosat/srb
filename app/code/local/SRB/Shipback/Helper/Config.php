<?php 
/* 
    DAO class for working with table shoprunback_conf in CMS
*/
class SRB_Shipback_Helper_Config extends SRB_Shipback_Helper_Dao
{
    /**
        @description: get URL access to dashboard of SRB
    */
    public function getPublicURL()
    {
        $status = $this->getAPIstatus();
        $public_url = array(
            "0" => Mage::getStoreConfig('shoprunback/url/public/sandbox'),
            "1" => Mage::getStoreConfig('shoprunback/url/public/production')
        );
        return trim($public_url[$status]);
    }

    public function getApiUrl()
    {
        $status = $this->getAPIstatus();
        $api_url = array(
            '0' => Mage::getStoreConfig('shoprunback/url/api_stagging'),
            '1' => Mage::getStoreConfig('shoprunback/url/api_production')
        );
        return trim($api_url[$status]);
    }

    public function getItemLimitPagination()
    {
        return Mage::getStoreConfig('shoprunback/pagination/limit_item');
    }

    /**
        @description: Add 2 rows (for storing token and api_production) to table shoprunback_conf
        @note: api_production means API is on the production url (1=yes, 0=no)
    */
    public function initConfig()
    {
        $token = array(
            'confkey' => 'token',
            'confvalue' => ' '
        );
        $api_production = array(
            'confkey' => 'api_production',
            'confvalue' => '0'
        );
        $srb_companyId = array(
            'confkey' => 'srb_companyid',
            'confvalue' => ' '
        );
        $weight_unit = array(
            'confkey' => 'weight_unit',
            'confvalue' => 'gram'
        );
        $this->add($token);
        $this->add($api_production);
        $this->add($srb_companyId);
        $this->add($weight_unit);
    }

    public function getToken()
    {
        $model = $this->getByField('confkey', 'token');
        return trim($model->getConfvalue());
    }

    public function setToken($token)
    {
        $companyId = $this->getCompanyId();
        $newCompany = $this->checkCompany($token);
        $result = 0;
        if (trim($token) != $this->getToken()) {
            $result = "incorrect";
            if ($newCompany['code'] == 200) {
                $newCompanyId = $newCompany['id'];
                if ($newCompanyId == $companyId) {
                    $model = $this->getByField('confkey', 'token');
                    $this->update($model, array('confvalue' => $token));
                    Mage::helper('srb_shipback/srbapi')->updateWebhook();
                } else {
                    $modelToken = $this->getByField('confkey', 'token');
                    $modelCompanyId = $this->getByField('confkey', 'srb_companyid');
                    $this->update($modelToken, array('confvalue' => $token));
                    $this->update($modelCompanyId, array('confvalue' => $newCompanyId));
                    Mage::helper('srb_shipback/srbapi')->updateWebhook();
                    $this->_clearMappingData();
                }
                $result = 0;
            }
        }
        return $result;
    }

    function _clearMappingData()
    {
        $table_ref = "srb_shipback/srbconf";
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName($table_ref);
        // User SQL instead of ORM for better performance on delete all.
        // Because we need to loop the collection and delete one by one in ORM
        $clean_data_sql = "delete from shoprunback_mapper; delete from shoprunback_shipback";
        $readConnection->query($clean_data_sql);
    }

    public function resetSrbTable()
    {
        $modelToken = $this->getByField('confkey', 'token');
        $modelCompanyId = $this->getByField('confkey', 'srb_companyid');
        $this->update($modelToken, array('confvalue' => " "));
        $this->update($modelCompanyId, array('confvalue' => " "));
        $this->_clearMappingData();
    }

    //status: 1 = production, 0 = sandbox
    public function updateAPIstatus($status)
    {
        $model = $this->getByField('confkey', 'api_production');
        $this->update($model, array('confvalue' => $status));
        Mage::helper('srb_shipback/srbapi')->updateWebhook();
        $this->resetSrbTable();
    }

    public function updateWeightUnit($weight_unit)
    {
        $model = $this->getByField('confkey', 'weight_unit');
        $this->update($model, array('confvalue' => $weight_unit));
    }

    public function getAPIstatus()
    {
        $model = $this->getByField('confkey', 'api_production');
        return trim($model->getConfvalue());
    }

    public function getWeightUnit()
    {
        $model = $this->getByField('confkey', 'weight_unit');
        return trim($model->getConfvalue());
    }

    public function getCompanyId()
    {
        $model = $this->getByField('confkey', 'srb_companyid');
        return trim($model->getConfvalue());
    }

    public function getTableConfig()
    {
        return "srb_shipback/srbconf";
    }

    public function checkCompany($token)
    {
        $config_helper = Mage::helper("srb_shipback/config");
        $api_url = $config_helper->getApiUrl();
        $headers = array(
            "accept : application/json",
            "Authorization : Token token=".$token,
            "Content-Type : application/json"
        );
        $url = trim($api_url)."company";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($response, true);
        $response['code'] = $code;
        return $response;
    }
}
