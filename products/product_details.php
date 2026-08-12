<?php

session_start();
include("../db.php");

if(!isset($_GET['id']))
{
    header("Location: products.php");
    exit();
}

$id = (int)$_GET['id'];

// Every time someone opens a product, the view count increases.
$update = mysqli_prepare($conn,
"UPDATE products SET views=views+1 WHERE id=?");

mysqli_stmt_bind_param($update,"i",$id);
mysqli_stmt_execute($update);

// Get Product Details
$stmt = mysqli_prepare($conn,

"SELECT

products.*,

users.name,

users.phone,

categories.category_name

FROM products

JOIN users
ON users.id=products.user_id

JOIN categories
ON categories.id=products.category_id

WHERE products.id=?");

mysqli_stmt_bind_param($stmt,"i",$id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    die("Product not found.");
}

$product = mysqli_fetch_assoc($result);
?>

<?php include("../includes/header.php"); ?>

<section class="product-details">

<div class="left">

<img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>">

</div>

<div class="right">

<h1>

<?php echo htmlspecialchars($product['title']); ?>

</h1>

<div class="price">

Rs. <?php echo number_format($product['price']); ?>

</div>

<p>

<strong>Condition:</strong>

<?php echo htmlspecialchars($product['condition_type']); ?>

</p>

<p>

<strong>Category:</strong>

<?php echo htmlspecialchars($product['category_name']); ?>

</p>

<p>

<strong>Location:</strong>

<?php echo htmlspecialchars($product['location']); ?>

</p>

<p>

<strong>Views:</strong>

<?php echo $product['views']+1; ?>

</p>

<p>

<strong>Seller:</strong>

<?php echo htmlspecialchars($product['name']); ?>

</p>

<p>

<strong>Phone:</strong>

<?php echo htmlspecialchars($product['phone']); ?>

</p>

<?php

if(isset($_SESSION['user_id']) &&
$_SESSION['user_id']!=$product['user_id'])

{

?>

<a
href="../chat/chat.php?user=<?php echo $product['user_id']; ?>&product=<?php echo $product['id']; ?>"

class="message-btn">

Message Seller

</a>

<?php } ?>

</div>

</section>

<!-- Description -->

<section class="description">

<h2>Description</h2>

<p>

<?php echo nl2br(htmlspecialchars($product['description'])); ?>

</p>

</section>

<!-- Related Product -->

<?php
 $related = mysqli_prepare($conn,

"SELECT *

FROM products

WHERE category_id=?

AND id!=?

LIMIT 4");

mysqli_stmt_bind_param($related,
"ii",
$product['category_id'],
$id);

mysqli_stmt_execute($related);

$relatedResult = mysqli_stmt_get_result($related);
?>