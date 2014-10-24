<?php


use Adapter;
use DDelivery\DDeliveryUI;

ini_set("display_errors", "1");
error_reporting(E_ALL);

    try{
        $IntegratorShop = new Adapter();
        $ddeliveryUI = new DDeliveryUI($IntegratorShop);
        $order = $ddeliveryUI->initOrder(6);
        $ddeliveryUI->sendOrderToDD($order);
    }catch(Exception $e){
        $IntegratorShop->logMessage($e);
        echo $e->getMessage();
        exit;
    }
    exit();
    $data['getApiKey'] = $IntegratorShop->getApiKey();
    $data['isTestMode'] = $IntegratorShop->isTestMode();
    $data['getPathByDB'] = $IntegratorShop->getPathByDB();
    $data['getStaticPath'] = $IntegratorShop->getStaticPath();
    $data['getPhpScriptURL'] = $IntegratorShop->getPhpScriptURL();
    $data['getDeclaredPercent'] = $IntegratorShop->getDeclaredPercent();
    $data['getCustomPointsString-propuchen'] = $IntegratorShop->getCustomPointsString();
    $data['_getProductsFromCart'] = $IntegratorShop->getProductsFromCart();
    $data['getClientLastName'] = $IntegratorShop->getClientLastName();
    $data['getClientFirstName'] = $IntegratorShop->getClientFirstName();
    $data['getClientPhone'] = $IntegratorShop->getClientPhone();
    $data['getClientCityId'] = $IntegratorShop->getClientCityId();
    $data['getClientAddress'] = $IntegratorShop->getClientAddress();
    $data['getSupportedType'] = $IntegratorShop->getSupportedType();
    $data['getIntervalsByPoint'] = $IntegratorShop->getIntervalsByPoint();
    $data['filterCompanyPointCourier'] = $IntegratorShop->filterCompanyPointCourier();
    $data['filterCompanyPointSelf'] = $IntegratorShop->filterCompanyPointSelf();
    $data['filterPointByPaymentTypeCourier - надо ли настраивать в админке'] = $IntegratorShop->filterPointByPaymentTypeCourier();
    $data['filterPointByPaymentTypeSelf- надо ли настраивать в админке'] = $IntegratorShop->filterPointByPaymentTypeCourier();
    $data['isPayPickup'] = $IntegratorShop->isPayPickup();
    $data['aroundPriceStep'] = $IntegratorShop->aroundPriceStep();
    $data['aroundPriceType'] = $IntegratorShop->aroundPriceType();
    $data['onFinishChange($orderId, DDeliveryOrder $order, $customPoint)'] = 'работает'; //$IntegratorShop->onFinishChange($orderId, DDeliveryOrder $order, $customPoint);
    $data['isStatusToSendOrder($status)'] = $IntegratorShop->isStatusToSendOrder(86);
    $data['setCmsOrderStatus( $cmsOrderID  $status)'] = 'работает';//$IntegratorShop->setCmsOrderStatus(888, 87);
    $data['getDbConfig'] = $IntegratorShop->getDbConfig();

    echo '<pre>';
    var_export($data);
    echo '</pre>';
    exit;

