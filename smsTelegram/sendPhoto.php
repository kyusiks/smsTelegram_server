<?
	include_once "comn.php";

  $vMsgId = "";
  $vReplyToMessageId = "";

  if ( $_REQUEST['msgId'] != "" ) $vMsgId = $_REQUEST['msgId']; // 중복체크를 위한 메시지아이디
  if ( $_REQUEST['orgMsgId'] != "" ) $vReplyToMessageId = $_REQUEST['orgMsgId']; // 원본 연결을 위한 오리지널 메시지아이디

  $vDplArr = explode("-", $vDpl);
  $vMsg = fn_makeImgId($vConId, $vDplArr[0], $vDplArr[1], $vReplyToMessageId);

  //$vData = array( "conId" => $vConId, "orgMsgId" => $vOrgMsgId, "dpl" => $vDpl, "msgId" => $message_id );
  
  $vDpl005 = $vDpl."-".$vMsgId; // 이미지는 중복 발송 가능하게 하기위해 005 테이블 체크시 위 변수를 씀.
  if  ( $vMsgId == null || $vMsgId == "" ) { // 텔레그램 커멘드가 아닌 디바이스에서 자동 호출시 vMsgId가 없이 호출됨
    $vMsgId = "";
    $vDpl005 = $vDpl;
  }

	if ( $gvUrl[$vBid] == "" ) {
		echo "<A>0</A>";
		echo "<F>222</F>";
		echo "<G>[Error]: $gvUrl[$vBid] is null</G>";
		return;
	}

	//$vNam = urldecode($vNam); // POST방식이므로 디코드한다.
	//$vMsg = urldecode($vMsg);
	$filename = "upload/".$vCid."-".$vDpl005.".jpg";
	$base = $_REQUEST['photo'];

	if ( $base != '' ) {
		// 안드로이드에서 올라왔을때
		$binary = base64_decode($base);
		$file = fopen($filename, 'wb');
		fwrite($file, $binary);
		fclose($file);
	} else {
		// 테스트용 웹페이지 업로드
		move_uploaded_file($_FILES['photo']['tmp_name'],$filename);
	}

	if ( $vMsg == '' ) $vMsg = 'Incomming MMS Images';

	$caption = fn_makeMsg($vMod, $vNum, $vNam, $vMyn, $vTim, $vDpl, $vMsg);

  // 체크 1.이미지전송포인트 2.기발송여부 3.POINT기지불여부
	$vQuery = " SELECT IMG_LIMIT
, (SELECT SVR_OUT_DH FROM TB_TL005 WHERE CON_ID = B.CON_ID AND  MODE = '$vMod' AND DPL = '$vDpl005') AS SVR_OUT_DH
, ( SELECT COUNT(1) FROM TB_TL006 WHERE CON_ID = B.CON_ID AND DPL = '$vDpl' AND USE_PT > 0 ) AS PAY_PT
FROM TB_TL002 A, TB_TL004 B
WHERE A.CID = B.CID
AND B.CON_ID = '$vConId' ";
  $row = mysqli_fetch_array(fn_sql($vQuery));

  $vSvrOutDh = $row["SVR_OUT_DH"];
  $vPayPt = $row["PAY_PT"];
  $vImgLimit = $row["IMG_LIMIT"];

  if ( $vSvrOutDh != ""  ) { // 기발송. SVR_OUT_DH는 발송시도 TL_COMP_DH는 발송완료
    fn_sql("UPDATE TB_TL005 SET DPL_CNT = DPL_CNT + 1, LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND  MODE = '$vMod' AND DPL = '$vDpl005' ");
    echo "<A>0</A>";
    echo "<F>222</F>";
    echo "<G>Duplication</G>";
    if ( is_file($filename) == true ) unlink($filename); // 파일 삭제
		return;
  }

  if ( $vImgLimit <= 0 ) { // IMG 발송에 필요한 포인트가 없다면
    if ( $vPayPt > 0 ) { // 기 지불한 포인트가 있다면 계속
    } else {
      // 포인트 부족 메시지와 리턴
      // TODO return;
    } 
  } 

	fn_sql( "INSERT INTO TB_TL005 ( CON_ID, MODE, DPL, MSG_IN_DH, SVR_IN_DH, SVR_OUT_DH, RPL_MSG_ID, DPL_CNT, LST_UPD_DH ) VALUES (
 '$vConId', '$vMod', '$vDpl005', CASE WHEN '$vTim' = '' THEN NULL ELSE FROM_UNIXTIME('$vTim') END, NOW(), NOW(), '$vReplyToMessageId', 0, NOW() )" );

  fn_sql( "INSERT INTO TB_TL006 ( CON_ID, DPL, IN_MSG_ID, MSG_IN_DH, SVR_OUT_DH, RPL_MSG_ID, LST_UPD_DH ) VALUES (
	'$vConId', '$vDpl', '$vMsgId', CASE WHEN '$vTim' = '' THEN NULL ELSE FROM_UNIXTIME('$vTim') END, NOW(), '$vReplyToMessageId', NOW() )" );


	$byte_array = unpack('C*', $caption);
	//$caption=count($byte_array);
	if ( count($byte_array) > 200 ) $caption = ""; // 텔레그램방침.
	
	$postfields = array( "photo" => "@$filename"
	                   , "filename" => $filename
	                   , "chat_id" => $vCid
	                   , "caption" => $caption
	                   , "reply_to_message_id" => $vReplyToMessageId 
	                   , "reply_markup" => "" 
	                   , "parse_mode" => "HTML" 
	                   , "disable_web_page_preview" => true 
                     , "method" => "sendPhoto" );

  // 서버로 전송 및 결과 반환
  $handle = curl_init();
  curl_setopt($handle, CURLOPT_URL, $gvUrl[$vBid]);
  curl_setopt($handle, CURLOPT_POST, true);
  curl_setopt($handle, CURLOPT_POSTFIELDS, $postfields);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  $vResult = curl_exec($handle);
  curl_close($handle);
  $response = json_decode($vResult, true);

	if ( $response['ok'] == true ) {
    if ( $vPayPt == '0' ) { // 기 지불한 포인트가 없으면 포인트 차감
      $vQuery = "UPDATE TB_TL002 SET IMG_LIMIT = IFNULL(IMG_LIMIT, 0) -1, IMG_DN_CNT = IFNULL(IMG_DN_CNT, 0) + 1, LST_UPD_DH = NOW() WHERE CID = ( SELECT CID FROM TB_TL004 WHERE CON_ID = '$vConId')";
      fn_sql($vQuery);
    }

     fn_sql("UPDATE TB_TL006 SET USE_PT = CASE WHEN '$vPayPt' = '0' THEN '1' ELSE '0' END, TL_COMP_DH = FROM_UNIXTIME('".$response["result"]["date"]."'), MSG_ID = '".$response["result"]["message_id"]."', ERR_CD = '', ERR_DSC = '', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND DPL = '$vDpl' AND IN_MSG_ID = '$vMsgId' " );

    fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = FROM_UNIXTIME('".$response["result"]["date"]."'), MSG_ID = '".$response["result"]["message_id"]."', ERR_CD = '', ERR_DSC = '', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl005' " );
		echo "<A>1</A>";
		echo "<B>".fn_setCDATA($response['result']['chat']['first_name'])."</B>";
		echo "<C>".fn_setCDATA($response['result']['chat']['last_name'])."</C>";
		echo "<D>".$response['result']['chat']['type']."</D>";
		echo "<E>".$response['result']['date']."</E>";
	} else {
		fn_sql("UPDATE TB_TL006 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND DPL = '$vDpl' AND IN_MSG_ID = '$vMsgId' " );
		fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
		echo "<A>0</A>";
		echo "<F>".fn_setCDATA($response['error_code'])."</F>";
		echo "<G>".fn_setCDATA($response['description'])."</G>";
	}
	
	if ( is_file($filename) == true ) unlink($filename); // 파일 삭제

?>
