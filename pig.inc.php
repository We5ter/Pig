<?php 
	/**
	 * @name pig.inc.php
	 * @author fendar
	 * @desciption 对以前河畔2b写的猪头术进行移植
	 */
	if( !defined('IN_DISCUZ') ){
		exit('Access Denied');
	}
	define('HACK_ROOT', DISCUZ_ROOT.'/source/plugin/'.$identifier);
	require_once HACK_ROOT.'/class_pig.php';

	$pig_ctl = new star_pig();
	$pig_ctl->init();
	$_PIG = $pig_ctl->_PIG;
	
	if($_PIG['user']['state'] == -1){
		showmessage('to_login','','',array('login'=>true));
	}
	
	$actionArr = array('pig','piglist','nopig','index');
	$action = $_GET['action'] ?  trim($_GET['action']) : 'index';
	if( !in_array($action , $actionArr)){
		showmessage('undefined_action');
	}
	
	(!$_PIG['config']['pig_status'] && $_G['uid'] !=1 && $_G['uid'] != 131623) && showmessage('pig:pig_close',dreferer(),'',array('alert'=>'info'));
	
	$method = "on_".$action;
	$pig_ctl->$method();
	
?>
