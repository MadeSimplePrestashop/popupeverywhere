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
    public $html;
    public $close;
    public $button;
    public $header;
    public $link;

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
            'html' => array('type' => self::TYPE_HTML, 'lang' => true),
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
        return array('Sensitivity', 'Aggressive', 'Timer', 'Delay', 'CookieExpiration', 
            'backgroundColor', 'borderColor', 'borderWidth', 'style', 'borderStyle', 'backgroundColorHeader');
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
}
