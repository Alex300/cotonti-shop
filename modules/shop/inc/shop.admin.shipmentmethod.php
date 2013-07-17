<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Admin Controller class for the Shipment Methods
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class ShipmentmethodController{


    /**
     * Main (index) Action.
     * Shipment Methods List
     */
    public function indexAction(){
        global $adminpath, $db, $db_shop_shipmethods, $db_shop_shipmethods_gr, $cfg,  $L, $cot_plugins, $cot_groups,
                $cot_plugins_enabled;
        
        $adminpath[] = '&nbsp;'.$L['shop']['shipment_methods'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        //$maxrowsperpage = 1;
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list
        
        $so = empty($s) ? 'shipm_title' : $so;  // shipm_order - может так?
        $w = empty($w) ? 'ASC' : $w;
        $where = array();
        
        $list_url_path = array('m' => 'shop', 'n' => 'shipmentmethod', 'fil' => $fil, 'so' => $so, 'w' => $w);
        
        // Выбрать все установленные плагины доствки
        // Годится любой хук впринципе. Нам нужно проверить установленные
        // плагины доставки
        $shipPlgs = array();
        $sqllist_rowset = array();
        if (isset($cot_plugins['shop.shipment.calc_price']) && count($cot_plugins['shop.shipment.calc_price']) > 0){
            foreach($cot_plugins['shop.shipment.calc_price'] as $pl){
                $shipPlgs[] = $pl['pl_code'];
            }
            $totallines = ShipmentMethod::count($where);
            $sqllist_rowset = ShipmentMethod::find($where,  $d, $maxrowsperpage, $so.' '.$w);
            $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);
        }
        
        if (count($shipPlgs) == 0) cot_message ($L['shop']['no_shipment_plugins_installed'], 'warning');
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.shipmentmethod'));

        if(count($sqllist_rowset) > 0){
            $uGrpRaw = array();
            $i = $d;
            foreach ($sqllist_rowset as $row) {
                $i++;
                if(!empty($row->user_groups)){
                    foreach($row->user_groups as $grp){
                        $uGrpRaw[$row->shipm_id][] = $cot_groups[$grp];
                    }
                }
                $tpl->assign(array(
                    'LIST_ROW_ID' => $row->shipm_id,
                    'LIST_ROW_NUM' => $i,
                    'LIST_ROW_TITLE' => htmlspecialchars($row->shipm_title),
                    'LIST_ROW_DESC' => htmlspecialchars($row->shipm_desc),
                    'LIST_ROW_GROUPS_ARR' => $row->user_groups,
                    'LIST_ROW_GROUPS_ARR_RAW' => $uGrpRaw[$row->shipm_id],
                    'LIST_ROW_PLUGIN_CODE' => $row->pl_code,
                    'LIST_ROW_PUBLISHED' => $row->shipm_published ? $L['Yes'] : $L['No'],
                    'LIST_ROW_SHARED' => $row->shipm_shared ? $L['Yes'] : $L['No'],
                     // TODO Текст подтверждения
                    'LIST_ROW_DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=shipmentmethod&a=del&id='.
                            $row->shipm_id.'&'.cot_xg()), 'shop', ''),
                ));
                if ( isset($cot_plugins_enabled[$row->pl_code])){
                    $tpl->assign(array(
                       'LIST_ROW_PLUGIN_TITLE' =>  $cot_plugins_enabled[$row->pl_code]['title'],
                    ));
                }else{
                    $ext_info = $cfg['plugins_dir'].'/'.$row->pl_code.'/'.$row->pl_code.'.setup.php';
                    if (file_exists($ext_info)){
                        $info = cot_infoget($ext_info, 'COT_EXT');
                    }else{
                        $info = array('Name' => $row->pl_code);
                    }
                    $tpl->assign(array(
                       'LIST_ROW_PLUGIN_TITLE' =>  $info['Name']."<br /><b>{$L['adm_notinstalled']}</b>",
                    ));
                    cot_message(sprintf($L['shop']['plug_not_installed_but_used'], $row->pl_code,
                            htmlspecialchars($row->shipm_title)), 'warning');
                    
                }
                $tpl->parse('MAIN.LIST_ROW');
            }
        }
        $tpl->assign(array(
            'LIST_PAGINATION' => $pagenav['main'],
            'LIST_SHIP_PLUG_INSTALLED' => (count($shipPlgs) > 0) ? true : false,
            'LIST_PAGEPREV' => $pagenav['prev'],
            'LIST_PAGENEXT' => $pagenav['next'],
            'LIST_CURRENTPAGE' => $pagenav['current'],
            'LIST_TOTALLINES' => $totallines,
            'LIST_MAXPERPAGE' => $maxrowsperpage,
            'LIST_TOTALPAGES' => $pagenav['total'],
            //'LIST_ITEMS_ON_PAGE' => $jj
            'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
            'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
            'PAGE_TITLE' => $L['shop']['shipment_methods'],

        ));
        $tpl->parse('MAIN');
        return $tpl->text();
        //$productlist = $this->_productAdapter->getProductListing(false,false,false,false,true);
	}
    
    /**
     * Создание редактирование способа доставки
     * @todo Мультипродавец
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $cot_plugins, $usr, $cot_groups, $cot_plugins_enabled;
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'shipmentmethod')), 
            $L['shop']['shipment_methods']);
        
        $id = cot_import('id', 'G', 'INT'); 
        
        $item = array();
        $act = cot_import('act', 'P', 'ALP'); 
        if ($act == 'save'){
            $item['shipm_id']       = cot_import('rid', 'P', 'INT');
            $item['shipm_title']    = cot_import('rtitle', 'P', 'TXT'); 
            $item['shipm_desc']     = cot_import('rdesc', 'P', 'TXT');
            $item['shipm_published']= cot_import('rpublished', 'P', 'BOL');
            $item['pl_code']        = cot_import('rplugincode', 'P', 'TXT');
            $item['user_groups']    = cot_import('rusergroup', 'P', 'ARR');
            $item['vendor_id']      = cot_import('rvendor', 'P', 'ARR');
            // импорт настроек (только для существующих методов)
            if ($item['shipm_id'] > 0) $item['shipm_params']   = shop_importPlgConig($item['pl_code']);
            $method = new ShipmentMethod($item);
            $sid = $method->save($item);
            if ($sid > 0){
                cot_message($L['shop']['saved']);
                cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'shipmentmethod','a'=>'edit', 'id'=>$sid), 
                        '', true));
            }
        }
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $item = ShipmentMethod::getById($id);
            }
            $adminpath[] = $item->shipm_title." [".$L['Edit']."]";
        }
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.shipmentmethod'));
        $shipPlgs = array();
        if (isset($cot_plugins['shop.shipment.calc_price']) && count($cot_plugins['shop.shipment.calc_price']) > 0){
            $shipPlgsInstalled = true;
            $shipPlgs = array();
            foreach($cot_plugins['shop.shipment.calc_price'] as $pl){
                // Может быть путаница т.к. локализованное название не соотвествует названию в списке плагинов
                $shipPlgs[$pl['pl_code']] = $cot_plugins_enabled[$pl['pl_code']]['title'];
                
            }
            $uGroups = array();
            foreach($cot_groups as $k => $i){
                $uGroups[$k] = $cot_groups[$k]['title'];
            }
            
            $tpl->assign(array(
                'FORM_ID' => $id,
                'FORM_TITLE' => cot_inputbox('text', 'rtitle', $item->shipm_title, array('size' => '64', 'maxlength' => '255')),
                'FORM_DESC' => cot_inputbox('text', 'rdesc', $item->shipm_desc, array('size' => '64', 'maxlength' => '255')),
                'FORM_PUBLISHED' => cot_radiobox( isset($item->shipm_published) ? $item->shipm_published : 1,
                        'rpublished', array(1, 0), array($L['Yes'], $L['No'])),
                'FORM_PLUGIN_CODE' => cot_selectbox($item->pl_code, 'rplugincode', array_keys($shipPlgs), array_values($shipPlgs), false),
                'FORM_USER_GROUP' => cot_selectbox($item->user_groups, 'rusergroup[]', array_keys($uGroups),
                        array_values($uGroups), false, array('multiple'=>'multiple', 'placeholder'=>'Select...')),
                'FORM_VENDOR' => 'In development',
            ));
           
            // Вывод специфических настроек
            if ($id > 0){
                shop_renderPlgConfig($item->pl_code, $item->shipm_params, $tpl, 'EDIT.FORM.PLUG_CONFIG');
            }
            
            $tpl->parse('EDIT.FORM');
        }else{
            $shipPlgsInstalled = false;
            cot_message ($L['shop']['no_shipment_plugins_installed'], 'warning');
        }
        
        $tpl->assign(array(
            'LIST_SHIP_PLUG_INSTALLED' => $shipPlgsInstalled,
            'PAGE_TITLE' => isset($item->shipm_title) ? $L['shop']['shipment_method'].': '.
                    htmlspecialchars($item->shipm_title) : '',
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }
    
}