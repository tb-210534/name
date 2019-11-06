<?php
//データベース接続
$posted_at = date('Y/m/d H:i:s');
try{
// ドライバ呼び出しを使用して MySQL データベースに接続します
	$dsn ="ユーザー名";//データソース名またはDSN(mysql:host=[データベースサーバーのホスト名]:dbname=[データベース名]の形式で記述。)
	$user = '　';//データベースのユーザー名
	$password = 'パスワード';//ユーザーのパスワード
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
}
catch(PDOException $e){
 echo "Error:{$e->getMessage()}";
 die();
}

//掲示板テーブルの作成
$sql = "CREATE TABLE IF NOT EXISTS keijibann1"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"
	. "name char(32),"
	. "comment TEXT," 
	. "time DATETIME,"
	. "password char(32)"//2つ目のカラム名前:password データ型 char(32)
	.");";
	$stmt = $pdo->query($sql);

$name = filter_input(INPUT_POST, 'name');//入力したnameが妥当であるか調べる（エラー回避）。  
$comment = filter_input(INPUT_POST, 'comment');//（エラー回避）  
$editNO = filter_input(INPUT_POST, 'editNO');//（エラー回避）
$edit =filter_input(INPUT_POST, 'edit');//（エラー回避）
$editpass =filter_input(INPUT_POST, 'editpass');//（エラー回避）
$password = filter_input(INPUT_POST, 'password');//（エラー回避）  
$id = filter_input(INPUT_POST, 'id');

//新規投稿

if (isset($name)&&isset($comment)&&!empty($password)) {//nameもcommentもかきこまれたら 
	if(empty($editNO)){
////プリペアドステートメント(実行したい SQL をコンパイルした 一種のテンプレートのようなもの。パラメータ変数を使用することで SQL をカスタマイズすることが可能。
//prepare=PDOStatement::execute()関数によって実行されるSQLステートメントを準備する「:」はプレースホルダー
//-> は「アロー演算子」って言って。日本語の「?の」って考えると近い
	$sql = $pdo -> prepare("INSERT INTO keijibann1 (name, comment,password,time) VALUES (:name, :comment, :password, :time)");
//プリペアドステートメントで使用するSQL文の中で、 プレースホルダに値をバインドする。
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql -> bindParam(':password', $password, PDO::PARAM_STR);
	$sql -> bindParam(':time', $posted_at, PDO::PARAM_STR);
//execute=プリペアドステートメントを実行する。
	$sql -> execute();
	}//if
}//if
if (isset($name)&&isset($comment)&&empty($password)){
echo"パスワードを入力してください";
}
//編集
if (isset($name)&&isset($comment)&&!empty($password)) {//nameもcommentもかきこまれたら 
	if(!empty($editNO)){
	$id = filter_input(INPUT_POST, 'id');
	$id = $editNO; //変更する投稿番号
	$sql = "UPDATE keijibann1 SET comment = :comment,name = :name,password = :password WHERE id = :id ";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':name', $name, PDO::PARAM_STR);
	$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
	$stmt->bindParam(':password', $password, PDO::PARAM_STR);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->execute();

	}//if
}//if
if (!empty($_POST['s_edit'])) { //編集フォームの送信があったら
	// SELECT文を変数に格納
	$sql = 'SELECT * FROM keijibann1';
	//結果を変数に格納
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	//foreach文でデータベースより取得したデータを1行ずつループ処理（連想配列で取得したデータのうち、1行文が$rowに格納される）
		foreach ($results as $row){
			if ($edit == $row["id"] && $row["password"]==$editpass ) {  //投稿番号[０]と編集対象番号が一致したらその投稿の「名前」[１]と「コメント」[２]を取得 
			
			//投稿のそれぞれの値を取得し変数に代入 
			$editnumber = $row["id"]; 
			$editname = $row["name"]; 
			$editcomment = $row["comment"]; 
			}//if
		}//forezch
			if(!empty($edit)&&empty($editpass)){
			echo"パスワードを入力してください";
			}
				if(!empty($edit)&&!empty($editpass)&&$row["password"]!==$editpass){
				 echo "パスワードが違います";
				}

}//if

//削除
$delno = filter_input(INPUT_POST, 'deleteNo');//入力したdeleteoNoが妥当であるか調べる（エラー回避） 
$sakuzyo=filter_input(INPUT_POST, 'delete');//（エラー回避）
$delpass=filter_input(INPUT_POST, 'deletepassword');//（エラー回避）
$id = $delno;
if (!empty($delno)&&!empty($delpass)&&isset($sakuzyo)) {//もし削除番号、パスワードが入力されて削除ボタンが押されたら 


$sql = 'SELECT * FROM keijibann1';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
		foreach ($results as $row){		
			if ($delno == $row["id"] && $row["password"]==$delpass ) {  //投稿番号[０]と編集対象番号とパスワードが一致したら 
			echo "削除しました";
			$sql = 'delete from keijibann1 where id=:id';
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			}//if
 		}//forezch
if(!empty($delno) && !empty($delpass) && $row["password"] !== $delpass){
echo"パスワードが違います";
}//if
}//if	
if(empty($delpass) && !empty($delno) &&isset($sakuzyo)){
	echo"パスワードを入力してください";
}

?>
<!DOCTYPE html> 
<html lang="ja"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>掲示板</title> 
</head> 
<body> 
<form action="mission_insert.php" method="post"> 
<input type="text" name="name" placeholder="名前" value="<?php if(isset($editname)) {echo $editname;} ?>"><br> 
<input type="text" name="comment" placeholder="コメント" value="<?php if(isset($editcomment)) {echo $editcomment;} ?>"><br> 
<input type="hidden" name="editNO" value="<?php if(isset($editnumber)) {echo $editnumber;} ?>"> 
<input type = "password" name = "password"placeholder="パスワード">
<input type="submit" name="submit" value="送信"> 
</form> 
<br /> 
<form action="mission_insert.php" method="POST"> 
<input type="text" name="deleteNo"placeholder="削除対象番号" size="10"> <br>
<input type = "password" name = "deletepassword"placeholder="削除パスワード" size="10">
<input type="submit" name="delete" value="削除"> 
</form> 
<br /> 
<form action="mission_insert.php" method="POST"><br /> 
<input type="text" name="edit"placeholder="編集対象番号"size="10" > <br>
<input type = "password" name = "editpass"placeholder="編集パスワード" size="10">
<input type="submit"  name="s_edit"value="編集"> 
</form> 
<br /> 
</body>
</html>
<?php
//表示
$sql = 'SELECT * FROM keijibann1';
	$stmt = $pdo->query($sql);

	$results = $stmt->fetchAll();
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].',';
		echo $row['time'].',';

	echo "<hr>";
	}
?>
