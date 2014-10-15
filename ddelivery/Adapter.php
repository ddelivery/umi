<?php


use DDelivery\Order\DDeliveryProduct;
use DDelivery\Order\DDStatusProvider;
use DDelivery\DDeliveryUI;


require_once(implode(DIRECTORY_SEPARATOR, array('classes', 'modules', 'ddelivery', 'DDelivery', 'public_html', 'application', 'bootstrap.php')));
require_once('Shop.php');


class Adapter extends \DDelivery\Adapter\PluginFilters
{

    public $regedit;
    public $Shop;
    public $config;
    public $dbdriver = self::DB_MYSQL;

    public function __construct()
    {
        $this->regedit = regedit::getInstance();
        $this->config = mainConfiguration::getInstance();
        $this->cmsOrderStatus = $this->getDDStatuses();
    }

    /**
     * Синхронизация локальных статусов
     * @var array
     */
    protected $cmsOrderStatus = array(DDStatusProvider::ORDER_IN_PROGRESS => 'В обработке',
        DDStatusProvider::ORDER_CONFIRMED => 'Подтверждена',
        DDStatusProvider::ORDER_IN_STOCK => 'На складе ИМ',
        DDStatusProvider::ORDER_IN_WAY => 'Заказ в пути',
        DDStatusProvider::ORDER_DELIVERED => 'Заказ доставлен',
        DDStatusProvider::ORDER_RECEIVED => 'Заказ получен',
        DDStatusProvider::ORDER_RETURN => 'Возврат заказа',
        DDStatusProvider::ORDER_CUSTOMER_RETURNED => 'Клиент вернул заказ',
        DDStatusProvider::ORDER_PARTIAL_REFUND => 'Частичный возврат заказа',
        DDStatusProvider::ORDER_RETURNED_MI => 'Возвращен в ИМ',
        DDStatusProvider::ORDER_WAITING => 'Ожидание',
        DDStatusProvider::ORDER_CANCEL => 'Отмена');

    public function getDDStatuses()
    {
        $this->cmsOrderStatus = array(
            DDStatusProvider::ORDER_IN_PROGRESS => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_PROGRESS),
            DDStatusProvider::ORDER_CONFIRMED => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CONFIRMED),
            DDStatusProvider::ORDER_IN_STOCK => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_STOCK),
            DDStatusProvider::ORDER_IN_WAY => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_IN_WAY),
            DDStatusProvider::ORDER_DELIVERED => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_DELIVERED),
            DDStatusProvider::ORDER_RECEIVED => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RECEIVED),
            DDStatusProvider::ORDER_RETURN => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RETURN),
            DDStatusProvider::ORDER_CUSTOMER_RETURNED => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CUSTOMER_RETURNED),
            DDStatusProvider::ORDER_PARTIAL_REFUND => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_PARTIAL_REFUND),
            DDStatusProvider::ORDER_RETURNED_MI => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_RETURNED_MI),
            DDStatusProvider::ORDER_WAITING => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_WAITING),
            DDStatusProvider::ORDER_CANCEL => $this->regedit->getVal('//modules/ddelivery/dd.order_status_' . DDStatusProvider::ORDER_CANCEL),
        );
        return $this->cmsOrderStatus;
    }

    /**
     * Верните true если нужно использовать тестовый(stage) сервер
     * @return bool
     */
    public function isTestMode()
    {
        return $this->regedit->getVal('//modules/ddelivery/dd.test');
    }

    /**
     * Возвращает товары находящиеся в корзине пользователя, будет вызван один раз, затем закеширован
     * @return DDeliveryProduct[]
     */
    protected function _getProductsFromCart()
    {

        /**
         * Общая подготовка
         */
        $hierarchy = umiHierarchy::getInstance();

        //Соответствие габаритов
        $widthField = $this->regedit->getVal('//modules/ddelivery/dd.widthsootv');
        $heightField = $this->regedit->getVal('//modules/ddelivery/dd.heightsootv');
        $lengthField = $this->regedit->getVal('//modules/ddelivery/dd.lengthsootv');
        $weightField = $this->regedit->getVal('//modules/ddelivery/dd.weightsootv');


        $widthDefault = $this->regedit->getVal('//modules/ddelivery/dd.width_default');
        $heightDefault = $this->regedit->getVal('//modules/ddelivery/dd.height_default');
        $lengthDefault = $this->regedit->getVal('//modules/ddelivery/dd.length_default');
        $weightDefault = $this->regedit->getVal('//modules/ddelivery/dd.weight_default');


        /**
         * Получаем товар
         */
        $items = Shop::getBasketOrder()->getItems();
        $products = array();
        foreach ($items as $item) {
            $id = $item->getId();
            $page = $hierarchy->getObjectInstances('51'); //Получаем page_id элемента
            $path = $hierarchy->getPathById('51');

            //Получим ссылку на страницу объекта каталога
            $link = $item->getValue('item_link');
            if (!empty($link)) {
                $page_id = $link['0']->getId('id');
                //Получим элемент по id страницы
                $element = $hierarchy->getElement($page_id);
                $width = ( $width = $element->getObject()->getPropById( $widthField ) AND is_numeric( $width ) ) ? $width->getValue() : $widthDefault;
                $height = ( $height = $element->getObject()->getPropById( $heightField ) AND is_numeric( $height ) ) ? $height->getValue() : $heightDefault;
                $length = ( $length = $element->getObject()->getPropById( $lengthField ) AND is_numeric( $length ) ) ? $length->getValue() : $lengthDefault;
                $weight = ( $weight = $element->getObject()->getPropById( $weightField ) AND is_numeric( $weight ) ) ? $weight->getValue() : $weightDefault;



            }


            $price = $item->getItemPrice();
            $quantity = $item->getAmount();
            $name = $item->getName();

            $products[] = new DDeliveryProduct($id, $width, $height, $length, $weight, $price, $quantity, $name);
        }

        return $products;
    }

    /**
     * Меняет статус внутреннего заказа cms
     *
     * @param $cmsOrderID - id заказа
     * @param $status - статус заказа для обновления
     *
     * @return bool
     */
    public function setCmsOrderStatus($cmsOrderID, $status)
    {
        //$status - это id CMS Статуса
        $col = umiObjectsCollection::getInstance();
        $Status = $col->getObject($status);
        $Type = $Status->getType();
        $typeId = $Type->getId();
        $guid = $Type->getGUID();

        $order = order::get($cmsOrderID);
        if (isset ($order)) {
            if ($guid == 'emarket-orderdeliverystatus') {
                $order->setDeliveryStatus($status);
            }
            if ($guid == 'emarket-orderstatus') {
                $order->setOrderStatus($status);
            }

            if ($guid == 'emarket-orderpaymentstatus') {
                $order->setPaymentStatus($status);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Возвращает API ключ, вы можете получить его для Вашего приложения в личном кабинете
     * @return string
     */
    public function getApiKey() //Сделано
    {

        return $this->regedit->getVal('//modules/ddelivery/dd.api_key');
    }

    /**
     * Должен вернуть url до каталога с статикой
     * @return string
     */
    public function getStaticPath() //Сделано
    {
        $ini = cmsController::getInstance();
        $domain = $ini->getCurrentDomain()->getHost();
        return 'http://' . $domain . '/assets/html/';
    }

    /**
     * URL до скрипта где вызывается DDelivery::render
     * @return string
     */
    public function getPhpScriptURL()
    {
        $ini = cmsController::getInstance();
        $domain = $ini->getCurrentDomain()->getHost();
        return 'http://' . $domain . '/ddelivery/ajax/';
    }

    /**
     * Возвращает путь до файла базы данных, положите его в место не доступное по прямой ссылке
     * @return string
     */
    public function getPathByDB() //Сделано
    {
        //return 'db.sqlite';
//        return implode(DIRECTORY_SEPARATOR,
//            array('DDelivery', 'public_html', 'db', 'db.sqlite'));

        return implode(DIRECTORY_SEPARATOR,
            array(__DIR__, 'DDelivery', 'public_html', 'db', 'db.sqlite'));
    }

    /**
     * Метод будет вызван когда пользователь закончит выбор способа доставки
     *
     * @param int $orderId
     * @param \DDelivery\Order\DDeliveryOrder $order
     * @param bool $customPoint Если true, то заказ обрабатывается магазином
     * @return void
     */
    public function onFinishChange($orderId, \DDelivery\Order\DDeliveryOrder $order, $customPoint)
    {
        $ini = cmsController::getInstance()->getModule("emarket");
        //Запоминаем заказ в любом случае
        if ($customPoint) {
            //Заказ обрабатывается магазином
            $this->regedit->setVal('//modules/ddelivery/orderID_' . Shop::getBasketOrder()->getId() . '_orderSDkId', $orderId);
        } else {
            $this->regedit->setVal('//modules/ddelivery/orderID_' . Shop::getBasketOrder()->getId() . '_orderSDkId', $orderId);
            // Запомни id заказа
        }
    }

    /**
     * Какой процент от стоимости страхуется
     * @return float
     */
    public function getDeclaredPercent()
    {
        return $this->regedit->getVal('//modules/ddelivery/dd.declared_percent');
    }

    /**
     * Должен вернуть те компании которые НЕ показываются в курьерке
     * см. список компаний в DDeliveryUI::getCompanySubInfo()
     * @return int[]
     */
    public function filterCompanyPointCourier()
    {
        $firms = DDeliveryUI::getCompanySubInfo();
        $ff = array();
        $company = array();
        foreach ($firms as $k => $f) {
            $in_use = $this->regedit->getVal('//modules/ddelivery/ddc.' . $k);
            $type = $this->regedit->getVal('//modules/ddelivery/ddctype.' . $k);
            if ($in_use == 0 or $type == 2) {
                $company[] = $k;
            }
        }
        return $company;
    }

    /**
     * Должен вернуть те компании которые НЕ показываются в самовывозе
     * см. список компаний в DDeliveryUI::getCompanySubInfo()
     * @return int[]
     */
    public function filterCompanyPointSelf()
    {

        $firms = DDeliveryUI::getCompanySubInfo();
        $ff = array();

        $company = array();
        foreach ($firms as $k => $f) {
            $in_use = $this->regedit->getVal('//modules/ddelivery/ddc.' . $k);
            $type = $this->regedit->getVal('//modules/ddelivery/ddctype.' . $k);

            if ($in_use == 0 or $type == 3) {
                $company[] = $k;
            }
        }
        return $company;
    }

    /**
     * Возвращаем способ оплаты константой PluginFilters::PAYMENT_, предоплата или оплата на месте. Курьер
     * @return int
     */
    public function filterPointByPaymentTypeCourier()
    {
        return self::PAYMENT_POST_PAYMENT;
        // выбираем один из 3 вариантов(см документацию или комменты к констатам)
        return self::PAYMENT_POST_PAYMENT;
        return self::PAYMENT_PREPAYMENT;
        return self::PAYMENT_NOT_CARE;
        // TODO: Implement filterPointByPaymentTypeCourier() method.
    }

    /**
     * Возвращаем способ оплаты константой PluginFilters::PAYMENT_, предоплата или оплата на месте. Самовывоз
     * @return int
     */
    public function filterPointByPaymentTypeSelf()
    {
        return self::PAYMENT_POST_PAYMENT;
        // выбираем один из 3 вариантов(см документацию или комменты к констатам)
        return self::PAYMENT_POST_PAYMENT;
        return self::PAYMENT_PREPAYMENT;
        return self::PAYMENT_NOT_CARE;
        // TODO: Implement filterPointByPaymentTypeSelf() method.
    }

    /**
     * Если true, то не учитывает цену забора
     * @return bool
     */
    public function isPayPickup()
    {
        return $this->regedit->getVal('//modules/ddelivery/dd.to_present_st_dost');;
    }

    /**
     * Метод возвращает настройки оплаты фильтра которые должны быть собраны из админки
     *
     * @return array
     */
    public function getIntervalsByPoint()
    {
        $min_1 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_1_min');
        $min_2 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_2_min');
        $min_3 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_3_min');

        $max_1 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_1_max');
        $max_2 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_2_max');
        $max_3 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_3_max');

        $type_1 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_1_type');
        $type_2 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_2_type');
        $type_3 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_3_type');

        $amount_1 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_1_amount');
        $amount_2 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_2_amount');
        $amount_3 = $this->regedit->getVal('//modules/ddelivery/dd.price_if_3_amount');


        return array(
            array('min' => $min_1, 'max' => $max_1, 'type' => $type_1, 'amount' => $amount_1),
            array('min' => $min_2, 'max' => $max_2, 'type' => $type_2, 'amount' => $amount_2),
            array('min' => $min_3, 'max' => $max_3, 'type' => $type_3, 'amount' => $amount_3),
            array('min' => $min_1, 'max' => $max_1, 'type' => $type_1, 'amount' => $amount_1),

        );
    }

    /**
     * Тип округления
     * @return int
     */
    public function aroundPriceType()
    {
        return $this->regedit->getVal('//modules/ddelivery/dd.around');
    }

    /**
     * Шаг округления
     * @return float
     */
    public function aroundPriceStep()
    {
        return $this->regedit->getVal('//modules/ddelivery/dd.around_step');
    }

    /**
     * описание собственных служб доставки
     * @return string
     */
    public function getCustomPointsString()
    {
        return '';
    }

    /**
     * Если вы знаете имя покупателя, сделайте чтобы оно вернулось в этом методе
     * @return string|null
     */
    public function getClientFirstName()
    {
        $field = 'lname';
        $customer = customer::get();
        $customerId = $customer->getId();

        if (isset($_SESSION['ddeliverys']['CustomerId'][$customerId][$field]) AND $_SESSION['ddeliverys']['CustomerId'][$customerId][$field] != false) {
            return $_SESSION['ddeliverys']['CustomerId'][$customerId][$field];
        } else {
            return Shop::getCustomer()->getValue($field);
        }

    }

    /**
     * Если вы знаете фамилию покупателя, сделайте чтобы оно вернулось в этом методе
     * @return string|null
     */
    public function getClientLastName()
    {
        $field = 'fname';
        $customer = customer::get();
        $customerId = $customer->getId();

        if (isset($_SESSION['ddeliverys']['CustomerId'][$customerId][$field]) AND $_SESSION['ddeliverys']['CustomerId'][$customerId][$field] != false) {
            return $_SESSION['ddeliverys']['CustomerId'][$customerId][$field];
        } else {
            return Shop::getCustomer()->getValue($field);
        }
    }

    /**
     * Если вы знаете телефон покупателя, сделайте чтобы оно вернулось в этом методе. 11 символов, например 79211234567
     * @return string|null
     */
    public function getClientPhone()
    {
        $field = 'phone';
        $customer = customer::get();
        $customerId = $customer->getId();

        if (isset($_SESSION['ddeliverys']['CustomerId'][$customerId][$field]) AND $_SESSION['ddeliverys']['CustomerId'][$customerId][$field] != false) {
            return $_SESSION['ddeliverys']['CustomerId'][$customerId][$field];
        } else {
            return Shop::getCustomer()->getValue($field);
        }
    }

    /**
     * Верни массив Адрес, Дом, Корпус, Квартира. Если не можешь можно вернуть все в одном поле и настроить через get*RequiredFields
     * @return string[]
     */
    public function getClientAddress()
    {

        $address = array();
        /**
         * Если существует это значение, то это форма в 1 клик иначе это мастер форм
         */
        if (isset($_SESSION['ddeliverys']['delivery-address'])) {
            $address_id = $_SESSION['ddeliverys']['delivery-address'];
            /**
             * Если значений new то просто переносим новые значения в поля SDK иначе у нас есть id существующего адреса
             */
            if ($address_id == 'new') {

                $address[] = (isset($_SESSION['ddeliverys']['CustomerId']['new']['street'])) ? $_SESSION['ddeliverys']['CustomerId']['new']['street'] : '';
                $address[] = (isset($_SESSION['ddeliverys']['CustomerId']['new']['house'])) ? $_SESSION['ddeliverys']['CustomerId']['new']['house'] : '';
                $address[] = '';
                $address[] = (isset($_SESSION['ddeliverys']['CustomerId']['new']['flat'])) ? $_SESSION['ddeliverys']['CustomerId']['new']['flat'] : '';
                $address[] = (isset($_SESSION['ddeliverys']['CustomerId']['new']['order_comments'])) ? $_SESSION['ddeliverys']['CustomerId']['new']['order_comments'] : '';
                return $address;
            }

        } else {
            $order = Shop::getBasketOrder(false);
            $address_id = $order->getValue('delivery_address');

            /**
             * Если адреса не существует то возвращаем родительский метод
             */
            if (is_null($address_id)) {
                return array();
            }

        }
        $Addr = umiObjectsCollection::getInstance()->getObject($address_id);

        //$address['index'] = $Addr->getPropByName('index')->getValue();
        // $address['city'] = $Addr->getPropByName('city')->getValue();
        $address[] = $Addr->getPropByName('street')->getValue();
        $address[] = $Addr->getPropByName('house')->getValue();
        $address[] = '';
        $address[] = $Addr->getPropByName('flat')->getValue();
        $address[] = $Addr->getPropByName('order_comments')->getValue();

        return $address;


    }

    /**
     * Верните id города в системе DDelivery
     * @return int
     */
    public function getClientCityId()
    {

        /**
         * Если существует это значение в сессии, то просто возвращаем его
         */
        if (isset($_SESSION['ddeliverys']["bid"])) {
            return $_SESSION['ddeliverys']["bid"];
        }


        /**
         * Если существует это значение, то это форма в 1 клик
         */
        if (isset($_SESSION['ddeliverys']['delivery-address'])) {
            $address_id = $_SESSION['ddeliverys']['delivery-address'];
            /**
             * Если значений new то просто переносим новые значения в поля SDK иначе у нас есть id существующего адреса
             */
            if ($address_id == 'new') {
                $cityName = (isset($_SESSION['ddeliverys']['CustomerId']['new']['city'])) ? $_SESSION['ddeliverys']['CustomerId']['new']['city'] : '';
                $cityRegion = (isset($_SESSION['ddeliverys']['CustomerId']['new']['region'])) ? $_SESSION['ddeliverys']['CustomerId']['new']['region'] : '';
            } else {
                $address = umiObjectsCollection::getInstance()->getObject($address_id);
                $cityName = $address->getValue('city');
                $cityRegion = $address->getValue('Region');
            }
        } /**
         *  Это мастер форм
         */
        else {
            $order = Shop::getBasketOrder(false);
            $address_id = $order->getValue('delivery_address');

            /**
             * Если адреса не существует то возвращаем родительский метод
             */
            if (is_null($address_id)) {
                return parent::getClientCityId();
            }

            $Addr = umiObjectsCollection::getInstance()->getObject($address_id);
            //$address['index'] = $Addr->getPropByName('index')->getValue();
            $cityName = $Addr->getPropByName('city')->getValue();
            $cityRegion = $Addr->getPropByName('region')->getValue();
        }


        $deliveryUI = new DDeliveryUI($this, true);
        $prefix = $this->getDbConfig();
        $prefix = $prefix['prefix'];

        //Сначала ищем по городу и региону
        $query = "SELECT * FROM " . $prefix . "ps_dd_cities WHERE name = :name AND region = :region";
        $pdo = Shop::getPdo(new Adapter);
        $sth = $pdo->prepare($query);
        $sth->bindParam(':name', $cityName);
        $sth->bindParam(':region', $cityRegion);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_ASSOC);

        //Если не найдено по городу и региону, то ищем просто по городу
        if (!$result) {
            $query = "SELECT * FROM " . $prefix . "ps_dd_cities WHERE name = :name";
            $sth = $pdo->prepare($query);
            $sth->bindParam(':name', $cityName);
            $sth->execute();
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return parent::getClientCityId();
            }
        }

        //Если не найдено возвращаем родительский метод
        if (isset($result['_id'])) {
            return $result['_id'];
        } else {
            return parent::getClientCityId();
        }

    }

    /**
     * Возвращает true если статус $cmsStatus равен
     * статусу в настройках
     *
     * @param $cmsStatus mixed
     * @return bool
     */
    public function isStatusToSendOrder($cmsStatus)
    {
        if ($cmsStatus == $this->regedit->getVal('//modules/ddelivery/dd.send_status')) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Конфигурация соединения с бд
     * @return array
     */
    public function getDbConfig()
    {
        if ($this->dbdriver === self::DB_SQLITE) {
            return array(
                'type' => self::DB_SQLITE,
                'dbPath' => $this->getPathByDB(),
                'prefix' => '',
            );
        }

        if ($this->dbdriver === self::DB_MYSQL) {
            return array(
                'type' => self::DB_MYSQL, //self::DB_SQLITE, //self::DB_MYSQL,
                'dsn' => 'mysql:host=' . $this->config->get('connections', 'core.host') . ';dbname=' . $this->config->get('connections', 'core.dbname'), //'mysql:host=localhost;dbname=ddelivery',
                'user' => $this->config->get('connections', 'core.login'), //root',
                'pass' => $this->config->get('connections', 'core.password'),
                'prefix' => 'ddelivery_',
            );
        }
    }

    public function getCourierRequiredFields()
    {
        // ВВести все обязательно, кроме корпуса
        return self::FIELD_EDIT_FIRST_NAME | self::FIELD_REQUIRED_FIRST_NAME | self::FIELD_EDIT_SECOND_NAME | self::FIELD_REQUIRED_SECOND_NAME
        | self::FIELD_EDIT_PHONE | self::FIELD_REQUIRED_PHONE
        | self::FIELD_EDIT_ADDRESS | self::FIELD_REQUIRED_ADDRESS
        | self::FIELD_EDIT_ADDRESS_HOUSE | self::FIELD_REQUIRED_ADDRESS_HOUSE
        | self::FIELD_EDIT_ADDRESS_HOUSING
        | self::FIELD_EDIT_ADDRESS_FLAT;

    }

    /**
     * Возвращает бинарную маску обязательных полей для пунктов самовывоза
     * Если редактирование не включено, но есть обязательность то поле появится
     * Если редактируемых полей не будет то пропустим шаг
     * @return int
     */
    public function getSelfRequiredFields()
    {
        // Имя, фамилия, мобилка
        return self::FIELD_EDIT_FIRST_NAME | self::FIELD_REQUIRED_FIRST_NAME
        | self::FIELD_EDIT_SECOND_NAME | self::FIELD_REQUIRED_SECOND_NAME
        | self::FIELD_EDIT_PHONE | self::FIELD_REQUIRED_PHONE;
    }


}
