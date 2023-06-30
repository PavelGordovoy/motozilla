<?php

class mylangRouting extends waRouting
{
    /**
     * @var waSystem
     */
    protected $system;
    protected $routes = [];
    protected $domain;
    protected $route;
    protected $root_url;
    protected $aliases = [];
    
    public function __construct()
    {
        parent::__construct(wa());
    }

    public function getAppByUrl($url)
    {
        $r = $this->dispatchRoutes($this->getRoutes(), $url);
        return $r['app'];
    }

    /**
     * Make url to redirect
     * 2.0 not used
     * @param null $locale
     */
    public static function getRedirectUrlByApp($app, $redirect_url, $locale = null)
    {
        $routes = wa()->getRouting()->getRoutes();
        $redirect_url = trim($redirect_url, '*');
        if (strlen($redirect_url) > 1) {
            $redirect_url = ltrim($redirect_url, '/');
        }
        foreach ($routes as $route) {
            if ($route['app'] === $app && $route['locale'] === $locale && (stripos($route['url'], $redirect_url) !==false)) {
                $redirect_url = '/'.trim($route['url'],'*');
                break;
            }
        }
        return $redirect_url;
    }
    
    public static function redirect()
    {
        if (wa()->getEnv() == 'frontend') {
            $redirect_url = null;
            $current_app = waRequest::param('app', false,'string');
            $user = wa()->getUser();
            $locale = waRequest::get('locale', null, 'string');
            if ($locale && waLocale::getInfo($locale)) {
                $domain = waConfig::get('mylang_this_domain');
                if (!$domain) {
                    $domain = wa()->getConfig()->getDomain();
                }
                //Check for Alias
                $routing = wa()->getRouting();
                if ($routing->isAlias($domain)) {
                    $old_domain = $domain;
                    $domain = $routing->isAlias($domain);
                }
                //
                $rules = new mylangRules();
                $rules_user = $rules->get($domain);
                $redirect_url = ifset($rules_user[$locale]);
                $redirect_url = self::getRedirectUrlByApp($current_app, $redirect_url, $locale);
                if ($redirect_url && $user->getLocale() != $locale) {
                    if (strpos($redirect_url, '//') === false) {
                        if (isset($old_domain)) {
                            $domain = $old_domain;
                        }
                        //
                        wa()->getStorage()->remove('locale'); //убираем.
                        wa()->getResponse()->redirect('//'.$domain.$redirect_url, 302);
                    } else {
                        wa()->getStorage()->remove('locale'); //убираем.
                        wa()->getResponse()->redirect($redirect_url, 302);
                    }
                }
            //No rule save locale to session and remove get
                if (!$redirect_url) {
                    $redirect_url = wa()->getConfig()->getCurrentUrl();
                }
                wa()->getStorage()->set('locale', $locale);
                $redirect_url = preg_replace('~&?locale='.$locale.'~i', '', $redirect_url);
            }
            if ($redirect_url) {
                wa()->getResponse()->redirect(rtrim($redirect_url, '?&'));
                return;
            }
        }
    }
}
