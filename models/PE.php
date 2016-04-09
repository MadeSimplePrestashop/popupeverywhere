<?php

/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
class PE extends ObjectModel
{

    public $alias;
    public $options;
    public $date_to;
    public $position;
    public $active;
    public $content;
    public $html;
    public $close;
    public $button;
    public $header;
    public $link;
    public $sticker;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        self::init();
        parent::__construct($id, $id_lang, $id_shop);
    }

    private static function init()
    {
        if (Shop::isFeatureActive()) {
            Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        }
    }

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'popupeverywhere',
        'primary' => 'id_popupeverywhere',
        'multilang' => true,
        'fields' => array(
            'alias' => array('type' => self::TYPE_STRING),
            'options' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_to' => array('type' => self::TYPE_DATE),
            'position' => array('type' => self::TYPE_INT),
            'active' => array('type' => self::TYPE_BOOL, 'required' => true),
            'content' => array('type' => self::TYPE_HTML, 'lang' => true),
            'html' => array('type' => self::TYPE_HTML, 'lang' => true),
            'sticker' => array('type' => self::TYPE_HTML, 'lang' => true),
            'header' => array('type' => self::TYPE_STRING, 'lang' => true),
            'button' => array('type' => self::TYPE_STRING, 'lang' => true),
            'close' => array('type' => self::TYPE_STRING, 'lang' => true),
            'link' => array('type' => self::TYPE_STRING, 'lang' => true),
        )
    );

    public static function getAll($parms = array())
    {
        self::init();
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'c');
        $sql->leftJoin(self::$definition['table'] . '_lang', 'l', 'c.' . self::$definition['primary'] . ' = l.' . self::$definition['primary'] . ' AND l.id_lang = ' . (int) Context::getContext()->language->id);
        if (Shop::isFeatureActive()) {
            $sql->innerJoin(self::$definition['table'] . '_shop', 's', 'c.' . self::$definition['primary'] . ' = s.' . self::$definition['primary'] . ' AND s.id_shop = ' . (int) Context::getContext()->shop->id);
        }
        if (empty($parms) == false) {
            foreach ($parms as $k => $p) {
                $sql->where('' . $k . ' =\'' . $p . '\'');
            }
        }
        return Db::getInstance()->executeS($sql);
    }

    public function add($autodate = true, $null_values = false)
    {
        $options = $this->transform_options();
        if ($options != false) {
            $this->options = $options;
        }

        parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {

        $options = $this->transform_options();
        if ($options != false) {
            $this->options = $options;
        }
        parent::update($null_values);
    }

    public function delete()
    {
        parent::delete();
    }

    public static function duplicate()
    {
        $pe = new PE(Tools::getValue(self::$definition['primary']));
        if (!is_object($pe))
            return;
        unset($pe->id);
        $pe->active = 0;
        $pe->save();
    }

    private function transform_options()
    {
        if (!Tools::getIsset('submitUpdate' . self::$definition['table']) && !Tools::getIsset('submitAdd' . self::$definition['table']))
            return false;
        $parms = array();
        foreach (self::getOptionFields() as $option) {
            $parms[$option] = Tools::getValue($option);
        }
        return Tools::jsonEncode($parms);
    }

    public static function getOptionFields()
    {
        return array(
            'categories', 'controllers', 'products', 'cms',
            'AutoPopup','DiscountCountdown','GoogleAnalytics','Sensitivity', 'Aggressive', 'Timer', 'Delay', 'CookieExpiration',
            'backgroundColor', 'borderColor', 'borderWidth', 'style', 'borderStyle', 'backgroundColorHeader', 'colorHeader', 'backgroundColorButton', 'colorButton',
            'stickerActive','stickerDisplay','stickerMargin','stickerPadding','stickerPosition','backgroundColorSticker', 'borderColorSticker', 'borderWidthSticker', 'styleSticker', 'borderStyleSticker','colorSticker'            
            );
    }

    protected static function updateRestrictions($id_group)
    {
        Group::truncateModulesRestrictions((int) $id_group);
        $shops = Shop::getShops(true, null, true);
        $auth_modules = array();
        $modules = Module::getModulesInstalled();
        foreach ($modules as $module) {
            $auth_modules[] = $module['id_module'];
        }
        if (is_array($auth_modules)) {
            return Group::addModulesRestrictions($id_group, $auth_modules, $shops);
        }
    }
    /* Get all CMS blocks */

    public static function getAllCMSStructure($id_shop = false)
    {
        $categories = self::getCMSCategories();
        $id_shop = ($id_shop !== false) ? $id_shop : Context::getContext()->shop->id;
        $all = array();
        foreach ($categories as $value) {
            $array_key = 'category_' . $value['id_cms_category'];
            $value['name'] = str_repeat("- ", $value['level_depth']) . $value['name'];
            $value['id'] = $array_key;
            $all[$array_key] = $value;
            $pages = self::getCMSPages($value['id_cms_category'], $id_shop);
            foreach ($pages as $page) {
                $array_key = 'cms_' . $page['id_cms'];
                $page['name'] = str_repeat("&nbsp;&nbsp;", $value['level_depth']) . $page['meta_title'];
                $page['id'] = $array_key;
                $all[$array_key] = $page;
            }
        }
        return $all;
    }

    public static function getCMSPages($id_cms_category, $id_shop = false)
    {
        $id_shop = ($id_shop !== false) ? $id_shop : Context::getContext()->shop->id;

        $sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `' . _DB_PREFIX_ . 'cms` c
			INNER JOIN `' . _DB_PREFIX_ . 'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `' . _DB_PREFIX_ . 'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE c.`id_cms_category` = ' . (int) $id_cms_category . '
			AND cs.`id_shop` = ' . (int) $id_shop . '
			AND cl.`id_lang` = ' . (int) Context::getContext()->language->id . '
			AND c.`active` = 1
			ORDER BY `position`';

        return Db::getInstance()->executeS($sql);
    }

    public static function getCMSCategories($recursive = false, $parent = 0)
    {
        if ($recursive === false) {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
					FROM `' . _DB_PREFIX_ . 'cms_category` bcp
					INNER JOIN `' . _DB_PREFIX_ . 'cms_category_lang` cl
					ON (bcp.`id_cms_category` = cl.`id_cms_category`)
					WHERE cl.`id_lang` = ' . (int) Context::getContext()->language->id;
            if ($parent)
                $sql .= ' AND bcp.`id_parent` = ' . (int) $parent;

            return Db::getInstance()->executeS($sql);
        }
        else {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
					FROM `' . _DB_PREFIX_ . 'cms_category` bcp
					INNER JOIN `' . _DB_PREFIX_ . 'cms_category_lang` cl
					ON (bcp.`id_cms_category` = cl.`id_cms_category`)
					WHERE cl.`id_lang` = ' . (int) Context::getContext()->language->id;
            if ($parent)
                $sql .= ' AND bcp.`id_parent` = ' . (int) $parent;

            $results = Db::getInstance()->executeS($sql);
            $categories = array();
            foreach ($results as $result) {
                $sub_categories = self::getCMSCategories(true, $result['id_cms_category']);
                if ($sub_categories && count($sub_categories) > 0)
                    $result['sub_categories'] = $sub_categories;
                $categories[] = $result;
            }

            return isset($categories) ? $categories : false;
        }
    }
}
