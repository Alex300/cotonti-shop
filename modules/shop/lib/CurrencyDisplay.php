<?php
/**
 * CurrencyDisplay
 * 
 * @package shop
 * @subpackage currency
 * @TODO почистить код
 * @todo может это перенести в модель валюты?
 * @todo плагины как альтернативные конвертеры валют
 */
defined('COT_CODE') or die('Wrong URL.');

class CurrencyDisplay {

	static $_instance;
	private $_currencyConverter;

    /**
     * id валюты продавца
     * @var int 
     */
    public $_vendorCurrency;
    
	private $_currency_id   = '0';		// string ID related with the currency (ex : language)
	private $_symbol    		= 'udef';	// Printable symbol
	private $_nbDecimal 		= 2;	// Number of decimals past colon (or other)
	private $_decimal   		= ',';	// Decimal symbol ('.', ',', ...)
	private $_thousands 		= ' '; 	// Thousands separator ('', ' ', ',')
	private $_positivePos	= '{number}{symbol}';	// Currency symbol position with Positive values :
	private $_negativePos	= '{sign}{number}{symbol}';	// Currency symbol position with Negative values :
	/**
     * @var array of 0 and 1 first is if price should be shown, second is rounding, third title
     */
    public $_priceConfig	= array();
	var $exchangeRateShopper = 1.0;

	private function __construct ($vendorId = 0){
        global $cfg, $db;
        
		$converterFile  = $cfg["shop"]['currency_converter'];
		if (file_exists( $cfg['modules_dir'].DS.'shop'.DS.'inc'.DS.'currency_converter'.DS.$converterFile.'.php' )) {
			$module_filename = $converterFile;
			require_once($cfg['modules_dir'].DS.'shop'.DS.'inc'.DS.'currency_converter'.DS.$converterFile.'.php');
			if( class_exists( $module_filename )) {
				$this->_currencyConverter = new $module_filename();
			}
		} else {
			if(!class_exists('convertECB')) require($cfg['modules_dir'].DS.'shop'.DS.'inc'.DS.'currency_converter'.DS.'convertECB.php');
			$this->_currencyConverter = new convertECB();
		}

		if(empty($vendorId)) $vendorId = 1;

        $this->_vendorCurrency = Vendor::getCurrencyId($vendorId);
        if (!$this->_vendorCurrency) $this->_vendorCurrency = $cfg["shop"]['default_currency'];

		$this->setPriceArray();
	}

	/**
	 *
	 * Gives back the format of the currency, gets $style if none is set, with the currency Id, when nothing is found it tries the vendorId.
	 * When no param is set, you get the format of the mainvendor
	 *
	 * @param int 		$currencyId Id of the currency
	 * @param int 		$vendorId Id of the vendor
	 * @return CurrencyDisplay
	 */
	public function getInstance($currencyId=0, $vendorId=0){
        global $usr, $L, $n, $m;

        if(empty(self::$_instance)  || (!empty($currencyId) && $currencyId != self::$_instance->_currency_id) ){

			self::$_instance = new CurrencyDisplay($vendorId);

			if(empty($currencyId)){
                // TODО возможно надо учесть и страницу редактирования товара
				if(!defined('COT_ADMIN')){
                    self::$_instance->_currency_id = ($usr['currency_id']) ? $usr['currency_id'] : 0;
				}
				if(empty(self::$_instance->_currency_id)){
					self::$_instance->_currency_id = self::$_instance->_vendorCurrency;
				}
			} else {
				self::$_instance->_currency_id = $currencyId;
			}

            $style = Currency::getById(self::$_instance->_currency_id);

			if(!empty($style)){
				self::$_instance->setCurrencyDisplayToStyleStr($style);
			} else {
                $usrIsAdmin = cot_auth('shop', 'any', 'A');
				if(!empty(self::$_instance->_currency_id)){
                    $tmp = '';
                    if ($usrIsAdmin){
                        $link = cot_url('admin', array('m'=>'shop', 'n'=> 'vendor', 'a'=>'edit'));
                        $tmp = ' <a href="'.$link.'">'.$L['shop']['pls_configure'].'</a>'; // TODO рессурсы
                    }
                    $msg = $L['shop']['conf_warn_no_format_defined'].$tmp;
                    cot_message($msg, 'warning');

				}else{
                    // Проверка на то что мы не на странице настройки валюты...
					if(!defined('COT_ADMIN') || ($m != 'shop' && $n != 'currency')){
                        $tmp = '';
                        if ($usrIsAdmin){
                            $link = cot_url('admin', array('m'=>'shop', 'n'=>'currency'));
                            $tmp = ' <a href="'.$link.'">'.$L['shop']['pls_configure'].'</a>'; // TODO рессурсы
                        }
                        $msg = $L['shop']['conf_warn_no_currency_defined'].$tmp;
                        cot_message($msg, 'warning');
					}
				}
				//would be nice to automatically unpublish the product/currency or so
			}
		}

		return self::$_instance;
	}

	/**
     * Установить стиль отображения валюты
     *
	 * @param Currency  $style object containing the currency display settings
	 */
	private function setCurrencyDisplayToStyleStr($style) {
		$this->_currency_id = $style->curr_id;
		$this->_symbol = $style->curr_symbol;
		$this->_nbDecimal = $style->curr_decimal_place;
		$this->_decimal = $style->curr_decimal_symbol;
		$this->_thousands = $style->curr_thousands;
		$this->_positivePos = $style->curr_positive_style;
		$this->_negativePos = $style->curr_negative_style;
	}

	/**
	 * This function sets an array, which holds the information if
	 * a price is to be shown and the number of rounding digits
	 *
	 * @todo доработать отображение цен заданным группам 
	 */
	function setPriceArray(){
        global $usr, $cfg;
        
        // Отображать ли цены данному пользователю
        $result = false;
//        if($usr['id'] > 0){
//            $q = 'SELECT `vx`.`virtuemart_shoppergroup_id` FROM `#__virtuemart_vmusers` as `u`
//									LEFT OUTER JOIN `#__virtuemart_vmuser_shoppergroups` AS `vx` ON `u`.`virtuemart_user_id`  = `vx`.`virtuemart_user_id`
//									LEFT OUTER JOIN `#__virtuemart_shoppergroups` AS `sg` ON `vx`.`virtuemart_shoppergroup_id` = `sg`.`virtuemart_shoppergroup_id`
//									WHERE `u`.`virtuemart_user_id` = "'.$user->id.'" ';
//            $this->_db->setQuery($q);
//            $result = $this->_db->loadResult();
//        }
//		if(!$result){
            // Отображать ли цены гостю
//            $q = 'SELECT `price_display`,`custom_price_display` FROM `#__virtuemart_shoppergroups` AS `sg`
//                                WHERE `sg`.`default` = "'.($user->guest+1).'" ';
//
//            $this->_db->setQuery($q);
//            $result = $this->_db->loadRow();
//
//		} else {
//			$q = 'SELECT `price_display`,`custom_price_display` FROM `#__virtuemart_shoppergroups` AS `sg`
//										WHERE `sg`.`virtuemart_shoppergroup_id` = "'.$result.'" ';
//
//			$this->_db->setQuery($q);
//			$result = $this->_db->loadRow();
//		}

        if(!empty($result[0])){
            $result[0] = unserialize($result[0]);
        }

        $custom_price_display = 0;
        if(!empty($result[1])){
            $custom_price_display = $result[1];
        }

        if($custom_price_display && !empty($result[0])){
            $show_prices = $result[0]->get('show_prices', $cfg['shop']['show_prices']);
        } else {
            $show_prices = $cfg['shop']['show_prices'];
        }

        $priceFields = array('basePrice','variantModification','basePriceVariant',
            'basePriceWithTax','discountedPriceWithoutTax',
            'salesPrice','priceWithoutTax',
            'salesPriceWithDiscount','discountAmount','taxAmount');

		if($show_prices==1){
			foreach($priceFields as $name){
				$show = 0;
				$round = 0;
				$text = 0;

				//Here we check special settings of the shoppergroup
				if($custom_price_display==1){
					$show = (int)$result[0]->get($name);
					$round = (int)$result[0]->get($name.'Rounding');
					$text = $result[0]->get($name.'Text');
				} else {
					$show =  $cfg["shop"][$name] ? $cfg["shop"][$name] : 0;
					$round = $cfg["shop"][$name.'Rounding'] ? $cfg["shop"][$name.'Rounding'] : 2;
					$text =  $cfg["shop"][$name.'Text'] ? $cfg["shop"][$name.'Text'] : 0;
				}


				$this->_priceConfig[$name] = array($show,$round,$text);
			}
		} else {
			foreach($priceFields as $name){
				$this->_priceConfig[$name] = array(0,0,0);
			}
		}
	}

	/**
	 * get The actual displayed Currency
	 * Use this only in a view, plugin or modul, never in a model
	 *
	 * @param integer $currencyId
	 * @return integer $currencyId: displayed Currency
	 */
	public function getCurrencyDisplay( $currencyId = 0 ){

		if(empty($currencyId)){
            // $this->_currency_id заполняется в getInstance
            $currencyId = $this->_currency_id;
			if(empty($currencyId)){
				$currencyId = $this->_vendorCurrency;
			}
		}

		return $currencyId;
	}

    /**
     * This function is for the gui only!
     * Use this only in a controller, view, plugin or module, never in a model
     *
     * @param float $price
     * @param integer $currencyId
     * @param float $quantity
     * @param bool $inToShopCurrency
     * @param integer $nb кол-во знаков после запятой
     * @return string formatted price
     */
	public function priceDisplay($price=0.0, $currencyId = 0, $quantity = 1.0, $inToShopCurrency = false, $nb = -1){
		$currencyId = $this->getCurrencyDisplay($currencyId);
        $price = (float)$price * (float)$quantity;
		$price = $this->convertCurrencyTo($currencyId, $price, $inToShopCurrency);
		return $this->getFormattedCurrency($price, $nb);
	}

    /**
     * function to create a div to show the prices, is necessary for JS
     *
     * @param string $name name of the price
     * @param String $description
     * @param array|float $product_price the prices of the product
     * @param bool $priceOnly
     * @param bool $switchSequel
     * @param float $quantity
     * @return string a div for prices which is visible according to config and have all ids and class set
     */
	public function createPriceDiv($name, $description, $product_price, $priceOnly=false, $switchSequel=false,$quantity = 1.0){

		if(empty($product_price)) return '';

        //The fallback, when this price is not configured
        if(empty($this->_priceConfig[$name])) $name = "salesPrice";
        if(is_array($product_price)){
            $price = $product_price[$name] ;
        } else {
            $price = $product_price;
        }
        $price = (float)$price;
		//This could be easily extended by product specific settings
		if(!empty($this->_priceConfig[$name][0])){
			if(!empty($price)){
				$vis = "block";
                $priceFormatted = $this->priceDisplay($price,0,(float)$quantity,false,$this->_priceConfig[$name][1] );
			} else {
                $priceFormatted = '';
				$vis = "none";
			}
			if($priceOnly){
                return $priceFormatted;
			}
			$descr = '';
			if($this->_priceConfig[$name][2]) $descr = $description;

            if(!$switchSequel){
                return '<div class="Price'.$name.'" style="display : '.$vis.';" >'.$descr.'<span class="Price'.$name.'" >'.$priceFormatted.'</span></div>';
            } else {
                return '<div class="Price'.$name.'" style="display : '.$vis.';" ><span class="Price'.$name.'" >'.$priceFormatted.'</span>'.$descr.'</div>';
            }
		}
	}

	/**
	 *
	 * @param int|Currency $currency
	 * @param float $price
	 * @param bool $shop
     * @return float
     */
	function convertCurrencyTo($currency, $price, $shop=true){
        global $db, $db_shop_currencies;

        if(empty($currency)){
			return $price;
		}

		// If both currency codes match, do nothing
		if( $currency == $this->_vendorCurrency ) {
			return $price;
		}

        if(is_Object($currency)){
            $exchangeRate = $currency->curr_exchange_rate;
        }
        else {
            $currency = (int)$currency;
            $currency = Currency::getById($currency);
            if(!empty($currency->curr_exchange_rate) && $currency->curr_exchange_rate !== '0.00000'){
                $exchangeRate = (float)$currency->curr_exchange_rate;
            } else {
                $exchangeRate = FALSE;
            }
        }

        $this->exchangeRateShopper = $exchangeRate;

        if(!empty($exchangeRate)){
            // convertCurrencyTo Use custom rate'
            if($shop){
                $price = $price / $exchangeRate;
            } else {
                $price = $price * $exchangeRate;
            }
        } else {
            $currencyCode = $currency->curr_code_3;
            $vendorCurrencyCode = Currency::getCode3ById($this->_vendorCurrency);
            $globalCurrencyConverter = $_REQUEST['globalCurrencyConverter'];   // TODO Оно нада ????
            if($shop){
                $price = $this->_currencyConverter->convert( $price, $currencyCode, $vendorCurrencyCode);
            } else {
                $price = $this ->_currencyConverter->convert( $price , $vendorCurrencyCode, $currencyCode);
            }
        }

        return $price;
	}


	/**
	 * Format, Round and Display Value
	 * @param val number
	 */
	private function getFormattedCurrency( $nb, $nbDecimal=-1){

		if($nbDecimal===-1) $nbDecimal = $this->_nbDecimal;
		if($nb>=0){
			$format = $this->_positivePos;
			$sign = '+';
		} else {
			$format = $this->_negativePos;
			$sign = '-';
			$nb = abs($nb);
		}

		//$res = $this->formatNumber($nb, $nbDecimal, $this->_thousands, $this->_decimal);
		$res = number_format((float)$nb,$nbDecimal,$this->_decimal,$this->_thousands);
		$search = array('{sign}', '{number}', '{symbol}');
		$replace = array($sign, $res, $this->_symbol);

		$formattedRounded = str_replace ($search, $replace, $format);

		return $formattedRounded;
	}

	/**
	 *
	 * @author Horvath, Sandor [HU] http://de.php.net/manual/de/function.number-format.php
	 * @author Max Milbers
	 * @param double $number
	 * @param int $decimals
	 * @param string $thousand_separator
	 * @param string $decimal_point
	 */
	function formatNumber($number, $decimals = 2, $decimal_point = '.', $thousand_separator = '&nbsp;' ){

		//    	$tmp1 = round((float) $number, $decimals);

		return number_format($number,$decimals,$decimal_point,$thousand_separator);
		//		while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1){
		//			$tmp1 = $tmp2;
		//		}
		//
		//		return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
	}

	/**
	 * Return the currency symbol
	 */
	public function getSymbol() {
		return($this->_symbol);
	}

	/**
	 * Return the currency ID
	 */
	public function getId() {
		return($this->_currency_id);
	}

	/**
	 * Return the number of decimal places
	 *
	 * @author RickG
	 * @return int Number of decimal places
	 */
	public function getNbrDecimals() {
		return($this->_nbDecimal);
	}

	/**
	 * Return the decimal symbol
	 *
	 * @author RickG
	 * @return string Decimal place symbol
	 */
	public function getDecimalSymbol() {
		return($this->_decimal);
	}

	/**
	 * Return the decimal symbol
	 *
	 * @author RickG
	 * @return string Decimal place symbol
	 */
	public function getThousandsSeperator() {
		return($this->_thousands);
	}

	/**
	 * Return the positive format
	 *
	 * @author RickG
	 * @return string Positive number format
	 */
	public function getPositiveFormat() {
		return($this->_positivePos);
	}

	/**
	 * Return the negative format
	 *
	 * @author RickG
	 * @return string Negative number format
	 */
	public function getNegativeFormat() {
		return($this->_negativePos);
	}



}
// pure php no closing tag