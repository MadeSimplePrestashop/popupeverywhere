<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
include_once dirname(__FILE__) . '/models/PE.php';

class Popupeverywhere extends Module
{

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'popupeverywhere';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Kuzmany';
        $this->need_instance = 0;
        $this->module_key = 'd8d8628d8eac6cca423206473a40ef16';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Popup Everywhere');
        $this->description = $this->l('Popups wherever you want.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include_once(dirname(__FILE__) . '/sql/install.php');

        $this->context->controller->getLanguages();
        $lang_array = array();
        $id_parent = 0;
        foreach ($this->context->controller->_languages as $language) {
            $lang_array[(int) $language['id_lang']] = $this->displayName;
        }
        $this->installAdminTab($lang_array, 'AdminPE', $id_parent);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        include_once(dirname(__FILE__) . '/sql/uninstall.php');
        $this->uninstallAdminTab('AdminPE');
        return parent::uninstall();
    }

    private function installAdminTab($name, $className, $parent)
    {
        $tab = new Tab();
        $tab->name = $name;
        $tab->class_name = $className;
        $tab->id_parent = $parent;
        $tab->module = $this->name;
        $tab->add();
        return $tab;
    }

    private function uninstallAdminTab($className)
    {
        $tab = new Tab((int) Tab::getIdFromClassName($className));
        $tab->delete();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        Tools::redirectAdmin('index.php?controller=AdminPE&token=' . Tools::getAdminTokenLite('AdminPE'));
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPopupeverywhereModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (in_array(Dispatcher::getInstance()->getController(), array('AdminPE'))) {
            $this->context->controller->addCSS($this->_path . '/views/css/admin.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addjqueryPlugin('cooki-plugin');
        $this->context->controller->addJS($this->_path . '/views/js/ouibounce.js');
        $this->context->controller->addCSS($this->_path . '/views/css/ouibounce.css');
    }

    public function hookDisplayFooter()
    {
        $popup = $this->load_popup();
        if ($popup) {
            Context::getContext()->smarty->assign(array(
                'pe' => $popup,
            ));
            return $this->display(__FILE__, 'views/templates/hook/popup.tpl');
        }
    }

    private function load_popup()
    {
        $parms = array();
        $parms['active'] = 1;
        $popups = PE::getAll($parms);
        if (empty($popups))
            return;

        foreach ($popups as $key => $popup) {
            if ($popup['date_to'] && $popup['date_to'] != '0000-00-00 00:00:00' && time() > strtotime($popup['date_to'])) {
                continue;
            }

            $popup['options'] = $popups[$key]['options'] = Tools::jsonDecode($popup['options']);

            $view = array();
            (isset($popup['options']->categories) && empty($popup['options']->categories) == false ? array_push($view, 'category') : '');
            (isset($popup['options']->cms) && empty($popup['options']->cms) == false ? array_push($view, 'cms') : '');
            (isset($popup['options']->controllers) && empty($popup['options']->controllers) == false ? $view = array_merge(array_values($view), array_values($popup['options']->controllers)) : '');
            (isset($popup['options']->products) && empty($popup['options']->products) == false ? array_push($view, 'product') : '');

            if (empty($view) == false) {
                if (!in_array(Dispatcher::getInstance()->getController(), $view)) {
                    continue;
                }
                if (Dispatcher::getInstance()->getController() == 'category' && in_array(Tools::getValue('id_category'), $popup['options']->categories) == false) {
                    continue;
                }
                if (Dispatcher::getInstance()->getController() == 'product' && !empty($popup['options']->products) && (!Tools::getIsset('id_product') || !in_array(Tools::getValue('id_product'), $popup['options']->products))) {
                    continue;
                }
                if (Dispatcher::getInstance()->getController() == 'cms') {
                    $categories = array();
                    $cms = array();
                    foreach ($popup['options']->cms as $c) {
                        if (strpos($c, 'category_') !== false)
                            $categories[] = str_replace('category_', '', $c);
                        if (strpos($c, 'cms_') !== false)
                            $cms[] = str_replace('cms_', '', $c);
                    }
                    if (!in_array(Tools::getValue('id_cms'), $cms) && !in_array(Tools::getValue('id_cms_category'), $categories))
                        continue;
                }
            }
            return $popup;
        }
    }
}
