<?php
// +----------------------------------------------------------------------
// | wuzhicms [ 五指互联网站内容管理系统 ]
// | Copyright (c) 2014-2015 http://www.wuzhicms.com All rights reserved.
// | Licensed ( http://www.wuzhicms.com/licenses/ )
// | Author: wangcanjia <phpip@qq.com>
// +----------------------------------------------------------------------
defined('IN_WZ') or exit('No direct script access allowed');
/**
 * 优惠券管理
 */
load_class('admin');
class card extends WUZHI_admin {
	private $db;
	function __construct() {
		$this->db = load_class('db');
	}
    /**
     * 列表
     */
    public function listing() {
        $page = isset($GLOBALS['page']) ? intval($GLOBALS['page']) : 1;
        $page = max($page,1);
        $result = $this->db->get_list('coupon_card', '', '*', 0, 20,$page,'cardid DESC');
        $pages = $this->db->pages;
        $total = $this->db->number;

        $status_arr = array('<b>待发送</b>','未预约','已激活','已使用');
        include $this->template('card_listing');
    }

    /**
     * 生成卡
     */
    public function add() {
        if(isset($GLOBALS['submit'])) {
            $download = intval($GLOBALS['download']);
            $number = intval($GLOBALS['number']);
            $number = max($number,1);
            $batchid = uniqid();
            $tmpdata = iconv('gbk','utf-8','优惠券,面值,截至日期');
            $ip = get_ip();
            $usetype = intval($GLOBALS['form']['usetype']);
            if($usetype) {$number=1;}
            for($i=0;$i<$number;$i++) {
                $formdata = array();
                $formdata['addtime'] = SYS_TIME;
                $formdata['endtime'] = strtotime($GLOBALS['endtime']);
                $formdata['status'] = $download==1 ? 1 : 0;
                $formdata['adminname'] = get_cookie('username');
                $formdata['id'] = $GLOBALS['form']['id'];
                $formdata['usetype'] = $usetype;
                $formdata['mount'] = $GLOBALS['form']['mount'];
                $formdata['title'] = remove_xss($GLOBALS['form']['title']);
                $formdata['batchid'] = $batchid;
                $tr = $this->db->get_one('tuangou',array('id'=>$formdata['id']));
                $formdata['remark'] = $tr['title'];
                $formdata['url'] = $tr['url'];

                $cardid = $this->db->insert('coupon_card',$formdata);

                $card_no = $GLOBALS['pre'].rand(100,1000).rand(100,999).str_pad(rand(1,99).$cardid, 6, "0", STR_PAD_LEFT);
                $this->db->update('coupon_card',array('card_no'=>$card_no),array('cardid'=>$cardid));
                $tmpdata .= "\r\n".$card_no.','.$formdata['mount'].','.$GLOBALS['endtime'];
                if($download) {
                    $formdata2 = array();
                    $formdata2['cardid'] = $cardid;
                    $formdata2['type'] = 0;
                    $formdata2['senduser'] = $formdata['adminname'];
                    $formdata2['sendtime'] = SYS_TIME;
                    $formdata2['ip'] = $ip;
                    $this->db->insert('coupon_card_send',$formdata2);
                }
            }
            if($download) {


                $filename = $batchid . '.txt';

                //$content = ob_get_contents();
                header('Content-Description: File Transfer');
                header('Content-Type: application/txt');
                header('Content-Disposition: attachment; filename=' . $filename);
                // header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Pragma: public');
                header('Content-Transfer-Encoding: binary');
                header('Content-Encoding: none');
                echo $tmpdata;
            } else {
                MSG('生成成功','?m=coupon&f=card&v=listing' . $this->su());
            }
        } else {
            $show_formjs = '';
            $endtime = mktime(0,0,0,date('m')+3,date('d'),date('Y'));
            $endtime = date('Y-m-d',$endtime);
            load_class('form');
            include $this->template('card_add');
        }
    }
    public function send() {
        $cardid = intval($GLOBALS['cardid']);
        if(isset($GLOBALS['submit'])) {
            $mobile = $GLOBALS['mobile'];
            $email = $GLOBALS['email'];
            if(empty($mobile) && empty($email)) {
                MSG('手机，邮箱必须填写一项');
            }
            $formdata2 = array();
            $formdata2['cardid'] = $cardid;
            $formdata2['type'] = 0;
            $formdata2['senduser'] = get_cookie('username');
            $formdata2['mobile'] = $mobile;
            $formdata2['email'] = $email;
            $formdata2['note'] = $GLOBALS['note'];
            $formdata2['sendtime'] = SYS_TIME;
            $formdata2['ip'] = get_ip();
            $this->db->insert('coupon_card_send',$formdata2);
            $r = $this->db->get_one('coupon_card',array('cardid'=>$cardid));
            if($mobile) {
                $sendsms = load_class('sms','sms');
                //尊敬的用户，合一健康网优惠券为：sss，截止日期为：2016-12-1，请登录www.h1jk.cn及时使用！
                $sendsms->send_sms($mobile, $r['card_no'].'||'.date('Y-m-d',$r['endtime']), 269); //发送短信
            }
            if($email) {
                load_function('preg_check');
                if(empty($email) || !is_email($email)) {
                    MSG('邮箱地址错误');
                }
                $config = get_cache('sendmail');
                $password = decode($config['password']);
                //load_function('sendmail');
                $subject = $GLOBALS['email_title'] ? $GLOBALS['email_title'] : '合一健康网－优惠券';
                $email_content = $GLOBALS['email_content'];
                $email_content = format_textarea($email_content);
                $email_content = str_replace('##title##',$r['remark'],$email_content);
                $email_content = str_replace('##url##',substr(WEBURL,0,-1).$r['url'],$email_content);
                $email_content = str_replace('##money##',$r['money'],$email_content);
                $email_content = str_replace('##card_no##',$r['card_no'],$email_content);
                $email_content = str_replace('##endtime##',date('Y-m-d',$r['endtime']),$email_content);


                $mail = load_class('sendmail');
                $mail->setServer($config['smtp_server'], $config['smtp_user'], $password);
                $mail->setFrom($config['send_email']); //设置发件人
                $mail->setReceiver($email); //设置收件人，多个收件人，调用多次
                //$mail->setCc("XXXX"); //设置抄送，多个抄送，调用多次
                //$mail->setBcc("XXXXX"); //设置秘密抄送，多个秘密抄送，调用多次
                //$mail->addAttachment("XXXX"); //添加附件，多个附件，调用多次
                $mail->setMail($subject, $email_content); //设置邮件主题、内容
                $mail->sendMail(); //发送
            }
            $this->db->update('coupon_card',array('status'=>1),array('cardid'=>$cardid));
            MSG('发送成功',$GLOBALS['forward']);
        } else {
            $show_formjs = '';
            $r = $this->db->get_one('coupon_card',array('cardid'=>$cardid));
            $email_setting = get_cache('email_setting','coupon');
            include $this->template('card_send');
        }

    }
    /**
     * 发送记录
     */
    public function history() {
        $cardid = intval($GLOBALS['cardid']);
        $page = isset($GLOBALS['page']) ? intval($GLOBALS['page']) : 1;
        $page = max($page,1);
        $result = $this->db->get_list('coupon_card_send', array('cardid'=>$cardid), '*', 0, 20,$page,'sendtime DESC');
        $pages = $this->db->pages;
        $total = $this->db->number;
        include $this->template('card_history');
    }

    /**
     * 邮件模板发送配置
     */
    public function email_setting() {
        if(isset($GLOBALS['submit'])) {
            $formdata = array();
            $formdata['email_title'] = $GLOBALS['email_title'];
            $formdata['email_content'] = $GLOBALS['email_content'];
            set_cache('email_setting',$formdata,'coupon');
            $data = serialize($formdata);
            $this->db->update('setting', array('data'=>$data),array('keyid'=>'email_setting','m' => 'coupon'));
            MSG('更新成功',HTTP_REFERER);
        } else {
            $r = $this->db->get_one('setting', array('keyid'=>'email_setting','m' => 'coupon'));
            $setting = unserialize($r['data']);
            $email_title = $setting['email_title'];
            $email_content = $setting['email_content'];
            include $this->template('email_setting');
        }
    }
}