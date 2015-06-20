<?php 
if ( !defined('IN_DISCUZ')){
	exit('Access Denied');
}
$sql = <<<EOF

DROP TABLE IF EXISTS `pre_star_pig`;

EOF;

runquery($sql);

$finish = TRUE;
?>