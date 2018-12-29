<?
// echo $text;
// /img12432073146876 
// 구성 : img[conid글자수][conid][원본msgId글자수][원본msgId][MMS의DPL글자수][DPL][IMG의DPL]
	$vArr = array();
	$vCnt = 4; // 첫 /img를 빼기위해
	
	for ( $i = 0; $i < 3; $i++ ) { // 0:conId 1:orgMsgId 2:mmsDPL
	  $vNum = substr($text, $vCnt, 1);
	  $vArr[$i] = substr($text, $vCnt+1, $vNum);
	  $vCnt += 1 + $vNum; 
	}
	$vArr[3] = substr($text, $vCnt); // img DPL
	
	$vConId = $vArr[0];
	$vOrgMsgId = $vArr[1];
	$vDpl = $vArr[2]."-".$vArr[3];

  $row = mysqli_fetch_array(fn_sql( "SELECT A.RID, A.NICK_NM FROM TB_TL001 A, TB_TL004 B WHERE A.LID = B.LID AND B.CON_ID = '$vConId'"));
  $vRid = $row["RID"];
  $vNickNm = $row["NICK_NM"];
  //중복방지를위한 $message_id
	//$TF = $text."\nConId:".$vConId."\nOrgMsgId:".$vOrgMsgId."\nDpl:".$vDpl1."-".$vDpl2;
  $vData = array( "conId" => $vConId, "orgMsgId" => $vOrgMsgId, "dpl" => $vDpl, "msgId" => $message_id );

  $content = sendGCM("mmsimg", $vRid, $vData);
  $update = json_decode($content, true);
	
	if ( $update["success"] == true ) { // GCM 성공. 대기하세요 메시지.
		$vMsg = "[$vNickNm]에게 사진 전송 요청을 보냈습니다.\n1.통신상태에 따라 전송이 불가능 할 수 있습니다.\n2.원본이 지워진 경우 전송되지 않습니다\n3.보통 1분이내 확인 가능\n4.[$vNickNm]의 데이터 상태가 와이파이가 아니라면 데이터 통화료가 부가 될 수 있습니다.";
	} else {
		$vMsg = "[$vNickNm]에게 사진 전송 요청을 보내는데 실패하였습니다.";
		// GCM실패
	}

//$TF=$content;

$TF = sendMessage($vMsg, array("reply_to_message_id" => $message_id) );
    
?>