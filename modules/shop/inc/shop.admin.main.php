<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Admin Controller class for the Tax & Calculation Rule
 * 
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class MainController{
    

    /**
     * Construct the controller
     * @access public
     */
    public function __construct() {
        global $cfg;
        
        //$this->_calcModel = new Calc();

    }
    
    /**
     * Main (index) Action.
     * Calc Rules List
     * @todo выборка листов через модель
     */
    public function indexAction(){
        global $adminpath, $adminhelp, $db,  $cfg,  $L;
        
//        $adminpath[] = '&nbsp;'.$L['shop']['control_panel'];
        $pAccountUrl = cot_url('shop', '&m=order');
        $adminhelp = $L['shop']['user_account_here'].": <a href=\"{$pAccountUrl}\" target=\"_blank\">{$pAccountUrl}</a>";

        $tpl = new XTemplate(cot_tplfile('shop.admin.main'));

        $tpl->assign(array(

            'PAGE_TITLE' => $L['shop']['control_panel'],

        ));
        $tpl->parse('MAIN');
        return $tpl->text();
        //$productlist = $this->_productAdapter->getProductListing(false,false,false,false,true);
	}

}