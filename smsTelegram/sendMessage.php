<?
include_once "comn.php";

//  $vBid = "0"; //TODO 로컬에서던지게.
//  $vConId = "2"; //TODO 로컬에서던지게.
//  $vImg = "|983|984|985";
	if ( $vMod == "gcm" ) {
		$vMethod = $_REQUEST['method'];
		if ( $vMethod == "mmsimg" ) {
			$vMsgId = $_REQUEST['msgId'];
			$vDpl = $vDpl."-".$vMsgId;
	    $vMsg = ms("W1001"); // 응답 받았습니다. 이미지 전송중....
	
		  $vResult = sendMessage($vMsg, array( 'parse_mode' => 'HTML', 'reply_to_message_id' => $vMsgId  ));
		  
			return;
		} else if ( $vMethod == "wakeup" ) {
			// 최초 gcm은 느리지만, 이후 두번째부터는 응답이 빠르기때문에
			// 최초 기기 깨우는 용도. 아무 로직도 없다.
			return;
		} else {
			return;
		}
		
	}


  $vMsg = fn_makeMsg($vMod, $vNum, $vNam, $vMyn, $vTim, $vDpl, $vMsg);
  $vResult = sendMessage($vMsg, array( 'parse_mode' => 'HTML' ));

  $Result = json_decode($vResult, true);
  if ( $Result['ok'] == true ) {
    echo "<A>1</A>";
    echo "<B>".fn_setCDATA($Result['result']['chat']['first_name'])."</B>";
    echo "<C>".fn_setCDATA($Result['result']['chat']['last_name'])."</C>";
    echo "<D>".$Result['result']['chat']['type']."</D>";
    echo "<E>".$Result['result']['date']."</E>";

  	if ( $vMod == "mms" && $vImg != "" ) { // img를 포함한 mms라면
	    $vQuery = "SELECT AUTO_IMG_YN FROM TB_TL002 WHERE CID = (SELECT CID FROM TB_TL004 WHERE CON_ID = '$vConId')";
	    $row = mysqli_fetch_array(fn_sql($vQuery));
	    $vAutoImg = $row["AUTO_IMG_YN"];
   		$vReplyToMessageId = $Result['result']['message_id'];
  		
  		if ( $vAutoImg == "Y" ) {
    		echo "<H>Y</H>"; // 이미지 자동전송하라
    		echo "<I>".$vReplyToMessageId."</I>"; // 이것에 대한 답장으로 전송하라
  		} else { // 이미지 링크를 만들어 제공.
    		echo "<H>N</H>"; // 이미지 자동전송X
  		}
  		// 자동전송인 경우에도 이 메시지를 내보내는데
  		// 추후 다시 다운로드 받을 수 있도록 하기 위해서이다.
			$vMod = "pmg";
		  $vMsg = fn_makeImgId($vConId, $vDpl, $vImg, $vReplyToMessageId);
		  $vMsg = fn_makeMsg($vMod, "", "Image(s)", "", "", $vDpl, $vMsg);
      sendMessage($vMsg, array('parse_mode' => 'HTML', 'reply_to_message_id' => $vReplyToMessageId ));
      //////////////////
  	}

  } else {
    echo "<A>0</A>";
    echo "<F>".fn_setCDATA($Result['error_code'])."</F>";
    echo "<G>".fn_setCDATA($Result['description'])."</G>";
  }



  /*
{"ok":true,"result":{"message_id":3120,"from":{"id":114864593,"first_name":"\uc559\ub7a9_\ud14c\uc2a4\ud2b8_\ubd07","username":"anglab_test_bot"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1458190053,"text":"\ud83d\udce9 ALARM (120)\ud83d\udd3b\n\nmessage test"}}

  $a = $Result['ok'];
  $b = $Result['result']['message_id'];
  $c = $Result['result']['chat']['first_name'];
  $d = $Result['result']['chat']['last_name'];
  $e = $Result['result']['chat']['type'];
  $f = $Result['result']['date'];
  echo $a."<br>".$b."<br>".$c."<br>".$d."<br>".$e."<br>".$f."<br>";
  
  */
  
/*
	TB_WT001 웹툰 정보
	TB_WT002 사이트 설정
	TB_WT003 웹툰당 회차정보
	TB_WT004 시스템 설정
	TB_WT005 내가보는웹툰 목록 저장
	TB_WT006 사용자 정보
	TB_LOG01 디버깅로그

  트래픽 감소를 위해 컬럼 단축
	A	OK 0실패 1성공
	B	RT_FST_NM
	C	RT_LST_NM
	D	RT_TYPE
	E	TL_COMP_DH
	F ERR_CD	error_code 텔레그램 에러코드
	G	ERR_DSC	 description 텔레그램 에러설명
	H	AUTO_IMG_SND mms의 이미지를 전송하라고 리턴. 
	I	MSG_ID
	=----
	J	SET_CONT
	K	SET_ID
	L	SET_VALUE
	M	SITE
	N	SORT
	O	THUMB_COMN
	P	THUMB_NAIL
	Q	USE_YN
	R	CNT
	S	ARTIST 작가명 ks20151125
	T	SELL_YN 유료여부 ks20151125
	U	ORG_UPD_DH 웹서버의 업데이트 일자 ks20151125
*/

?>
