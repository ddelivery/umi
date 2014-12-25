<?php
use \DDelivery\DDeliveryUI;
use DDelivery\Order\DDStatusProvider;

require_once(implode(DIRECTORY_SEPARATOR, array('DDelivery', 'public_html', 'application', 'bootstrap.php')));

class __ddelivery_adm extends baseModuleAdmin
{

    /**
     * Инициализация вкладок в админке
     */
    public function onInit()
    {
        $commonTabs = $this->getCommonTabs();
        if ($commonTabs) {
            $commonTabs->add('DdBaseConfig');
            $commonTabs->add('DdMain');
            $commonTabs->add('DdSootv');
            $commonTabs->add('DdSposoby');
            $commonTabs->add('DdOplata');
        }
    }

    /**
     * Настройки базовой конфигурация
     */
    public function DdBaseConfig()
    {
        $regedit = regedit::getInstance();
//системы доставки
        $params['base'] = Array(

            'boolean:is_active' => null,
            'int:sort' => null, //100,
            'string:title' => null, //'DDelivery',
            'string:description' => null, // 'Доставка через DDelivery мега крутая штука.',
            'string:tax_rate' => null, //0,
           // 'string:photo' => null,
        );

        $mode = (string)getRequest('param0');
        if ($mode == "do") {
            $params = $this->expectParams($params);

            $regedit->setVal('//modules/ddelivery/dd.is_active', $params['base']['boolean:is_active']);
            $regedit->setVal('//modules/ddelivery/dd.sort', $params['base']['int:sort']);
            $regedit->setVal('//modules/ddelivery/dd.title', $params['base']['string:title']);
            $regedit->setVal('//modules/ddelivery/dd.description', $params['base']['string:description']);
            $regedit->setVal('//modules/ddelivery/dd.tax_rate', $params['base']['string:tax_rate']);
           // $regedit->setVal('//modules/ddelivery/dd.photo', $params['base']['string:photo']);

            $this->chooseRedirect();
        }

        //Получение базовых настроек
        $params['base']['boolean:is_active'] = $regedit->getVal('//modules/ddelivery/dd.is_active');
        $params['base']['int:sort'] = $regedit->getVal('//modules/ddelivery/dd.sort');
        $params['base']['string:title'] = $regedit->getVal('//modules/ddelivery/dd.title');
        $params['base']['string:description'] = $regedit->getVal('//modules/ddelivery/dd.description');
        $params['base']['string:tax_rate'] = $regedit->getVal('//modules/ddelivery/dd.tax_rate'); //$config->get('ddelivery', 'dd.tax_rate');
       // $params['base']['string:photo'] = $regedit->getVal('//modules/ddelivery/dd.photo'); //$config->get('ddelivery', 'dd.tax_rate');


        $this->setDataType('settings');
        $this->setActionType('modify');
        $data = $this->prepareData($params, 'settings');
        $this->setData($data);
        return $this->doData();
    }

    /**
     * Настройки основных параметров
     */
    public function DdMain()
    {
        $regedit = regedit::getInstance();

        $sel = new selector('objects');
        $sel->types('object-type')->name('emarket', 'payment');

        $coll = umiObjectTypesCollection::getInstance();
        foreach ($sel->result() as $obj) {
            $typeId = $obj->getTypeId();
            $payments[$obj->getId()] = $coll->getType($typeId)->getName() . ' : ' . $obj->getName();
        }


        $params['main'] = Array(
            'string:api_key' => null,
            'select:test' => array('1' => 'тестирование (stage)', '0' => 'Боевое (client)'),
            'string:declared_percent' => null,
            'select:payment' => $payments
        );


        $mode = (string)getRequest('param0');
        if ($mode == "do") {
            $params = $this->expectParams($params);

            $regedit->setVal('//modules/ddelivery/dd.api_key', $params['main']['string:api_key']);
            $regedit->setVal('//modules/ddelivery/dd.test', $params['main']['select:test']);
            $regedit->setVal('//modules/ddelivery/dd.declared_percent', $params['main']['string:declared_percent']);
            $regedit->setVal('//modules/ddelivery/dd.payment', $params['main']['select:payment']);

            $this->chooseRedirect();
        }

        $params['main']['string:api_key'] = $regedit->getVal('//modules/ddelivery/dd.api_key');
        $params['main']['select:test']['value'] = $regedit->getVal('//modules/ddelivery/dd.test');
        $params['main']['string:declared_percent'] = $regedit->getVal('//modules/ddelivery/dd.declared_percent');
        $params['main']['select:payment']['value'] = $regedit->getVal('//modules/ddelivery/dd.payment');


        $this->setDataType('settings');
        $this->setActionType('modify');
        $data = $this->prepareData($params, 'settings');
        $this->setData($data);
        return $this->doData();
    }

    /**
     * Настройки соответствия полей
     */
    public function DdSootv()
    {

        $sel = new selector('objects');
        $sel->types('object-type')->name('emarket', 'order_status');

        $coll = umiObjectTypesCollection::getInstance();
        foreach ($sel->result() as $obj) {
            $typeId = $obj->getTypeId();
            $statuses[$obj->getId()] = $coll->getType($typeId)->getName() . ' : ' . $obj->getName();
        }

        $regedit = regedit::getInstance();

        //Соответствия ордеров
        $params['sootv_dost'] = Array(
            'select:order_status_' . DDStatusProvider::ORDER_IN_PROGRESS => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_CONFIRMED => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_IN_STOCK => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_IN_WAY => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_DELIVERED => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_RECEIVED => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_RETURN => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_CUSTOMER_RETURNED => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_PARTIAL_REFUND => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_RETURNED_MI => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_WAITING => $statuses,
            'select:order_status_' . DDStatusProvider::ORDER_CANCEL => $statuses,
        );

        //Базовые соответствия
        $params['sootv_base'] = Array(
            'select:send_status' => $statuses,
        );

        //Соответствие полей
//        $sel = new selector('objects');
//        $sel-types('object-type')->name('catalog', 'object');
//        var_dump($sel->result());

        $objectTypes = umiObjectTypesCollection::getInstance();
        $objectTypeId = $objectTypes->getBaseType('catalog', 'object');
        $subTypesIds = $objectTypes->getSubTypesList($objectTypeId);

        $par = array();
        foreach ($subTypesIds as $id) {
            $type = $objectTypes->getType($id);
            $groupList = $type->getFieldsGroupsList();
            foreach ($groupList as $group) {
                $groupName = $group->getTitle();
                $fields = $group->getFields();

                foreach ($fields as $field) {
                    $par[$field->getId()] = $groupName . ': ' . $field->getTitle();
                }
            }
        }

        $params['sootv_gabarity'] = Array(
            'select:swidth' => $par,
            'select:sheight' => $par,
            'select:slength' => $par,
            'select:sweight' => $par,
        );

        $params['sootv_gabarity_default'] = Array(
            'string:dwidth' => 10,
            'string:dheight' => 10,
            'string:dlength' => 10,
            'string:dweight' => 10,
        );


        $mode = (string)getRequest('param0');
        if ($mode == "do") {
            $params = $this->expectParams($params);

            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_PROGRESS,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_IN_PROGRESS]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CONFIRMED,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_CONFIRMED]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_STOCK,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_IN_STOCK]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_WAY,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_IN_WAY]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_DELIVERED,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_DELIVERED]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RECEIVED,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_RECEIVED]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RETURN,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_RETURN]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CUSTOMER_RETURNED,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_CUSTOMER_RETURNED]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_PARTIAL_REFUND,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_PARTIAL_REFUND]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RETURNED_MI,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_RETURNED_MI]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_WAITING,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_WAITING]);
            $regedit->setVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CANCEL,
                $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_CANCEL]);

            //Базовые соответствия
            $regedit->setVal('//modules/ddelivery/dd.send_status', $params['sootv_base']['select:send_status']);

            //Соответствие габаритов
            $regedit->setVal('//modules/ddelivery/dd.widthsootv', $params['sootv_gabarity']['select:swidth']);
            $regedit->setVal('//modules/ddelivery/dd.heightsootv', $params['sootv_gabarity']['select:sheight']);
            $regedit->setVal('//modules/ddelivery/dd.lengthsootv', $params['sootv_gabarity']['select:slength']);
            $regedit->setVal('//modules/ddelivery/dd.weightsootv', $params['sootv_gabarity']['select:sweight']);

            //Габариты по умолчанию
            $regedit->setVal('//modules/ddelivery/dd.width_default', $params['sootv_gabarity_default']['string:dwidth']);
            $regedit->setVal('//modules/ddelivery/dd.height_default', $params['sootv_gabarity_default']['string:dheight']);
            $regedit->setVal('//modules/ddelivery/dd.length_default', $params['sootv_gabarity_default']['string:dlength']);
            $regedit->setVal('//modules/ddelivery/dd.weight_default', $params['sootv_gabarity_default']['string:dweight']);
            $this->chooseRedirect();
        }

        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_IN_PROGRESS]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_PROGRESS);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_CONFIRMED]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CONFIRMED);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_IN_STOCK]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_STOCK);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_IN_WAY]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_WAY);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_DELIVERED]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_DELIVERED);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_RECEIVED]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RECEIVED);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_RETURN]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RETURN);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_CUSTOMER_RETURNED]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CUSTOMER_RETURNED);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_PARTIAL_REFUND]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_PARTIAL_REFUND);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_RETURNED_MI]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RETURNED_MI);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_WAITING]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_WAITING);
        $params['sootv_dost']['select:order_status_' . DDStatusProvider::ORDER_CANCEL]['value']
            = $regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CANCEL);

        //Базовые соответствия
        $params['sootv_base']['select:send_status']['value'] = $regedit->getVal('//modules/ddelivery/dd.send_status');

        //Соответствие габаритов
        $params['sootv_gabarity']['select:swidth']['value'] = $regedit->getVal('//modules/ddelivery/dd.widthsootv');
        $params['sootv_gabarity']['select:sheight']['value'] = $regedit->getVal('//modules/ddelivery/dd.heightsootv');
        $params['sootv_gabarity']['select:slength']['value'] = $regedit->getVal('//modules/ddelivery/dd.lengthsootv');
        $params['sootv_gabarity']['select:sweight']['value'] = $regedit->getVal('//modules/ddelivery/dd.weightsootv');

        //Габариты по умолчанию
        $params['sootv_gabarity_default']['string:dwidth'] = $regedit->getVal('//modules/ddelivery/dd.width_default');
        $params['sootv_gabarity_default']['string:dheight'] = $regedit->getVal('//modules/ddelivery/dd.height_default');
        $params['sootv_gabarity_default']['string:dlength'] = $regedit->getVal('//modules/ddelivery/dd.length_default');
        $params['sootv_gabarity_default']['string:dweight'] = $regedit->getVal('//modules/ddelivery/dd.weight_default');


        $this->setDataType('settings');
        $this->setActionType('modify');
        $data = $this->prepareData($params, 'settings');
        $this->setData($data);
        return $this->doData();
    }

    /**
     * Вкладка настроек способов доставки
     */
    public function DdSposoby()
    {
        $regedit = regedit::getInstance();
        $firms = DDeliveryUI::getCompanySubInfo();

        //ddelivery-sposoby
        foreach ($firms as $k => $f) {
            $ff['bool:com_' . $k] = 1;
            $ff['select:supported_type_' . $k] = array(

                '1' => 'ПВЗ и курьеры',
                '2' => 'Самовывоз из ПВЗ DDelivery',
                '3' => 'Курьеры DDelivery'
            );
        }

        $params['sposoby'] = $ff;

        $mode = (string)getRequest('param0');
        if ($mode == "do") {
            $params = $this->expectParams($params);
            foreach ($firms as $k => $f) {
                $regedit->setVal('//modules/ddelivery/ddc.' . $k, $params['sposoby']['bool:com_' . $k]);
                $regedit->setVal('//modules/ddelivery/ddctype.' . $k, $params['sposoby']['select:supported_type_' . $k]);

            }

            $this->chooseRedirect();
        }

        foreach ($firms as $k => $f) {
            $params['sposoby']['bool:com_' . $k] = $regedit->getVal('//modules/ddelivery/ddc.' . $k);
            $params['sposoby']['select:supported_type_' . $k]['value'] = $regedit->getVal('//modules/ddelivery/ddctype.' . $k);
        }


        $this->setDataType('settings');
        $this->setActionType('modify');
        $data = $this->prepareData($params, 'settings');
        $this->setData($data);
        return $this->doData();
    }

    /**
     * Вкладка настроек оплаты доставки
     */
    public function DdOplata()
    {
        $regedit = regedit::getInstance();

        $params['oplata'] = array(

            'string:price_if_1_min' => null,
            'string:price_if_1_max' => null,
            'select:price_if_1_type' => array(

                '1' => 'Клиент оплачивает все',
                '2' => 'Магазин оплачивает все',
                '3' => 'Магазин оплачивает % от стоимости доставки',
                '4' => 'Магазин оплачивает руб. от стоимости доставки'

            ),
            'string:price_if_1_amount' => null,


            'string:price_if_2_min' => null,
            'string:price_if_2_max' => null,
            'select:price_if_2_type' => array(

                '1' => 'Клиент оплачивает все',
                '2' => 'Магазин оплачивает все',
                '3' => 'Магазин оплачивает % от стоимости доставки',
                '4' => 'Магазин оплачивает руб. от стоимости доставки'

            ),
            'string:price_if_2_amount' => null,

            'string:price_if_3_min' => null,
            'string:price_if_3_max' => null,
            'select:price_if_3_type' => array(

                '1' => 'Клиент оплачивает все',
                '2' => 'Магазин оплачивает все',
                '3' => 'Магазин оплачивает % от стоимости доставки',
                '4' => 'Магазин оплачивает руб. от стоимости доставки'

            ),
            'string:price_if_3_amount' => null,

            'select:around' => array(

                '1' => 'Математическое',
                '2' => 'Вниз',
                '3' => 'Вверх'
            ),
            'string:around_step' => null,
            'boolean:to_present_st_dost' => null,
        );


        $mode = (string)getRequest('param0');
        if ($mode == "do") {
            $params = $this->expectParams($params);


            $regedit->setVal('//modules/ddelivery/dd.price_if_1_min', $params['oplata']['string:price_if_1_min']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_1_max', $params['oplata']['string:price_if_1_max']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_1_type', $params['oplata']['select:price_if_1_type']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_1_amount', $params['oplata']['string:price_if_1_amount']);

            $regedit->setVal('//modules/ddelivery/dd.price_if_2_min', $params['oplata']['string:price_if_2_min']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_2_max', $params['oplata']['string:price_if_2_max']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_2_type', $params['oplata']['select:price_if_2_type']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_2_amount', $params['oplata']['string:price_if_2_amount']);

            $regedit->setVal('//modules/ddelivery/dd.price_if_3_min', $params['oplata']['string:price_if_3_min']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_3_max', $params['oplata']['string:price_if_3_max']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_3_type', $params['oplata']['select:price_if_3_type']);
            $regedit->setVal('//modules/ddelivery/dd.price_if_3_amount', $params['oplata']['string:price_if_3_amount']);

            $regedit->setVal('//modules/ddelivery/dd.around', $params['oplata']['select:around']);
            $regedit->setVal('//modules/ddelivery/dd.around_step', $params['oplata']['string:around_step']);
            $regedit->setVal('//modules/ddelivery/dd.to_present_st_dost', $params['oplata']['boolean:to_present_st_dost']);

            $this->chooseRedirect();
        }
        $params['oplata']['string:price_if_1_min'] = $regedit->getVal('//modules/ddelivery/dd.price_if_1_min');
        $params['oplata']['string:price_if_1_max'] = $regedit->getVal('//modules/ddelivery/dd.price_if_1_max');
        $params['oplata']['select:price_if_1_type']['value'] = $regedit->getVal('//modules/ddelivery/dd.price_if_1_type');
        $params['oplata']['string:price_if_1_amount'] = $regedit->getVal('//modules/ddelivery/dd.price_if_1_amount');

        $params['oplata']['string:price_if_2_min'] = $regedit->getVal('//modules/ddelivery/dd.price_if_2_min');
        $params['oplata']['string:price_if_2_max'] = $regedit->getVal('//modules/ddelivery/dd.price_if_2_max');
        $params['oplata']['select:price_if_2_type']['value'] = $regedit->getVal('//modules/ddelivery/dd.price_if_2_type');
        $params['oplata']['string:price_if_2_amount'] = $regedit->getVal('//modules/ddelivery/dd.price_if_2_amount');

        $params['oplata']['string:price_if_3_min'] = $regedit->getVal('//modules/ddelivery/dd.price_if_3_min');
        $params['oplata']['string:price_if_3_max'] = $regedit->getVal('//modules/ddelivery/dd.price_if_3_max');
        $params['oplata']['select:price_if_3_type']['value'] = $regedit->getVal('//modules/ddelivery/dd.price_if_3_type');
        $params['oplata']['string:price_if_3_amount'] = $regedit->getVal('//modules/ddelivery/dd.price_if_3_amount');

        $params['oplata']['select:around']['value'] = $regedit->getVal('//modules/ddelivery/dd.around');
        $params['oplata']['string:around_step'] = $regedit->getVal('//modules/ddelivery/dd.around_step');

        $params['oplata']['boolean:to_present_st_dost'] = $regedit->getVal('//modules/ddelivery/dd.to_present_st_dost');


        $this->setDataType('settings');
        $this->setActionType('modify');
        $data = $this->prepareData($params, 'settings');
        $this->setData($data);
        return $this->doData();
    }

}