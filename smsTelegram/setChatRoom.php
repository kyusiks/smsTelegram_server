<?
include_once "comn.php";

if ( $vCid == '' ) {
	echo "<A>0</A>"; // false
	echo "<B>".fn_setCDATA("CID부재")."</B>";
	mysqli_close($conn); // 디비 접속 끊기
	return;
}

if ( $vLid == '' ) {
	$vLid = fn_getLid();
}

if ( $vLid == '' ) {
	echo "<A>0</A>"; // false
	echo "<B>".fn_setCDATA("LID 작성 실패")."</B>";
	mysqli_close($conn); // 디비 접속 끊기
	return;
}	

fn_setChatRoom();





	mysqli_close($conn); // 디비 접속 끊기
	////////////////////
	// 끝.
	////////////////////
	
	function fn_getLid() { // 로컬아이디 작성
		global $conn;

		$result = mysqli_query($conn,
"SELECT MAX(IF_NULL(LID, 0)) + 1 AS LID
 FROM TB_SMS01 ");

		while ( $row = mysqli_fetch_array($result) ) {
			return $row['LID'];
		}
}

 function fn_chatRoom() { // 필터등록
		global $conn,$vLid,$vCid,$vAppVer;

		$result = mysqli_query($conn,
"SELECT ID_SEQ, MAX_NO, THUMB_NAIL, LST_UPD_DH - '$vLstUpdDh' AS LST_UPD_DH
   FROM TB_WT001 A WHERE EXISTS (SELECT 1 FROM TB_WT003 WHERE ID_SEQ = A.ID_SEQ)
	  AND LST_UPD_DH + 0 > '$vLstUpdDh' + 0 AND NAME != ''
	  AND ID_SEQ IN ($vIdSeqRep) ");

		while ( $row = mysqli_fetch_array($result) ) {
			echo "<C>".$row['ID_SEQ']."</C>";
			echo "<G>".$row['MAX_NO']."</G>";
			echo "<P>".fn_setCDATA($row['THUMB_NAIL'])."</P>";
			echo "<F>".$row['LST_UPD_DH']."</F>";
		}

 function fn_mode_V0() { // 내구독목록에 있는 툰만 업데이트
		global $conn,$vMode,$vMyNm,$vParam,$vLstUpdDh,$vAppVer;

		$result = mysqli_query($conn,
"SELECT ID_SEQ, MAX_NO, THUMB_NAIL, LST_UPD_DH - '$vLstUpdDh' AS LST_UPD_DH
   FROM TB_WT001 A WHERE EXISTS (SELECT 1 FROM TB_WT003 WHERE ID_SEQ = A.ID_SEQ)
	  AND LST_UPD_DH + 0 > '$vLstUpdDh' + 0 AND NAME != ''
	  AND ID_SEQ IN ($vIdSeqRep) ");

		while ( $row = mysqli_fetch_array($result) ) {
			echo "<C>".$row['ID_SEQ']."</C>";
			echo "<G>".$row['MAX_NO']."</G>";
			echo "<P>".fn_setCDATA($row['THUMB_NAIL'])."</P>";
			echo "<F>".$row['LST_UPD_DH']."</F>";
		}

 function fn_mode_V0() { // 내구독목록에 있는 툰만 업데이트
		global $conn,$vMode,$vMyNm,$vParam,$vLstUpdDh,$vAppVer;

		$result = mysqli_query($conn,
"SELECT ID_SEQ, MAX_NO, THUMB_NAIL, LST_UPD_DH - '$vLstUpdDh' AS LST_UPD_DH
   FROM TB_WT001 A WHERE EXISTS (SELECT 1 FROM TB_WT003 WHERE ID_SEQ = A.ID_SEQ)
	  AND LST_UPD_DH + 0 > '$vLstUpdDh' + 0 AND NAME != ''
	  AND ID_SEQ IN ($vIdSeqRep) ");

		while ( $row = mysqli_fetch_array($result) ) {
			echo "<C>".$row['ID_SEQ']."</C>";
			echo "<G>".$row['MAX_NO']."</G>";
			echo "<P>".fn_setCDATA($row['THUMB_NAIL'])."</P>";
			echo "<F>".$row['LST_UPD_DH']."</F>";
		}





		$vMsg = fn_makeMsg($vCid, $vMod, $vNum, $vNam, $vMyn, $vTim, $vDpl, $vMsg);

    $Result = GetCurl(BASE_URL.'sendMessage?chat_id='.$vCid.'&text='.urlencode($vMsg).'&parse_mode=HTML' );
    /*
    $a = $Result['ok'];
    $b = $Result['result']['message_id'];
    $c = $Result['result']['chat']['first_name'];
    $d = $Result['result']['chat']['last_name'];
    $e = $Result['result']['chat']['type'];
    $f = $Result['result']['date'];
    echo $a."<br>".$b."<br>".$c."<br>".$d."<br>".$e."<br>".$f."<br>";
    */
    echo "<A>".$Result['ok']."</A>";
    echo "<B>".fn_setCDATA($Result['result']['chat']['first_name'])."</B>";
    echo "<C>".fn_setCDATA($Result['result']['chat']['last_name'])."</C>";
    echo "<D>".$Result['result']['chat']['type']."</D>";
    echo "<E>".$Result['result']['date']."</E>";

/*
	TB_WT001 웹툰 정보
	TB_WT002 사이트 설정
	TB_WT003 웹툰당 회차정보
	TB_WT004 시스템 설정
	TB_WT005 내가보는웹툰 목록 저장
	TB_WT006 사용자 정보
	TB_LOG01 디버깅로그

  트래픽 감소를 위해 컬럼 단축
	A	OK // 1:true 0:false
	B	RT_FST_NM
	C	RT_LST_NM
	D	RT_TYPE
	E	TRN_COMP_DH
	=----
	F	LST_UPD_DH / 이 컬럼은 LST_UPD_DH - '$vLstUpdDh' AS LST_UPD_DH 이렇게 보낸다. 트래픽 감소를 위해.(로컬에서 vParam값을 더할것이다.)
	G	MAX_NO
	H	NAME
	I	SEL_MODE
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
