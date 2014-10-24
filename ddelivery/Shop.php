<?php

/**
 * Helper для модуля
 * Class Shop
 */
class Shop
{
    /**
     * Возвращает объект заказа из корзины
     * @param bool $useDummyOrder
     * @return int|null|order
     */
    public static function getBasketOrder($useDummyOrder = true)
    {
        static $cache;

        if ($cache instanceof order) {
            //Если ордер имеет стату, то это означает, что он не из корзины и поэтому необходимо сбросить кеш
            if (($cache->getOrderStatus() && $cache->getOrderStatus() != order::getStatusByCode('editing')) || $useDummyOrder == false) {
                $cache = null;
            } else return $cache;
        }

        $customer = customer::get();

        $cmsController = cmsController::getInstance();
        $domain = $cmsController->getCurrentDomain();
        $domainId = $domain->getId();

        $orderId = $customer->getLastOrder($domainId);

        if ($orderId) return $cache = order::get($orderId);

        return $cache = order::create($useDummyOrder);
    }

    /**
     * Возвращает текущего пользователя
     * @return customer
     */
    public static function getCustomer()
    {
        return customer::get();
    }

    /**
     * Метод который копирует директории с содержимым в место назначения
     * Используется при установке
     * @param $source
     * @param $dest
     */
    public static function copydirect($source, $dest)
    {
        if(!is_dir($dest))
            mkdir($dest);
        if($handle = opendir($source))
        {
            while(false !== ($file = readdir($handle)))
            {
                if($file != '.' && $file != '..')
                {

                    $path = $source . '/' . $file;


                    if(is_file($path))
                    {
                        if(!is_file($dest . '/' . $file))
                            if(!@copy($path, $dest . '/' . $file))
                            {
                                echo "('.$path.') Ошибка!!! ";
                            }
                    }
                    elseif(is_dir($path))
                    {
                        if(!is_dir($dest . '/' . $file))
                            mkdir($dest . '/' . $file);
                        self::copydirect($path, $dest . '/' . $file);
                    }
                }
            }
            closedir($handle);
        }
    }

    public static function getPdo(Adapter $dShopAdapter){
        $dbConfig = $dShopAdapter->getDbConfig();
        if(isset($dbConfig['pdo']) && $dbConfig['pdo'] instanceof \PDO) {
            $pdo = $dbConfig['pdo'];
        }elseif($dbConfig['type'] == Adapter::DB_SQLITE) {
            if(!$dbConfig['dbPath'])
                throw new DDeliveryException('SQLite db is empty');

            $dbDir = dirname($dbConfig['dbPath']);
            if(  (!is_writable( $dbDir )) || ( !is_writable( $dbConfig['dbPath'] ) ) || (!is_dir( $dbDir )) ) {
                throw new DDeliveryException('SQLite database does not exist or is not writable');
            }

            $pdo = new \PDO('sqlite:'.$dbConfig['dbPath']);
            $pdo->exec('PRAGMA journal_mode=WAL;');
        } elseif($dbConfig['type'] == Adapter::DB_MYSQL) {
            $pdo = new \PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass']);
            $pdo->exec('SET NAMES utf8');
        }else{
            throw new DDeliveryException('Not support database type');
        }
        return $pdo;
    }




} 