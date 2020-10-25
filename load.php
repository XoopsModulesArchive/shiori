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
require dirname(__DIR__, 2) . '/mainfile.php';

//ユーザーで無ければ
if (!$xoopsUser) {
    redirect_header(XOOPS_URL, 3, _NOPERM);

    exit();
}

//IDが無ければ
if (!$_GET['id']) {
    redirect_header(XOOPS_URL, 3, _NOPERM);

    exit();
}
$id = (int)$_GET['id'];

//モジュール名取得
$mydirname = basename(__DIR__);
$myurl = XOOPS_URL . '/modules/' . $mydirname;

//栞クラス呼び出し
require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/class/shiori.php';

//ブックマークひきだし
$bookmark = new Shiori($id);
$uid = $xoopsUser->getVar('uid');
if ($bookmark->getVar('uid') != $uid) {
    redirect_header(XOOPS_URL, 3, _NOPERM);

    exit();
}

//訪問数カウント
Shiori::incrementCounter($id, $uid);

$url = $bookmark->getVar('url');

//転送
header("Location: $url");
exit();
