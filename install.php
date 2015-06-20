<?php 
if ( !defined('IN_DISCUZ')){
	exit('Access Denied');
}
$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `pre_star_pig` (
  `uid` mediumint(8) unsigned NOT NULL,
  `usera` varchar(30) NOT NULL COMMENT '发起诅咒的人',
  `userb` varchar(30) NOT NULL COMMENT '被诅咒的人',
  `type` tinyint(2) unsigned NOT NULL,
  `time` tinyint(2) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `notools` tinyint(1) NOT NULL,
  `cdtime` int(11) NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `overtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;
?>