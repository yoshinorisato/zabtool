<html>
<head>
<LINK href="css/menu.css" type="text/css" rel=stylesheet>
<SCRIPT src="js/script.js" type="text/javascript"></SCRIPT>
<style>
.head {margin:10px;padding-top:10px;}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>ファイルインポート</title>
<!-- <link rel="stylesheet" href="../css/style.css" type="text/css"> -->

</head>
<body>
<SCRIPT >
<!--
WriteMenu(1)




//-->
</SCRIPT>

<div class="head">
<h4>インポートファイルパスを入力し、アップロードボタンを押下してください。</h4>
</div>

<?php
$auth=$_GET['auth'];

?>
<form action="upload.php" name="form1" method="post" enctype="multipart/form-data">
  <input type="hidden" name="titleline" value="">
  <table >
	  ファイル：
	  <input type="file" name="upfile" size="100"><br><br>
	  <input type="button" value="アップロード" onClick="submit()" >
  </table>
</form>
</body>
</html>
