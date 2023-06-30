<?php

use Gettext\Translations;

require_once wa()->getAppPath('lib/vendors/pofile.php', 'mylang');
require_once wa()->getAppPath('lib/vendors/autoload.php', 'mylang');

class mylangGettext
{
    public function __construct()
    {
        if (waConfig::get('is_template')) {
            throw new waException();
        }
    }

    /**
     * @param $filename
     * @return Translations
     */
    public static function readPO($filename): Translations
    {
        static $poLoader;
        if (!$poLoader) {
            $poLoader = new Gettext\Loader\PoLoader();
        }
        return $poLoader->loadFile($filename);
    }

    public static function writePO($data, $filename): bool
    {
        static $poGenerator;
        if (!$poGenerator) {
            $poGenerator = new Gettext\Generator\PoGenerator();
        }
        return $poGenerator->generateFile($data, $filename);
    }

    public static function writeMO($data, $filename): bool
    {
        static $moGenerator;
        if (!$moGenerator) {
            $moGenerator = new Gettext\Generator\MoGenerator();
        }
        $filename = str_ireplace('.po', '.mo', $filename);
        return $moGenerator->generateFile($data, $filename);
    }

    public static function writePOMO($data, $filename): bool
    {
        self::writePO($data, $filename);
        $filename = str_ireplace('.po', '.mo', $filename);
        return self::writeMO($data, $filename);
    }
}