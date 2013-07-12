<?php
/**
 * ECB Currency Converter
 *
 * This class uses the currency rates provided by an XML file from the European Central Bank
 * Requires cURL or allow_url_fopen
 */
defined('COT_CODE') or die('Wrong URL.');

class convertECB {

// 	var $archive = true;
// 	var $last_updated = '';

	var $document_address = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

	var $info_address = 'http://www.ecb.int/stats/eurofxref/';
	var $supplier = 'European Central Bank';
    
    protected $_cacheDrv = 'mem';
    protected $_cacheLife; 
    
    public function __construct(){
        global $cache;
        
        if (!$cache){
            $this->_cacheDrv = false;
        }elseif(!$cache->mem){
            $this->_cacheDrv = 'db';
        }

        $this->_cacheLife = 86400/4; // check 4 time per day
    }
    
	/**
	 * Converts an amount from one currency into another using
	 * the rate conversion table from the European Central Bank
	 *
	 * @param float $amountA
	 * @param string $currA defaults to $vendor_currency
	 * @param string $currB defaults to
	 * @return mixed The converted amount when successful, false on failure
	 */
	function convert( $amountA, $currA='', $currB='' ) {
        global $cache;
        
        //var_dump($cache);
        
		$cache_key = 'convertECB';
        $realm = 'shop';
        $cached = true;
        $globalCurrencyConverter = false;
        if ($cache) $globalCurrencyConverter = $cache->{$this->_cacheDrv}->get($cache_key, $realm);
        if(!$globalCurrencyConverter){
            $cached = false;
            $globalCurrencyConverter = $this->getExchangeRates($this->document_address);
        }
		
		if(!$globalCurrencyConverter ){
			return $amountA;
		} else {
            if (!$cached && $cache){
                // todo потестить с db
                $cache->{$this->_cacheDrv}->store($cache_key, $globalCurrencyConverter, $realm, $this->_cacheLife);
            }
			$valA = isset( $globalCurrencyConverter[$currA] ) ? $globalCurrencyConverter[$currA] : 1.0;
			$valB = isset( $globalCurrencyConverter[$currB] ) ? $globalCurrencyConverter[$currB] : 1.0;

			$val = (float)$amountA * (float)$valB / (float)$valA;

			return $val;
		}
	}
    
    /**
     * Получить обменные курсы
     * @param type $ecb_filename
     * @return array 
     */
	function getExchangeRates($ecb_filename){
            global $cfg, $sys;
            
			$archive = true;
            // TODO на коте $sys['now'] и так в GMT, переделать не трогая настрое локали.
			setlocale(LC_TIME, "en-GB");
			$now = time() + 3600; // Time in ECB (Germany) is GMT + 1 hour (3600 seconds)
			if (date("I")) {
				$now += 3600; // Adjust for daylight saving time
			}
			$weekday_now_local = gmdate('w', $now); // week day, important: week starts with sunday (= 0) !!
			$date_now_local = gmdate('Ymd', $now);
			$time_now_local = gmdate('Hi', $now);
			$time_ecb_update = '1415';

            $store_path = $cfg['modules_dir'].'/shop/cache';
			$archivefile_name = $store_path.'/convertECB_daily.xml';

			$val = '';


			if(file_exists($archivefile_name) && filesize( $archivefile_name ) > 0 ) {
				// timestamp for the Filename
				$file_datestamp = date('Ymd', filemtime($archivefile_name));

				// check if today is a weekday - no updates on weekends
				if( date( 'w' ) > 0 && date( 'w' ) < 6
				// compare filedate and actual date
				&& $file_datestamp != $date_now_local
				// if localtime is greater then ecb-update-time go on to update and write files
				&& $time_now_local > $time_ecb_update) {
					$curr_filename = $ecb_filename;
				}
				else {
					$curr_filename = $archivefile_name;
					$last_updated = $file_datestamp;
					$archive = false;
				}
			}
			else {
				$curr_filename = $ecb_filename;
			}

			if( !is_writable( $store_path )) {
				$archive = false;
                cot_error("The file $archivefile_name can't be created. The directory $store_path is not writable");
			}

			// TODO коннектиться по CURL ??
			if( 0 && $curr_filename == $ecb_filename ) {
//				// Fetch the file from the internet
//				if(!class_exists('VmConnector')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'connection.php');
//				//				JError::raiseNotice(1, "Updating currency " );
//				$contents = VmConnector::handleCommunication( $curr_filename );
//				$last_updated = date('Ymd');
			}else {
				$contents = @file_get_contents( $curr_filename );
			}
            
			if( $contents ) {
				// if archivefile does not exist
				if( $archive ) {
					// now write new file
					file_put_contents( $archivefile_name, $contents );
				}

				$contents = str_replace ("<Cube currency='USD'", " <Cube currency='EUR' rate='1'/> <Cube currency='USD'", $contents);

				/* XML Parsing */
				$xmlDoc = new DomDocument();

				if( !$xmlDoc->loadXML($contents) ) {
					//todo
                    cot_message('Failed to parse the Currency Converter XML document.', 'warning');
                    cot_message('The content: '.$contents, 'warning');
					//					$GLOBALS['product_currency'] = $vendor_currency;
					return false;
				}

				$currency_list = $xmlDoc->getElementsByTagName( "Cube" );
				// Loop through the Currency List
				$length = $currency_list->length;
				for ($i = 0; $i < $length; $i++) {
					$currNode = $currency_list->item($i);
					if(!empty($currNode) && !empty($currNode->attributes->getNamedItem("currency")->nodeValue)){
						$currency[$currNode->attributes->getNamedItem("currency")->nodeValue] = $currNode->attributes->getNamedItem("rate")->nodeValue;
						unset( $currNode );
					}

				}
				$globalCurrencyConverter = $currency;
			}
			else {
				$globalCurrencyConverter = false;
                cot_message('Failed to retrieve the Currency Converter XML document.', 'warning');
			}
			return $globalCurrencyConverter;
	}

}
// pure php no closing tag