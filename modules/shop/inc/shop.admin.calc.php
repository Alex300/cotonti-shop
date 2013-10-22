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
class CalcController{

    /**
     * Main (index) Action.
     * Calc Rules List
     */
    public function indexAction(){
        global $adminpath, $cot_groups, $structure, $cot_countries, $cfg,  $L, $sys;
        
        $adminpath[] = '&nbsp;'.$L['shop']['calc'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        //$maxrowsperpage = 1;
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        // Получить все категории магазина
        $shopCats = shop_readShopCats();
        $catsKeyVal = array();
        if (is_array($shopCats)){
            foreach ($shopCats as $cat){
                if(isset($structure['page'][$cat])){
                    $catsKeyVal[$cat] = $structure['page'][$cat]['title'];
                }
            }
        }
        // Получить все правила
        $condition = array(
            "calc_published=1",
            "calc_publish_up <='".date('Y-m-d H:i:s', $sys['now'])."'",
            array('RAW', "calc_publish_down >='".date('Y-m-d H:i:s', $sys['now'])."' OR calc_publish_down ='".date('Y-m-d H:i:s', 0)."'"),
        );
        // Под php 5.2 не работает передача массива напрямую в качесте условия
        $allCalcs = Calc::find($condition);
        // Ищем те разделы, для которых не определены правила
        if (!empty($allCalcs)){
            foreach($allCalcs as $calc){
                if (empty($calc->categories)){
                    $catsKeyVal = array();
                    break;
                }
                foreach($calc->categories as $cat){
                    if (isset($catsKeyVal[$cat])) unset($catsKeyVal[$cat]);
                }
            }
        }
        $vendorId = Vendor::getLoggedVendorId();
        
        $so = empty($s) ? 'calc_title' : $so;   // Или order?
        $w = empty($w) ? 'ASC' : $w;
//        $where = array();
        
        $list_url_path = array('m' => 'shop', 'n' => 'calc', 'fil' => $fil, 'so' => $so, 'w' => $w);
        
        $totallines = Calc::count();
        $sqllist_rowset = Calc::find(array(),$maxrowsperpage, $d, $so, $w);
        $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.calc'));

        if (!empty($catsKeyVal)){
            // Выводим категории без правил:
            $kk = 1;
            foreach($catsKeyVal as $cat => $title){
                $tpl->assign(array(
                   'NR_ROWCAT_URL' => cot_url('page', array('c'=>$cat)),
                   'NR_ROWCAT_TITLE' => $structure['page'][$cat]['title'],
                   'NR_ROWCAT_DESC' => $structure['page'][$cat]['desc'],
                   'NR_ROWCAT_ICON' => $structure['page'][$cat]['icon'],
//                   'NR_ROWCAT_COUNT' => $sub_count,
                   'NR_ROWCAT_ODDEVEN' => cot_build_oddeven($kk),
                   'NR_ROWCAT_NUM' => $kk
                ));
                $tpl->parse('MAIN.NR_CATS.ROW');
                $kk++;
            }
            $tpl->parse('MAIN.NR_CATS');
        }

        if(count($sqllist_rowset) > 0){

            $i = $d;
            foreach ($sqllist_rowset as $row) {
                $i++;

                $catsRaw = array();
                foreach($row->categories as $tmp){
                    if (isset($structure['page'][$tmp])){
                        $catsRaw[] = $structure['page'][$tmp];
                    }
                }

                $countriesRaw = array();
                foreach($row->countries as $tmp){
                    if (isset($cot_countries[$tmp])){
                        $countriesRaw[] = $cot_countries[$tmp];
                    }
                }

                $uGrpRaw = array();
                foreach($row->user_groups as $tmp){
                    if (isset($cot_groups[$tmp])){
                        $uGrpRaw[] = $cot_groups[$tmp];
                    }
                }

                $statesRaw = array();

                $currency = Currency::getById($row->curr_id);

                $expire = $L['shop']['never'];
                $expTime = strtotime($row->calc_publish_down);
                if ($expTime > 1){
                    $expire = cot_date('date_full', $expTime);
                }
                $tpl->assign(array(
                    'LIST_ROW_ID' => $row->calc_id,
                    'LIST_ROW_NUM' => $i,
                    'LIST_ROW_TITLE' => htmlspecialchars($row->calc_title),
                    'LIST_ROW_DESC' => htmlspecialchars($row->calc_desc),
                    'LIST_ROW_VENDOR_ID' => $row->vendor_id,
                    'LIST_ROW_KIND' => $row->calc_kind,
                    'LIST_ROW_KIND_TITLE' => htmlspecialchars(Calc::$_entryPoints[$row->calc_kind]),
                    'LIST_ROW_MATH_OPERATION' => $row->calc_value_mathop,
                    'LIST_ROW_VALUE' => $row->calc_value,
                    'LIST_ROW_CURRENCY' => $row->curr_id,
                    'LIST_ROW_CURRENCY_TITLE' => $currency->curr_title,
                    'LIST_ROW_CURRENCY_SYMBOL' => $currency->curr_symbol,
                    'LIST_ROW_CATEGORIES_ARR' => $row->calc_categories,
                    'LIST_ROW_CATEGORIES_ARR_RAW' => $catsRaw,
                    'LIST_ROW_COUNTRIES_ARR' => $row->countries,
                    'LIST_ROW_COUNTRIES_ARR_RAW' => $countriesRaw,
                    'LIST_ROW_GROUPS_ARR' => $row->user_groups,
                    'LIST_ROW_GROUPS_ARR_RAW' => $uGrpRaw,
                    'LIST_ROW_SHOPPER_PUBLISHED' => $row->calc_shopper_published,
                    'LIST_ROW_SHOPPER_PUBLISHED_TITLE' => ($row->calc_shopper_published > 0) ? $L['Yes'] : $L['No'],
                    'LIST_ROW_BEGIN' => cot_date('date_full', strtotime($row->calc_publish_up)),
                    'LIST_ROW_EXPIRE' => $expire,
                    'LIST_ROW_PUBLISHED' => $row->calc_published,
                    'LIST_ROW_PUBLISHED_TITLE' => ($row->calc_published > 0) ? $L['Yes'] : $L['No'],
                     // TODO Текст подтверждения
                    'LIST_ROW_DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=calc&a=delete&cid[]='.
                            $row->calc_id.'&'.cot_xg()), 'shop', ''),
                ));

                $tpl->parse('MAIN.LIST_ROW');
            }
        }
        $tpl->assign(array(
            'LIST_PAGINATION' => $pagenav['main'],
            'LIST_PAGEPREV' => $pagenav['prev'],
            'LIST_PAGENEXT' => $pagenav['next'],
            'LIST_CURRENTPAGE' => $pagenav['current'],
            'LIST_TOTALLINES' => $totallines,
            'LIST_MAXPERPAGE' => $maxrowsperpage,
            'LIST_TOTALPAGES' => $pagenav['total'],
            //'LIST_ITEMS_ON_PAGE' => $jj
            'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
            'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
            'PAGE_TITLE' => $L['shop']['calc'],

        ));
        $tpl->parse('MAIN');
        return $tpl->text();
	}
    
    /**
     * Создание / редактирование правила расчета
     * @todo Мультипродавец multix
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $usr, $cot_groups, $structure;
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'calc')), $L['shop']['calc']);
        
        $id = cot_import('id', 'G', 'INT'); 
        
        $item = array();
        $act = cot_import('act', 'P', 'ALP'); 
        if ($act == 'save'){
            $item['calc_id']       = cot_import('rid', 'P', 'INT');
            $item['calc_title']    = cot_import('rtitle', 'P', 'TXT'); 
            $item['calc_desc']     = cot_import('rdesc', 'P', 'TXT');
            $item['calc_published'] = cot_import('rpublished', 'P', 'BOL');
            $item['calc_kind'] = cot_import('rkind', 'P', 'ALP');
            $item['calc_value_mathop'] = cot_import('rvalue_mathop', 'P', 'TXT', 8);
            $item['calc_value'] = cot_import('rvalue', 'P', 'NUM');
            $item['curr_id'] = cot_import('rcurr_id', 'P', 'INT');
            $item['categories']    = cot_import('rcategories', 'P', 'ARR');
            $item['user_groups']    = cot_import('rusergroup', 'P', 'ARR');
            $item['countries']    = cot_import('rcountries', 'P', 'ARR');
            $item['calc_shopper_published'] = cot_import('rshopper_published', 'P', 'BOL');
            $item['calc_vendor_published'] = cot_import('rvendor_published', 'P', 'BOL');
            $item['calc_publish_up'] = date('Y-m-d H:i:s', (int)cot_import_date('rpublish_up'));
            $item['calc_publish_down'] = date('Y-m-d H:i:s', (int)cot_import_date('rpublish_down'));
            $item['vendor_id']      = cot_import('rvendor', 'P', 'INT');

            $calc = new Calc($item);

            if ($sid = $calc->save($item)){
                /* === Hook === */
                //vmcalculation->plgVmStorePluginInternalDataCalc
                foreach (cot_getextplugins('shop.calc.edit.save.done') as $pl){
                    include $pl;
                }
                cot_message($L['shop']['saved']);
                cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'calc','a'=>'edit', 'id'=>$sid), 
                        '', true));
            }
        }
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $item = Calc::getById($id);
            }
            $adminpath[] = $item->calc_title." [".$L['Edit']."]";
        }
        
        $entryPoints = Calc::$_entryPoints;
        
        //MathOp array
		$mathOps = array('+','-','+%','-%');
        
         // Load the currencies
        $currencies = Currency::getKeyValPairsList();
        
        $cats = shop_readShopCats();
        $catsKeyVal = array();
        if (is_array($cats)){
            foreach ($cats as $cat){
                if(isset($structure['page'][$cat])){
                    $catsKeyVal[$cat] = $structure['page'][$cat]['title'];
                }
            }
        }   

        $t = new XTemplate(cot_tplfile('shop.admin.calc'));
        
        $uGroups = array();
        foreach($cot_groups as $k => $i){
            $uGroups[$k] = $cot_groups[$k]['title'];
        }

        $t->assign(array(
            'FORM_ID' => $id,
            'FORM_TITLE' => cot_inputbox('text', 'rtitle', $item->calc_title, array('size' => '64',
                'maxlength' => '255')),
            'FORM_DESC' => cot_inputbox('text', 'rdesc', $item->calc_desc, array('size' => '64',
                'maxlength' => '255')),
            'FORM_PUBLISHED' => cot_radiobox( isset($item->calc_published) ? $item->calc_published : 1,
                    'rpublished', array(1, 0), array($L['Yes'], $L['No'])),
            'FORM_CALC_KIND' => cot_selectbox($item->calc_kind, 'rkind', array_keys($entryPoints),
                    array_values($entryPoints), false),
            'FORM_MATH_OPERATION' => cot_selectbox($item->calc_value_mathop, 'rvalue_mathop', $mathOps,$mathOps, false),
            'FORM_VALUE' => cot_inputbox('text', 'rvalue', $item->calc_value, array('size' => '64',
                'maxlength' => '255')),
            'FORM_CURRENCY' => cot_selectbox($item->curr_id, 'rcurr_id',
               array_keys($currencies), array_values($currencies), false),
            // TODO человеский мультиселект
            'FORM_CATEGORIES' => cot_selectbox($item->categories, 'rcategories[]', array_keys($catsKeyVal),
                    array_values($catsKeyVal), true, array('multiple'=>'multiple', 'size'=>'10')),
            // TODO человеский мультиселект
            'FORM_USER_GROUP' => cot_selectbox($item->user_groups, 'rusergroup[]', array_keys($uGroups),
                    array_values($uGroups), true, array('multiple'=>'multiple')),
            // TODO человеский мультиселект
            'FORM_COUNTRIES' => cot_selectbox_countries($item->countries, 'rcountries[]', false,
                    array('multiple'=>'multiple', 'size'=>'10')),
            'FORM_SHOPPER_PUBLISHED' => cot_radiobox( isset($item->calc_shopper_published) ? $item->calc_shopper_published : 0,
                    'rshopper_published', array(1, 0), array($L['Yes'], $L['No'])),
            'FORM_VENDOR_PUBLISHED' => cot_radiobox( isset($item->calc_vendor_published) ? $item->calc_vendor_published : 0,
                    'rvendor_published', array(1, 0), array($L['Yes'], $L['No'])),
            'FORM_BEGIN' => cot_selectbox_date(strtotime($item->calc_publish_up), 'short', 'rpublish_up').' '.
                $usr['timetext'],
            'FORM_EXPIRE' => cot_selectbox_date(strtotime($item->calc_publish_down), 'short', 'rpublish_down').' '.
                $usr['timetext'],
        ));
        
        if($cfg["shop"]['multix'] != 0 && cot_auth('shop', 'any', 'A') ){
            // todo vendor list
			$t->assign(array(
                'FORM_VENDOR' => 'In development',
            ));
		}
        
         /* === Hook === */
        //vmcalculation->plgVmOnDisplayEdit
        foreach (cot_getextplugins('shop.calc.edit.tags') as $pl){
            include $pl;
        }
        
        $t->parse('EDIT.FORM');
        
        $t->assign(array(
            'PAGE_TITLE' => isset($item->calc_title) ? $L['shop']['calc'].': '.
                    htmlspecialchars($item->calc_title) : $L['shop']['calc'].': '.$L['Add'],
        ));
        $t->parse('EDIT');
        return $t->text('EDIT');
    }

    public function deleteAction(){
        global $L;

        if(empty($_GET['cid'])) cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'calc'), '', true));

        if(is_array($_GET['cid'])){
            $ids = cot_import('cid', 'G', 'ARR');
        }else{
            $ids = cot_import('cid', 'G', 'INT');
            $ids = array($ids);
        }

        $cnt = 0;
        foreach($ids as $id){
            $id = (int)$id;
            if(!$id) continue;

            $calc = Calc::getById($id);
            if(!$calc) continue;

            $title = $calc->calc_title;
            if($calc->delete()){
                cot_message($L['Deleted']." «{$title}»");
            }

        }

        cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'calc'), '', true));

        return '';
    }

}