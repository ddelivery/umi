<?php
use DDelivery\DDeliveryUI;
use DDelivery;

require_once( implode( DIRECTORY_SEPARATOR, array( 'DDelivery', 'public_html', 'application', 'bootstrap.php' ) ) );
require_once 'Shop.php';
require_once 'Adapter.php';


/**
 * Шаг 1 разместим все необходимые файлы и папки по своим местам
 */
try {

    $old = umask();
    //Создание папки
    mkdir( "assets" );
    chmod( implode( DIRECTORY_SEPARATOR, array( "assets" ) ), 0777 );

    //Класс ddelivery
    copy( implode( DIRECTORY_SEPARATOR, array( __DIR__, "tmp", "ddelivery.php" ) )
        , implode( DIRECTORY_SEPARATOR, array( "classes", "modules", "emarket", "classes", "delivery", "systems", "ddelivery.php" ) ) );
    chmod( implode( DIRECTORY_SEPARATOR, array( "classes", "modules", "emarket", "classes", "delivery", "systems", "ddelivery.php" ) ), 0760 );

    //Иконки
    copy( implode( DIRECTORY_SEPARATOR, array( __DIR__, "tmp", 'icons', "ddelivery_big.png" ) )
        , implode( DIRECTORY_SEPARATOR, array( 'images', 'cms', 'admin', 'mac', 'icons', 'big', 'ddelivery.png' ) ) );

    chmod( implode( DIRECTORY_SEPARATOR, array( 'images', 'cms', 'admin', 'mac', 'icons', 'big', 'ddelivery.png' ) ), 0777 );

    copy( implode( DIRECTORY_SEPARATOR, array( __DIR__, "tmp", 'icons', "ddelivery_medium.png" ) )
        , implode( DIRECTORY_SEPARATOR, array( 'images', 'cms', 'admin', 'mac', 'icons', 'medium', 'ddelivery.png' ) ) );
    chmod( implode( DIRECTORY_SEPARATOR, array( 'images', 'cms', 'admin', 'mac', 'icons', 'medium', 'ddelivery.png' ) ), 0777 );


    copy( implode( DIRECTORY_SEPARATOR, array( __DIR__, "tmp", 'icons', "ddelivery_small.png" ) )
        , implode( DIRECTORY_SEPARATOR, array( 'images', 'cms', 'admin', 'mac', 'icons', 'small', 'ddelivery.png' ) ) );
    chmod( implode( DIRECTORY_SEPARATOR, array( 'images', 'cms', 'admin', 'mac', 'icons', 'small', 'ddelivery.png' ) ), 0777 );

    //Папку html
    Shop::copydirect( implode( DIRECTORY_SEPARATOR, array( __DIR__, "DDelivery", "public_html", "html" ) )
        , implode( DIRECTORY_SEPARATOR, array( "assets", "html" ) ) );
    chmod( implode( DIRECTORY_SEPARATOR, array( "assets/html" ) ), 0777 );

    //Дополнительные скрипты включая dd.js
    Shop::copydirect( implode( DIRECTORY_SEPARATOR, array( __DIR__, 'tmp', 'assets' ) )
        , implode( DIRECTORY_SEPARATOR, array( 'assets', 'html', 'assets' ) ) );

} catch (Exception $e) {
    echo "Копирование файлов в папку /assets не удалось, убедитесь что она существует в корневой папке и доступна для записи</br></br>";

    var_dump( $e );
    exit;
}




/**
 * Шаг 3 формируем настройки модуля
 */
try {
    $ddcomlist = DDeliveryUI::getCompanySubInfo();

    $companys = array();
    foreach ($ddcomlist as $k => $arr) {
        $INFO['ddctype.' . $k] = 1;
        $INFO['ddc.' . $k] = 1;
    }
    $INFO['name'] = "ddelivery";
    $INFO['filename'] = "modules/ddelivery/class.php";
    $INFO['config'] = "1";
    $INFO['ico'] = "ico_ddelivery";
    $INFO['default_method_admin'] = "DdBaseConfig";
    $INFO['is_indexed'] = "1";
    $INFO['per_page'] = "10";
    $INFO['func_perms/view'] = "Основные";


    $INFO['dd.is_active'] = "1";
    $INFO['dd.sort'] = "100";
    $INFO['dd.title'] = "DDelivery";
    $INFO['dd.description'] = "Сервис доставки DDelivery.ru объединяет множество транспортных компаний и дает возможность работать с ними по принципу одного окна";

    //$INFO['dd.api_key'] = '';
    $INFO['dd.tax_rate'] = "0";
    $INFO['dd.test'] = "1";
    $INFO['dd.declared_percent'] = "100";
    $INFO['dd.boolean:declared_percent'] = "1";
    $INFO['dd.around_step'] = "1";


    $INFO['dd.width_default'] = "10";
    $INFO['dd.height_default'] = "10";
    $INFO['dd.length_default'] = "10";
    $INFO['dd.weight_default'] = "10";


    $COMPONENTS = array();
    $COMPONENTS[] = "./classes/modules/ddelivery/__admin.php";
    $COMPONENTS[] = "./classes/modules/ddelivery/class.php";
    $COMPONENTS[] = "./classes/modules/ddelivery/i18n.php";
    $COMPONENTS[] = "./classes/modules/ddelivery/lang.php";
    $COMPONENTS[] = "./classes/modules/ddelivery/permissions.php";
    $COMPONENTS[] = "./classes/modules/ddelivery/events.php";
    $COMPONENTS[] = "./classes/modules/emarket/classes/customer/customer.php";

} catch (Exception $e) {
    var_dump( $e );
    exit;
}

/**
 * Шаг 4 Формирование нужного типа данных
 */


try {

    $regedit = regedit::getInstance();
    $objectTypesCollection = umiObjectTypesCollection::getInstance();
    $objectsCollection = umiObjectsCollection::getInstance();


//Если существует тип способа доставки то удалим
    if (( $ddelivery_typeId = $regedit->getVal( '//modules/ddelivery/dd.delivery_type_id' ) ) != false) {

        // $objectsCollection->getGuidedItems($ddelivery_typeId);


        if ($objectTypesCollection->getType( $ddelivery_typeId )) {

            if ($objectTypesCollection->delType( $ddelivery_typeId )) {

                $regedit->setVal( '//modules/ddelivery/dd.delivery_type_id', false );
            }
        }
    }

//Если существует объект способа доставки - то удалим его
    if (( $internalTypeId = $regedit->getVal( '//modules/ddelivery/dd.delivery_emarket-delivery-id' ) ) != false) {
        if ($objectsCollection->getObject( $internalTypeId )) {
            if ($objectsCollection->delObject( $internalTypeId ) != false) {
                $regedit->setVal( '//modules/ddelivery/dd.delivery_emarket-delivery-id', false );

            }
        }
    }


    $className = "ddelivery";

// получаем родительский тип Способ доставки
    $parentTypeId = $objectTypesCollection->getBaseType( "emarket", "delivery" );

// Тип для внутреннего объекта, связанного с публичным типом (Тип доставки)
    $internalTypeId = $objectTypesCollection->getBaseType( "emarket", "delivery_type" );

//Создание нового спсособа доставки
    $ddelivery_typeId = $objectTypesCollection->addType( $parentTypeId, "Доставка c DDelivery" );
    $type = $objectTypesCollection->getType( $ddelivery_typeId );
    $type->commit();

// Создаем внутренний объект
    $internalObjectId = $objectsCollection->addObject( "Доставка DDelivery", $internalTypeId );

    $internalObject = $objectsCollection->getObject( $internalObjectId );
    $internalObject->setValue( "class_name", $className ); // имя класса для реализации

// связываем его с типом
    $internalObject->setValue( "delivery_type_id", $ddelivery_typeId );
    $internalObject->setValue( "delivery_type_guid", "emarket-delivery-" . $ddelivery_typeId );
    $internalObject->commit();

    // Связываем внешний тип и внутренний объект
    $type = $objectTypesCollection->getType( $ddelivery_typeId );
    $type->setGUID( $internalObject->getValue( "delivery_type_guid" ) );
    $type->commit();

    $regedit->setVal( '//modules/ddelivery/dd.delivery_type_id', $ddelivery_typeId );
    $regedit->setVal( '//modules/ddelivery/dd.delivery_emarket-delivery-id', $internalObjectId );
    $INFO['dd.delivery_type_id'] = $ddelivery_typeId; //143
    $INFO['dd.delivery_emarket-delivery-id'] = $internalObjectId; //633


    //Добавляем способ доставки
    //$dostavkaId = $objectsCollection->addObject( 'DDelivery - (агрегатор служб доставки) (Пункты самовывоза и курьерская доставка)', $ddelivery_typeId );
    // $objectsCollection->getGuidedItems( $dostavkaId );


} catch (Exception $e) {
    echo "Данные не сформированы";
    var_dump( $e );
    exit;
}



/**
 * Шаг 2 формируем БД
 */

$Adapter = new Adapter();
if ($Adapter->dbdriver == $Adapter::DB_MYSQL) {
    try {
        ini_set( 'mysql.max_allowed_packet', '30M' );
        require_once "import.php";
      
        $IntegratorShop = new Adapter();

        $ddeliveryUI = new DDeliveryUI( $IntegratorShop, true );
        $ddeliveryUI->createTables();

    } catch (Exception $e) {

        echo "Импорт базы данных не удался</br></br>";

        var_dump( $e );
        exit;
    }
}

if ($Adapter->dbdriver == $Adapter::DB_SQLITE) {
    try {
//        copy( implode( DIRECTORY_SEPARATOR, array( __DIR__, "DDelivery", 'public_html', "db", 'db.sqlite' ) )
//            , implode( DIRECTORY_SEPARATOR, array( _DIR__, 'db.sqlite' ) ) );
        chmod( implode( DIRECTORY_SEPARATOR, array( __DIR__, "DDelivery", 'public_html', "db", 'db.sqlite' ) ), 0770 );
    } catch (Exception $e) {
        echo "Копирование базы mysql не удалось</br></br>";
        var_dump( $e );
        exit;

    }
}
