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
            $vid = cot_import('rid', 'P', 'INT');
            if(!$vid){
                $vendor = new Vendor($vid);
            }else{
                $vendor = Vendor::getById($vid);
            }
            $vendor->vendor_title    = cot_import('rtitle', 'P', 'TXT');
            $vendor->vendor_desc     = cot_import('rdesc', 'P', 'TXT');
            $vendor->curr_id         = cot_import('rvendor_currency', 'P', 'INT');
            $vendor->vendor_acc_currencies  = cot_import('rvendor_acc_currencies', 'P', 'ARR');

            $sid = $vendor->save();
            if ($sid > 0){
                cot_message($L['shop']['saved']);
                cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'vendor','a'=>'edit', 'id'=>$sid), 
                        '', true));
            }
        }
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
            $item = new Vendor();
        }else{
            if ($act != 'save'){
                $item = Vendor::getById($id);
            }
            $adminpath[] = $item->vendor_title." [".$L['Edit']."]";
        }
        
        // Load the currencies
        $currencies = Currency::getKeyValPairsList();
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.vendor'));
        
        if (!$item->curr_id)  $item->curr_id = $cfg["shop"]['default_currency'];
        if (!$item->vendor_acc_currencies || count($item->vendor_acc_currencies) == 0){
            $item->vendor_acc_currencies = array($cfg["shop"]['default_currency']);
        }
        $tpl->assign(array(
            'FORM_ID' => $id,
            'FORM_TITLE' => cot_inputbox('text', 'rtitle', $item->vendor_title, array('size' => '64',
                'maxlength' => '255')),
            'FORM_DESC' => cot_inputbox('text', 'rdesc', $item->vendor_desc, array('size' => '64',
                'maxlength' => '255')),
            'FORM_CURRENCY' => cot_selectbox($item->curr_id, 'rvendor_currency',
               array_keys($currencies), array_values($currencies), false),
            'FORM_ACC_CURRENCIES' => cot_selectbox($item->vendor_acc_currencies, 'rvendor_acc_currencies[]',
               array_keys($currencies), array_values($currencies), false, array('size'=>'10', 'multiple'=>'multiple')),
//            'FORM_PUBLISHED' => cot_radiobox( isset($item['paym_published']) ? $item['paym_published'] : 1,
//                    'rpublished', array(1, 0), array($L['Yes'], $L['No'])),


        ));

        $tpl->parse('EDIT.FORM');
        
        
        $tpl->assign(array(
            'PAGE_TITLE' => isset($item->vendor_title) ? htmlspecialchars($item->vendor_title) : '',
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

}