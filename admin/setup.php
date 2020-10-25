<?php

//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//               This module; Shiori Copyright (c) 2005 suin                 //
//                          <http://www.suin.jp>                             //
//  ------------------------------------------------------------------------ //
require dirname(__DIR__, 3) . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/include/cp_header.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

$mydirname = basename(dirname(__DIR__));
$mydir = XOOPS_URL . '/modules/' . $mydirname . '/';

// セキュリティチェック
if (!isset($module) || !is_object($module)) {
    $module = $xoopsModule;
} elseif (!is_object($xoopsModule)) {
    die('$xoopsModule is not set');
}

//オペレーション初期化
$op = $_REQUEST['op'] ?? 'default';

switch ($op) {
    default:
    case 'default':
        xoops_cp_header();

        $mid = $xoopsModule->getVar('mid');
        $module = $xoopsModule;

        echo '<h4 style="text-align:left">' . $module->getVar('name') . ' - ' . _AM_ONSETUP . '</h4>';
        echo '<p>';
        echo _AM_INSTALL . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        echo '<span style="color:red;">' . _AM_MODULE_SETTING . '</span>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        $config = &$configHandler->getConfigs(new Criteria('conf_modid', $mid));
        $count = count($config);
        if ($count > 0) {
            echo _AM_MODCONFIG . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        }
        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/admin/myblocksadmin.php')) {
            echo _AM_GROUP_BLOCK . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        }
        echo _AM_FINISH . '<p>';

        $form = new XoopsThemeForm(_AM_MODULE_SETTING, 'form', 'setup.php');
        $form->addElement(new XoopsFormLabel(_AM_MOD_ICON, '<img src="' . XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/' . $module->getInfo('image') . '" alt="' . $module->getVar('name', 'E') . '" border="0">'));
        $form->addElement(new XoopsFormText(_AM_MOD_NAME, 'newname', 20, 150, $module->getVar('name', 'E')));
        $form->addElement(new XoopsFormLabel(_AM_MOD_VERSION, round($module->getVar('version') / 100, 2)));
        $form->addElement(new XoopsFormLabel(_AM_MOD_DATE, formatTimestamp($module->getVar('last_update'), 'm')));
        if (1 == $module->getVar('hasmain')) {
            $form->addElement(new XoopsFormText(_AM_MOD_SORT, 'weight', 5, 5, $module->getVar('weight')));
        }
        $form->addElement(new XoopsFormHidden('op', 'modsave'));
        $form->addElement(new XoopsFormButton('', 'submit', _AM_NEXT, 'submit'));
        $form->display();

        xoops_cp_footer();
        break;
    case 'modsave':
        $mid = $xoopsModule->getVar('mid');
        $module = $xoopsModule;
        $name = $_POST['newname'] ?? $module->getVar('name');
        $weight = $_POST['weight'] ?? $module->getVar('weight');

        $moduleHandler = xoops_getHandler('module');
        $module = $moduleHandler->get($mid);
        $module->setVar('weight', $weight);
        $module->setVar('name', $name);
        $myts = MyTextSanitizer::getInstance();
        if (!$moduleHandler->insert($module)) {
            redirect_header('setup.php?op=preferance', 5, $module->getHtmlErrors());
        }
        redirect_header('setup.php?op=preferance', 2, _AM_DBUPDATED);

        break;
    case 'preferance':
        $configHandler = xoops_getHandler('config');
        $mid = $xoopsModule->getVar('mid');
        if (empty($mid)) {
            header('Location: setup.php');

            exit();
        }
        $config = &$configHandler->getConfigs(new Criteria('conf_modid', $mid));
        $count = count($config);
        if ($count < 1) {
            header('Location: setup.php?op=finish');

            exit();
        }
        require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
        $form = new XoopsThemeForm(_AM_MODCONFIG, 'pref_form', 'setup.php');
        $moduleHandler = xoops_getHandler('module');
        $module = $moduleHandler->get($mid);
        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/modinfo.php')) {
            require_once XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/modinfo.php';
        }

        // if has comments feature, need comment lang file
        if (1 == $module->getVar('hascomments')) {
            require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/comment.php';
        }
        // RMV-NOTIFY
        // if has notification feature, need notification lang file
        if (1 == $module->getVar('hasnotification')) {
            require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/notification.php';
        }

        $modname = $module->getVar('name');
        for ($i = 0; $i < $count; $i++) {
            $title = (!defined($config[$i]->getVar('conf_desc')) || '' == constant($config[$i]->getVar('conf_desc'))) ? constant($config[$i]->getVar('conf_title')) : constant($config[$i]->getVar('conf_title')) . '<br><br><span style="font-weight:normal;">' . constant(
                $config[$i]->getVar('conf_desc')
            ) . '</span>';

            switch ($config[$i]->getVar('conf_formtype')) {
                case 'textarea':
                    $myts = MyTextSanitizer::getInstance();
                    if ('array' == $config[$i]->getVar('conf_valuetype')) {
                        // this is exceptional.. only when value type is arrayneed a smarter way for this

                        $ele = ('' != $config[$i]->getVar('conf_value')) ? new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), htmlspecialchars(implode('|', $config[$i]->getConfValueForOutput()), ENT_QUOTES | ENT_HTML5), 5, 50) : new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), '', 5, 50);
                    } else {
                        $ele = new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), htmlspecialchars($config[$i]->getConfValueForOutput(), ENT_QUOTES | ENT_HTML5), 5, 50);
                    }
                    break;
                case 'select':
                    $ele = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
                    $options = &$configHandler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
                    $opcount = count($options);
                    for ($j = 0; $j < $opcount; $j++) {
                        $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');

                        $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');

                        $ele->addOption($optval, $optkey);
                    }
                    break;
                case 'select_multi':
                    $ele = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
                    $options = &$configHandler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
                    $opcount = count($options);
                    for ($j = 0; $j < $opcount; $j++) {
                        $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');

                        $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');

                        $ele->addOption($optval, $optkey);
                    }
                    break;
                case 'yesno':
                    $ele = new XoopsFormRadioYN($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), _YES, _NO);
                    break;
                case 'group':
                    require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                    $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                    break;
                case 'group_multi':
                    require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                    $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                    break;
                // RMV-NOTIFY: added 'user' and 'user_multi'
                case 'user':
                    require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                    $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                    break;
                case 'user_multi':
                    require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                    $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                    break;
                case 'password':
                    $myts = MyTextSanitizer::getInstance();
                    $ele = new XoopsFormPassword($title, $config[$i]->getVar('conf_name'), 50, 255, htmlspecialchars($config[$i]->getConfValueForOutput(), ENT_QUOTES | ENT_HTML5));
                    break;
                case 'textbox':
                default:
                    $myts = MyTextSanitizer::getInstance();
                    $ele = new XoopsFormText($title, $config[$i]->getVar('conf_name'), 50, 255, htmlspecialchars($config[$i]->getConfValueForOutput(), ENT_QUOTES | ENT_HTML5));
                    break;
            }

            $hidden = new XoopsFormHidden('conf_ids[]', $config[$i]->getVar('conf_id'));

            $form->addElement($ele);

            $form->addElement($hidden);

            unset($ele);

            unset($hidden);
        }
        $form->addElement(new XoopsFormHidden('op', 'prefsave'));
        $form->addElement(new XoopsFormButton('', 'button', _AM_NEXT, 'submit'));
        xoops_cp_header();
        echo '<h4 style="text-align:left">' . $module->getVar('name') . ' - ' . _AM_ONSETUP . '</h4>';
        echo '<p>';
        echo _AM_INSTALL . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        echo _AM_MODULE_SETTING . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        $config = &$configHandler->getConfigs(new Criteria('conf_modid', $mid));
        $count = count($config);
        if ($count > 0) {
            echo '<span style="color:red;">' . _AM_MODCONFIG . '</span>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        }
        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/admin/myblocksadmin.php')) {
            echo _AM_GROUP_BLOCK . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        }
        echo _AM_FINISH . '<p>';
        $form->display();
        xoops_cp_footer();
        exit();

        break;
    case 'prefsave':
        $mid = $xoopsModule->getVar('mid');
        $moduleHandler = xoops_getHandler('module');
        $module = $moduleHandler->get($mid);
        $conf_ids = $_POST['conf_ids'] ?? [];
        $count = count($_POST['conf_ids']);
        $tpl_updated = false;
        $theme_updated = false;
        $startmod_updated = false;
        $lang_updated = false;
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $config = &$configHandler->getConfig($conf_ids[$i]);

                $new_value = &$_POST[$config->getVar('conf_name')];

                if (is_array($new_value) || $new_value != $config->getVar('conf_value')) {
                    $config->setConfValueForInput($new_value);

                    $configHandler->insertConfig($config);
                }

                unset($new_value);
            }
        }
        $modname = $module->getVar('name');
        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/admin/myblocksadmin.php')) {
            redirect_header(XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/admin/setup.php?op=block', 2, _AM_DBUPDATED);
        } elseif ($module->getInfo('adminindex')) {
            redirect_header(XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/' . $module->getInfo('adminindex'), 2, _AM_DBUPDATED);
        } else {
            redirect_header('setup.php?op=finish', 2, _AM_DBUPDATED);
        }
        break;
    case 'block':
        $mid = $xoopsModule->getVar('mid');
        $navi = '<p>';
        $navi .= _AM_INSTALL . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        $navi .= _AM_MODULE_SETTING . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        $config = &$configHandler->getConfigs(new Criteria('conf_modid', $mid));
        $count = count($config);
        if ($count > 0) {
            $navi .= _AM_MODCONFIG . '&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        }
        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/admin/myblocksadmin.php')) {
            $navi .= '<span style="color:red;">' . _AM_GROUP_BLOCK . '</span>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;';
        }
        $navi .= '<span style="color:red;">' . _AM_FINISH . '</span><p>';
        function blockadmin_modify($str)
        {
            global $navi;

            $in = [
                "/<h3 style='text-align:left;'>([^<]*)<\/h3>/i",
            ];

            $out = [
                '<h4>$1 - ' . _AM_ONSETUP . '</h4>' . $navi,
            ];

            $str = preg_replace($in, $out, $str);

            return $str;
        }

        ob_start('blockadmin_modify');
        require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/admin/myblocksadmin.php';
        break;
}
