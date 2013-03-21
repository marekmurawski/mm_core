<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * MultiEdit Plugin for Wolf CMS
 * Provides convenient interface to quickly edit multiple pages metadata.
 *
 * @package Plugins
 * @subpackage multiedit
 *
 * @author Marek Murawski <http://marekmurawski.pl>
 * @copyright Marek Murawski, 2012
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if ( !defined('IN_CMS') ) {
    exit();
}

Plugin::setInfos(array(
            'id'                   => 'mm_core',
            'title'                => __('mmCore library'),
            'description'          => __('Support plugin for mm plugins.'),
            'version'              => '0.1.0',
            'license'              => 'GPL',
            'author'               => 'Marek Murawski',
            'website'              => 'http://marekmurawski.pl/',
            'update_url'           => 'http://marekmurawski.pl/static/wolfplugins/plugin-versions.xml',
            'require_wolf_version' => '0.7.3'
));

AutoLoader::addFile('mmCore', dirname(__FILE__) . '/lib/mmCore.php');

Observer::observe('mm_core_stylesheet', 'mmCore::callback_mm_core_stylesheet');
