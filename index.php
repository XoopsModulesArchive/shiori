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
$xoopsOption['pagetype'] = 'user';

require dirname(__DIR__, 2) . '/mainfile.php';

//ユーザーで無ければ
if (!$xoopsUser) {
    redirect_header(XOOPS_URL, 3, _NOPERM);

    exit();
}

//モジュール名取得
$mydirname = basename(__DIR__);
$myurl = XOOPS_URL . '/modules/' . $mydirname;

//オペレーション初期化
$op = $_POST['op'] ?? 'list';

//Gチケットシステム呼び出し
require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/include/gtickets.php';

switch ($op) {
    case 'del':

        if (!$xoopsGTicket->check()) {
            redirect_header(XOOPS_URL, 3, $xoopsGTicket->getErrors());

            exit();
        }

        if (empty($_POST['del_bok']) || !is_array($_POST['del_bok'])) {
            redirect_header($myurl . '/index.php', 3, _MD_SELECT_DL);

            exit();
        }

        //栞クラス呼び出し
        require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/class/shiori.php';

        //削除ループ
        $errors = [];
        foreach ($_POST['del_bok'] as $i) {
            $del_id = (int)$i;

            $shiori = new Shiori($del_id);

            if (!$shiori->delete()) {
                $errors[] = $i;
            }

            unset($del_id, $shiori);
        }

        //エラーがあれば
        if (count($errors) > 0) {
            redirect_header($myurl . '/index.php', 3, sprintf(_MD_FAIL_DEL, count($errors)));

            exit();
        }

        //問題なく成功
        redirect_header($myurl . '/index.php', 3, _MD_SUCC_DEL);
        break;
    default:
    case 'list':

        //栞クラス呼び出し
        require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/class/shiori.php';

        $uid = $xoopsUser->getVar('uid');
        $limit = $xoopsModuleConfig['shiori_bookmark_apage'];
        $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;

        //ブックマークひきだし
        $criteria = ['uid=' . $uid];
        $book_arr = &Shiori::getAll($criteria, true, 'date DESC', $limit, $start);

        //ナビ
        $navi = '';
        $numrows = Shiori::CountbyUid($uid);
        if ($numrows > $limit) {
            require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

            $nav = new XoopsPageNav($numrows, $limit, $start, 'start');

            $navi = $nav->renderNav();
        }

        $GLOBALS['xoopsOption']['template_main'] = 'shiori_index.html';

        require_once XOOPS_ROOT_PATH . '/header.php';

        $count = count($book_arr);
        for ($i = 0; $i < $count; $i++) {
            $bookmarks = [];

            $id = $book_arr[$i]->getVar('id');

            $url = $book_arr[$i]->getVar('url', 'p');

            $title = $book_arr[$i]->getVar('name', 'p');

            $mid = $book_arr[$i]->getVar('mid');

            $icon = $book_arr[$i]->getVar('icon', 'p');

            $counter = $book_arr[$i]->getVar('counter');

            //モジュール名

            $modname = _MD_BOOK_NOTMOD;

            if ($mid > 0) {
                $moduleHandler = xoops_getHandler('module');

                $module = $moduleHandler->get($mid);

                $modname = $module->getVar('name');
            } else {
                switch ($mid) {
                    case '-1':
                        $modname = _MD_BOOK_USERINFO;
                        break;
                    case '-2':
                        $modname = _MD_BOOK_SEARCH;
                        break;
                    case '-3':
                        $modname = _MD_BOOK_PM;
                        break;
                    case '-4':
                        $modname = _MD_BOOK_INDEX;
                        break;
                    case '-5':
                        $modname = _MD_BOOK_OUTER;
                        break;
                }
            }

            //訪問頻度

            $countimg = '&nbsp;';

            if ($counter >= $xoopsModuleConfig['shiori_one_star']) {
                $countimg .= '<img src="' . $myurl . '/images/star.png" alt="">';
            }

            if ($counter >= $xoopsModuleConfig['shiori_two_stars']) {
                $countimg .= '<img src="' . $myurl . '/images/star.png" alt="">';
            }

            if ($counter >= $xoopsModuleConfig['shiori_three_stars']) {
                $countimg .= '<img src="' . $myurl . '/images/star.png" alt="">';
            }

            if ($counter >= $xoopsModuleConfig['shiori_four_stars']) {
                $countimg .= '<img src="' . $myurl . '/images/star.png" alt="">';
            }

            if ($counter >= $xoopsModuleConfig['shiori_five_stars']) {
                $countimg .= '<img src="' . $myurl . '/images/star.png" alt="">';
            }

            //割り当て

            $bookmarks['id'] = $id;

            $bookmarks['address'] = $url;

            $bookmarks['url'] = $myurl . '/load.php?id=' . $id;

            $bookmarks['link'] = $title;

            $bookmarks['module'] = $modname;

            $bookmarks['icon'] = (!empty($icon)) ? XOOPS_URL . "/images/subject/$icon" : XOOPS_URL . '/images/blank.gif';

            $bookmarks['counter'] = $counter;

            $bookmarks['countimg'] = $countimg;

            $xoopsTpl->append('bookmarks', $bookmarks);
        }

        $onlythisite = (0 == $xoopsModuleConfig['shiori_prmt_outofsite']) ? '<br>' . _MD_ONLY_THISITE : '';

        //割り当て
        $xoopsTpl->assign('userid', $uid);
        $xoopsTpl->assign('perm_by_url', $xoopsModuleConfig['shiori_use_freeurl']);
        $xoopsTpl->assign('action_url_add', $myurl . '/bookmark.php');
        $xoopsTpl->assign('action_url', $myurl . '/index.php');
        $xoopsTpl->assign('navi', $navi);
        $xoopsTpl->assign('lang_bookmark', _MD_BOOKMARK);
        $xoopsTpl->assign('lang_profile', _MD_PROFILE);
        $xoopsTpl->assign('lang_checkall', _MD_CHECHKALL);
        $xoopsTpl->assign('lang_link', _MD_BOOK_NAME);
        $xoopsTpl->assign('lang_module', _MD_BOOK_MODNAME);
        $xoopsTpl->assign('lang_del', _MD_DEL);
        $xoopsTpl->assign('lang_addbm_by_url', _MD_ADD_BM_BY_URL);
        $xoopsTpl->assign('lang_onlyself', $onlythisite);
        $xoopsTpl->assign('lang_url', _MD_BOOK_URL);
        $xoopsTpl->assign('lang_add', _MD_ADD_BM_NEXT);
        $xoopsTpl->assign('lang_counter', _MD_COUNTER);
        //チケット発行
        $xoopsTpl->assign('hiddenelements', '<input id="op" name="op" type="hidden" value="del">' . $xoopsGTicket->getTicketHtml(__LINE__));
        $xoopsTpl->assign('lang_nobookmarks', _MD_NO_BOOKMARKS);
        $xoopsTpl->assign('copyright', '<a href="http://www.suin.jp/" target="_blank">shiori</a>');

        require_once XOOPS_ROOT_PATH . '/footer.php';
        break;
}
