<?php

require_once __DIR__ . '/../vendors/autoload.php';

class miniHtml
{
    const EXTRACT_REGEX = '/(<!--\[if\s(?:[^<]+|<(?!!\[endif\]-->))*<!\[endif\]-->)|(<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>)|(<link[^<>]*stylesheet.*>)|(<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>)/iU';
    const QUERY = "//script | //link[@rel='stylesheet'] | //style";
    const SPLIT_REGEX = '/\r\n|\r|\n/';
    const MOBILE_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1';
    const DESKTOP_UA = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36';
    const IGNORE_JS = [' async', 'api-maps', 'googletagmanager', 'yadro', 'ld+json', 'webpcheck'];
    const EXCLUDED_ACTIONS = ['checkout', 'cart', 'order'];

    private $html;
    private $settings;
    private $start;
    private $cdn;

    public function __construct($html)
    {
        $this->start = microtime(true);
        $this->settings = mini::settings();
        $this->settings['ignorecss'] = preg_split(self::SPLIT_REGEX, $this->settings['ignorecss']);
        $this->settings['ignorejs'] = array_merge(preg_split(self::SPLIT_REGEX, $this->settings['ignorejs']), self::IGNORE_JS);
        $this->html = $html;
        $this->cdn = wa()->getCdn();
    }

    public function output()
    {
        if (in_array(waRequest::param('action'), self::EXCLUDED_ACTIONS) || strpos(waRequest::param('action'), 'my') === 0) {
            return $this->html;
        }
        if ($this->settings['css'] > 0 || $this->settings['js'] > 0) {
            list($combined, $result) = $this->getElementsFromHtml();
            foreach ($result as $element) {
                $type = $element['type'];
                if ($this->settings[$type] == 2 && $this->isLocal($element)) {
                    $path = wa()->getDataPath($type, true, 'mini');
                    $url = wa()->getDataUrl($type, true, 'mini');
                    $hash = hash('adler32', json_encode($element) . mini::salt());
                    $file = $path . '/' . $hash . ".$type";
                    if (!file_exists($file)) {
                        $class = "MatthiasMullie\Minify\\" . strtoupper($type);
                        /** @var MatthiasMullie\Minify\CSS $minifier */
                        /** @var MatthiasMullie\Minify\JS $minifier */
                        $minifier = new $class();
                        $r = $minifier->add($element['file'])->minify($file);
                        waLog::log('Saved and compressed: "' . $element['href'] . '" to ' . basename($file), 'mini.log');
                        mini::postProcess($r, $file, $type);
                    }
                    $this->html = mini::addPreload("$url/$hash.$type", $this->html);
                    if ($this->settings['defer_css'] && $type === 'css') {
                        $combined = str_replace(array($element['href'], htmlspecialchars($element['href'])), "$url/$hash.$type" . '" media="none" onload="if(media!=\'all\') media=\'all\'" type="text/css', $combined);
                    } else {
                        $combined = str_replace(array($element['href'], htmlspecialchars($element['href'])), "$url/$hash.$type", $combined);
                    }

                }
            }
            $this->html = str_replace('</body>', $combined . '</body>', $this->html);
        }
        $this->addCriticalCss();
        if ($this->settings['lazyimage']) {
            $this->lazyimage();
        }
        if ($this->settings['menu']) {
            $this->addAdminMenu();
        }
        $this->html = str_replace('</html>', '<!-- ' . round(microtime(true) - $this->start, 4) . ' --></html>', $this->html);
        return $this->html;
    }

    private function getElementsFromHtml()
    {
        $combined = '';
        $this->html = preg_replace_callback(self::EXTRACT_REGEX, function ($m) use (&$combined) {
            if (!empty($m[2]) && $this->settings['js'] > 0 && !$this->checkIgnored($m[2], $this->settings['ignorejs'])) {
                $combined .= $m[2];
                return '';
            }
            if (!empty($m[3]) && $this->settings['css'] > 0 && !$this->checkIgnored($m[3], $this->settings['ignorecss'])) {
                $combined .= $m[3];
                return '';
            }
            if (!empty($m[4]) && $this->settings['css'] > 0 && !$this->checkIgnored($m[4], $this->settings['ignorecss'])) {
                $combined .= $m[4];
                return '';
            }
            return $m[0];
        }, $this->html);

        $code = "<?xml encoding='UTF-8'><html lang='en'><head><title></title></head><body>$combined</body></html>";
        $dom = new DOMDocument();
        $dom->loadHTML($code);
        $xpath = new DOMXPath($dom);
        $elements = $xpath->query(self::QUERY);
        $result = [];
        foreach ($elements as $k => $el) {
            $result[$k]['type'] = $el->tagName === 'script' ? 'js' : 'css';
            if ($v = $el->nodeValue) {
                $result[$k]['value'] = $v;
            }
            elseif ($v = $el->getAttribute('src')) {
                $result[$k]['href'] = $v;
            }
            elseif ($v = $el->getAttribute('href')) {
                $result[$k]['href'] = $v;
            }
        }
        return [$combined, $result];
    }

    private function checkIgnored($string, $ignored)
    {
        foreach ($ignored as $rule) {
            if (!empty($rule) && false !== stripos($string, $rule)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if element is local and needs compression at all
     * @param $element
     * @return bool
     */
    public function isLocal(&$element)
    {
        if (!empty($element['value'])) {
            return false;
        }
        $href = $element['href'] = str_replace($this->cdn, '', $element['href']);
        if (strpos($element['href'], '//') === 0) {
            $href = str_replace('//', 'http://', $element['href']);
        }
        if ($this->settings['cache_resources'] && filter_var($href, FILTER_VALIDATE_URL) !== false) {
            $element['file'] = mini::net($href, false, true);
            return true;
        }
        if (strpos($element['href'], '.min.')) {
            $this->html = mini::addPreload($element['href'], $this->html);
            return false;
        }
        $root = waRequest::server('DOCUMENT_ROOT');
        $href = preg_replace('/\?.*/', '', $element['href']);

        if (file_exists($root . $href)) {
            $element['file'] = $root . $href;
            return true;
        }
        return false;
    }

    private function addCriticalCss()
    {
        $strategy = waRequest::isMobile() ? 'mobile' : 'desktop';
        $theme = waRequest::isMobile() ? waRequest::param('theme_mobile') : waRequest::param('theme');
        $action = waRequest::param('action') ?: 'main';
        $id = "mini.$theme.$strategy.$action.css";
        $path = wa()->getDataPath("critical/$id", true, 'mini');
        if (file_exists($path)) {
            waRequest::setParam('mini_css_loaded', 1);
            $this->html = str_replace('</head>', '<style>' . file_get_contents($path) . '</style></head>', $this->html);
            return;
        }
    }

    /**
     * @link https://github.com/vralle/vralle-lazyload/blob/master/app/lazysizes.php
     */
    private function lazyimage()
    {
        $this->html = str_replace('</title>', '</title><script src="' . wa()->getAppStaticUrl('mini') . 'js/lazysizes.min.js?v=5.0" async></script>', $this->html);
        $pattern =
            '<\s*'                      // Opening tag
            . '(img|iframe)'                   // Tag name
            . '('
            . '[^>\\/]*'                // Not a closing tag or forward slash
            . '(?:'
            . '\\/(?!>)'                // A forward slash not followed by a closing tag
            . '[^>\\/]*'                // Not a closing tag or forward slash
            . ')*?'
            . ')'
            . '\\/?>';

        $count = mini::settings('lazy_count') + 1;
        $this->html = preg_replace_callback(
            "/$pattern/i",
            function ($m) use (&$count) {
                if (--$count > 0) {
                    return $m[0];
                }

                $tag = $m[1];
                $attrs = $m[2];
                /**
                 * The wordpress handler is used. This may seem redundant, but it is reliable and verified
                 * @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
                 */
                $parced = $this->shortcode_parse_atts($attrs);
                $attr_arr = $this->attrHandler($parced, $tag);

                if ($attr_arr) {
                    $attr = '';
                    foreach ($attr_arr as $key => $value) {
                        if (is_int($key)) {
                            $attr .= ' ' . $value;
                        } else {
                            $attr .= sprintf(' %s="%s"', $key, $value);
                        }
                    }
                    $m[0] = str_replace($attrs, $attr, $m[0]);
                }
                return $m[0];
            },
            $this->html
        );
    }

    /**
     * @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
     * @param $text
     * @return array
     */
    private function shortcode_parse_atts($text)
    {
        $atts = [];
        $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]) && strlen($m[8]))
                    $atts[] = stripcslashes($m[8]);
                elseif (isset($m[9]))
                    $atts[] = stripcslashes($m[9]);
            }

            // Reject any unclosed HTML elements
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    /**
     * Image attribute handler
     * @param  array $attr_arr List of image attributes and their values.
     * @param  string $tag current tag
     * @return mixed    Array List of image attributes and their values,
     *                  where the necessary attributes for the loader are added
     *                  or false, if exclude
     */
    private function attrHandler($attr_arr = [], $tag = 'img')
    {
        $lazy_class = 'mini-lazyload';
        $img_placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        $classes_arr = [];
        $have_src = false;
        $exlude_class_arr = ['mini-no-lazyload'];

        if (isset($attr_arr['class'])) {
            $classes_arr = explode(' ', $attr_arr['class']);
            if (!empty(array_intersect($exlude_class_arr, $classes_arr))) {
                return false;
            }
            if (in_array($lazy_class, $classes_arr)) {
                return false;
            }
        }
        if (mini::settings('lazy_sizes') && isset($attr_arr['src']) && $s = $this->getSizes($attr_arr['src'])) {
            $attr_arr['width'] = $s[0];
            $attr_arr['height'] = $s[1];
        }
        if (isset($attr_arr['srcset'])) {
            $attr_arr['data-srcset'] = $attr_arr['srcset'];
            $attr_arr['srcset'] = $img_placeholder;
            $have_src = true;
            if (0) {
                $attr_arr['data-sizes'] = 'auto';
                unset($attr_arr['sizes']);
            }
        } elseif (isset($attr_arr['src'])) {
            $attr_arr['data-mini-original'] = $attr_arr['src'];
            $attr_arr['src'] = $img_placeholder;
            // Cleanup Dry Tags
            unset($attr_arr['sizes']);
            $have_src = true;
        }
        // Do lazyloaded, only if the image have src or srcset
        if ($have_src) {
            $classes_arr[] = $lazy_class;
            $attr_arr['class'] = implode(' ', $classes_arr);
        }
        return $attr_arr;
    }

    private function getSizes($src)
    {
        $file = wa()->getConfig()->getRootPath() . $src;
        $file = str_replace($this->cdn, '', $file);
        if (file_exists($file)) {
            return @getimagesize($file);
        }
        return false;
    }

    private function addAdminMenu()
    {
        if (!mini::checkAccess() || waRequest::param('mini_menu') ) {
            return false;
        }
        $path = wa()->getConfig()->getBackendUrl(true) . 'mini/';
        $view = wa()->getView();
        $view->assign('path', $path);
        $html = $view->fetch(wa()->getAppPath('templates/menu.html', 'mini'));
        $this->html = str_replace('</body>', $html . '</body>', $this->html);
        waRequest::setParam('mini_menu', true);
    }
}