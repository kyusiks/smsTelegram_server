<?
	include "comn.php";
	
	$vVipYn = 'N';
	$vImgLimit = 100;
	
	//$vRndNum = mt_rand(1000000000,1999999999);
	$vRndNum = mt_rand(0, 900000000);
	//중복방지를 위한 임시 키값
	
	$vQuery = "INSERT INTO TB_SMS01 ( LID, IMG_SND_LIMIT, VIP_YN, FST_INS_DH, LST_UPD_DH
	) VALUES (
	(SELECT CNT FROM (SELECT IFNULL(MAX(LID),0)+1 CNT FROM TB_SMS01 A) B)
	,'$vImgLimit','$vVipYn', NOW(), FROM_UNIXTIME('$vRndNum'))";
	// LST_UPD_DH 에 vRndNum을 넣어 LID 중복 발급을 피할 키값으로 활용.
	fn_sql($vQuery);
	
	$result = fn_sql("SELECT LID FROM TB_SMS01 WHERE LST_UPD_DH = FROM_UNIXTIME('$vRndNum')");
	$row = mysqli_fetch_array($result);
	$vLid = $row['LID'];
	
	if ( $vLid != "" ) {
	  echo "<A>1</A>";
	  echo "<D>".$vLid."</D>";
	} else {
	  echo "<A>0</A>";
	}
	
	/*
			mysqli_query($conn, 
	"UPDATE TB_SMS01
	    SET IMG_SND_LIMIT = '$vImgLimit'
	  WHERE LID = '$vLid' ");
	*/

?>
