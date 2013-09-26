<?php
/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');

$L['info_desc'] = 'Интеренет магазин для CMF Cotonti Siena';


/**
 * Shop Confirmations
 */
//$L['shop_delete'] = '';


/**
 * Admin
 */
$L['shop']['calc'] = "Налоги и правила расчета";
$L['shop']['calc_epoint_datax'] = "Изменение цены после удержания налога";
$L['shop']['calc_epoint_dataxbill'] = "Изменение цены после удержания налога за заказ";
$L['shop']['calc_epoint_dbtax'] = "Изменение цены до удержания налога";
$L['shop']['calc_epoint_dbtaxbill'] = "Изменение цены до удержания налога за заказ";
$L['shop']['calc_epoint_pmargin'] = "Постоянная наценка/скидка (размер прибыли)";
$L['shop']['calc_epoint_tax'] = "Налог на товар";
$L['shop']['calc_epoint_taxbill'] = "Налог на заказ в целом";
$L['shop']['calc_epoint_vattax'] = 'НДС по каждому товару';
$L['shop']['calc_kind'] = "Вид расчета";
$L['shop']['calc_kind_desc'] = "Изменение цены - величина, на которую изменится основная цена товара";
$L['shop']['calc_value_mathop'] = "Арифметическое действие";
$L['shop']['cats_with_no_calcs'] = "Категории без правил";
$L['shop']['check_updates'] = "Проверить обновления";
$L['shop']['control_panel'] = "Панель управления";
$L['shop']['countries'] = "Страны";
$L['shop']['coupons'] = "Купоны";
$L['shop']['coupon_deleted'] = 'Купон удален';
$L['shop']['coupon_expiry'] = 'Срок годности купона';
$L['shop']['coupon_expiry_tip'] = 'Оставте пустым для бесконечного действия срока купона';
$L['shop']['coupon_new'] = "Новый купон";
$L['shop']['coupon_percent'] = 'Процент';
$L['shop']['coupon_percent_total'] = "Процент или Всего";
$L['shop']['coupon_start'] = 'Дата начала действия купона';
$L['shop']['coupon_total'] ='Общая сумма';
$L['shop']['coupon_type'] = 'Тип купона';
$L['shop']['coupon_type_gift'] = 'Подарочный';
$L['shop']['coupon_type_permanent'] = 'Постоянный';
$L['shop']['coupon_type_tip'] = 'Действие подарочного купона истекает после его активации. Постоянным купоном можно
    пользоваться постоянно пока он активен.';
$L['shop']['customer_notify'] = 'Уведомить покупателя?';
$L['shop']['customer_notified'] = 'Покупатель уведомлен?';
$L['shop']['currency_decimal_symbol'] = 'Десятичный разделитель';
$L['shop']['currency_decimals'] = 'Знаков после запятой';
$L['shop']['currency_decimals_tip'] = '(может быть \'0\'). Округляется, если количество знаков в значении отлично
    от указанного';
$L['shop']['currency_edit'] = 'Редактировать валюту';
$L['shop']['currency_exchange_rate'] = 'Обменный курс';
$L['shop']['currency_format_tip']= 'Для положительного или отрицательного формата, используйте теги:
    <ul>
        <li>{sign} для алгебраического знака,</li>
        <li>{number} для значений и</li>
        <li>{symbol} для валюты, </li>
    </ul>
    разрешены все HTML-теги, кроме знака &#124;<br />
    Например <pre><code>&quot;{sign} &lt;b&gt;{number}&lt;/b&gt; {symbol}&quot;</code></pre>
     отобразится как &quot;+ <b>42,23</b> &#8364;&quot';
$L['shop']['currency_negative_format'] = 'Формат отрицательных значений';
$L['shop']['currency_new'] = 'Новая валюта';
$L['shop']['currency_positive_format'] = 'Формат положительных значений';
$L['shop']['currency_symbol'] = 'Символ валюты';
$L['shop']['currency_thousands'] = 'Тысячный разделитель';
$L['shop']['currency_title'] = 'Название валюты';
$L['shop']['documentation'] = 'Документация';
$L['shop']['documentation_url'] = 'http://portal30.ru/sozdanie-internet-sajtov/free-scripts/cotonti-shop/shop-documentation';
$L['shop']['extfields_added'] = 'добавлено полей';
$L['shop']['extfields_skipped'] = 'пропущено полей';
$L['shop']['from_lang_file'] = 'Из lang-файла';
$L['shop']['include_comment'] = 'Включить комментарий?';
$L['shop']['module_homepage_url'] = "http://portal30.ru/sozdanie-internet-sajtov/free-scripts/cotonti-shop/opisanie-skachat";
$L['shop']['module_version'] = "Версия модуля";
$L['shop']['never'] = "Никогда";
$L['shop']['numeric_code'] = 'Числовой код';
$L['shop']['order'] = "Заказ";
$L['shop']['orders'] = "Заказы";
$L['shop']['order_min_total'] = "Минимальная сумма заказа";
$L['shop']['order_pass'] = "Секретный ключ";
$L['shop']['order_update_status'] = "Изменить статус";
$L['shop']['order_updated_success'] = "Заказ обновлен";
$L['shop']['order_statuses'] = 'Статусы заказов';
$L['shop']['price_modifier'] = 'Изменение цены';
$L['shop']['stock_handle'] = 'Обработка остатков на складе';
$L['shop']['stock_handle_A'] = 'Доступно';
$L['shop']['stock_handle_O'] = 'Списано со склада';
$L['shop']['stock_handle_R'] = 'Зарезервировано';
$L['shop']['stock_handle_tip'] = 'Укажите движение товара при смене статуса заказа.<br />Зарезервированные товары списываются
    с фондов на продажу, но числятся на складе';
$L['shop']['titletooshort'] = 'Заголовок слишком короткий либо отсутствует';
$L['shop']['update_linestatus'] ='Обновить статус для всех позиций?';
$L['shop']['user_account_here'] = 'Личный кабинет пользователя доступен по ссылке';
$L['shop']['visible_for_shopper'] = "Отобразить для покупателей";
$L['shop']['visible_for_vendor'] = "Отобразить для продавцов";
$L['shop']['order_alredy_present'] = 'Товар «%1$s» уже присутсвует в заказе';

/**
 * Main
 */
$L['shop']['accepted_currencies'] = "Принимаемые валюты";
$L['shop']['accepted_currencies_desc'] = "Покупатель может просматривать цены на товары в этих валютах";
$L['shop']['address'] = "Адрес";
$L['shop']['address_2'] = "Адрес 2";
$L['shop']['ajax_cart_plz_wait'] = "Пожалуйста, подождите";
$L['shop']['cart_action'] = "Обновить";
$L['shop']['cart_add_to'] = 'Добавить в корзину';
$L['shop']['cart_add_notify'] = 'Уведомить меня';
$L['shop']['cart_change_payment'] = "Изменить способ оплаты";
$L['shop']['cart_change_shipping'] = "Изменить способ доставки";
$L['shop']['cart_checkout_done_confirm_order'] = "Заказ оформлен. Пожалуйста, подтвердите его.";
$L['shop']['cart_confirm'] = "Подтвердите заказ";
$L['shop']['cart_data_not_valid'] = "Введены недопустимые данные";
$L['shop']['cart_delete'] = "Удалить товаров из корзины";
$L['shop']['cart_edit_coupon'] = "Изменить купон";
$L['shop']['cart_edit_payment'] = "Выбрать способ оплаты";
$L['shop']['cart_edit_shipping'] = "Выбрать способ доставки";
$L['shop']['cart_empty_cart'] = "Корзина пуста";
$L['shop']['cart_error_no_product_ids'] = "Ошибка при добавлении товара в корзину: нет идентификатора (id)";
$L['shop']['cart_error_no_valid_quantity'] = "Пожалуйста, введите количество для данного элемента.";
$L['shop']['cart_max_order'] = 'Максимальное количество для заказа данного товара %d.';
$L['shop']['cart_min_order'] = 'Минимальное количество для заказа данного товара %d.';
$L['shop']['cart_name'] = "Название";
$L['shop']['cart_no_payment_method_public'] = "К сожалению нет способа оплаты соответствующего характеристикам вашего
    заказа.";
$L['shop']['cart_no_payment_selected'] = "Способ оплаты не выбран";
$L['shop']['cart_no_product'] = "Нет товаров в корзине.";
$L['shop']['cart_no_shipment_selected'] = "Способ доставки не выбран";
$L['shop']['cart_no_shipping_method_public'] = "К сожалению нет способа доставки соответствующего характеристикам вашего
    заказа.";
$L['shop']['cart_one_product'] = "1 товар";
$L['shop']['cart_only_registered'] = "Пожалуйста, зарегистрируйте, чтобы продолжить оформление заказа";
$L['shop']['cart_overview'] = "Корзина";
$L['shop']['cart_payment'] = "Оплата";
$L['shop']['cart_please_accept_tos'] = "Пожалуйста, примите Условия обслуживания, чтобы продолжить";
$L['shop']['cart_price'] = "Цена";
$L['shop']['cart_price_free'] = "Бесплатно";
$L['shop']['cart_price_per_unit'] = "Цена за единицу";
$L['shop']['cart_product_added'] = "Товар был добавлен в корзину.";
$L['shop']['cart_product_updated'] ="Количество товара было обновлено.";
$L['shop']['cart_quantity'] = "Количество";
$L['shop']['cart_selectpayment'] = "Выберите способ оплаты";
$L['shop']['cart_selectshipment'] = "Выберите способ доставки";
$L['shop']['cart_shipping'] = "Доставка";
$L['shop']['cart_show'] = "Показать корзину";
$L['shop']['cart_sku'] = "Артикул";
$L['shop']['cart_subtotal_discount_amount'] = "Скидка";
$L['shop']['cart_subtotal_tax_amount'] = "Налоги";
$L['shop']['cart_title'] = "Корзина";
$L['shop']['cart_thankyou'] = "Спасибо за Ваш заказ!";
$L['shop']['cart_tos'] = "Условия обслуживания";
$L['shop']['cart_tos_read_and_accepted'] = "Пожалуйста, прочтите и примите Условия обслуживания.";
$L['shop']['cart_total'] = "Итого";
$L['shop']['cart_total_payment'] = "Итоговая стоимость в вашей валюте";
$L['shop']['cart_update'] = "Обновить количество в корзине";
$L['shop']['cart_x_products']= '%s товаров';
$L['shop']['checkout_as_guest'] = "Оформить заказ как Гость";
$L['shop']['checkout_please_enter_address'] = "Пожалуйста, заполните Ваши реквизиты";
$L['shop']['city'] = "Город";
$L['shop']['comment'] = "Комментарий";
$L['shop']['company'] = "Компания";
$L['shop']['conf_warn_no_currency_defined'] = "Валюта не настроена!";
$L['shop']['conf_warn_no_format_defined'] = "Валюта магазина не выбрана!";
$L['shop']['continue_shopping'] = "Продолжить покупки";
$L['shop']['coupon_code'] = 'Код купона';
$L['shop']['coupon_code_change'] = "Изменить код купона";
$L['shop']['coupon_code_enter'] = "Введите код купона";
$L['shop']['coupon_code_expired'] = 'Cрок действия купона истек';
$L['shop']['coupon_code_notyet'] = 'Купон еще пока активирован, его можно использовать с %1$s';
$L['shop']['coupon_code_tolow'] = 'Этот купон действителен для заказа с минимальной суммой %1$s';
$L['shop']['coupon_discount'] = "Купон на скидку";
$L['shop']['coupon_notfound'] = 'Купон не найден. Пожалуйста попробуйте еще раз.';
$L['shop']['currency'] = "Валюта";
$L['shop']['currency_select'] = "Выберите валюту";
$L['shop']['customer'] = "Покупатель";
$L['shop']['discount'] = "Скидка";
$L['shop']['default_vendor_currency'] = "Валюта продавца по умолчанию";
$L['shop']['height'] = 'Высота';
$L['shop']['last_modified'] = "Последние изменения";
$L['shop']['leave_comment'] = "Комментарий к заказу";
$L['shop']['lenght'] = 'Длина';
$L['shop']['login_form'] = "Если вы уже зарегистрированы, пожалуйста, введите логин и пароль";
$L['shop']['manufacturer'] = "Производитель";
$L['shop']['minicart_added_js'] = " добавлен в корзину";
$L['shop']['minicart_error_js'] = "Ошибка обновления корзины";
$L['shop']['missing_value_for_field'] = "Отсутствуют значения %s";
$L['shop']['mobile_phone'] = "Мобильный телефон";
$L['shop']['my_orders'] = 'Мои заказы';
$L['shop']['no_payment_methods_configured'] = 'Способ оплаты не был настроен%1$s';
$L['shop']['no_payment_methods_configured_link'] = ', перейдите %1$s';
$L['shop']['no_payment_plugins_installed'] = 'Нет установленных платежных плагинов. Пожалуйста,
<a href="http://portal30.ru/sozdanie-internet-sajtov/free-scripts/cotonti-shop" target="_blank">скачайте</a> и установите их.';
$L['shop']['no_price_set'] = "Нет установленной цены";
$L['shop']['no_shipping_methods_configured'] = 'Способ доставки не настроен %1$s';
$L['shop']['no_shipping_methods_configured_link'] = ', пожалуйста настройте: %1$s';
$L['shop']['notify_me'] = "Уведомить меня";
$L['shop']["notify_me_desc"] = 'Мы вынуждены вам сообщить, что этот товар (<b>%1$s</b>) отсутствует на складе или не в
    достаточном количестве для вашего заказа.<br />Пожалуйста, оставьте ваш адрес электронной почты, если вы
    пожелаете быть проинформированы о поступлении новой партии данного товара на наш склад.<br />Спасибо!';
$L['shop']['oncheckout_default_text_register'] = "Чтобы оформить заказ и получить доступ к истории Ваших заказов, 
    Вы можете <strong>зарегистрироваться</strong>. Если Вы не хотите создавать учетную запись, Вы можете <strong>оформить заказ как Гость</strong>.";
$L['shop']['no_shipment_plugins_installed'] = 'Нет установленных плагинов доставки. Пожалуйста,
<a href="http://portal30.ru/sozdanie-internet-sajtov/free-scripts/cotonti-shop" target="_blank">скачайте</a> и установите их.';
$L['shop']['order_confirm_mnu'] = "Подтвердить заказ";
$L['shop']['order_create_date'] = "Дата заказа";
$L['shop']['order_deleted'] = 'Заказ id# %1$s удален';
$L['shop']['order_id'] = "ID заказа";
$L['shop']['order_info'] = "Информация о заказе";
$L['shop']['order_items'] = "Позиции заказа";
$L['shop']['order_number'] = "Номер заказа";
$L['shop']['order_notfound'] = "Заказ не найден! Возможно он был удален.";
$L['shop']['order_pay_description'] = 'Оплата заказа № %1$s от %2$s';
$L['shop']['order_print_product_prices_total'] = "Результат";
$L['shop']['order_status'] = "Статус заказа";
$L['shop']['order_total'] = "Сумма заказа";
$L['shop']['packs_count'] = "Количество упаковок";
$L['shop']['pay'] = 'Оплатить';
$L['shop']['payment_description'] = "Описание платежа";
$L['shop']['payment_method'] = "Способ оплаты";
$L['shop']['payment_methods'] = "Способы оплаты";
$L['shop']['payment_title'] = "Название способа оплаты";
$L['shop']['payment_plugin'] = "Платежный плагин";
$L['shop']['phone'] = "Телефон";
$L['shop']['pls_configure'] = "Пожалуйста настройте";
$L['shop']['plug_not_installed_but_used'] = 'Плагин &laquo;%1$s&raquo; не установлен, но используется в &laquo;%2$s&raquo;';
$L['shop']['products'] = "Товары";
$L['shop']['product_addprice'] = "Цена";
$L['shop']['product_addprice_from'] = "При заказе от";
$L['shop']['product_addprice_to'] = "до";
$L['shop']['product_baseprice'] = "Основная цена";
$L['shop']['product_baseprice_variant'] = "Основная цена для комбинации";
$L['shop']['product_baseprice_withtax'] = "Основная цена с налогами";
$L['shop']['product_dim'] = 'Габариты / Вес';
$L['shop']['product_discoun_amount'] = "Скидка";
$L['shop']['product_discount_override'] = 'Переопределение цены';
$L['shop']['product_discount_override_tip'] = "Вы можте использовать этот параметр для назначения временной скидки на товар";
$L['shop']['product_discounted_price'] = "Цена со скидкой";
$L['shop']['product_form_add_prices'] = "Дополнительные цены";
$L['shop']['product_form_calc_base_price'] = 'Расчет себестоимости';
$L['shop']['product_form_calc_base_price_tip'] = 'Отметьте, чтобы расчитать себестоимость на основе желаемой
    окончательной цены';

$L['shop']['product_form_in_stock'] = "В наличии";
$L['shop']['product_form_max_quantity'] = "Макс. кол-во";
$L['shop']['product_form_min_quantity'] = "Мин. кол-во";
$L['shop']['product_form_max_order'] = "Максимальное количество для заказа";
$L['shop']['product_form_min_order'] = "Минимальное количество для заказа";
$L['shop']['product_form_ordered_stock'] = "Зарезервировано, заказанные товары";
$L['shop']['product_form_prices'] = "Стоимость товара";
$L['shop']['product_form_price_base_tip'] = "Основная цена - это цена, преобразованная в валюту продавца";
$L['shop']['product_form_price_cost'] = "Цена (себестоимость)";
$L['shop']['product_form_price_cost_tip'] = "Фактическая цена в выбранной валюте. Может быть закупочной ценой или себестоимостью";
$L['shop']['product_form_price_final'] = "Окончательная цена";
$L['shop']['product_form_price_final_tip'] = "Окончательная цена - это основная цена со всеми правилами, расчитанная в валюте продавца.";
$L['shop']['product_form_override_final'] = 'Переопределение окончательной цены';
$L['shop']['product_form_override_to_tax'] = 'Переопределение до удержания налога';
$L['shop']['product_form_rules_overrides'] = "Правила расчета цены";
$L['shop']['product_form_step'] = "Шаг";
$L['shop']['product_form_step_tip'] = 'Значение на которое прибавляется количество заказываемого товара при нажатии +/-.
    По умолчанию 1.<br />Если дробное, то разрешено заказывать дробное кол-во товара';
$L['shop']['product_not_found'] = "Запрашиваемый товар не найден!";
$L['shop']['product_out_of_stock'] = "Товар отсутствует на складе";
$L['shop']['product_out_of_quantity'] = 'На складе осталось меньше товара, количество установлено в %s';
$L['shop']['product_out_of_pack'] = 'Данный товар продается только упаковками по %2$s %3$s, количество установлено в %1$s';
$L['shop']['product_order_by_pack'] = "Продавать упаковками?";
$L['shop']['product_packaging'] = "Количество в упаковке";
$L['shop']['product_quantity_corrected'] = 'Товар &laquo;%1$s&raquo; можно заказывать c шагом в &laquo;%2$s&raquo;.
 Количество товара установлено в &laquo;%3$s&raquo;';
$L['shop']['product_quantity_corrected_min'] = 'Товар &laquo;%1$s&raquo; можно заказывать c шагом в &laquo;%2$s&raquo; начиная
 от &laquo;%3$s&raquo;. Количество товара установлено в &laquo;%4$s&raquo;';
$L['shop']['product_quantity_error'] = "Количество товара не обновлено";
$L['shop']['product_quantity_success'] = "Количество товара успешно обновлено";
$L['shop']['product_remove_error'] = "Товар не удален";
$L['shop']['product_removed'] = "Товар успешно удален";
$L['shop']['product_tax_amount'] = "Размер налога";
$L['shop']['product_tax_no_special'] = "По умолчанию";
$L['shop']['product_tax_none'] = "Не применять правил";
$L['shop']['product_salesprice'] = "Цена";
$L['shop']['product_salesprice_widthout_tax'] = "Цена без скидки";
$L['shop']['product_salesprice_width_discount'] = "Цена со скидкой";
$L['shop']['product_unit'] = 'Единица измерения товара';
$L['shop']['product_variant_mod'] = "Модификатор цены";
$L['shop']['published'] = "Опубликовано";
$L['shop']['receipt_goods'] = 'Поступление товара';
$L['shop']['register_and_checkout'] = "Зарегистрироваться и оформить заказ";
$L['shop']['request_accepted'] = 'Ваша заявка принята';
$L['shop']['rules_affecting'] = 'Правила';
$L['shop']['saved'] = "Сохранено";
$L['shop']['select_payment_method'] = "Пожалуйста, выберите способ оплаты выше, и нажмите 
    &laquo;{$L['Submit']}&raquo; для отображаения дополнительных параметров";
$L['shop']['select_shipping_method'] = "Пожалуйста, выберите Способ доставки выше, и нажмите 
    &laquo;{$L['Submit']}&raquo; для отображаения дополнительных параметров";
$L['shop']['shared'] = "Общий";
$L['shop']['shipment_description'] = "Описание доставки";
$L['shop']['shipment_method'] = "Способ доставки";
$L['shop']['shipment_methods'] = "Способы доставки";
$L['shop']['shipment_title'] = "Название способа доставки";
$L['shop']['shipment_plugin'] = "Плагин доставки";
$L['shop']['shop'] = "Магазин";
$L['shop']['shopper_info'] = "Информация о покупателе";
$L['shop']['tax'] = "Налог";
$L['shop']['tax_affecting'] = "Правила налогообложения";
$L['shop']['total'] = "Всего на сумму";
$L['shop']['user_form_billto'] = "Ваши данные (реквизиты)";
$L['shop']['user_form_billto_lbl'] = "Реквизиты покупателя";
$L['shop']['user_form_billto_edit'] = "Редактировать ваши данные (реквизиты)";
$L['shop']['user_form_billto_as_shipto'] = "Использовать адрес из реквизитов как адрес доставки";
$L['shop']['user_form_cart_step2'] = "Оформление заказа. Шаг 2";
$L['shop']['user_form_cart_step3'] = "Оформление заказа. Шаг 3";
$L['shop']['user_form_cart_step4'] = "Оформление заказа. Шаг 4";
$L['shop']['user_form_edit_billto_explain'] = "Использовать адрес из ваших Реквизитов. Чтобы ввести другой 
    адрес доставки воспользуйтесь ссылкой ниже";
$L['shop']['user_form_shipto'] = "Адрес доставки";
$L['shop']['user_form_shipto_add'] = "Добавить адрес доставки";
$L['shop']['user_form_shipto_add_edit'] = "Добавить/Изменить адрес доставки";
$L['shop']['user_form_shipto_edit'] = 'Изменить адрес доставки';
$L['shop']['user_guest_checkout'] = "Вы можете пока продолжить оформление заказа как гость.";
$L['shop']['vendor'] = "Продавец";
$L['shop']['vendors'] = "Продавцы";
$L['shop']['waiting_users'] = "Ожидают поступления";
$L['shop']['waiting_users_notify'] = 'Уведомить этих пользователей сейчас (если вы обновите количество товаров на складе)';
$L['shop']['width'] = 'Ширина';
$L['shop']['weight'] = "Вес";
$L['shop']['your_account_details'] = "Ваши данные";
$L['shop']['zip'] = "Индекс";

/**
 * Order State
 */
$L['shop']['order_P'] = 'Ожидает оплаты';
$L['shop']['order_C'] = 'Оплачен';
$L['shop']['order_X'] = 'Отменен';
$L['shop']['order_R'] = 'Возврат';
$L['shop']['order_S'] = 'Отгружено';
$L['shop']['order_U'] = 'Подтвержден покупателем';

/**
 * LWH Units
 */
$L['shop']['LWH_unit_CM']     = 'Сантиметр';
$L['shop']['LWH_unit_FT']     = 'Фут';
$L['shop']['LWH_unit_IN']     = 'Дюйм';
$L['shop']['LWH_unit_M']      = 'Метр';
$L['shop']['LWH_unit_MM']     = 'Миллиметр';
$L['shop']['LWH_unit_YD']     = 'Ярд';
$L['shop']['LWH_unit_symbol_CM']     = 'см';
$L['shop']['LWH_unit_symbol_FT']   = 'фт';
$L['shop']['LWH_unit_symbol_IN']   = 'дюйм';
$L['shop']['LWH_unit_symbol_M']      = 'м';
$L['shop']['LWH_unit_symbol_MM']     = 'мм';
$L['shop']['LWH_unit_symbol_YD']   = 'ярд';

/**
 * Weight Units
 */
$L['shop']['weight_unit_KG']     = 'Килограммы';
$L['shop']['weight_unit_GR']     = 'Граммы';
$L['shop']['weight_unit_MG']     = 'Миллиграммы';
$L['shop']['weight_unit_LB']     = 'Фунты';
$L['shop']['weight_unit_OZ']     = 'Унции';
$L['shop']['weight_unit_symbol_KG'] = 'кг';
$L['shop']['weight_unit_symbol_GR'] = 'г';
$L['shop']['weight_unit_symbol_MG'] = 'мг';
$L['shop']['weight_unit_symbol_LB'] = 'фт';
$L['shop']['weight_unit_symbol_OZ'] = 'уц';

/**
 * Mail 
 */
$L['shop']['mail_default_subj'] = 'Информация о заказе [%3$s], %1$s, на сумму %2$s';
$L['shop']['mail_hello'] = "Здравствуйте";
$L['shop']['mail_shopper_thankyou'] ="Благодарим вас за покупку на ";
$L['shop']['mail_shopper_info_lbl'] = "Информация о Вашем заказе представлена ниже";
$L['shop']['mail_shopper_comment'] = "Ваш комментарий";
$L['shop']['mail_shopper_your_order_link'] = "Чтобы просмотреть заказ, проследуйте по ссылке";
$L['shop']['mail_shopper_question'] = "Если у вас возникли вопросы, то пишите";
$L['shop']['mail_shopper_new_order_confirmed'] = '[%3$s], Оформлен заказ в %1$s, на сумму %2$s';
$L['shop']['mail_shopper_subj_C'] = 'Заказ [%3$s] оплачен. Сумма заказа: %2$s. %1$s.';
$L['shop']['mail_shopper_subj_P'] = 'Заказ [%3$s] ожидает оплаты. Сумма заказа: %2$s. %1$s.';
$L['shop']['mail_shopper_subj_R'] = '[%3$s], Возвращено от заказа на %1$s, всего %2$s';
$L['shop']['mail_shopper_subj_S'] = '[%3$s], Доставка на %1$s, всего %2$s';
$L['shop']['mail_shopper_subj_X'] = '[%3$s], Отмена заказа на %1$s, всего %2$s';

$L['shop']['mail_vendor_order_confimed'] = 'Получен новый заказ';
$L['shop']['mail_vendor_shopper_comment'] = "Комментарий покупателя к заказу";
$L['shop']['mail_vendor_new_order_confirmed'] = '[%3$s], Получен новый заказ от %1$s, на сумму %2$s';
$L['shop']['mail_vendor_subj_P'] = 'Заказ [%3$s] ожидает оплаты в размере %2$s. Покупатель %1$s.';
$L['shop']['mail_vendor_subj_C'] = 'Заказ [%3$s] оплачен, сумма заказа %2$s. Покупатель %1$s.';
$L['shop']['mail_vendor_subj_R'] = '[%3$s], Заказ возвращен для %1$s, всего %2$s';
$L['shop']['mail_vendor_subj_S'] = 'Заказ [%3$s] отправлен для %1$s, сумма заказа %2$s';
$L['shop']['mail_vendor_subj_X'] = '[%3$s], Заказ отменен для %1$s, всего %2$s';

$L['shop']['mail_wu_notify_subj'] = '%1$s поступил на склад!';
$L['shop']['mail_wu_notify_text'] = 'Спасибо за ваше терпение. Товар <b>&laquo;%1$s&raquo;</b> поступил на склад и
 может быть приобретен по этой ссылке: %2$s';

// Системные строки
// TODO перекрывать системные названия экстраполей учитывая названия эестраполей
//if ( $L['page_prod_in_pack_title'] )
$L['page_prod_in_pack_title'] = $L['shop']['product_order_by_pack'];


/**
 * Module Config
 */

$L['cfg_rootCats'] = array('Категории магазина', 'Категории страниц, которые будут корневыми категориями магазина. Все
    страницы этих категорий будут товарами. <i>(Коды категорий. Через запятую)</i>');
$L['cfg_manuf_cat'] = array('Категория производителей', 'Страницы этой и всех вложенных категорий - описания производителей.
     <i>(Код категории)</i>.');
$L['cfg_use_as_catalog'] = array("Использовать как каталог", 'Если включено, все функции корзины будут отключены');
$L['cfg_display_stock'] = array("Отображать остатки", 'Если включено, то количество товара на складе будет отображаться
    в листах и на странице товара');
$L['cfg_coupons_enable'] = array("Использовать купоны", 'Если включено, покупатели могут использовать номера купонов,
    чтобы получить скидку при заказе.');
$L['cfg_coupons_default_expire'] = array("Время действия купона по-умолчанию", 'Время действия купона после его создания
    по-умолчанию. Может быть изменено в самом купоне.');
$L['cfg_coupons_default_expire_params'] = array(
    '1_day' => '1 день',
    '1_week' => '1 неделя',
    '2_weeks' => '2 недели',
    '1_month' => '1 месяц',
    '3_months' => '3 месяца',
);
$L['cfg_weight_unit_default'] = array("Единица массы по умолчанию", 'Единица измерения веса по умолчанию, используемая
    для товаров. Это значение может быть изменено для каждого товара отдельно.');
$L['cfg_weight_unit_default_params'] = array(
    'KG' => $L['shop']['weight_unit_KG'],
    'GR' => $L['shop']['weight_unit_GR'],
    'MG' => $L['shop']['weight_unit_MG'],
    'LB' => $L['shop']['weight_unit_LB'],
    'OZ' => $L['shop']['weight_unit_OZ'],
);
$L['cfg_lwh_unit_default'] = array("Единица Д/Ш/В по умолчанию", '');
$L['cfg_lwh_unit_default_params'] = array(
    'CM' => $L['shop']['LWH_unit_CM'],
    'FT' => $L['shop']['LWH_unit_FT'],
    'IN' => $L['shop']['LWH_unit_IN'],
    'M' => $L['shop']['LWH_unit_M'],
    'MM' => $L['shop']['LWH_unit_MM'],
    'YD' => $L['shop']['LWH_unit_YD'],
);
$L['cfg_mCartOnShopOnly'] = array("Отображать миникорзину только в магазине?", 'Если выключено, то миникорзина отображается
    на всем сайте');
$L['cfg_mCartShowPrice'] = array("Показать цену в миникорзине", '');
$L['cfg_mCartShowProdList'] = array("Показать список товаров в миникорзине", '');
$L['cfg_currency_converter'] = array("Конвертер валют", '');


$L['cfg_checkout'] = array('Оформление заказа');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_c'] = 'Оформление заказа';
$L['cfg_addtocart_act'] = array('Действие при добавлении в корзину');
$L['cfg_addtocart_act_params'] = array(
    'popup' => 'Всплывающее подтверждение',
    'cart' => 'Перейти в корзину (старое поведение магазинов)',
    'none' => 'Ничего не делать. Просто добавить товар в корзину',
);
$L['cfg_automatic_shipment'] = array('Автоматический выбор способа доставки', 'Когда включено,
    если доступен только один способ доставки, он выбирается автоматически.<br />
    Когда выключено, пользователю будет предложено выбрать способ доставки, даже если доступен только один способ.
    Это может быть полезно, если плагин доставки должен проверить данные, введенные пользователем.');
$L['cfg_automatic_payment'] = array('Автоматический выбор способа оплаты', 'Когда включено,
    если доступен только один способ оплаты, он выбирается автоматически.<br />
    Когда выключено, пользователю будет предложено выбрать способ оплаты, даже если доступен только один способ.
    Это может быть полезно, если плагин оплаты должен проверить данные, введенные пользователем.');
$L['cfg_agree_to_tos_onorder'] = array('Покупатели должны соглашаться с условиями обслуживания при КАЖДОМ ЗАКАЗЕ?',
    'Включите, если хотите чтобы покупатель соглашался с условиями обслуживания при оформлении каждого заказа
    (до его размещения).');
$L['cfg_oncheckout_show_legal_info'] = array('Показать условия обслуживания в корзине/на странице подтверждения заказа?',
    'Владельцы магазинов по закону обязаны информировать своих покупателей о политике возврата и политике отмены заказа
    в большинстве европейских стран. Поэтому скорее всего эта опция должна быть включена.');
$L['cfg_oncheckout_show_register'] = array('Регистрация во время оформлениия заказа ', 'В процессе оформления заказа
    клиент может зарегистрироваться');
$L['cfg_oncheckout_only_registered'] = array('Только зарегистрированный пользователь может оформить заказ ',
    "Оформление заказа только для зарегистрированного пользователя, при этом желательно включить 'Регистрацию во время
     оформлениия заказа'");
$L['cfg_oncheckout_show_steps'] = array('Показать шаги оформления заказа', "");
$L['cfg_notify_if_user_admin'] = array('Отправлять уведомления о заказе для покупателя если покупатель - администратор?', "");


$L['cfg_outofstock'] = array('Когда товар на складе заканчивается');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_o'] = 'Когда товар на складе заканчивается';
$L['cfg_lstockmail'] = array('Отправить уведомление о том, что товар заканчивается', 'Отправить уведомление если остатки
    товара на складе меньше чем указано в соотсвующем поле в настройках товара');
$L['cfg_stockhandle'] = array('Если товара нет на складе', "");
$L['cfg_stockhandle_params'] = array(
    'none' => "Товары, которых нет в наличии, могут заказываться. Никаких специальных действий.",
    'disableit' => 'Не показывать товар',
    'disableit_children' => 'Не показывать товар, if child products также не осталось (в разработке)',
    'disableadd' => "Показывать '{$L['shop']['cart_add_notify']}' вместо кнопки '{$L['shop']['cart_add_to']}'",
    'risetime' => 'Отсутствующие товары могут заказываться. Отображается сообщение из поля `Доступность`',
    );
$L['cfg_rised_availability'] = array('Доступность', 'Отображается в карточке товара в случае когда отсутствующий на складе
    товар доступен для покупки.<br />Например: 24 часа, 48 часов, 3-5 дней, На заказ и т. д.');

$L['cfg_page_extfields'] = array('Экстраполя страниц <i>(Значения - названия экстраполей)</i>');
$L['cfg_pextf_min_order_level'] = array('Минимальное кол-во для заказа', 'Экстраполе страницы со значением минимального
  количества для заказа данного товара.<br />тип: double, по-умолч.: 1, min: 0');
$L['cfg_pextf_max_order_level'] = array('Максимальное кол-во для заказа', '0 - неограничено<br />тип: double, по-умолч.: 0, min: 0');
$L['cfg_pextf_sku'] = array('Артикул', 'Артикулы товаров должны быть уникальными<br />тип: input;');
$L['cfg_pextf_unit'] = array('Единицы товара', '(шт. м2 и т.п.)<br />тип: input;');
$L['cfg_pextf_manufacturer_id'] = array('Производитель', 'тип: select, по-умолч.: 0');
$L['cfg_pextf_in_stock'] = array('В наличии', 'тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_ordered'] = array('Зарезервировано', 'Заказанные товары<br />тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_low_stock_notification'] = array('Уведомлять продавца', 'Когда в наличии остается меньше этого значения (0 - не
    уведомлять)<br />тип: double,  по-умолч.: 0');
$L['cfg_pextf_in_pack'] = array('Кол-во в упаковке', 'тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_order_by_pack'] = array('Продавать только упаковками?', 'тип: checkbox,  по-умолч.: 0');
$L['cfg_pextf_step'] = array('Шаг', 'Шаг прибавления плюсиком<br />тип: double,  по-умолч.: 0');
$L['cfg_pextf_allow_decimal_quantity'] = array('Разрешить заказывать дробное кол-во', 'тип: checkbox,  по-умолч.: 0');
$L['cfg_pextf_length'] = array('Длина', 'тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_width'] = array('Ширина', 'тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_height'] = array('Высота', 'тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_lwh_uom'] = array('Единицы ДШВ', 'тип: select, по-умолч.: 0');
$L['cfg_pextf_weight'] = array('Масса товара', 'тип: double,  по-умолч.: 0, min: 0');
$L['cfg_pextf_weight_uom'] = array('Единицы массы', 'тип: select, по-умолч.: 0');
$L['cfg_pextf_no_coupon_discount'] = array('Не применять скидку по купону', 'тип: checkbox,  по-умолч.: 0<br /><br />
    <button id="makePextf" type="button">Создать все экстраполя</button>  Существующие экстраполя затронуты не будут');


$L['cfg_user_extfields'] = array('Экстраполя пользователей (Реквизиты) <i>(Значения - названия экстраполей)</i>');
$L['cfg_uextf_agreed'] = array('Я согласен с Условиями обслуживания', 'тип: checkbox, по-умолч.: 0,');
$L['cfg_uextf_company'] = array($L['shop']['company'], 'тип: input');
$L['cfg_uextf_firstname'] = array('Имя (настоящее)', 'тип: input');
$L['cfg_uextf_middlename'] = array('Отчество', 'тип: input');
$L['cfg_uextf_lastname'] = array('Фамилия', 'тип: input');
$L['cfg_uextf_address'] = array('Адрес', 'тип: inputint');
$L['cfg_uextf_zip'] = array('Почтовый индекс', 'тип: input');
$L['cfg_uextf_city'] = array('ID города', 'тип: inputint');
$L['cfg_uextf_city_name'] = array('Название города', 'тип: input');
$L['cfg_uextf_region'] = array('ID области', 'тип: inputint');
$L['cfg_uextf_region_name'] = array('Название области', 'тип: input');
$L['cfg_uextf_phone'] = array('Телефон', 'тип: input<br /><br />
    <button id="makeUextf" type="button">Создать все экстраполя</button>  Существующие экстраполя затронуты не будут');

$L['cfg_bt_fields_setup'] = array('Заполняемые пользователем реквизиты');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_b'] = 'Заполняемые пользователем реквизиты';
$L['cfg_bt_fields'] = array('Список полей', 'формат записи:<br>
    &lt;поле_1&gt;|&lt;обязательно (1 или 0)&gt;,<br />
    &lt;поле_2&gt;|&lt;обязательно (1 или 0)&gt;<br /><br />
    Записи разделяются запятыми. Для удобства можно использовать перенос строк, но запятые - обязательно.<br />
    Поля будут выводиться в том порядке, в котором они перечислены<br />
    Поля адресов доставки <a href="/admin.php?m=extrafields&n=cot_shop_userinfo">настраиваются тут</a>.
    Желательно, чтобы названия полей профиля пользователя и адреса доставки совпадали');

$L['cfg_showprices'] = array('Формирование цен');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_s'] = 'Формирование цен';
$L['cfg_show_prices'] = array('Показывать цены', "");
$L['cfg_show_tax'] = array('Показать налог в корзине', "");
$L['cfg_checkout_show_origprice'] = array('Показать Цену без скидки в корзине', "");
$L['cfg_askprice'] = array("Показывать 'Позвоните, чтобы узнать цену', если цена отсуствует ");
$L['cfg_sbasePrice'] = array('Базовая цена',
    "В зависимости от настроек налогов и правил расчета это себестоимость или закупочная цена.<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_svariantModification'] = array('Baseprice modificator',
    "The modificator of the baseprice due the chosen product variant<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_sbasePriceVariant'] = array('New baseprice modified by chosen product variant',
    "The baseprice gets modified by the chosen product variant<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_sdiscountedPriceWithoutTax'] = array('Цена со скидкой без НДС',
    "Это интересно для оптовиков и продавцов (B2B)<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название ценыу (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_spriceWithoutTax'] = array('Цена без налога',
    "Это интересно для оптовиков и продавцов (B2B)<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_staxAmount'] = array('Сумма налога', "Выводит только размер налога<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_sbasePriceWithTax'] = array('Базовая цена с НДС, но без скидок',
    "Полезно для вывода старой цены без скидки<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_ssalesPrice'] = array('Окончательная цена',
    "Фактическая цена, которую должен оплатить покупатель.<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_ssalesPriceWithDiscount'] = array('Цена со скидкой, но без переопределения',
    "This is the same as the salesprice, except you used the product specific override option<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_sdiscountAmount'] = array('Размер скидки',
    "Полезно для вывода сообщений: Вы экономите X рублей<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");
$L['cfg_sunitPrice'] = array('Standarized price', "A standarized price for products sold in units, for example in m,l,kg<br />
    формат: &lt;Вкл.(1 или 0)&gt;|&lt;Показать название цены (1 или 0)&gt;|&lt;Округление (число от 1 до 5)&gt;");