<?php

/* Security measure */
if ( !defined('IN_CMS') )
    exit();

Flash::set('success', __('Successfully activated <b>mm_core</b> plugin'));
exit();
