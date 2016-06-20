<?php
class Srdan_TNT_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'srdan_tnt';

    protected $destCountry;
    protected $destTownName;
    protected $destPostcode;
    protected $destTownGroup;
    protected $Weight;
    protected $Items;

    protected $Height;
    protected $Width;
    protected $Depth;
    protected $Volume;

    protected $arrayResult;

   // protected $userName;


    public function collectRates(
        Mage_Shipping_Model_Rate_Request $request
    )
    {
      //  $this->userName = Mage::getStoreConfig('carriers/srdan_tnt/user_name');
        $this->destCountry   = $request->getDestCountryId();
        $this->destTownName  = $request->getDestCity();
        $this->destPostcode  = $request->getDestPostcode();
        $this->destTownGroup = $request->getDestRegionCode();

        $this->Weight = $request->getPackageWeight();

        $this->Height = $request->getPackageHeight();
        $this->Width  = $request->getPackageWeight();
        $this->Depth  = $request->getPackageDepth();
        $this->Volume = $this->Height*$this->Width*$this->Depth;

        if ($this->Volume > 0){
            $this->Volume = $this->Volume;
        }else{
            $this->Volume = 0.001;
        }

        $this->Items = $request->getPackageQty();
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */

        $result->append($this->_getExpressShippingRate());

        $result->append($this->_getEconomyShippingRate());

        return $result;

    }

    protected function _getExpressShippingRate()
    {

        /////////////////////////////////////////////////////////////////////////
        $service = "15N";

// start to build XML
        $Xml ="<?xml version='1.0' encoding='UTF-8' standalone='no'?>
<priceRequest>
	<appId>PC</appId>
	<appVersion>3.0</appVersion>
	<priceCheck>
		<rateId>rate2</rateId>
		<sender>
			<country>SI</country>
			<town>Ljubljana</town>
			<postcode>1000</postcode>
		</sender>
		<delivery>
			<country>$this->destCountry</country>
			<town>$this->destTownName</town>
			<postcode></postcode>
		</delivery>
		<collectionDateTime>2014-10-28T16:13:00</collectionDateTime>
		<product>
			<id>15N</id>
			<division>G</division>
			<type>N</type>
			<options>
				<option>
					<optionCode></optionCode>
					<optionDesc></optionDesc>
				</option>
			</options>
		</product>
		<account>
			<accountNumber>29934</accountNumber>
			<accountCountry>SI</accountCountry>
		</account>
		<termsOfPayment/>
		<currency>EUR</currency>
		<priceBreakDown>true</priceBreakDown>
		<consignmentDetails>
			<totalWeight>$this->Weight</totalWeight>
			<totalVolume>$this->Volume</totalVolume>
			<totalNumberOfPieces>$this->Items</totalNumberOfPieces>
		</consignmentDetails>
	</priceCheck>
</priceRequest>";

        $xmlResult = $this->doPost($Xml);


        /**
         * Parse XML data into $RATE string
        */

        $xmlParse = simplexml_load_string($xmlResult[1]);
        $json = json_encode($xmlParse);
        $this->arrayResult = json_decode($json,TRUE);


        $ratemagento = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */


        $ratemagento->setCarrier($this->_code);
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $ratemagento->setCarrierTitle($this->getConfigData('title'));

        $ratemagento->setMethod('express');
        $ratemagento->setMethodTitle('Express');

        $ratemagento->setPrice($this->arrayResult["priceResponse"]["ratedServices"]["ratedService"]["totalPriceExclVat"]);
        $ratemagento->setCost(0);

        if ($this->arrayResult["priceResponse"]["ratedServices"]["ratedService"]["totalPriceExclVat"] > 0){

            return $ratemagento;

        }else{

            $result = Mage::getModel('shipping/rate_result');
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage("** Express - " . $this->getConfigData('specificerrmsg'));
            $result->append($error);
            return $result;
        }

    }

    protected function _getEconomyShippingRate()
    {

// start to build XML
        $Xml ="<?xml version='1.0' encoding='UTF-8' standalone='no'?>
<priceRequest>
	<appId>PC</appId>
	<appVersion>3.0</appVersion>
	<priceCheck>
		<rateId>rate2</rateId>
		<sender>
			<country>SI</country>
			<town>Ljubljana</town>
			<postcode>1000</postcode>
		</sender>
		<delivery>
			<country>$this->destCountry</country>
			<town>$this->destTownName</town>
			<postcode></postcode>
		</delivery>
		<collectionDateTime>2014-10-28T16:13:00</collectionDateTime>
		<product>
			<id>48N</id>
			<division>G</division>
			<type>N</type>
			<options>
				<option>
					<optionCode></optionCode>
					<optionDesc></optionDesc>
				</option>
			</options>
		</product>
		<account>
			<accountNumber>29934</accountNumber>
			<accountCountry>SI</accountCountry>
		</account>
		<termsOfPayment/>
		<currency>EUR</currency>
		<priceBreakDown>true</priceBreakDown>
		<consignmentDetails>
			<totalWeight>$this->Weight</totalWeight>
			<totalVolume>$this->Volume</totalVolume>
			<totalNumberOfPieces>$this->Items</totalNumberOfPieces>
		</consignmentDetails>
	</priceCheck>
</priceRequest>";

        $xmlResult = $this->doPost($Xml);

        /**
         * Parse XML data into $RATE string
        */

        $xmlParse = simplexml_load_string($xmlResult[1]);
        $json = json_encode($xmlParse);
        $this->arrayResult = json_decode($json,TRUE);

        /////////////////////////////////////////////////////////////////////////

        $ratemagento = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */


        $ratemagento->setCarrier($this->_code);
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $ratemagento->setCarrierTitle($this->getConfigData('title'));

        $ratemagento->setMethod('economy');
        $ratemagento->setMethodTitle('Economy Express');

        $ratemagento->setPrice($this->arrayResult["priceResponse"]["ratedServices"]["ratedService"]["totalPriceExclVat"]);
        $ratemagento->setCost(0);

        if ($this->arrayResult["priceResponse"]["ratedServices"]["ratedService"]["totalPriceExclVat"] > 0){

            return $ratemagento;

        }else{

            $result = Mage::getModel('shipping/rate_result');
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage("** Economy Express - ".$this->getConfigData('specificerrmsg'));
            $result->append($error);
            return $result;
        }
    }

    /**
     * @param $Content
     * @return array
     */
    function doPost($Content) {

        $postContent = $Content;

        $host="express.tnt.com";
        $contentLen = strlen($postContent);
        $username = "USERNAME";
        $password = "password";
        $auth=base64_encode($username.":".$password);
        $httpHeader ="POST /expressconnect/pricing/getprice HTTP/1.1\r\n"
            ."Host: $host\r\n"
            ."Authorization: Basic ".$auth."\r\n"
            ."Content-Type: text/xml\r\n"
            ."Content-Length: $contentLen\r\n"
            ."Connection: close\r\n"
            ."\r\n";

        $httpHeader .= $postContent;

        try {
            $fp = fsockopen("ssl://".$host, 443);

            if(fputs($fp, $httpHeader)){

                $result = "";

                while(!feof($fp)) {
                    // receive the results of the request
                    $result .= fgets($fp);
                }
                // close the socket connection:
                fclose($fp);

                $result = explode("\r\n\r\n", $result,3);

                $header = isset($result[0]) ? $result[0] : '';
                $content = isset($result[1]) ? $result[1] : '';

                return array($header, $content);
            } else {
                return array("Error", "Connection Failed: fputs");
            }

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        return array('Error', 'Error - likely cause: fsockopen');
    }


    public function getAllowedMethods()
    {
        return array(
            'express' => 'Express',
            'economy' => 'Economy Express'
        );
    }
}