<?php
//use Adapter;
use DDelivery\DDeliveryUI;

//require_once(implode(DIRECTORY_SEPARATOR, array('DDelivery', 'public_html', 'application', 'bootstrap.php')));
require_once( 'Adapter.php' );

abstract class __ddelivery_custom
{
    /**
     * Метод перенаправления на страницу настройки DDelivery
     */
    public function choosecust()
    {
        $ini = cmsController::getInstance()->getModule( "emarket" );
        $order = $ini->getBasketOrder( false );
        $deliveryId = (int)getRequest( 'delivery-id' );

        //Если не передан id доставки перенаправление на страницу выбора доставки
        if (!$deliveryId) {
            $this->redirect( $ini->pre_lang . '/emarket/purchase/delivery/choose/' );
        }


        if (@isset( $_SESSION['emarket']['delivery'][$deliveryId] )) {
            $deliveryPrice = (float)$_SESSION['emarket']['delivery'][$deliveryId];
        } else {
            $delivery = delivery::get( $deliveryId );
            $deliveryPrice = (float)$delivery->getDeliveryPrice( $order );
        }

        $order->setValue( 'delivery_id', $deliveryId );
        $order->setValue( 'delivery_price', $deliveryPrice );
        $order->refresh();
        $order->commit();

        $regedit = regedit::getInstance();

        //Получим id типа доставки
        $delivery_type = umiObjectsCollection::getInstance()->getObject( $deliveryId )->getType()->getId();

        //Если полученный id является id доставки DD то перенаправляем на страницу настроек
        if ($delivery_type == $regedit->getVal( '//modules/ddelivery/dd.delivery_type_id' )) { //915
            $this->redirect( $this->pre_lang . '/ddelivery/ddelivery_start' );
        } else {
            $this->redirect( $this->pre_lang . '/emarket/purchase/payment/choose/' );
        }
    }

    /**
     * Метод страницы настроек (может быть с пустым телом - не удалять)
     */
    public function ddelivery_start()
    {
    }

    /**
     * Метод через который ведется взаимодействие с SDK
     */
    public function ajax()
    {
        try {
            $IntegratorShop = new Adapter();
            $ddeliveryUI = new DDeliveryUI( $IntegratorShop );
            echo $ddeliveryUI->render( isset( $_REQUEST ) ? $_REQUEST : array() );
        } catch (Exception $e) {
            echo $e;
        }
        exit;
    }


    /**
     * Метод для синхронизации статусов с сервером DDelivery
     * Запус по крону
     */
    public function getStatusesFromDD()
    {
        try {
            $IntegratorShop = new Adapter();
            $ddeliveryUI = new \DDelivery\DDeliveryUI( $IntegratorShop, true );
            $ddeliveryUI->getPullOrdersStatus();
        } catch (\DDelivery\DDeliveryException $e) {
            echo $e->getMessage();

        }
        exit;
    }

    /**
     * Метод для установки промежуточных данных
     */
    public function setAddressFromForm()
    {
        //Обнуляем предшествующие данные

        if (isset( $_SESSION['ddeliverys'] )) {
            unset( $_SESSION['ddeliverys'] );
        }

        $_SESSION['ddeliverys']["CustomerId"] = getRequest( 'data' );;
        $_SESSION['ddeliverys']["delivery-address"] = getRequest( 'delivery-address' );;
        $_SESSION['ddeliverys']["delivery-id"] = getRequest( 'delivery-id' );
        $_SESSION['ddeliverys']["payment-id"] = getRequest( 'payment-id' );
        $_SESSION['ddeliverys']["bid"] = getRequest( 'bid' );
    }


    /**
     * Обработка заказа SDK при завершении оформления внутри CMS
     * @param iUmiEventPoint $event
     */
    public function onCmsOrderFinish(iUmiEventPoint $event)
    {
        if ($event->getMode() == "after" && $event->getParam( "old-status-id" ) != $event->getParam( "new-status-id" )) {
            $CmsOrder = $event->getRef( "order" );
            $CmsOrderId = $CmsOrder->getId();

            $sdkOrderId = regedit::getInstance()->getVal( '//modules/ddelivery/orderID_' . $CmsOrderId . '_orderSDkId' );

            try {
                $IntegratorShop = new Adapter();
                $ddeliveryUI = new DdeliveryUI( $IntegratorShop, true );

                $status = $event->getParam( "new-status-id" );
                $payment = $CmsOrder->getPaymentStatus();
                //Отправление данных на сервер
                $ddeliveryUI->onCmsOrderFinish( $sdkOrderId, $CmsOrderId, $status, $payment );

            } catch (\DDelivery\DDeliveryException $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Обработка изменения статуса заказа при быстром редактировании в админке (список заказаов)
     */
    public function onModifyStatusValue(iUmiEventPoint $event)
    {
        if ($event->getMode() != 'after') return;

        $propName = $event->getParam( 'property' );
        $entity = $event->getRef( 'entity' );

        if ($entity instanceof iUmiObject && $propName == 'status_id') {

            try {
                $IntegratorShop = new Adapter();
                $ddeliveryUI = new \DDelivery\DDeliveryUI( $IntegratorShop, true );

                //Проверка необходимости отправления данных  на сервер  в зависимости от нового статуса
                if ($IntegratorShop->isStatusToSendOrder( $event->getRef( 'newValue' ) )) {

                    $CMSOrder = order::get( $entity->id );
                    $CMSOrder_id = $CMSOrder->getId();
                    $sdkOrderId = regedit::getInstance()->getVal( '//modules/ddelivery/orderID_' . $CMSOrder_id . '_orderSDkId' );

                    $order = $ddeliveryUI->initOrder( array( $sdkOrderId ) );
                    if (empty( $order ))
                        return null;
                    $order = reset( $order );

                    $payment = $CMSOrder->getValue( 'payment_id' );

                    //Если это нужный статус, то шлем на сервер
                    $ddeliveryUI->sendOrderToDD( $order, $CMSOrder_id, $payment );
                }
            } catch (\DDelivery\DDeliveryException $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Обработка изменения статуса заказа при редактировании в подробной форме заказа
     * @param iUmiEventPoint $event
     * @return null
     */
    public function onModifyStatusObject(iUmiEventPoint $event)
    {

        if ($event->getMode() == "after") {

            // static $modifiedCache = array();
            // static $orderStatus = array();
            $object = $event->getRef( "object" );
            // $typeId = umiObjectTypesCollection::getInstance()->getBaseType('emarket', 'order');

            if ($object->getTypeGUID() == 'emarket-order') {

                try {
                    $IntegratorShop = new Adapter();
                    $ddeliveryUI = new \DDelivery\DDeliveryUI( $IntegratorShop, true );

                    //Проверка - надо ли отправлять данные  на сервер  взависимости от нового статуса
                    //isStatusToSendOrder
                    //

                    $CMSOrder = order::get( $object->getId() );
                    if ($IntegratorShop->isStatusToSendOrder( $CMSOrder->getOrderStatus() )) {
                        $CMSOrder_id = $CMSOrder->getId();
                        $sdkOrderId = regedit::getInstance()->getVal( '//modules/ddelivery/orderID_' . $CMSOrder_id . '_orderSDkId' );

                        $order = $ddeliveryUI->initOrder( array( $sdkOrderId ) );
                        if (empty( $order ))
                            return null;
                        $order = reset( $order );

                        $payment = $CMSOrder->getValue( 'payment_id' );

                        //Если это нужный статус, то шлем на сервер
                        $ddeliveryUI->sendOrderToDD( $order, $CMSOrder_id, $payment );
                    }


                } catch (\DDelivery\DDeliveryException $e) {
                    echo $e->getMessage();
                }
            } else {
                return;
            }

        }


    }


    public function cities()
    {
        /**
         * Настройки соединения
         */
        $adapter = new Adapter();
        $prefix = $adapter->getDbConfig();
        $prefix = $prefix['prefix'];

        /**
         * Поиск по городам и регионам
         */
        $citiName = '%' . $_GET['term'] . '%';
        if ($_GET['region'] == null OR $_GET['region'] == 'undefined') {
            $regionQuery = '';
            $regionName = '';
        } else {
            $regionQuery = 'AND region LIKE :region';
            $regionName = '%' . $_GET['region'] . '%';
        };


        $citiName = '%' . $_GET['term'] . '%';

        $pdo = Shop::getPdo( $adapter );

        /**
         * Сортировка по типам
         */
        $qtype = "SELECT DISTINCT type FROM {$prefix}ps_dd_cities";
        $sth = $pdo->prepare( $qtype );


        $sth->execute();
        $types = $sth->fetchAll( PDO::FETCH_COLUMN );

        $types = array_unique( array_merge( array( 'г', 'город', 'пгт', 'п', 'с', 'д' ), $types ) );

        $typeval = implode( '","', $types );


        $query = "SELECT type, name, area, region, _id FROM "
            . $prefix . "ps_dd_cities WHERE  name LIKE :name $regionQuery ORDER BY FIELD(type," . '"' . $typeval . '"' . ") LIMIT 0,100";
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':name', $citiName );
        if ($regionName != '') {
            $sth->bindParam( ':region', $regionName );
        }

        $sth->execute();

        $result = $sth->fetchAll( PDO::FETCH_ASSOC );

        if (!empty( $result )) {
            foreach ($result as $k => $arr) {
                $element[$k]['value'] = "{$arr['type']}. {$arr['name']}, обл. {$arr['region']}, {$arr['area']} р-н";
                $element[$k]['label'] = "{$arr['type']}. {$arr['name']}, обл. {$arr['region']}, {$arr['area']} р-н";
                $element[$k]['id'] = $arr['_id'];
            }
        }


        //$s = "[".implode(",", $elements)."]";
        echo json_encode( $element );
        //echo $s;
        exit;
    }

    public function regions()
    {

        $adapter = new Adapter();
        $prefix = $adapter->getDbConfig();
        $prefix = $prefix['prefix'];

        $regionName = '%' . $_GET['term'] . '%';
        $query = "SELECT DISTINCT region FROM " . $prefix . "ps_dd_cities WHERE region LIKE :region LIMIT 0,10";
        $pdo = Shop::getPdo( $adapter );
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':region', $regionName );
        $sth->execute();

        $result = $sth->fetchAll( PDO::FETCH_ASSOC );

        if (!empty( $result )) {
            foreach ($result as $k => $arr) {
                $elements[] = $arr['region'];
            }
        }

        // $s = "[".implode(",", $elements)."]";
        echo json_encode( $elements );
        //echo $s;
        exit;
    }

    /**
     * Метод для просмотра работы методов Адаптера необходим дополнительный файл test.php
     * который не должен быть включенным в сборку
     */
    public function test()
    {

        $regedit = regedit::getInstance();
        $objects = umiObjectsCollection::getInstance();
        $typesCollection = umiObjectTypesCollection::getInstance();


        $delivery_type_id = $regedit->getVal( '/modules/ddelivery/dd.delivery_emarket-delivery-id' );

        //  $typesCollection->getType()

     //   var_dump ( $delivery_type_id);

        $obj = $objects->getObject($delivery_type_id);
        var_dump($obj);
        var_dump($objects->delObject($delivery_type_id));



        $objs = $objects->getGuidedItems( $delivery_type_id );
        $type = $typesCollection->getType( 632 );
     //   var_dump($type);
     //   $type->setIsLocked(false);
      //  var_dump($typesCollection->delType(632));
        $bb = $objects->getGuidedItems( 'emarket-deliverytype' );

       // var_dump( $typesCollection->delType( $delivery_type_id ) );

          var_dump( $bb );


     //   var_dump( $delivery_type_id );
        exit;

        //  $dostavkaId = $objectsCollection->addObject( 'DDelivery - (агрегатор служб доставки) (Пункты самовывоза и курьерская доставка)', $delivery_type_id );


        //  var_dump($delivery_type_id);

        // require_once( 'test.php' );
    }


}

