<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');
/**
 * Admin Controller class for the Vendors
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 * @todo добавление продавцов
 * @todo права на редактирование продавцов
 *
 */
class VendorController{
    
   // protected $_vendorAdapter;
    
    /**
     * Construct the controller
     * @access public
     */
    public function __construct() {
        global $cfg;
        
        //$this->_vendorAdapter = new Vendor();

    }
    
    /**
     * Main (index) Action.
     * Payment Methods List
     */
    public function indexAction(){
        
	}
    
    /**
     * Action. Создание редактирование продавца 
     * @todo Мультипродавец
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $cot_plugins, $usr, $cot_groups, $cot_plugins_enabled;

        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'vendors')), $L['shop']['vendors']);
        
        $id = cot_import('id', 'G', 'INT'); 
        if (!$id) $id = Vendor::getLoggedVendorId();
       
        
        $item = array();
        $act = cot_import('act', 'P', 'ALP'); 
        if ($act == 'save'){
            $item['vendor_id']       = cot_import('rid', 'P', 'INT');
            $item['vendor_title']    = cot_import('rtitle', 'P', 'TXT'); 
            $item['vendor_desc']     = cot_import('rdesc', 'P', 'TXT');
            $item['vendor_currency']    = cot_import('rvendor_currency', 'P', 'INT');
            $item['vendor_acc_currencies']  = cot_import('rvendor_acc_currencies', 'P', 'ARR');

            // TODO импорт настроек
            //$item['paym_params']   = shop_importPlgConig($item['pl_code']);
            $vendorModel = new Vendor();
            $sid = $vendorModel->save($item);
            if ($sid > 0){
                cot_message($L['shop']['saved']);
                cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'vendor','a'=>'edit', 'id'=>$sid), 
                        '', true));
            }
        }
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                //$shipAdapter = new ShipmentMethod();
                $item = Vendor::getById($id);
                $item = (array) $item; // Пока так
            }
            $adminpath[] = $item['vendor_title']." [".$L['Edit']."]";
        }
        
        // Load the currencies
        if(!class_exists('Currency')){
            require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'currency.php');
        }
        $currency_model = new Currency();
        $currencies = Currency::getKeyValPairsList();
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.vendor'));
        
        if (!$item['vendor_currency'])  $item['vendor_currency'] = $cfg["shop"]['default_currency'];  
        if (!$item['vendor_acc_currencies'] || count($item['vendor_acc_currencies']) == 0){
            $item['vendor_acc_currencies'] = array($cfg["shop"]['default_currency']);
        }
        $tpl->assign(array(
            'FORM_ID' => $id,
            'FORM_TITLE' => cot_inputbox('text', 'rtitle', $item['vendor_title'], array('size' => '64', 
                'maxlength' => '255')),
            'FORM_DESC' => cot_inputbox('text', 'rdesc', $item['vendor_desc'], array('size' => '64', 
                'maxlength' => '255')),
            'FORM_CURRENCY' => cot_selectbox($item['vendor_currency'], 'rvendor_currency', 
               array_keys($currencies), array_values($currencies), false),
            'FORM_ACC_CURRENCIES' => cot_selectbox($item['vendor_acc_currencies'], 'rvendor_acc_currencies[]', 
               array_keys($currencies), array_values($currencies), false, array('size'=>'10', 'multiple'=>'multiple')),
//            'FORM_PUBLISHED' => cot_radiobox( isset($item['paym_published']) ? $item['paym_published'] : 1,
//                    'rpublished', array(1, 0), array($L['Yes'], $L['No'])),


        ));

        // Вывод специфических настроек
        if ($id > 0){
            shop_renderPlgConfig($item['pl_code'], $item['paym_params'], $tpl, 'EDIT.FORM.PLUG_CONFIG');
            //cot_selectbox_countries($adminpath, $name, $add_empty, $uGroups)
            //$tpl->parse('EDIT.FORM');
        }

        $tpl->parse('EDIT.FORM');
        
        
        $tpl->assign(array(
            'LIST_PAY_PLUG_INSTALLED' => $payPlgsInstalled,
            'PAGE_TITLE' => isset($item['paym_title']) ? $L['shop']['payment_method'].': '.
                    htmlspecialchars($item['paym_title']) : '',
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

}