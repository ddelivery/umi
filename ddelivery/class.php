<?php

class ddelivery extends def_module
{
    public function __construct()
    {
        parent::__construct(); // Вызываем конструктор родительского класса def_module

        if (cmsController::getInstance()->getCurrentMode() == "admin") {

            $this->__loadLib("__admin.php");
            $this->__implement("__ddelivery_adm");
        }

        $this->__loadLib("__custom.php");
        $this->__implement("__ddelivery_custom");

        $this->__loadLib("ddelivery.php");
        $this->__implement("ddeliveryDelivery");
    }
}