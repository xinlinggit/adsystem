<?php

/**  
 * @param array $weight 权重 例如array('a'=>20,'b'=>30,'c'=>50)  
 * @return string key 键名   
 */  
function roll($weight = array()) {   
	$roll = rand ( 1, array_sum ( $weight ) );   
	$_tmpW = 0;   
	$rollnum = 0;   
	foreach ( $weight as $k => $v ) {   
		$min = $_tmpW;   
		$_tmpW += $v;   
		$max = $_tmpW;   
		if ($roll > $min && $roll <= $max) {   
		 	$rollnum = $k;   
			break;   
		}   
	}   
	return $rollnum;   
}

$ad1 = [
    "revive-0-0" => [
        "html" => "<a href='http://www.baidu.com/' target='_blank'><img src='http://ad.api.dev.cnfol.wh/upload/aaaaaa.png' width='360' height='240' alt='' title='' border='0' /></a>",
        "width" => "360",
        "height"=> "240",
        "iframeFriendly"=>false
    ]
];
$ad2 = [
    "revive-0-0" => [
        "html" => "<a href='http://www.baidu.com/' target='_blank'><img src='http://ad.api.dev.cnfol.wh/upload/bbbbbbb.png' width='360' height='240' alt='' title='' border='0' /></a>",
        "width" => "360",
        "height"=> "240",
        "iframeFriendly"=>false
    ]
];
$ad3 = [
    "revive-0-0" => [
        "html" => "<a href='http://www.baidu.com/' target='_blank'><img src='http://ad.api.dev.cnfol.wh/upload/cccccccc.png' width='360' height='240' alt='' title='' border='0' /></a>",
        "width" => "360",
        "height"=> "240",
        "iframeFriendly"=>false
    ]
];



$row=roll(array('1'=>20,'2'=>30,'3'=>50));
$b = 'ad'.$row;
$c = $$b;
echo json_encode($c);