<?php
/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
require_once(_PS_MODULE_DIR_ . 'popupeverywhere/models/PE.php');

class AdminPEController extends ModuleAdminController
{

    public function __construct()
    {

        $this->bootstrap = true;
        $this->show_toolbar = true;
        $this->show_toolbar_options = true;
        $this->show_page_header_toolbar = true;

        $this->table = PE::$definition['table'];
        $this->className = 'PE';

        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');

        Shop::addTableAssociation($this->table, array('type' => 'shop'));
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        parent::__construct();
    }

    public function initContent()
    {
        if (Tools::getIsset('duplicate' . $this->table)) {
            PE::duplicate();
        }
        parent::initContent();
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::getIsset('delete' . $this->table)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminPE'));
        } elseif (Tools::getIsset('delete' . $this->table)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminPE'));
        } elseif (Tools::getIsset('submitStay')) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminPE') . '&' . PE::$definition['primary'] . '=' . $this->object->id . '&update' . $this->table);
        } elseif (Tools::isSubmit('submitAdd' . $this->table)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminPE'));
        }
    }

    public function renderForm()
    {

        $obj = $this->loadObject(true);
        if (!$obj) {
            return;
        }
        if (is_object($obj)) {
            $options = Tools::jsonDecode($obj->options);
        } else {
            $options = '';
        }

        $this->fields_form = array(
            'legend' => array(
                'tinymce' => true,
                'title' => $this->l('Popup Everyhwere'),
                'icon' => 'icon-cogs'
            ),
            'tabs' => array(
                'popup' => $this->l('Popup'),
                'options' => $this->l('Options')
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => PE::$definition['primary'],
                    'tab' => 'options'
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'default_value' => isset($obj->active) ? $obj->active : 1
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'datetime',
                    'label' => $this->l('Active until'),
                    'name' => 'date_to',
                    'desc' => $this->l('Leave empty, If discount never expire'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alias'),
                    'name' => 'alias',
                    'class' => 'input fixed-width-md',
                    'desc' => $this->l('Non-public'),
                    'tab' => 'popup'
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'text',
                    'label' => $this->l('Header'),
                    'lang' => true,
                    'name' => 'caption',
                    'desc' => $this->l('Optional')
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'text',
                    'label' => $this->l('Text'),
                    'lang' => true,
                    'name' => 'html'
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'text',
                    'label' => $this->l('Button text'),
                    'name' => 'button',
                    'lang' => true,
                    'desc' => $this->l('Optional')
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'text',
                    'label' => $this->l('Button link'),
                    'name' => 'link',
                    'lang' => true,
                    'desc' => $this->l('Optional')
                ),
                array(
                    'tab' => 'popup',
                    'type' => 'text',
                    'label' => $this->l('Close text'),
                    'name' => 'close',
                    'lang' => true,
                    'desc' => $this->l('Optional')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
                'name' => 'submit',
            )
        );

        $this->fields_form['input'][] = array(
            'tab' => 'options',
            'type' => 'color',
            'label' => $this->l('Background color for header'),
            'name' => 'backgroundColorHeader',
            'default_value' => isset($options->backgroundColorHeader) ? $options->backgroundColorHeader : '#252525',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'options',
            'type' => 'color',
            'label' => $this->l('Background color'),
            'name' => 'backgroundColor',
            'default_value' => isset($options->backgroundColor) ? $options->backgroundColor : '#f6f6f6',
        );



        $this->fields_form['input'][] = array(
            'tab' => 'options',
            'type' => 'text',
            'label' => 'Border width',
            'name' => 'borderWidth',
            'class' => 'input fixed-width-sm',
            'default_value' => isset($options->borderWidth) ? $options->borderWidth : '3px',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'options',
            'type' => 'select',
            'label' => $this->l('Border Style'),
            'name' => 'borderStyle',
            'options' => array(
                'query' => array(
                    array(
                        'value' => 'none',
                        'label' => $this->l('None')
                    ),
                    array(
                        'value' => 'hidden',
                        'label' => $this->l('Hidden')
                    ),
                    array(
                        'value' => 'dotted',
                        'label' => $this->l('Dotted')
                    ),
                    array(
                        'value' => 'solid',
                        'label' => $this->l('Solid')
                    ),
                    array(
                        'value' => 'double',
                        'label' => $this->l('double')
                    ),
                    array(
                        'value' => 'groove',
                        'label' => $this->l('Groove')
                    ),
                    array(
                        'value' => 'ridge',
                        'label' => $this->l('Ridge')
                    ),
                    array(
                        'value' => 'inset',
                        'label' => $this->l('Inset')
                    ),
                    array(
                        'value' => 'outset',
                        'label' => $this->l('Outset')
                    )
                ),
                'id' => 'value',
                'name' => 'label'
            ),
            'default_value' => isset($options->borderStyle) ? $options->borderStyle : 'solid',
        );


        $this->fields_form['input'][] = array(
            'tab' => 'options',
            'type' => 'color',
            'label' => $this->l('Border color'),
            'name' => 'borderColor',
            'default_value' => isset($options->borderColor) ? $options->borderColor : '#e9e9e9',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'options',
            'type' => 'text',
            'label' => 'Inline CSS style',
            'name' => 'style',
            'desc' => $this->l('For advanced user'),
            'default_value' => isset($options->style) ? $options->style : '',
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'tab' => 'display',
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
                'tab' => 'popup'
            );
        }


        $this->page_header_toolbar_btn['save'] = array(
            'href' => 'javascript:$("#' . $this->table . '_form button:submit").click();',
            'desc' => $this->l('Save')
        );
        $this->page_header_toolbar_btn['save-and-stay'] = array(
            'short' => 'SaveAndStay',
            'href' => 'javascript:$("#' . $this->table . '_form").attr("action", $("#' . $this->table . '_form").attr("action")+"&submitStay");$("#' . $this->table . '_form button:submit").click();',
            'desc' => $this->l('Save and stay'),
            'force_desc' => true,
        );

        $this->page_header_toolbar_btn['edit'] = array(
            'href' => self::$currentIndex . '&token=' . $this->token,
            'desc' => $this->l('Return'),
            'icon' => 'process-icon-cancel'
        );


        return parent::

            renderForm();
    }

    public function renderList()
    {

        $this->fields_list = array(
            'id_popupeverywhere' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25,
                'orderby' => false,
                'search' => false,
            ),
            'alias' => array(
                'title' => $this->l('Alias'),
                'type' => 'text',
                'orderby' => false,
                'search' => false
            ),
            'date_to' => array(
                'title' => $this->l('Active until'),
                'type' => 'text',
                'orderby' => false,
                'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'search' => false
            )
        );

        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new'),
            'icon' => 'process-icon-new'
        );

        return parent::renderList();
    }
}
