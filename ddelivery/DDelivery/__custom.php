<?php
use Adapter;
use DDelivery\DDeliveryUI;

require_once(implode(DIRECTORY_SEPARATOR, array('DDelivery', 'public_html', 'application', 'bootstrap.php')));
require_once('Adapter.php');

abstract class __ddelivery_custom
{
    /**
     * Метод перенаправления на страницу настройки DDelivery
     */
    public function choosecust()
    {
        $ini = cmsController::getInstance()->getModule("emarket");
        $order = $ini->getBasketOrder(false);
        $deliveryId = (int)getRequest('delivery-id');

        //Если не передан id доставки перенаправление на страницу выбора доставки
        if (!$deliveryId) {
            $this->redirect($ini->pre_lang . '/emarket/purchase/delivery/choose/');
        }


        if (@isset($_SESSION['emarket']['delivery'][$deliveryId])) {
            $deliveryPrice = (float)$_SESSION['emarket']['delivery'][$deliveryId];
        } else {
            $delivery = delivery::get($deliveryId);
            $deliveryPrice = (float)$delivery->getDeliveryPrice($order);
        }

        $order->setValue('delivery_id', $deliveryId);
        $order->setValue('delivery_price', $deliveryPrice);
        $order->refresh();
        $order->commit();

        $regedit = regedit::getInstance();

        //Получим id типа доставки
        $delivery_type = umiObjectsCollection::getInstance()->getObject($deliveryId)->getType()->getId();

        //Если полученный id является id доставки DD то перенаправляем на страницу настроек
        if ($delivery_type == $regedit->getVal('//modules/ddelivery/dd.delivery_type_id')) { //915
            $this->redirect($this->pre_lang . '/ddelivery/ddelivery_start');
        } else {
            $this->redirect($this->pre_lang . '/emarket/purchase/payment/choose/');
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
            $ddeliveryUI = new DDeliveryUI($IntegratorShop);
            echo $ddeliveryUI->render(isset($_REQUEST) ? $_REQUEST : array());
        } catch (Exception $e) {
            echo $e;
        }
        exit;
    }




    /**
     * Обработка заказа SDK при завершении оформления внутри CMS
     * @param iUmiEventPoint $event
     */
    public function onCmsOrderFinish(iUmiEventPoint $event)
    {
        if ($event->getMode() == "after" && $event->getParam("old-status-id") != $event->getParam("new-status-id")) {
            $CmsOrder = $event->getRef("order");
            $CmsOrderId = $CmsOrder->getId();


            $sdkOrderId = regedit::getInstance()->getVal('//modules/ddelivery/orderID_' . $CmsOrderId . '_orderSDkId');


            try {
                $IntegratorShop = new Adapter();
                $ddeliveryUI = new DdeliveryUI($IntegratorShop, true);

                $status = $event->getParam("new-status-id");
                $payment = $CmsOrder->getPaymentStatus();
                $ddeliveryUI->onCmsOrderFinish($sdkOrderId, $CmsOrderId, $status, $payment);

// $payment – идентификатор способа оплаты в пределах CMS
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

        $propName = $event->getParam('property');
        $entity = $event->getRef('entity');

        if ($entity instanceof iUmiObject && $propName == 'status_id') {

            try {
                $IntegratorShop = new Adapter();
                $ddeliveryUI = new \DDelivery\DDeliveryUI($IntegratorShop, true);

                //Проверка - надо ли отправлять данные  на сервер  взависимости от нового статуса
                //isStatusToSendOrder
                if ($IntegratorShop->isStatusToSendOrder($event->getRef('newValue'))) {

                    $CMSOrder = order::get($entity->id);
                    $CMSOrder_id = $CMSOrder->getId();
                    $sdkOrderId = regedit::getInstance()->getVal('//modules/ddelivery/orderID_' . $CMSOrder_id . '_orderSDkId');

                    $order = $ddeliveryUI->initOrder(array($sdkOrderId));
                    if (empty($order))
                        return null;
                    $order = reset($order);

                    $payment = $CMSOrder->getValue('payment_id');


                    //Если это нужный статус, то шлем на сервер
                    $ddeliveryUI->sendOrderToDD($order, $CMSOrder_id, $payment);
                }


            } catch (\DDelivery\DDeliveryException $e) {
                echo $e->getMessage();
            }

        }

    }


    public function onModifyStatusObject(iUmiEventPoint $event)
    {

        if ($event->getMode() == "after") {

            // static $modifiedCache = array();
            // static $orderStatus = array();
            $object = $event->getRef("object");
            // $typeId = umiObjectTypesCollection::getInstance()->getBaseType('emarket', 'order');

            if ($object->getTypeGUID() == 'emarket-order') {

                try {
                    $IntegratorShop = new Adapter();
                    $ddeliveryUI = new \DDelivery\DDeliveryUI($IntegratorShop, true);

                    //Проверка - надо ли отправлять данные  на сервер  взависимости от нового статуса
                    //isStatusToSendOrder
                    //

                    $CMSOrder = order::get($object->getId());
                    if ($IntegratorShop->isStatusToSendOrder($CMSOrder->getOrderStatus())) {
                        $CMSOrder_id = $CMSOrder->getId();
                        $sdkOrderId = regedit::getInstance()->getVal('//modules/ddelivery/orderID_' . $CMSOrder_id . '_orderSDkId');

                        $order = $ddeliveryUI->initOrder(array($sdkOrderId));
                        if (empty($order))
                            return null;
                        $order = reset($order);

                        $payment = $CMSOrder->getValue('payment_id');

                        //Если это нужный статус, то шлем на сервер
                        $ddeliveryUI->sendOrderToDD($order, $CMSOrder_id, $payment);
                    }


                } catch (\DDelivery\DDeliveryException $e) {
                    echo $e->getMessage();
                }
            } else {
                return;
            }

        }


    }

    public function test()
    {

        ini_set("display_errors", "1");
        error_reporting(E_ALL);
        require_once(implode(DIRECTORY_SEPARATOR, array('DDelivery', 'public_html', 'application', 'bootstrap.php')));
        require_once('Adapter.php');
        require_once('Shop.php');




        $IntegratorShop = new Adapter();

        $ddeliveryUI = new DDeliveryUI($IntegratorShop);


        $data['getApiKey'] = $IntegratorShop->getApiKey();
        $data['isTestMode'] = $IntegratorShop->isTestMode();
        $data['getPathByDB'] = $IntegratorShop->getPathByDB();
        $data['getStaticPath'] = $IntegratorShop->getStaticPath();
        $data['getPhpScriptURL'] = $IntegratorShop->getPhpScriptURL();
        $data['getDeclaredPercent'] = $IntegratorShop->getDeclaredPercent();
        $data['getCustomPointsString-propuchen'] = $IntegratorShop->getCustomPointsString();
        $data['_getProductsFromCart-nado nastroit gabarity'] = $IntegratorShop->getProductsFromCart();
        $data['getClientLastName'] = $IntegratorShop->getClientLastName();
        $data['getClientFirstName'] = $IntegratorShop->getClientFirstName();
        $data['getClientPhone'] = $IntegratorShop->getClientPhone();
        $data['getClientCityId'] = $IntegratorShop->getClientCityId();

        $data['getClientAddress'] = $IntegratorShop->getClientAddress();
        $data['getSupportedType-что возвращать и надо ли настраивать? '] = $IntegratorShop->getSupportedType();
        $data['getIntervalsByPoint'] = $IntegratorShop->getIntervalsByPoint();
        $data['filterCompanyPointCourier'] = $IntegratorShop->filterCompanyPointCourier();
        $data['filterCompanyPointSelf'] = $IntegratorShop->filterCompanyPointSelf();
        $data['filterPointByPaymentTypeCourier-доделать - не понятно где настраивать'] = $IntegratorShop->filterPointByPaymentTypeCourier();
        $data['filterPointByPaymentTypeSelf-доделать - не понятно где настраивать'] = $IntegratorShop->filterPointByPaymentTypeCourier();
        $data['isPayPickup'] = $IntegratorShop->isPayPickup();
        $data['aroundPriceStep'] = $IntegratorShop->aroundPriceStep();
        $data['aroundPriceType'] = $IntegratorShop->aroundPriceType();

//
        $data['onFinishChange($orderId, DDeliveryOrder $order, $customPoint)-доделать'] = 'null'; //$IntegratorShop->onFinishChange($orderId, DDeliveryOrder $order, $customPoint);
        $data['isStatusToSendOrder($status)'] = $IntegratorShop->isStatusToSendOrder(86);
        $data['setCmsOrderStatus( $cmsOrderID  $status)'] = $IntegratorShop->setCmsOrderStatus(888, 87);
        $data['getDbConfig'] = $IntegratorShop->getDbConfig();

        echo '<pre>';
        var_export($data);
        echo '</pre>';

        exit;
        // return $data;

    }








//    public function onOrderPropChange(iUmiEventPoint $e) {
//
//        var_dump('Ok');
//
//        if($e->getMode() != 'after') return;
//
//        $propName = $e->getParam('property');
//        $entity = $e->getRef('entity');
//
//        if($entity instanceof iUmiObject && $propName == 'status_id') {
//            $type = selector::get('object-type')->id($entity->getTypeId());
//            if($type && $type->getMethod() == 'order') {
//                $status = selector::get('object')->id($e->getParam('newValue'));
//                if(($status instanceof iUmiObject) && $status->codename) {
//                    $order = order::get($entity->id);
//                    switch($status->codename) {
//                        case 'waiting': {
//                            $order->reserve();
//                            break;
//                        }
//
//                        case 'canceled': {
//                            $order->unreserve();
//                            break;
//                        }
//
//                        case 'ready': {
//                            $order->writeOff();
//                            break;
//                        }
//                    }
//                    $order->commit();
//                }
//            }
//        }
//    }
}

