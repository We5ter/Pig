<?php 
	/**
	 * @name class_pig.php
	 * @author fendar
	 * @description 猪头术的控制类
	 */
	if( !defined('IN_DISCUZ') || !defined('HACK_ROOT')){
		exit('Access Denied');
	}
	
	class star_pig{
		public $_PIG = array();
		public $user = array();
		public $config = array();
		public function __construct(){
			
		}
		
		public function init(){
			$this->init_user();
			$this->init_config();
			//$this->init_lang();
			//$this->init_input();
			$this->init_merge();
		}
		
		private function init_user(){
			global $_G;
			if( !$_G['uid'] )
				$this->user['user']['state'] = -1;
			//后面还有验证，比如为实名的，禁言的等等
			else{
				$this->user['user']['state'] = $_G['uid'];
				$this->user['user'] = &$_G['member'];
			}
		}
		
		private function init_config(){
			global $_G;
			
			$this->config['config'] = $_G['cache']['plugin']['pig'];
			if ( !is_array($this->config['config']) ){
				showmessage('open_config_error');
			} 
		}
		
		private function init_lang(){
			//when replacing the language,the cache will be loaded.
			global $_G;
			loadcache('pluginlanguage_script');
			$this->lang = $_G['cache']['pluginlanguage_script']['pig'];
		}
		
		private function init_input( $type ){
			if($type == 'pig'){
				$_GET['pigid'] = (int)$_GET['pigid'];
				
				$pigNum = $this->config['config']['pignum'];
				($_GET['pigid'] < 0 || $_GET['pigid'] > $pigNum) && $_GET['pigid'] = false;
							
				$_GET['piguser'] = is_string($_GET['piguser']) ? (strip_tags($_GET['piguser']) ? strip_tags($_GET['piguser']): false) : false;
				
				!in_array($_GET['pigtime'], array('1','2','6','12','24')) && $_GET['pigtime']=false;
			}elseif( $type == 'nopig' ){
				$_GET['nopiguser'] = strip_tags($_GET['nopiguser']);
				if (!$_GET['nopiguser'])
					$_GET['nopiguser'] = FALSE;
				$_GET['nopigid'] = trim($_GET['nopigid']); 
				!in_array($_GET['nopigid'], array('1','2')) && $_GET['nopigid'] = FALSE;
			}
		}
		private function init_merge(){
			$this->_PIG = array_merge($this->user,$this->config);
			$this->_PIG['baseurl'] = 'plugin.php?id=pig';
		}
		
		public function on_index(){
			global $_G;
			
			//分页
			$pigNum = $this->config['config']['pignum'];
			$perpage = 16;
			$curpage = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$curpage < 1 && $curpage = 1;
			$curpage > @ceil($pigNum/$perpage) && $curpage = @ceil($pigNum/$perpage); 
			
			$multiPages = multi($pigNum, $perpage, $curpage, $this->_PIG['baseurl']) ;
			//分页结束
			
			//显示哪些猪头
			$pigStart = ($curpage-1)*$perpage + 1;
			$pigEnd = ($pigStart+$perpage-1) > $pigNum ? $pigNum :($pigStart+$perpage-1);
			//结束
			
			$imageSrc = $_G['siteurl']."source/plugin/pig/image";
			$hash = $this->star_hash();
			include template('pig:pig');
			dexit();
		} 
		
		public function on_piglist(){
			global $_G;
		
			$counts = C::t('#pig#star_pig')->count_pig();
			//分页显示
			$perpage = 10;
			$curpage = isset($_GET['page']) ? intval($_GET['page']) : 1 ;
			$curpage > ceil($counts/$perpage) && $curpage= ceil($counts/$curpage);
			$curpage < 1 && $curpage = 1;
			$mpurl = $this->_PIG['baseurl']."&action=piglist";
			$pages = multi($counts, $perpage, $curpage, $mpurl);
			//分页结束
			
			//取数据
			$pigList = C::t('#pig#star_pig')->star_get_piglist_by_page($curpage,$perpage);
			foreach ($pigList as $key=>$val){
				$pigList[$key]['starttime'] = date('Y-m-d H:i:s',$pigList[$key]['starttime']);
				$pigList[$key]['overtime'] = date('Y-m-d H:i:s',$pigList[$key]['overtime']);
			}
			include template('pig:piglist');
			dexit();
		}
		
		public function on_pig(){
			global $_G;
			//这里有重复提交。可以通过session控制，但是dz的代码一直没有用PHP的session，所以就省略了
			$this->init_input('pig');
			if (!$this->verify_hash($_GET['verify'])) {
				showmessage('unexpected_error,please refresh your page and do it again');
			}
			if( !$_GET['piguser'] )
				showmessage('pig:input_user_error','','',array('alert'=>'info'));
			elseif (!$_GET['pigid'])
				showmessage('pig:input_pigid_error','','',array('alert'=>'info'));
			elseif (!$_GET['pigtime'])
				showmessage('pig:input_pigtime_error','','',array('alert'=>'info'));
			
			$config = $this->config['config'];
			$pigedUser = $_GET['piguser'];
			$pigId = $_GET['pigid'];
			$pigTime = $_GET['pigtime'];
			
			//验证用户是否要诅咒自己
			if ($pigedUser == $this->_PIG['user']['username']){
				showmessage('pig:pig_self_error','','',array('alert'=>'info'));
			}
			
			//验证水滴是否不够
			$userCount = C::t('common_member_count')->fetch($this->_PIG['user']['uid']);
			if( $userCount['extcredits2'] < $config['pig_money'] * $pigTime ){
				showmessage('pig:money_lack','','',array('alert'=>'info'));
			}
			
			//验证用户是否存在
			$pigedUserInfo = C::t('common_member')->fetch_by_username($pigedUser);
			if( !$pigedUserInfo )
				showmessage('pig:input_user_error','','',array('alert'=>'info'));
			
			//验证该用户是否正在诅咒中
			$pigedStatus = C::t('#pig#star_pig')->fetch($pigedUserInfo['uid']);
			if( !$pigedStatus ){
				$status = 8;
			}else{
				if ( $pigedStatus['overtime'] > TIMESTAMP ) {
					showmessage('pig:user_pigging','','',array('alert=>info'));
				}else{ 
						$status = 12;
					}	
			}
			//开始诅咒 8 表示要插入记录 12 表示更新记录
				//我擦。。。这个表没有提供 减少 credits 的方法，你敢信？
			$dmoney = $config['pig_money'] * $pigTime;
			
			C::t('common_member_count')->decrease_water_credits($this->user['user']['uid'],$dmoney);
				
				//计算概率
			
			$status = $this->make_chance($pigedUserInfo['uid'],$userCount['extcredits1'],$status);
				
			//如果 $status 为 9或13 成功   为 8 或者 12 失败	
			$pigAuthor = "<a href=\"home.php?mod=space&uid=".$this->_PIG['user']['uid']."\">".$this->_PIG['user']['username']."</a>";
			
			if ( ($status & 1) == 0 ){
				//发送短消息 失败
				helper_notification::notification_add($pigedUserInfo['uid'], 'system', 'pig:pig_fail_message',array('pigAuthor'=>$pigAuthor),1);
				showmessage('pig:pig_fail','','',array('alert'=>'info'));
			}else{//成功
				if($status == 9){
					//插入数据库
					$data = array(
							'uid' => $pigedUserInfo['uid'],
							'usera' => $this->_PIG['user']['username'],
							'userb' => $pigedUser,
							'type'	=> $pigId,
							'time'	=> $pigTime,
							'status' => $status,
							'starttime'=> TIMESTAMP,
							'overtime'=> TIMESTAMP+$pigTime*3600,
					);
					C::t('#pig#star_pig')->insert($data);
				}else{
					$val = array('uid' => $pigedUserInfo['uid']);
					$data = array(
							'usera' => $this->_PIG['user']['username'],
							'type'	=> $pigId,
							'time'	=> $pigTime,
							'status' => $status,
							'starttime'=> TIMESTAMP,
							'overtime'=> TIMESTAMP+$pigTime*3600,
					);
					C::t('#pig#star_pig')->update($val,$data);
				}
				//发送成功短消息
				helper_notification::notification_add($pigedUserInfo['uid'], 'system', 'pig:pig_success_message',array('pigAuthor'=>$pigAuthor,'pigtime'=>$pigTime),1);
				//更改头像是在pig.class.php中完成的 详细请看discuz 插件页面嵌入
				showmessage('pig:pig_success','','',array('alert'=>'right'));
			}
		}
		
		public function on_nopig(){
			global $_G;
			$config = $this->config['config'];
			if( !isset($_GET['nopigsubmit']) ){
				$hash = $this->star_hash();
				include template('pig:nopig');
				dexit();
			}
			$this->init_input('nopig');
			if( !$this->verify_hash($_GET['verify'])) {
				showmessage('unexpected_error,please refresh your page and do it again');
			}
			if( !($noPigUser = $_GET['nopiguser']) ){
				showmessage('pig:nopig_user_error','','',array('alert'=>'info'));
			}
			if( !($noPigTool = intval($_GET['nopigid'])) ){
				showmessage('pig:nopig_tool_error','','',array('alert'=>'info'));
			}
			//检验解咒用户名是否存在
			if(! ($noPigUserInfo = C::t('common_member')->fetch_by_username($noPigUser)) ){
				showmessage('pig:nopig_user_noexistence','','',array('alert'=>'info'));
			}
			//检验解咒用户是否在被诅咒..
			$noPigUserStatus = C::t('#pig#star_pig')->fetch($noPigUserInfo['uid']);
			if( !$noPigUserStatus || $noPigUserStatus['overtime'] < $_G['timestamp']){
				showmessage('pig:nopig_user_notin','',array('baseurl'=>$this->_PIG['baseurl']),array('alert'=>'info'));
			}
			//检验当前用户解咒的水滴是否够
			$noPigAllMoney = $config['pig_nopig'.$noPigTool.'money'] + $config['pig_nopigmoney'];
			$noPigAllRate  = $config['pig_nopig'.$noPigTool.'rate'] +$config['pig_nopigrate'];
			$memberInfo = C::t('common_member_count')->fetch($_G['uid']);
			if( $memberInfo['extcredits2'] < $noPigAllMoney * $noPigUserStatus['time'] ){
				showmessage('pig:nopig_lack_money','','',array('alert'=>'info'));
			}
			
			//判断冷却时间
			if( $noPigUserStatus['cdtime'] > $_G['timestamp'] ){
				$cddate = date('Y-m-d H:i:s',$noPigUserStatus['cdtime']);
				showmessage('pig:nopig_cdtime','',array('cddate'=>$cddate),array('alert'=>'info'));
			}
			//扣钱
			C::t('common_member_count')->decrease_water_credits($_G['uid'],$noPigAllMoney * $noPigUserStatus['time']);
			//开始判断是否成功或者失败
			$noPigOk = FALSE;
			$cdtime = '';
			$tempRate = mt_rand(1, 100);
			if( $tempRate <= $noPigAllRate ){
				$noPigOk = TRUE;
			}else{
				$cdtime = $_G['timestamp'] + $config['pig_nopig'.$noPigTool.'cd']*60;
			}
			//结果处理，更新数据库、发送短消息、返回结果
			$goodUser = "<a href=\"home.php?mod=space&uid".$_G['uid']."\">".$_G['member']['username']."</a>";
			$noPigToolName = lang('plugin/pig','nopig_tool'.$noPigTool.'_name');
			if( !$noPigOk ){
				C::t("#pig#star_pig")->update($noPigUserStatus['uid'],array('cdtime'=>$cdtime));
				if ($noPigUserStatus['uid'] != $_G['uid'] ){
					helper_notification::notification_add($noPigUserStatus['uid'], 'system', 'pig:nopig_fail_pm' ,array('gooduser'=>$goodUser,'nopigtoolname'=>$noPigToolName));
				}
				showmessage('pig:nopig_fail','','',array('alert'=>'info'));
			}else{
				C::t('#pig#star_pig')->delete($noPigUserStatus['uid']);
				//自己解咒不发送短消息
				if ($noPigUserStatus['uid'] != $_G['uid'] ){
					helper_notification::notification_add($noPigUserStatus['uid'],'system', 'pig:nopig_success_pm' , array('gooduser'=>$goodUser,'nopigtoolname'=>$noPigToolName),1);
				}
				showmessage('pig:nopig_success','','',array('alert'=>'success'));
			}
		}
		
		private function make_chance($pigedUid , $wwCredit1 , &$status){
			$credits = C::t('common_member_count')->fetch($pigedUid);
			$wwCredit2 = $credits['extcredits1'];
			$brvrc = $wwCredit2;
			$rvrcsc = (int)(($wwCredit1-$brvrc)/2);
			
			$pig_temp = mt_rand(0,100);
			$pig_temp = $pig_temp - $rvrcsc;
			
			if($pig_temp <= $this->config['config']['pig_rate']){
				return $status + 1;
			}
			return $status;
		}

		//生成hash值函数
		private function star_hash(){
			global $_G;
			$num = intval($_G['timestamp']/1000);
			$hash = $num.substr(md5(md5($_G['uid'].$num).'pighash_fendar'), 3,9);
			return $hash;
		}
		//验证hash值
		private function verify_hash($hash){
			if( !$hash )
				return false;
			global $_G;
			$num = intval($_G['timestamp']/1000);
			$input_hash = $num.substr(md5(md5($_G['uid'].$num).'pighash_fendar'), 3,9);
			if( $input_hash != $hash)
				return false;
			return true;
		}
	}
	
?>
