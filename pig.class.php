<?php 
/**
 * 
 */
	if( !defined('IN_DISCUZ') ){
		exit('Access Denied');
	}
	
class plugin_pig{
	public function __construct(){
		
	}
	function avatar(){
		//这里可以通过 $param 里的 size 来返回是否显示诅咒黑手
		global $_G;
		$param = func_get_args();
		$param = $param['0']['param'];//$param[0] 表示用户id $param[1] 表示用户的头像的大小没有表示 middle
		!defined('HACK_ROOT') && define('HACK_ROOT',DISCUZ_ROOT."/source/plugin/pig");//定义才能访问到pig的文件
		$pigInfo = C::t('#pig#star_pig')->fetch($param['0']);
		if( !$pigInfo )
			return '';
		elseif( $pigInfo['overtime'] < $_G['timestamp'] )
			return '';
		$_G['pigInfo'] = $pigInfo;
		//edump('aa',$pigInfo);
		$date = date("Y-n-j H:i:s" , $pigInfo['overtime']);
		$info = isset($param['1']) ? '' :"</a></br>诅咒黑手：</br><a href=\"home.php?mod=space&username=".$pigInfo['usera']."\"><font color=\"#FF3333\">".$pigInfo['usera']."</font>";
		$style = isset($param['1']) ? '' : "style=\"width:100px;height:130px;\"";
		
		$img = "<img src=\"http://bbs.stuhome.net/source/plugin/pig/image/".$pigInfo['type'].".gif\" alt=\"到期时间 $date\" $style/>$info";

		$_G['hookavatar'] = $img;
		return $img;
	}
}

// class plugin_pig_forum extends plugin_pig{
// 	function viewthread_avatar_output(){
// 		global $postlist;
// 		//edump($pigInfo);
// 		return array(
// 				$pigInfo['usera']
// 		);
// 	}
// }
?>