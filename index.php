<?php

include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'Nama pengguna atau email sudah ada!';
   }else{
      if($pass != $cpass){
         $message[] = 'Konfirmasi kata sandi tidak cocok!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'Berhasil mendaftar, silahkan masuk !';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'Jumlah keranjang diperbarui!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'silahkan login terlebih dahulu!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'Sudah ditambahkan ke keranjang';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'Ditambahkan ke keranjang!';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'Silahkan login terlebih dahulu!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = ''.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'pesanan berhasil dilakukan!';
      }else{
         $message[] = 'keranjang Anda kosong!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bubur Aegis</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

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

<!-- header section starts  -->

<header class="header">

   <section class="flex">

      <a href="#home" class="logo"><span>B</span>ubur.</a>

      <nav class="navbar">
         <a href="#home">home</a>
         <a href="#about">tentang</a>
         <a href="#menu">menu</a>
         <a href="#order">pesanan</a>
         <a href="#faq">faq</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
      </div>

   </section>

</header>

<!-- header section ends -->

<div class="user-account">

   <section>

      <div id="close-account"><span>Anda belum masuk !</span></div>
      <div id="close-account"><a href="admin_login.php" target="_blank"><span>Login Sebagai Admin !</span></a></div>

      <div class="user">
         <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
               while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>Selamat datang ! <span>'.$fetch_user['name'].'</span></p>';
                  echo '<a href="index.php?logout" class="btn">logout</a>';
               }
            }else{
               echo '<p><span>Anda tidak masuk sekarang!</span></p>';
            }
         ?>
      </div>

      <div class="display-orders">
         <?php
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
               }
            }else{
               echo '<p><span>keranjang anda kosong!</span></p>';
            }
         ?>
      </div>

      <div class="flex">

         <form action="user_login.php" method="post">
            <h3>masuk sekarang</h3>
            <input type="email" name="email" required class="box" placeholder="masukan email anda" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="masukan password anda" maxlength="20">
            <input type="submit" value="login sekarang" name="login" class="btn">
         </form>

         <form action="" method="post">
            <h3>daftar sekarang</h3>
            <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="masukan username anda" maxlength="20">
            <input type="email" name="email" required class="box" placeholder="masukan email anda" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="masukan password anda" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" required class="box" placeholder="konfirmasi password anda" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="daftar sekarang" name="register" class="btn">
         </form>

      </div>

   </section>

</div>

<div class="my-orders">

   <section>

      <div id="close-orders"><span>tutup</span></div>

      <h3 class="title"> pesanan saya </h3>

      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
      ?>
      <div class="box">
         <p> tanggal : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> nama : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> no hp : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> alamat : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> pembayaran : <span><?= $fetch_orders['method']; ?></span> </p>
         <p> pesanan anda : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> pesanan anda : <span>Rp. <?= $fetch_orders['total_price']; ?>/-</span> </p>
         <p> status pembayaran: <span style="color:<?php if($fetch_orders['payment_status'] == 'pending'){ echo 'red'; }else{ echo 'green'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">Belum ada yang dipesan!</p>';
      }
      ?>

   </section>

</div>

<div class="shopping-cart">

   <section>

      <div id="close-cart"><span>tutup</span></div>

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $sub_total; 
      ?>
      <div class="box">
         <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('Hapus item keranjang ini?');"></a>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="content">
            <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
            <form action="" method="post">
               <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
               <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
         </form>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty"><span>keranjang anda kosong!</span></p>';
      }
      ?>

      <div class="cart-total"> Total keseluruhan : <span>Rp. <?= $grand_total; ?>/-</span></div>

      <a href="#order" class="btn">pesan sekarang</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="slide-container">

         <div class="slide active">
               <div class="image">
                  <img src="images/bubur5-home.png" alt="">
               </div>
               <div class="content">
                  <h3>Bubur Ayam Special</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
         </div>

         <div class="slide">
               <div class="image">
                  <img src="images/bubur6-home.png" alt="">
               </div>
               <div class="content">
                  <h3>Bubur Ayam Ati Ampela</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
         </div>

         <div class="slide">
               <div class="image">
                  <img src="images/bubur4-home.png" alt="">
               </div>
               <div class="content">
                  <h3>Bubur Ayam Telor</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
         </div>

      </div>

   </section>

   </div>

   <!-- about section starts  -->

   <section class="about" id="about">

            <h1 class="heading">tentang kami</h1>

      <div class="box-container">
      
            <div class="box">
               <img src="images/about1.png" alt="">
               <h3>Dibuat dengan cinta</h3>
               <p>Bubur menjadi salah satu makanan bertekstur lembut yang banyak digemari berbagai kalangan. Biasanya bubur menjadi menu yang cocok untuk sarapan di pagi hari.</p>
               <a href="#menu" class="btn">menu kami</a>
            </div>

            <div class="box">
               <img src="images/about2.png" alt="">
               <h3>Pengiriman </h3>
               <p>Pengiriman hanya membutuhkan 30 menit dan akan sampai dengan aman </p>
               <br><br><br>
               <a href="#menu" class="btn">menu kami</a>
            </div>
      
            <div class="box">
               <img src="images/about3.png" alt="">
               <h3>Bagikan dengan teman</h3>
               <p>Jangan lupa berbagi bubur ataupun share bubur kami kepada teman-teman anda!</p>
               <br><br><br><br><br><br>
               <a href="#menu" class="btn">menu kami</a>
            </div>
      
      </div>

   </section>

<!-- about section ends -->

<!-- menu section starts  -->

<section id="menu" class="menu">

   <h1 class="heading">menu kami</h1>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price">Rp. <?= $fetch_products['price'] ?>/-</div>
         <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><?= $fetch_products['name'] ?></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add_to_cart" value="Masukan Keranjang">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">Belum ada produk yang ditambahkan!</p>';
      }
      ?>

   </div>

</section>

<!-- menu section ends -->

<!-- order section starts  -->

<section class="order" id="order">

   <h1 class="heading">pesan sekarang</h1>

   <form action="" method="post">

   <div class="display-orders">

   <?php
         $grand_total = 0;
         $cart_item[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $sub_total; 
               $cart_item[] = $fetch_cart['name'].' ( '.$fetch_cart['price'].' x '.$fetch_cart['quantity'].' ) - ';
               $total_products = implode($cart_item);
               echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
            }
         }else{
            echo '<p class="empty"><span>keranjang Anda kosong!</span></p>';
         }
      ?>

   </div>

      <div class="grand-total"> Total keseluruhan : <span>Rp. <?= $grand_total; ?>/-</span></div>

      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

         <div class="flex">
               <div class="inputBox">
                  <span>nama kamu :</span>
                  <input type="text" name="name" class="box" required placeholder="masukan nama kamu" maxlength="20">
               </div>
               <div class="inputBox">
                  <span>no hp kamu :</span>
                  <input type="number" name="number" class="box" required placeholder="masukan your number" min="0">
               </div>
               <div class="inputBox">
                  <span>pembayaran</span>
                  <select name="method" class="box">
                     <option value="bayar ditempat">--Pilih Pembayaran--</option>
                     <option value="bayar ditempat">bayar ditempat</option>
                     <option value="kartu kredit">kartu kredit</option>
                     <option value="uang digital">uang digital</option>
                     <option value="transfer bank">transfer bank</option>
                  </select>
               </div>
               <div class="inputBox">
                  <span>baris alamat 01 :</span>
                  <input type="text" name="flat" class="box" required placeholder="masukan alamat 01" maxlength="50">
               </div>
               <div class="inputBox">
                  <span>baris alamat 02 :</span>
                  <input type="text" name="street" class="box" required placeholder="masukan alamat 02" maxlength="50">
               </div>
               <div class="inputBox">
                  <span>kode pos :</span>
                  <input type="number" name="pin_code" class="box" required placeholder="80352" min="0">
               </div>
         </div>
      
         <input type="submit" value="pesan sekarang" class="btn" name="order">
      
         </form>
      
      </section>

<!-- order section ends -->

      <!-- faq section starts  -->

      <section class="faq" id="faq">

         <h1 class="heading">FAQ</h1>

         <div class="accordion-container">

               <div class="accordion active">
                  <div class="accordion-heading">
                     <span>Bagaimana cara kerjanya?</span>
                     <i class="fas fa-angle-down"></i>
                  </div>
                  <p class="accrodion-content">
                     Anda tinggal order di website bubur aegis dan tentukan menunya selanjutnya isi data diri anda.
                  </p>
               </div>

               <div class="accordion">
                  <div class="accordion-heading">
                     <span>Berapa lama waktu yang dibutuhkan untuk pengiriman?</span>
                     <i class="fas fa-angle-down"></i>
                  </div>
                  <p class="accrodion-content">
                     Hanya membutuhkan waktu 30 menit dan bubur akan .
                  </p>
               </div>

               <div class="accordion">
                  <div class="accordion-heading">
                     <span>Bisakah saya memesan untuk pesta besar?</span>
                     <i class="fas fa-angle-down"></i>
                  </div>
                  <p class="accrodion-content">
                     Bubur aegis tidak hanya diperjualkan untuk pribadi, bubur aegis kini bisa untuk pesta besar.
                  </p>
               </div>

               <div class="accordion">
                  <div class="accordion-heading">
                     <span>Berapa banyak protein yang dikandungnya?</span>
                     <i class="fas fa-angle-down"></i>
                  </div>
                  <p class="accrodion-content">
                     Seporsi bubur ayam tanpa topping atau sekitar 240 gram mengandung 372 kkal, 27,56 gram protein, 12,39 gram lemak, dan 36,12 gram karbohidrat. Kalau kamu menambahkan sate usus sebagai makanan pendamping,
                     maka artinya kamu menambahkan 94 kalori, 2,06 gram lemak, 17,66 protein.
                  </p>
               </div>

         </div>

      </section>

      <!-- faq section ends -->


      <!-- footer section starts  -->

      <section class="footer">

      <div class="box-container">

         <div class="box">
               <i class="fas fa-phone"></i>
               <h3>no hp</h3>
               <p>+62 895-7030-57031</p>
               <p>+62 831-1464-7830</p>
         </div>

         <div class="box">
               <i class="fas fa-map-marker-alt"></i>
               <h3>alamat kami</h3>
               <p>Bandung, Indonesia</p>
         </div>

         <div class="box">
               <i class="fas fa-clock"></i>
               <h3>buka</h3>
               <p>06.00 Sampai 10.00</p>
         </div>

         <div class="box">
               <i class="fas fa-envelope"></i>
               <h3>alamat Email</h3>
               <p>regisrifaldi44@gmail.com</p>
               <p>buburaegis18@gmail.com</p>
         </div>

      </div>

      <div class="credit">
         &copy; copyright @ 2023 by <span>Regis Syawaludin Rifaldi </span> | all rights reserved!
      </div>

      </section>

      <!-- custom js file link  -->
      <script src="js/script.js"></script>

</body>
</html>