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
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'popupeverywhere` (
    `id_popupeverywhere` int(11) NOT NULL AUTO_INCREMENT,
    `alias` varchar(50) NOT NULL,
    `options` TEXT,    
    `date_to` DATETIME NULL,
    `position` int(3) NOT NULL,
    `active` int(3) NOT NULL,    
    PRIMARY KEY  (`id_popupeverywhere`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = '
CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'popupeverywhere_lang` (
  `id_popupeverywhere` int(11),
  `id_lang` int(3) NOT NULL,
  `header` varchar(255),
  `button` varchar(100),
  `link` varchar(255),
  `close` varchar(100),
  `html` TEXT,
  PRIMARY KEY (`id_popupeverywhere`,id_lang)
) ENGINE = ' . _MYSQL_ENGINE_ . '  ';

$sql[] = ''
    . 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'popupeverywhere_shop` (
      `id_popupeverywhere` int(10)  NOT NULL,
      `id_shop` int(3) unsigned NOT NULL,
      PRIMARY KEY (`id_popupeverywhere`, `id_shop`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
    . '';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
