<?php

include 'config.php';

session_start();

if(isset($_POST['login'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $pass]);
   $row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if($select_admin->rowCount() > 0){
      $_SESSION['admin_id'] = $row['id'];
      header('location:admin_page.php');
   }else{
      $message[] = 'Username atau kata sandi salah!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>admin login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>



<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<section class="form-container">

   <form action="" method="post">
      <br><br><br>
      <h3>Login </h3>
      <!-- <br><br><br> -->
      <p>nama default = <span>admin</span> <br> & password default = <span>admin</span></p>
      <input type="text" name="name" required placeholder="masukan nama anda" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <br>
      <input type="password" name="pass" required placeholder="masukan password anda" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <br>
      <input type="submit" value="Login " class="btn" name="login">
      <a href="index.php"><input type="button" value="Back" class="btn" name="login"></a>
   </form>

</section>
   
</body>
</html>