<?php

class mylangGettextParser extends waGettextParser
{

    /**
     * mylangGettextParser constructor.
     * @param $params array
     * @throws waException
     */
    public function __construct($params)
    {
        $params = (array) $params;
        $parsed_path = $this->parsePath($params[0]);
        $entity = $this->factory($parsed_path);

        parent::__construct($entity, $this->getOptions($params));
    }

    protected function getOptions($params)
    {
        $options = [];
        if (isset($params['debug'])) {
            $options['debug'] = true;
        }

        return $options;
    }

    protected function parsePath($path)
    {
        $result = [
            'type'      => null,
            'app'       => null,
            'entity_id' => null,
        ];

        $chunks = explode('/', $path);
        $app = ifset($chunks, 0, false);

        // apps level
        if (count($chunks) === 1) {
            if ($chunks[0] === 'webasyst') {
                $result['app'] = 'webasyst';
                $result['type'] = 'webasyst';
            } else {
                $result['app'] = $chunks[0];
                $result['type'] = 'app';
            }
        } elseif (count($chunks) === 2) {
            // wa-widgets level
            if ($app === 'wa-widgets') {
                $result['type'] = 'wa-widgets';
                $result['entity_id'] = $chunks[1];
            }
        } elseif (count($chunks) === 3) {
            $result['app'] = $app;
            $type = $chunks[1];
            $result['entity_id'] = $chunks[2];

            if ($app === 'wa-plugins') {
                $result['type'] = 'wa-plugins';
                $result['app'] = $chunks[1];
            } elseif ($type === 'plugins') {
                $result['type'] = 'plugins';
            } elseif ($type === 'widgets') {
                $result['type'] = 'widgets';
            } elseif ($type === 'themes') {
                $result['type'] = 'themes';
            }
        }

        return $result;
    }

    /**
     * @param $parsed_path
     * @return waLocaleParseEntityApp|waLocaleParseEntityTheme|waLocaleParseEntityWaPlugins|waLocaleParseEntityWaWidgets
     * @throws waException
     */
    protected function factory($parsed_path)
    {
        $type = $parsed_path['type'];

        if ($type === 'webasyst') {
            $result = new waLocaleParseEntityWebasyst($parsed_path);
        } elseif ($type === 'app') {
            $result = new waLocaleParseEntityApp($parsed_path);
        } elseif ($type === 'plugins') {
            $result = new waLocaleParseEntityPlugins($parsed_path);
        } elseif ($type === 'widgets') {
            $result = new waLocaleParseEntityWidgets($parsed_path);
        } elseif ($type === 'themes') {
            $result = new waLocaleParseEntityTheme($parsed_path);
        } elseif ($type === 'wa-plugins') {
            $result = new waLocaleParseEntityWaPlugins($parsed_path);
        } elseif ($type === 'wa-widgets') {
            $result = new waLocaleParseEntityWaWidgets($parsed_path);
        } else {
            throw new waException('Unknown entity type');
        }

        return $result;
    }
}
