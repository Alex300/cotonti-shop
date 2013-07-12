<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

// Функции страниц для cot_generate_pagetags()
require_once cot_incfile('page', 'module');

/**
 * Admin Controller class for the products
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class ProductController{
    
    protected $useSSL = 0;
    protected $useXHTML = true;
    
    protected $_productAdapter;
    

    /**
     * Main (index) Action.
     * Produc List
     * @todo сортировка
     * @todo клонирование товара
     * @todo массовые операции
     * @todo после редактирования возврат назад сюда
     */
    public function indexAction(){
        global $adminpath, $adminhelp, $db, $db_pages, $cfg, $db_users, $structure, $db_shop_product_prices, $L;
        
        // Если установлены комментарии
        $commEnabl = false;
        if(isset($cfg["plugin"]["comments"])){
            require_once cot_incfile('comments', 'plug');
            $commEnabl = true;
        }
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'product')), 'Товары');
        $adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$c = cot_import('c', 'G', 'TXT'); // cat code
//        $o = cot_import('ord', 'G', 'ARR'); // filter field names without 'page_'
//        $p = cot_import('p', 'G', 'ARR'); // filter values
        $fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        //$maxrowsperpage = 1;
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $so = empty($s) ? 'title' : $so;
        $w = empty($w) ? 'ASC' : $w;
        $condition = array();

        // продавца брать из товара ??
        $vendorId = Vendor::getLoggedVendorId();
        $vendor = Vendor::getById($vendorId);
        $currencyDisplay = CurrencyDisplay::getInstance($vendor->curr_id, $vendor->vendor_id);

        $filters = array();
        if (isset($fil)){
            foreach ($fil as $key => $val){
                $key = cot_import($key, 'D', 'ALP', 255);
                $val = cot_import($val, 'D', 'TXT');
                $filters[$key] = $val;
                if ($key && $val && $db->fieldExists($db_pages, "page_$key")){
                    if ($key == 'title'){
                        $condition[] = array('page_title', "*{$val}*");
                    }elseif($key == '_cat'){
                        
                    }else{
                        $params[$key] = $val;
                        $condition[] = array("page_{$key}", $val);
                    }
                }
            }
        }
        
        // Если не установлен фильтр по категориям:
        $cats = shop_readShopCats();
        $fil['_cat'] = cot_import($fil['_cat'], 'D', 'TXT');
        if ($fil['_cat'] == ''){
            $condition[] = array('page_cat', $cats);
        }elseif((int)$fil['_sucat'] == 1){
            $tmp = cot_structure_children('page', $fil['_cat'], true, true,false);
            $condition[] = array('page_cat', $tmp);
        }else{
            $condition[] = array('page_cat', $fil['_cat']);
        }
        
        $list_url_path = array('m' => 'shop', 'n' => 'product', 'fil' => $fil, 'so' => $so, 'w' => $w);
        //$list_url = cot_url('admin', $list_url_path + array('d' => $durl));
        
        $totallines = Product::count($condition);
        $products = Product::find($condition, $maxrowsperpage, $d, "page_$so $w");
        
        $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);

        $mfArr = Manufacturer::getKeyValPairsList();

        $tpl = new XTemplate(cot_tplfile('shop.admin.product'));
        $jj = 0;
        foreach ($products as $prod){
            $jj++;

            $pag = $prod->toArray();

            $tpl->assign(cot_generate_pagetags($pag, 'LIST_ROW_'));
            $tpl->assign($this->generatePageEditTags($prod, 'LIST_ROW_EDIT_', true));
            $product_price = $L['shop']['no_price_set'];
            if(!empty($prod->price) && !empty($prod->price['curr_id']) ){
                $product_price = $currencyDisplay->priceDisplay($prod->price['price'], (int)$prod->price['curr_id'], 1, true);
            }
            $prodUrlP = empty($prod->page_alias) ? array('c' => $prod->page_cat, 'id' => $prod->prod_id) :
                    array('c' => $prod->page_cat, 'al' => $prod->page_alias);
            
            $pag['page_status'] = cot_page_status($pag['page_state'], $pag['page_begin'],$pag['page_expire']);
            $edit_url = cot_url('page', "m=edit&id={$prod->prod_id}");
            
            $tpl->assign(array(
                'LIST_ROW_SKU' => htmlspecialchars($prod->sku),
                'LIST_ROW_PRICE' => $product_price,
                'LIST_ROW_PUBLISH' => ($pag['page_status'] == 'published') ? $L['Yes'] : $L['No'],
                'LIST_ROW_STATUS' => $L['page_status_'.$pag['page_status']],
                'LIST_ROW_STATE' => $pag['page_state'],
                'LIST_ROW_EDIT_PRICE' => '<a href="'.$edit_url.'" id="price_'.$prod->prod_id.'" class="edit_price">'.
                    $product_price.'</a>',
                'LIST_ROW_OWNER' => cot_build_user($pag['page_ownerid'], htmlspecialchars($pag['user_name'])),
                'LIST_ROW_COMMENTS' => ($commEnabl) ? cot_comments_link('page', $prodUrlP, 'page', $prod->prod_id, '',
                        $pag) : '',
                'LIST_ROW_ODDEVEN' => cot_build_oddeven($jj),
                'LIST_ROW_NUM' => $jj,
            ));
            $tpl->assign(cot_generate_usertags($pag, 'LIST_ROW_OWNER_'));
            $tpl->parse('MAIN.LIST_ROW');
        }

        if (isset($pagenav['current']) && $pagenav['current'] > 1){
            $list_url_path = $list_url_path + array('d' => $durl);
        }
        // Фильтры
        foreach ($filters as $key => $value) {
            $tpl->assign('LIST_FILTER_'.mb_strtoupper($key), $value);
        }
        $catFilter = array('' => '---');
        foreach ($cats as $cat) {
            $catFilter[$cat] = $structure['page'][$cat]['tpath'];
            //var_dump($structure['page'][$cat]['tpath']);
        }
        $tpl->assign(array(
            'LIST_PAGINATION' => $pagenav['main'],
            'LIST_COMMENTS_ON' => $commEnabl,
            'LIST_PAGEPREV' => $pagenav['prev'],
            'LIST_PAGENEXT' => $pagenav['next'],
            'LIST_CURRENTPAGE' => $pagenav['current'],
            'LIST_TOTALLINES' => $totallines,
            'LIST_MAXPERPAGE' => $maxrowsperpage,
            'LIST_TOTALPAGES' => $pagenav['total'],
            //'LIST_ITEMS_ON_PAGE' => $jj
            'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
            'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
            'LIST_FILTER_CAT' => cot_selectbox($fil['_cat'], 'fil[_cat]' , array_keys($catFilter), 
                array_values($catFilter),false).' '.cot_checkbox($fil['_sucat'], 'fil[_sucat]', 'Subcats', 1),
            'LIST_FILTER_MF' => cot_selectbox($fil[$cfg["shop"]['pextf_manufacturer_id']],
                    "fil[{$cfg["shop"]['pextf_manufacturer_id']}]" , array_keys($mfArr),
                array_values($mfArr)),
        ));
        
        $tpl->parse('MAIN');
        return $tpl->text();

	}
    
    /**
     * Массовое сохранение товаров
     * @return string 
     */
    public function save(){
        global $db_pages, $db, $L, $cache, $structure, $cfg, $db_structure, $sys;

        $pagDatas = cot_import('s', 'P', 'ARR');
        $retUrl = cot_import('ret', 'P', 'TXT');

        $res = 0;
        foreach($pagDatas as $id => $data){
            $id = (int)$id;
            
            // Получаем все поля таблицы страниц
            // TODO возможно нужно обрабатывать данные в соотвествии с типом поля в БД
            $pagFields = $db->query("SHOW COLUMNS FROM `$db_pages`")->fetchAll(PDO::FETCH_COLUMN);
            
            // Предыдущие данные страницы
            $oldData = $db->query("SELECT page_cat, page_state FROM $db_pages WHERE page_id=$id")->fetch();
            
            // Обработка полей:
            $pagData = array();
            foreach ($data as $key => $value) {
                $field = str_replace('rpage', 'page_', $key);
                if (in_array($field, $pagFields)){
                    $pagData[$field] = $value;
                }
            }
            $pagData['page_updated'] = $sys['now'];
            // TODO проверка ошибок в т.ч. уникальности алияса

            $cnt = $db->update($db_pages, $pagData, 'page_id='.$id);
            if ($cnt < 1){
                //Если ни одно поле не изменено, то все равно дает 0
//                cot_error('Ошибка обновления товара id: '.$id);
//                continue;
            }else{
                $res += $cnt;
                cot_log("Edited page from shop #".$id,'adm');
                
                // обработка состояния страницы (опубликовано/ в очереди на утверждении), изменения категории
                // для правильного обновления счетчиков
                if (isset($pagData['page_cat'])){
                    if ($oldData['page_cat'] != $pagData['page_cat'] && $oldData['page_state'] == 0){
                        $db->query("UPDATE $db_structure SET structure_count=structure_count-1 
                                    WHERE structure_code='".$db->prep($oldData['page_cat'])."' AND structure_area = 'page'");
                    }
                }
                if (isset($pagData['page_cat']) && isset($pagData['page_state'])){
                    if ($pagData['page_state'] == 0){
                        if ($oldData['page_state'] != 0 || $oldData['page_cat'] != $pagData['page_cat']){
                            $db->query("UPDATE $db_structure SET structure_count=structure_count+1 
                                    WHERE structure_code='".$db->prep($pagData['page_cat'])."' AND structure_area = 'page'");
                        }
                    }
                    if ($pagData['page_state'] != 0 && $oldData['page_state'] == 0){
                        $db->query("UPDATE $db_structure SET structure_count=structure_count-1 
                                WHERE structure_code='".$db->prep($pagData['page_cat'])."' ");
                    }
                }
                // Сброс кеша
                if ($pagData['page_state'] == 0 && $cache){
                    if ($cfg['cache_page']){
                        $cache->page->clear('page/' . str_replace('.', '/', $structure['page'][$oldData['page_cat']]['path']));
                        if (isset($pagData['page_cat']) && ($pagData['page_cat'] != $oldData['page_cat'])){
                            $cache->page->clear('page/' . str_replace('.', '/', $structure['page'][$pagData['page_cat']]['path']));
                        }
                    }
                    if ($cfg['cache_index']) $cache->page->clear('index');
                }
            }
            
            /* === Hook === */
            // TODO подумать, надо ли.
            // Теги пока вручную подключим
//            foreach (cot_getextplugins('page.edit.update.done') as $pl){
//                //include $pl;
//                var_dump($pl);
//            }
            /* ===== */

            // === Cохраняем и теги ===
            if (isset($cfg['plugin']['tags'])){
                if ($cfg['plugin']['tags']['pages'] && ( !$admin_rights || cot_auth('plug', 'tags', 'W') )){
                    $_POST['rtags'] = $data['rpagetags'];
                    include $cfg['plugins_dir'].'/tags/tags.page.edit.php';
                }
            }
            // === /Cохраняем и теги ===
        }
        if ($res > 0) cot_message($L['Updated']);
        // TODO редирект с учетом фильтров
        cot_redirect($retUrl);
    }
    
    public function edit_price(){
        
    }

    /**
     * Ajax Создание необходимых экстраполей для страниц
     */
    public function addextfieldsAction(){
        global $L, $cfg, $db_pages;

        $extFields = Product::getShopDefaultExtrafields();

        $ret = array(
            'error' => '',
            'skiped' => 0,
            'added' => 0,
            'message' => '',
        );
        if (count($extFields) > 0){
            foreach($extFields as $key => $field){
                if (!empty($cfg['shop']['pextf_'.$key])){
                    $field['name'] = $cfg['shop']['pextf_'.$key];
                }else{
                    $ret['message'] .= "Field name empty for '$key'. Skipped<br />";
                    $ret['skiped']++;
                    continue;
                }
                $field['description'] = '';
                if (!empty($L['cfg_pextf_'.$key][0])){
                    $field['description'] = $L['cfg_pextf_'.$key][0];
                }else{
                    $field['description'] = $key;
                }

                if (cot_extrafield_add($db_pages, $field['name'], $field['type'], '', '', $field['default'], false, 'HTML',
                    $field['description'], $field['params'])){
                    $ret['added']++;
                }else{
                    $ret['skiped']++;
                }
            }
        }
        if ($ret['message'] != '') $ret['message'] .= '<br />';
//        $ret['message'] .= "{$L['Done']}<br />";
        $ret['message'] .= "- {$L['shop']['extfields_added']}: {$ret['added']}<br />";
        $ret['message'] .= "- {$L['shop']['extfields_skipped']}: {$ret['skiped']}<br />";
        $ret['buttonText'] = 'Ok';
        $ret['title'] = "{$L['Done']}";

        return json_encode($ret);
    }

    // === Служебные методы ===

    /**
     * Returns all page edit tags for coTemplate
     *
     * @param Product $product
     * @param string $tag_prefix Prefix for tags
     * @param bool $mass
     * @param bool $admin_rights Page Admin Rights
     * @internal param \Product $page_data Page Info Array or ID
     * @return array
     *
     * @todo i18n в тегах для редактирования
     */
    protected function generatePageEditTags($product, $tag_prefix = '', $mass = false, $admin_rights = null){
        global $db_pages, $usr, $cfg, $cot_extrafields, $R, $L;
        

        // Первая корневая категория магазина
        // TODO сделать возможность выводить из нескольких корневых категорий магазина
        $rootCat = explode(',', $cfg["shop"]['rootCats']);
        $rootCat = trim($rootCat[0]);
        
        // Получаем name для полей
        $names = array();
        $page_data = $product->toArray();
        foreach ($page_data as $key => $value) {
            if ($mass){
               $names[$key] = "s[{$page_data['page_id']}][".str_replace('page_', 'rpage',  $key)."]";  
            }else{
               $names[$key] = str_replace('page_', 'rpage',  $key);   
            }
        }
        $names['page_delete'] = ($mass) ? "s[{$page_data['page_id']}][rpagedelete]" : 'rpagedelete';
        $names['page_tags'] = ($mass) ? "s[{$page_data['page_id']}][rpagetags]" : 'rpagetags';
        //var_dump($names);
        
        $pageedit_array = array(
            $tag_prefix.'ID' => $page_data['page_id'],
            //'PAGEEDIT_FORM_LOCALSTATUS' => $L['page_status_'.$pag['page_status']],
            $tag_prefix.'CAT' => cot_selectbox_structure('page', $page_data['page_cat'], $names['page_cat']),
            $tag_prefix.'CAT_SHORT' => cot_selectbox_structure('page', $page_data['page_cat'], $names['page_cat'], $rootCat),
            $tag_prefix.'KEYWORDS' => cot_inputbox('text', $names['page_keywords'], $page_data['page_keywords'], 
                    array('maxlength' => '255')),
            $tag_prefix.'ALIAS' => cot_inputbox('text', $names['page_alias'], $page_data['page_alias'], 
                    array('size' => '32', 'maxlength' => '255')),
            $tag_prefix.'TITLE' => cot_inputbox('text', $names['page_title'], $page_data['page_title'], 
                    array('maxlength' => '255')),
            $tag_prefix.'DESC' => cot_inputbox('text', $names['page_desc'], $page_data['page_desc'], 
                    array('maxlength' => '255')),
            $tag_prefix.'AUTHOR' => cot_inputbox('text', $names['page_author'], $page_data['page_author'], 
                    array('maxlength' => '100')),
            $tag_prefix.'DATE' => cot_selectbox_date($page_data['page_date'], 'long', $names['page_date']).' '.$usr['timetext'],
            $tag_prefix.'DATENOW' => cot_checkbox(0, 'rpagedatenow'),
            $tag_prefix.'BEGIN' => cot_selectbox_date($page_data['page_begin'], 'long', $names['page_begin']).' '.$usr['timetext'],
            $tag_prefix.'EXPIRE' => cot_selectbox_date($page_data['page_expire'], 'long', $names['page_expire']).' '.$usr['timetext'],
            $tag_prefix.'UPDATED' => cot_date('datetime_full', $page_data['page_updated']).' '.$usr['timetext'],
            $tag_prefix.'FILE' => cot_selectbox($page_data['page_file'], $names['page_file'], range(0, 2), array($L['No'], $L['Yes'], $L['Members_only']), false),
            $tag_prefix.'URL' => cot_inputbox('text', $names['page_url'], $page_data['page_url'], 
                    array('maxlength' => '255')),
            $tag_prefix.'SIZE' => cot_inputbox('text', $names['page_size'], $page_data['page_size'], 
                    array('maxlength' => '255')),
            $tag_prefix.'TEXT' => cot_textarea($names['page_text'], $page_data['page_text'], 24, 120, '', 'input_textarea_editor'),
            // Надо ли ??? удаление ???
            $tag_prefix.'DELETE' => cot_radiobox(0, $names['page_delete'], array(1, 0), array($L['Yes'], $L['No'])),
            $tag_prefix.'PARSER' => cot_selectbox($page_data['page_parser'], $names['page_parser'], cot_get_parsers(), 
                    cot_get_parsers(), false),
            
            // Спецефично для магазина
            //$tag_prefix.'SKU' => htmlspecialchars($pag['page_'.$cfg["shop"]['pextf_sku']]),
            $tag_prefix.'SKU' => cot_inputbox('text', $names['page_'.$cfg["shop"]['pextf_sku']], 
                    $page_data['page_'.$cfg["shop"]['pextf_sku']], array('maxlength' => '255')),
        );
        
        if ($usr['isadmin'] || !$admin_rights){
            $pageedit_array += array(
                $tag_prefix.'OWNERID' => cot_inputbox('text', $names['page_ownerid'], $page_data['page_ownerid'], 
                        array('maxlength' => '24')),
                $tag_prefix.'PAGECOUNT' => cot_inputbox('text', $names['page_count'], $page_data['page_count'],
                        array('maxlength' => '8')),
                $tag_prefix.'FILECOUNT' => cot_inputbox('text', $names['page_filecount'], $page_data['page_filecount'],
                        array('maxlength' => '8'))
            );
        }
        
        // Extra fields
        foreach($cot_extrafields[$db_pages] as $i => $row_extf){
            $uname = strtoupper($row_extf['field_name']);
            $pageedit_array += array(
                $tag_prefix.$uname => cot_build_extrafields($names['page_'.$row_extf['field_name']], $row_extf, 
                        $page_data['page_'.$row_extf['field_name']]),
                $tag_prefix.$uname.'_TITLE' => isset($L['page_'.$row_extf['field_name'].'_title']) ?  
                    $L['page_'.$row_extf['field_name'].'_title'] : $row_extf['field_description'],
            );
        }
        
        // Теги
        if (isset($cfg['plugin']['tags'])){
            if ($cfg['plugin']['tags']['pages'] && ( !$admin_rights || cot_auth('plug', 'tags', 'W') )){
                require_once cot_incfile('tags', 'plug');
                $tags_extra = null;
                $tags = cot_tag_list($page_data['page_id'], 'pages', $tags_extra);
                $tags = implode(', ', $tags);
                $pageedit_array += array(
                    //$tag_prefix.'TAGS' => cot_rc('tags_input_editpage')
                    $tag_prefix.'TAGS' => cot_inputbox('text', $names['page_tags'], $tags,
                        array('class' => 'autotags'))
                );
            }
        }

        return $pageedit_array;
    }
}