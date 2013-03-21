<?php

/* Security measure */
if ( !defined('IN_CMS') )
    exit();

Plugin::setInfos(array(
            'id'                   => 'mm_core',
            'title'                => __('mmCore library'),
            'description'          => __('Support plugin for mm plugins.'),
            'version'              => '0.1.0',
            'license'              => 'GPL',
            'author'               => 'Marek Murawski',
            'website'              => 'http://marekmurawski.pl/',
            'require_wolf_version' => '0.7.3'
));

AutoLoader::addFile('mmCore', dirname(__FILE__) . '/lib/mmCore.php');

Observer::observe('mm_core_stylesheet', 'mmCore::callback_mm_core_stylesheet');
