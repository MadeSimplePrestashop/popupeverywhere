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
                'options' => $this->l('Content'),
                'style' => $this->l('Style'),
                'sticker' => $this->l('Sticker'),
                'display' => $this->l('Display'),
                'events' => $this->l('GA Events'),
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => PE::$definition['primary'],
                    'tab' => 'options'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alias'),
                    'name' => 'alias',
                    'class' => 'input fixed-width-md',
                    'tab' => 'popup'
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
                    'tab' => 'options',
                    'type' => 'text',
                    'label' => $this->l('Header'),
                    'lang' => true,
                    'name' => 'header',
                    'desc' => $this->l('Optional')
                ),
                array(
                    'tab' => 'options',
                    'type' => 'textarea',
                    'label' => $this->l('Content'),
                    'lang' => true,
                    'name' => 'content',
                    'wysiwyg' => 1,
                    'autoload_rte' => true
                ),
                array(
                    'tab' => 'options',
                    'type' => 'textarea',
                    'label' => $this->l('HTML'),
                    'name' => 'html',
                    'lang' => true,
                ),
                array(
                    'tab' => 'options',
                    'type' => 'text',
                    'label' => $this->l('Button text'),
                    'name' => 'button',
                    'lang' => true,
                    'desc' => $this->l('Optional')
                ),
                array(
                    'tab' => 'options',
                    'type' => 'text',
                    'label' => $this->l('Button link'),
                    'name' => 'link',
                    'lang' => true,
                    'desc' => $this->l('Optional')
                ),
                array(
                    'tab' => 'options',
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
            'tab' => 'style',
            'type' => 'color',
            'label' => $this->l('Background color'),
            'name' => 'backgroundColor',
            'default_value' => isset($options->backgroundColor) ? $options->backgroundColor : '#f6f6f6',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'style',
            'type' => 'text',
            'label' => 'Border width',
            'name' => 'borderWidth',
            'class' => 'input fixed-width-sm',
            'default_value' => isset($options->borderWidth) ? $options->borderWidth : '1px',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'style',
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
            'tab' => 'style',
            'type' => 'color',
            'label' => $this->l('Border color'),
            'name' => 'borderColor',
            'default_value' => isset($options->borderColor) ? $options->borderColor : '#e9e9e9',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'style',
            'type' => 'text',
            'label' => 'Inline CSS style for modal',
            'name' => 'style',
            'desc' => $this->l('For advanced user'),
            'default_value' => isset($options->style) ? $options->style : '',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'style',
            'type' => 'color',
            'label' => $this->l('Font header color'),
            'name' => 'colorHeader',
            'default_value' => isset($options->colorHeader) ? $options->colorHeader : '#ffffff',
        );
        $this->fields_form['input'][] = array(
            'tab' => 'style',
            'type' => 'color',
            'label' => $this->l('Background header color'),
            'name' => 'backgroundColorHeader',
            'default_value' => isset($options->backgroundColorHeader) ? $options->backgroundColorHeader : '#252525',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'style',
            'type' => 'color',
            'label' => $this->l('Font button color'),
            'name' => 'colorButton',
            'default_value' => isset($options->colorButton) ? $options->colorButton : '#ffffff',
        );
        $this->fields_form['input'][] = array(
            'tab' => 'style',
            'type' => 'color',
            'label' => $this->l('Background button color'),
            'name' => 'backgroundColorButton',
            'default_value' => isset($options->backgroundColorButton) ? $options->backgroundColorButton : '#4ab471',
        );

        $this->fields_form['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Display on controllers'),
            'name' => 'controllers[]',
            'class' => 'chosen element',
            'multiple' => true,
            'options' => array(
                'query' => $this->getControllers(),
                'id' => 'name',
                'name' => 'name'
            ),
            'tab' => 'display',
            'default_value' => $options->controllers
        );

        $this->fields_form['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Display on products page'),
            'name' => 'products[]',
            'class' => 'chosen element',
            'multiple' => true,
            'options' => array(
                'query' => Product::getProducts((int) Context::getContext()->language->id, 0, 1000, 'p.id_product', 'asc', false, true),
                'id' => 'id_product',
                'name' => 'name'
            ),
            'tab' => 'display',
            'default_value' => $options->products
        );

        $selected_categories = array();
        if (isset($options->categories) && empty($options->categories) == false) {
            $selected_categories = $options->categories;
        }

        $root_category = Category::getRootCategory();
        $root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);


        $this->fields_form['input'][] = array(
            'tab' => 'display',
            'type' => 'categories',
            'label' => $this->l('Categories'),
            'name' => 'categories',
            'desc' => $this->l('Empty is disabled.'),
            'tree' => array(
                'use_search' => false,
                'id' => 'categoryBox',
                'use_checkbox' => true,
                'selected_categories' => $selected_categories,
            ),
            'values' => array(
                'trads' => array(
                    'Root' => $root_category,
                    'selected' => $this->l('Selected'),
                    'Collapse All' => $this->l('Collapse All'),
                    'Expand All' => $this->l('Expand All'),
                    'Check All' => $this->l('Check All'),
                    'Uncheck All' => $this->l('Uncheck All')
                ),
                'selected_cat' => $selected_categories,
                'input_name' => 'categories[]',
                'use_radio' => false,
                'use_search' => false,
                'disabled_categories' => array(),
                'top_category' => Category::getTopCategory(),
                'use_context' => true,
            )
        );

        $this->fields_form['input'][] = array(
            'tab' => 'display',
            'type' => 'select',
            'multiple' => true,
            'size' => 7,
            'label' => $this->l('CMS categories and pages'),
            'name' => 'cms[]',
            'hint' => $this->l('It\'s optional.'),
            'desc' => $this->l('Optional. CTRL+click for select/unselect more options'),
            'options' => array(
                'query' => PE::getAllCMSStructure(),
                'id' => 'id',
                'name' => 'name'
            )
            , 'default_value' => isset($options->cms) ? $options->cms : array()
        );

        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'text',
            'label' => $this->l('Cookie expiration'),
            'name' => 'CookieExpiration',
            'suffix' => 'days',
            'class' => 'fixed-width-sm',
            'hint' => $this->l('Ouibounce sets a cookie by default to prevent the modal from appearing more than once per user. You can add a cookie expiration (in days) using cookieExpire to adjust the time period before the modal will appear again for a user. By default, the cookie will expire at the end of the session, which for most browsers is when the browser is closed entirely.'),
            'default_value' => isset($options->CookieExpiration) ? $options->CookieExpiration : 7
        );

        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'text',
            'label' => $this->l('Sensitivity'),
            'name' => 'Sensitivity',
            'suffix' => 'px',
            'class' => 'fixed-width-sm',
            'hint' => $this->l('Ouibounce fires when the mouse cursor moves close to (or passes) the top of the viewport. You can define how far the mouse has to be before Ouibounce fires. The higher value, the more sensitive, and the more quickly the event will fire. '),
            'default_value' => isset($options->Sensitivity) ? $options->Sensitivity : 20
        );
        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'switch',
            'label' => $this->l('Aggressive mode'),
            'hint' => $this->l('If you enable aggressive, the modal will fire any time the page is reloaded, for the same user.'),
            'name' => 'Aggressive',
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
            'default_value' => isset($options->Aggressive) ? $options->Aggressive : false
        );

        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'text',
            'label' => $this->l('Timer'),
            'desc' => $this->l('Set a min time before Ouibounce fires'),
            'name' => 'Timer',
            'suffix' => 'ms',
            'class' => 'fixed-width-sm',
            'hint' => $this->l('By default, Ouibounce won\'t fire in the first second to prevent false positives, as it\'s unlikely the user will be able to exit the page within less than a second. If you want to change the amount of time that firing is surpressed for, you can pass in a number of milliseconds.'),
            'default_value' => isset($options->Timer) ? $options->Timer : 0
        );
        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'text',
            'label' => $this->l('Delay'),
            'name' => 'Delay',
            'suffix' => 'ms',
            'class' => 'fixed-width-sm',
            'hint' => $this->l('By default, Ouibounce will show the modal immediately. You could instead configure it to wait x milliseconds before showing the modal. If the user\'s mouse re-enters the body before delay ms have passed, the modal will not appear. This can be used to provide a "grace period" for visitors instead of immediately presenting the modal window.'),
            'default_value' => isset($options->Delay) ? $options->Delay : 0
        );
        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'text',
            'label' => $this->l('Auto Popup'),
            'name' => 'AutoPopup',
            'suffix' => 'sec',
            'class' => 'fixed-width-sm',
            'desc' => $this->l('Zero is disabled'),
            'hint' => $this->l('By default,  Module display a modal before a user leaves your website. Auto Popup display after certain time, forcibly.'),
            'default_value' => isset($options->AutoPopup) ? $options->AutoPopup : 0
        );

        $this->fields_form['input'][] = array(
            'tab' => 'popup',
            'type' => 'switch',
            'label' => $this->l('Google Analytics Events'),
            'hint' => $this->l('If you enable it, these events will be sent to Google Analytics - OpenWindow, ClickSticker'),
            'name' => 'GoogleAnalytics',
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
            'default_value' => isset($options->GoogleAnalytics) ? $options->GoogleAnalytics : false
        );
        if (($module = Module::getInstanceByName('discountcountdown')) && $module->active) {
            $this->fields_form['input'][] = array(
                'tab' => 'popup',
                'type' => 'switch',
                'label' => $this->l('Dependence on module Discount with Countdown'),
                'desc' => $this->l('If discount is running from module Discount with Countdown, popup will be hidden. This great module find on addons.prestashop.com'),
                'name' => 'DiscountCountdown',
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
                'default_value' => isset($options->DiscountCountdown) ? $options->DiscountCountdown : false
            );
        }


        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'switch',
            'label' => $this->l('Active'),
            'name' => 'stickerActive',
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
            'default_value' => isset($options->stickerActive) ? $options->stickerActive : 1
        );

        $this->fields_form['input'][] = array(
                'tab' => 'sticker',
                'type' => 'radio',
                'label' => $this->l('Display'),
                'name' => 'stickerDisplay',
                'values' => array(
                    array(
                        'value' => 'after',
                        'id' => 'after',
                        'label' => $this->l('After closing popups')
                    ),
                    array(
                        'value' => 'before',
                        'id' => 'before',
                        'label' => $this->l('Before opening popups')
                    ),
                ),
                'default_value' => isset($options->stickerDisplay) ? $options->stickerDisplay : 'after'
        );
        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'textarea',
            'label' => $this->l('Content'),
            'lang' => true,
            'name' => 'sticker',
            'wysiwyg' => 1,
            'autoload_rte' => true
        );

        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'color',
            'label' => $this->l('Background color'),
            'name' => 'backgroundColorSticker',
            'default_value' => isset($options->backgroundColorSticker) ? $options->backgroundColorSticker : '#f6f6f6',
        );


        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'text',
            'label' => 'Border width',
            'name' => 'borderWidthSticker',
            'class' => 'input fixed-width-sm',
            'default_value' => isset($options->borderWidthSticker) ? $options->borderWidthSticker : '1px',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'select',
            'label' => $this->l('Border Style'),
            'name' => 'borderStyleSticker',
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
            'default_value' => isset($options->borderStyleSticker) ? $options->borderStyleSticker : 'solid',
        );


        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'color',
            'label' => $this->l('Border color'),
            'name' => 'borderColorSticker',
            'default_value' => isset($options->borderColorSticker) ? $options->borderColorSticker : '#e9e9e9',
        );
        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'text',
            'label' => $this->l('Padding'),
            'name' => 'stickerPadding',
            'class' => 'fixed-width-lg',
            'desc' => $this->l('top right bottom left'),
            'default_value' => isset($options->stickerPadding) ? $options->stickerPadding : '10px 10px 10px 10px',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'text',
            'label' => $this->l('Margin'),
            'name' => 'stickerMargin',
            'class' => 'fixed-width-lg',
            'desc' => $this->l('top right bottom left'),
            'default_value' => isset($options->stickerMargin) ? $options->stickerMargin : '10px 10px 10px 10px',
        );


        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'select',
            'label' => $this->l('Position'),
            'name' => 'stickerPosition',
            'default_value' => (isset($options->stickerPosition) ? $options->stickerPosition : ''),
            'options' => array(
                'query' => array(
                    array(
                        'id' => 'left:0; top:0;',
                        'name' => $this->l('left top')
                    ),
                    array(
                        'id' => 'left:0; bottom:0;',
                        'name' => $this->l('left bottom')
                    ),
                    array(
                        'id' => 'right:0; top:0;',
                        'name' => $this->l('right top')
                    ),
                    array(
                        'id' => 'right:0; bottom:0;',
                        'name' => $this->l('right bottom')
                    )
                ),
                'id' => 'id',
                'name' => 'name',
            ),
        );


        $this->fields_form['input'][] = array(
            'tab' => 'sticker',
            'type' => 'text',
            'label' => 'Inline CSS style for sticker',
            'name' => 'styleSticker',
            'desc' => $this->l('For advanced user'),
            'default_value' => isset($options->styleSticker) ? $options->styleSticker : '',
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

    private function getControllers()
    {
        $cache_id = __CLASS__ . __FUNCTION__ . '11';

        if (Cache::getInstance()->exists($cache_id)) {
            $controllers_array = Cache::getInstance()->get($cache_id);
        } else {

            // @todo do something better with controllers
            $controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
            ksort($controllers);
            foreach (array_keys($controllers) as $k) {
                $controllers_array[]['name'] = $k;
            }

            $modules_controllers_type = array('front' => $this->l('Front modules controller'));
            foreach (array_keys($modules_controllers_type) as $type) {
                $all_modules_controllers = Dispatcher::getModuleControllers($type);
                foreach ($all_modules_controllers as $module => $modules_controllers) {
                    foreach ($modules_controllers as $cont) {
                        $controllers_array[]['name'] = 'module-' . $module . '-' . $cont;
                    }
                }
            }

            $timeout = 3600 * 24;
            Cache::getInstance()->set($cache_id, $controllers_array, $timeout);
        }
        return $controllers_array;
    }
}
