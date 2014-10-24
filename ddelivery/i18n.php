<?php

use DDelivery\DDeliveryUI;

require_once(implode(DIRECTORY_SEPARATOR, array('DDelivery', 'public_html', 'application', 'bootstrap.php')));
$regedit = regedit::getInstance();
$ddcomlist = DDeliveryUI::getCompanySubInfo();


$companys = array();

foreach ($ddcomlist as $k => $arr) {
    $companys['option-supported_type_' . $k] = $arr['name'];
    $companys['option-com_' . $k] = 'Способы доставки:';
}

for ($k = 1; $k <= 3; $k++) {
    $opl["option-price_if_{$k}_min"] = 'От:';
    $opl["option-price_if_{$k}_max"] = 'До:';
    $opl["option-price_if_{$k}_amount"] = 'Стоимость доставки:';
    $opl["option-price_if_{$k}_type"] = 'Тип доставки:';
}

$i18n = Array(

    'header-ddelivery-tree' => "Редактирование",
    'module-ddelivery'=>'DDelivery.ru',


    'header-ddelivery-DdBaseConfig' => "Базовая конфигурация",
    'header-ddelivery-DdMain' => "Основые",
    'header-ddelivery-DdSootv' => 'Соответствия полей',
    'header-ddelivery-DdSposoby' => 'Способы доставки',
    'header-ddelivery-DdOplata' => 'Оплата доставки',
    'header-ddelivery-DdPunkty' => 'Пункты самовывоза и курьерская доставка',


    'group-base' => 'Группа базовых настроек',
    'option-is_active' => 'Активен:',
    'option-sort' => 'Сортировка:',
    'option-title' => 'Название:',
    'option-description' => 'Описание:',
    'option-tax_rate' => 'Наценка на стоимость доставки %:',
    'option-photo' => 'Логотип доставки:',

    'group-main' => 'Группа основных настроек',
    'option-api_key' => 'API Ключ:',
    'option-test' => 'Режим работы:',
    'option-declared_percent' => 'Какой % от стоимости товара страхуется:',


    'group-sposoby' => 'Настройка используемых способов доставки по службам',
    'group-oplata' => 'Как меняется стоимость доставки в зависимости от размера заказа.в руб. Вы можете гибко настроить условия доставки, чтобы учесть вашу маркетинговую политику.',

    'group-sootv_dost' => 'Соответствия статусов заказа и доставки',

    'option-order_status_10' => 'В обработке',
    'option-order_status_20' => 'Подтверждена',
    'option-order_status_30' => 'На складе ИМ',
    'option-order_status_40' => 'Заказ в пути',
    'option-order_status_50' => 'Заказ доставлен',
    'option-order_status_60' => 'Заказ получен',
    'option-order_status_70' => 'Возврат заказа',
    'option-order_status_80' => 'Клиент вернул заказ',
    'option-order_status_90' => 'Частичный возврат заказа',
    'option-order_status_100' => 'Возвращен в ИМ',
    'option-order_status_110' => 'Ожидание',
    'option-order_status_120' => 'Отмена',

    'option-send_status' => 'Выберите статус, при котором заявки из вашей системы будут уходить в DDelivery.',
    'group-sootv_base' => 'Статус для отправки в DDelivery.',

    'group-sootv_gabarity' => 'Габариты: соответствие полей',
    'option-swidth' => 'ширина',
    'option-slength' => 'длина',
    'option-sweight' => 'вес',
    'option-sheight' => 'высота',
    'group-sootv_gabarity_default' => 'Настройка габаритов по умолчанию',
    'option-dwidth' => 'ширина',
    'option-dlength' => 'длина',
    'option-dweight' => 'вес',
    'option-dheight' => 'высота',

    'option-around' => 'Округление цены доставки для покупателя:',
    'option-around_step' => 'шаг:',
    'option-punkt_is_active' => 'Активен:',
    'option-punkt_title' => 'Название:',
    'option-punkt_tax_rate' => 'Наценка на стоимость доставки %:',
    'option-pay_system' => 'Платёжные системы:',
    'option-restiction_wight_1' => 'Ограничение по весу г., От:',
    'option-restiction_wight_2' => 'До:',
    'option-restiction_sum_1' => 'Ограничение по сумме заказа, От:',
    'option-restiction_sum_2' => 'До:',
    'option-restiction_dimension_1' => 'Максимальный размер коробки (ДхШхВ) мм.:',
    'option-restiction_dimension_2' => '',
    'option-restiction_dimension_3' => '',
    'option-restiction_max_size' => 'Максимальный размер в одном из трех измерений мм.:',
    'option-restiction_dimensions_sum' => 'Максимальная сумма трех измерений мм.:',
    'option-to_present_st_dost' => 'Выводить стоимость забора в цене доставки?',


);
$i18n = array_merge($i18n, $companys, $opl);