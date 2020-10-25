<?php

// GIJOE's Ticket Class (based on Marijuana's Oreteki XOOPS)
// nobunobu's suggestions are applied

use Xmf\Request;

if (!class_exists('XoopsGTicket')) {
    class XoopsGTicket
    {
        public $_errors = [];

        public $_latest_token = '';

        // render form as plain html

        public function getTicketHtml($salt = '', $timeout = 1800)
        {
            return '<input type="hidden" name="XOOPS_G_TICKET" value="' . $this->issue($salt, $timeout) . '">';
        }

        // returns an object of XoopsFormHidden including theh ticket

        public function getTicketXoopsForm($salt = '', $timeout = 1800)
        {
            return new XoopsFormHidden('XOOPS_G_TICKET', $this->issue($salt, $timeout));
        }

        // returns an array for xoops_confirm() ;

        public function getTicketArray($salt = '', $timeout = 1800)
        {
            return ['XOOPS_G_TICKET' => $this->issue($salt, $timeout)];
        }

        // return GET parameter string.

        public function getTicketParamString($salt = '', $noamp = false, $timeout = 1800)
        {
            return ($noamp ? '' : '&amp;') . 'XOOPS_G_TICKET=' . $this->issue($salt, $timeout);
        }

        // issue a ticket

        public function issue($salt = '', $timeout = 1800)
        {
            // create a token

            [$usec, $sec] = explode(' ', microtime());

            $token = crypt($salt . $usec . $_SERVER['PATH'] . $sec);

            $this->_latest_token = $token;

            if (empty($_SESSION['XOOPS_G_STUBS'])) {
                $_SESSION['XOOPS_G_STUBS'] = [];
            }

            // limit max stubs 10

            if (count($_SESSION['XOOPS_G_STUBS']) > 10) {
                $_SESSION['XOOPS_G_STUBS'] = array_slice($_SESSION['XOOPS_G_STUBS'], -10);
            }

            // store stub

            $_SESSION['XOOPS_G_STUBS'][] = [
                'expire' => time() + $timeout,
'ip' => $_SERVER['REMOTE_ADDR'],
'token' => $token,
            ];

            // paid md5ed token as a ticket

            return md5($token . XOOPS_DB_PREFIX);
        }

        // check a ticket

        public function check($post = true)
        {
            $this->_errors = [];

            // CHECK: stubs are not stored in session

            if (empty($_SESSION['XOOPS_G_STUBS']) || !is_array($_SESSION['XOOPS_G_STUBS'])) {
                $this->clear();

                $this->_errors[] = 'Invalid Session';

                return false;
            }

            // get key&val of the ticket from a user's query

            if ($post) {
                $ticket = empty($_POST['XOOPS_G_TICKET']) ? '' : $_POST['XOOPS_G_TICKET'];
            } else {
                $ticket = empty($_GET['XOOPS_G_TICKET']) ? '' : $_GET['XOOPS_G_TICKET'];
            }

            // CHECK: no tickets found

            if (empty($ticket)) {
                $this->clear();

                $this->_errors[] = 'Irregular post found';

                return false;
            }

            // gargage collection & find a right stub

            $stubs_tmp = $_SESSION['XOOPS_G_STUBS'];

            $_SESSION['XOOPS_G_STUBS'] = [];

            foreach ($stubs_tmp as $stub) {
                // default lifetime 30min

                if ($stub['expire'] >= time()) {
                    if (md5($stub['token'] . XOOPS_DB_PREFIX) === $ticket) {
                        $found_stub = $stub;
                    } else {
                        // store the other valid stubs into session

                        $_SESSION['XOOPS_G_STUBS'][] = $stub;
                    }
                } else {
                    if (md5($stub['token'] . XOOPS_DB_PREFIX) === $ticket) {
                        // not CSRF but Time-Out

                        $timeout_flag = true;
                    }
                }
            }

            // CHECK: no right stub found

            if (empty($found_stub)) {
                $this->clear();

                if (empty($timeout_flag)) {
                    $this->_errors[] = 'Invalid Session';
                } else {
                    $this->_errors[] = 'Time out';
                }

                return false;
            }

            // CHECK: different ip

            /* if( $found_stub['ip'] != $_SERVER['REMOTE_ADDR'] ) {
                $this->clear() ;
                $this->_errors[] = 'IP has been changed' ;
                return false ;
            } */

            // all green

            return true;
        }

        // clear all stubs

        public function clear()
        {
            $_SESSION['XOOPS_G_STUBS'] = [];
        }

        // Ticket Using

        public function using()
        {
            if (!empty($_SESSION['XOOPS_G_STUBS'])) {
                return true;
            }

            return false;
        }

        // return errors

        public function getErrors($ashtml = true)
        {
            if ($ashtml) {
                $ret = '';

                foreach ($this->_errors as $msg) {
                    $ret .= "$msg<br>\n";
                }
            } else {
                $ret = $this->_errors;
            }

            return $ret;
        }

        // end of class
    }

    // create a instance in global scope

    $GLOBALS['xoopsGTicket'] = new XoopsGTicket();
}

if (!function_exists('admin_refcheck')) {
    //Admin Referer Check By Marijuana(Rev.011)

    function admin_refcheck($chkref = '')
    {
        if (empty(Request::getString('HTTP_REFERER', '', 'SERVER'))) {
            return true;
        }

        $ref = Request::getString('HTTP_REFERER', '', 'SERVER');

        $cr = XOOPS_URL;

        if ('' != $chkref) {
            $cr .= $chkref;
        }

        if (0 !== mb_strpos($ref, $cr)) {
            return false;
        }

        return true;
    }
}
