<?php
namespace PrestaShop\Module\Hsrdv\Link;

use PrestaShop\Module\AsBlog\Model\Post;
use Configuration;
use Context;
use Dispatcher;
use Language;
use PrestaShop\Module\AsBlog\Model\Category;

class BlogLink
{

    /** @var bool Rewriting activation */
    protected $allow;
    protected $url;
    public static $cache = array('page' => array());
    public $protocol_link;
    public $protocol_content;
    protected $ssl_enable;
    protected static $category_disable_rewrite = null;

    /**
     * Constructor (initialization only)
     */
    public function __construct($protocol_link = null, $protocol_content = null)
    {
        $this->allow = (int) Configuration::get('PS_REWRITING_SETTINGS');
        $this->url = $_SERVER['SCRIPT_NAME'];
        $this->protocol_link = $protocol_link;
        $this->protocol_content = $protocol_content;

        if (!defined('_PS_BASE_URL_')) {
            define('_PS_BASE_URL_', Tools::getShopDomain(true));
        }
        if (!defined('_PS_BASE_URL_SSL_')) {
            define('_PS_BASE_URL_SSL_', Tools::getShopDomainSsl(true));
        }

        /* if (Link::$category_disable_rewrite === null) {
          Link::$category_disable_rewrite = array(Configuration::get('PS_HOME_CATEGORY'), Configuration::get('PS_ROOT_CATEGORY'));
          } */

        $this->ssl_enable = Configuration::get('PS_SSL_ENABLED');
    }


    public  function getBlogPostLink($blogpost, $rewrite = null, $ssl = null, $id_lang = null, $id_shop = null, $relative_protocol = false)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBlogUrl();
        $dispatcher = Dispatcher::getInstance();

        if (!is_object($blogpost)) {
            if ($rewrite !== null) {
                return  $url .  $dispatcher->createUrl('module-asblog-blogpost', $id_lang, array('id_post' => (int)$blogpost, 'rewrite' => $rewrite), $this->allow, '', $id_shop);
            }
            $blogpost = new Post($blogpost, $id_lang);
        }

        $params = array();
        $params['rewrite'] = $blogpost->link_rewrite;
        $params['id_post'] = $blogpost->id_post;

        return $url . $dispatcher->createUrl('module-asblog-blogpost', $id_lang, $params, $this->allow);
    }


    public  function getBlogCategoryLink($blogcategory, $rewrite = null, $ssl = null, $id_lang = null, $id_shop = null, $relative_protocol = false)
    {

        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBlogUrl();
        $dispatcher = Dispatcher::getInstance();

        if (!is_object($blogcategory)) {
            if ($rewrite !== null) {
                return $url . $dispatcher->createUrl('module-asblog-blogcategory', $id_lang, array('id_category' => (int)$blogcategory, 'rewrite' => $rewrite), $this->allow, '', $id_shop);
            }
            $blogcategory = new Category($blogcategory, $id_lang);
        }

        $params = array();
        $params['rewrite'] = $blogcategory->link_rewrite;
        $params['id_category'] = $blogcategory->id_category;


        return $url . $dispatcher->createUrl('module-asblog-blogcategory', $id_lang, $params,  $this->allow);
    }

    /**
     * Returns a link to a product image for display
     * Note: the new image filesystem stores product images in subdirectories of img/p/
     *
     * @param string $name rewrite link of the image
     * @param string $ids id part of the image filename - can be "id_product-id_image" (legacy support, recommended) or "id_image" (new)
     * @param string $type
     */
    public function getImageLink($name, $ids, $type = null)
    {
        $return_val = 'false';
        $not_default = false;
        if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $this->protocol_content = 'https://';
        }
        // legacy mode or default image
        $theme = ((Shop::isFeatureActive() && file_exists(_MODULE_SMARTBLOG_DIR_ . $ids . ($type ? '-' . $type : '') . '-' . (int) Context::getContext()->shop->theme_name . '.jpg')) ? '-' . Context::getContext()->shop->theme_name : '');
        if ((Configuration::get('PS_LEGACY_IMAGES') && (file_exists(_MODULE_SMARTBLOG_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg'))) || ($not_default = strpos($ids, 'default') !== false)) {
            if ($this->allow == 1 && !$not_default) {
                $uri_path = __PS_BASE_URI__ . 'blog/' . $ids . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
            } else {
                $uri_path = _THEME_PROD_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
        } else {
            $split_ids = array();
            $split_ids = explode('-', $ids);
            $id_image = '0';
            if(isset($split_ids[1])){
                $id_image = $split_ids[1];
            }else{
                $id_image = $split_ids[0];
            }

            $theme = '';
            if ($this->allow == 1) {
                $uri_path = __PS_BASE_URI__ . 'blog/' . $id_image . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
            } else {
                $uri_path = __PS_BASE_URI__ . 'modules/smartblog/images/' . $id_image . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
        }
        $main_img_exist = _PS_ROOT_DIR_ . '/modules/smartblog/images/'.$id_image.'.jpg';

        if(file_exists($main_img_exist)){
            $media_uri_path = Tools::getMediaServer($uri_path);
            $protocol_content = ($this->ssl_enable) ? 'https://' : 'http://';
            $return_val = $protocol_content . $media_uri_path . $uri_path;
        }else{
            if(Configuration::get('smartshownoimg')){
                $no_img_exist = _PS_ROOT_DIR_ . '/modules/smartblog/images/no.jpg';
                if(file_exists($no_img_exist)){
                    $return_val = __PS_BASE_URI__ . 'modules/smartblog/images/no' . ($type ? '-' . $type : '') . '.jpg';
                } else {
                    $return_val = "false";
                }
            } else {
                $return_val = "false";
            }
        }

        return (isset($return_val))? $return_val : 'false';
    }

    public static function getBlogUrl()
    {
        $ssl_enable       = Configuration::get('PS_SSL_ENABLED');
        $id_lang          = (int) Context::getContext()->language->id;
        $id_shop          = (int) Context::getContext()->shop->id;
        $rewrite_set      = (int) Configuration::get('PS_REWRITING_SETTINGS');
        $ssl              = null;
        static $force_ssl = null;
        if ($ssl === null) {
            if ($force_ssl === null) {
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            }
            $ssl = $force_ssl;
        }
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }
        $base    = ($ssl == 1 && $ssl_enable == 1) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain;
        $langUrl = Language::getIsoById($id_lang) . '/';
        if ((!$rewrite_set && in_array($id_shop, array((int) Context::getContext()->shop->id, null))) || !Language::isMultiLanguageActivated($id_shop) || !(int) Configuration::get('PS_REWRITING_SETTINGS', null, null, $id_shop)) {
            $langUrl = '';
        }

        return $base . $shop->getBaseURI() . $langUrl;
    }


    public static function GetBlogLink($rewrite = '', $params = null, $id_shop = null, $id_lang = null)
    {
        $url          = self::getBlogUrl();
        $dispatcher   = Dispatcher::getInstance();
        $id_lang      = (int) Context::getContext()->language->id;

        $force_routes = (bool) Configuration::get('PS_REWRITING_SETTINGS');

        if ($params != null) {
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params, $force_routes);
        } else {
            $params = array();
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params, $force_routes);
        }
    }

    public  function getCategoryPagination($id_category, $link_rewrite, $pageNum)
    {
        $rewrite = 'module-asblog-blogcategory_pagination';
        $params = array();
        $params['rewrite'] = $link_rewrite;
        $params['id_category'] = $id_category;
        $params['page'] = $pageNum;
        $url          = self::getBlogUrl();
        $dispatcher = Dispatcher::getInstance();
        $id_lang = (int) Context::getContext()->language->id;

        if ($params != null) {
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params,  $this->allow);
        } else {
            $params = array();
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params,  $this->allow);
        }
    }

    public  function getListPagination($pageNum)
    {
        $rewrite = 'module-asblog-bloglist_pagination';
        $params = array();
        $params['page'] = $pageNum;
        $url          = self::getBlogUrl();
        $dispatcher = Dispatcher::getInstance();
        $id_lang = (int) Context::getContext()->language->id;

        if ($params != null) {
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params,  $this->allow);
        } else {
            $params = array();
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params,  $this->allow);
        }
    }
}
