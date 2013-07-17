<!-- BEGIN: MAIN -->
<div class="col3-2 first">
    <div class="block">
        <div id="breadcrumb">{BREAD_CRUMBS}</div>
        
		<h2 class="prefs">{PAGE_TITLE}</h2>
        
        {FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

        <!-- IF {PHP.usr.id} == 0 -->
        {FILE "{PHP.loginTpl}"}
        <!-- ENDIF -->
        
        <form action="{FORM_ACTION}" method="post" id="userForm" name="userForm"
              class="form-validate form-inline form-fullwidth">

            <!-- BEGIN: USER_REGISTER -->
            <h3 class="margintop10">{PHP.L.Register}:</h3>
            <table id="register" class="table">
                <!-- BEGIN: ROW -->
                <tr>
                    <td class="width30">{ROW_TITLE}:<!--  IF {ROW_REQUIRED} == 1 --> * <!-- ENDIF --></td>
                    <td class="width70 register<!--  IF {ROW_REQUIRED} == 1 --> required<!-- ENDIF -->">
                        {ROW_EDITCODE} <span id="{ROW_NAME}_desc"></span>
                    </td>
                </tr>
                <!-- END: ROW -->
			</table>
            <!-- END: USER_REGISTER -->
            
            <!-- IF {PHP.usr.id} == 0 -->
                 <!-- IF {ADDRESS_TYPE} == 'BT' -->
                    <h3 class="margintop10">{PHP.L.shop.user_form_billto}:</h3>
                 <!-- ELSE -->   
                    <h3 class="margintop10">{PHP.L.shop.user_form_shipto}</h3>
                 <!-- ENDIF -->
            <!-- ELSE -->
                <!-- IF {ADDRESS_TYPE} == 'BT' -->
                    <h3 class="margintop10">{PHP.L.shop.user_form_billto_edit}:</h3>
                 <!-- ELSE -->
                    <h3 class="margintop10">{PHP.L.shop.user_form_shipto}</h3>
                 <!-- ENDIF -->
            <!-- ENDIF -->

            
            <!-- 
            Выводим поля для редактирования адреса 
            Их можно выводить и традиционным способом как все екстраполя:
            {COUNTRY_TITLE} {COUNTRY} 

            Но тут они выводятся в цикле. Это позволяет добавлять поля в админке без необходимости править шаблоны
            -->

            <!-- BEGIN: USER_FIELDS -->
            <table id="adress" class="table">
                <!-- BEGIN: ROW -->
                <!-- IF {ROW_FIELD_NAME} != {PHP.cfg.shop.uextf_region} AND {ROW_FIELD_NAME} != {PHP.cfg.shop.uextf_city} -->
                <tr>
                    <td>{ROW_TITLE}:<!--  IF {ROW_REQUIRED} == 1 --> * <!-- ENDIF --></td>
                    <td class="<!--  IF {ROW_REQUIRED} == 1 -->required<!-- ENDIF -->">
                        {ROW_EDITCODE}
                         <span id="{ROW_NAME}_desc"></span>
                    </td>
                </tr>
                <!-- ENDIF -->
                <!-- END: ROW -->
            </table>
            <!-- END: USER_FIELDS -->
            
            <input type="hidden" name="addrtype" value="{ADDRESS_TYPE}" />
            <input type="hidden" id="task" name="task" value="" />

            <div class="margintop10">
                <!-- IF {PHP.cfg.shop.oncheckout_show_register} == 1 AND {PHP.usr.id} == 0 AND {PHP.cfg.shop.oncheckout_only_registered} == 0 AND {ADDRESS_TYPE} == 'BT' -->
                <p style="text-align: justify">{PHP.L.shop.oncheckout_default_text_register}</p>
                <!-- ENDIF -->
                <!-- IF {PHP.cfg.shop.oncheckout_show_register} == 1 AND {PHP.usr.id} == 0 AND {ADDRESS_TYPE} == 'BT' -->
                    <button id="reg_chekout" type="submit">{PHP.L.shop.register_and_checkout}</button>
                    <!-- IF {PHP.cfg.shop.oncheckout_only_registered} == 0 -->
                        <button id="chekout" type="submit">{PHP.L.shop.checkout_as_guest}</button>
                    <!-- ENDIF -->
                    <button type="reset" onclick="window.location.href='{PHP|cot_url('shop','m=cart')}'" >{PHP.L.Cancel}</button>
                <!-- ELSE -->
                <button id="chekout" type="submit">{PHP.L.Submit}</button>
                <button type="reset" onclick="window.location.href='{FORM_CANCEL_URL}'" >{PHP.L.Cancel}</button>
                <!-- ENDIF -->
                <span id="preloader"></span>
            </div>

        </form>
            
        <!-- TODO ссылка на добавление/редактирование адресов доставки -->
    </div>
</div>
<!-- END: MAIN -->