가나다라
<?
include "comn.php";
header('Content-Type: text/html; charset=euckr ');
    $vMsg = ms("W1001", array("일번","이번")); // 이미지 전송중...
echo $vMsg;
  $vErrDsc = "123한글123";
echo $vErrDsc;
echo "1";	
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2); 

echo $lang;
 $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE']; 

echo "2".$lang."3";


echo fn_err("2222","");
echo fn_err("2222","xml");



$vConId = "2";
  echo "1:".$vBid;
  fn_setLCBByConId();
  echo "2:".$vBid;
  echo "2:".$vCid;
  echo "2:".$vLid;
?>
fi1le upload program<br>select the file<br/>
<form method="post" action="./sendPhoto.php" acti1on="./sendMessage.php" enctype="multipart/form-data">
<input type="file" size=100 name="photo"><hr>
<input type="text" value="159284966" name="cid"><br>
<input type="text" value="message test" name="msg"> <br>
<input type="text" value="2" name="conId">conid<br>
<input type="text" value="1" name="lid">lid<br>
<input type="text" value="0" name="bid">bid<br>
<input type="text" value="img" name="mod"> <br>
<input type="text" value="<?=time();?>" name="dpl"> <br>
<input type="text" value="<?=time();?>" name="tim"> <br>

 
<input type="submit" value="send" name="send">
</form>


fi1le upload program<br>select the file<br/>
<form method="post" action="https://api.telegram.org/bot114864593:AAFoLCPVdfLV5TIu2ZO_IvECAIT7eKUbX-c/setWebhook">
<input type="file" size=100 name="certificate"><hr>
<input type="text" value="https://203.226.206.44:8443/index.htm" name="url">
<input type="submit" value="send" name="send">
</form>
