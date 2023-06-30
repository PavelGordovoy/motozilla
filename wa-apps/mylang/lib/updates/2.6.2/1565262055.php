<?php

/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 08.08.2019
 * Time: 14:02
 */
 $locales = mylangLocale::getAll();
 foreach ($locales as $loc) {
    mylangLocale::fixDatepicker($loc);
 }
