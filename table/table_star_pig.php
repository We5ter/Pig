<?php 
	/**
	 * @name tale_star_pig.php
	 * @author zhongchao
	 * @desc 猪头术的记录表
	 */
	if( !defined('IN_DISCUZ') || !defined('HACK_ROOT')){
		exit('Access Denied');
	}
	class table_star_pig extends discuz_table{
		public function __construct(){
			$this->_pk = 'uid';
			$this->_table = 'star_pig';
			
			parent::__construct();
		}
		public function star_get_piglist_by_page($curpage,$perpage){
			if( !is_int($curpage) || !is_int($perpage)){
				return  array();
			}			
			$time = time();
			$left = ($curpage-1)*$perpage;
			$query = DB::query("SELECT * FROM ".DB::table($this->_table)." WHERE overtime > $time"." LIMIT $left,$perpage");
			$return = array();
			while ($row = DB::fetch($query)){
				$return[] = $row;
			}
			return $return;
		}

		public function count_pig() {
			return DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).' WHERE `overtime` >'.TIMESTAMP);
		}
	}
?>