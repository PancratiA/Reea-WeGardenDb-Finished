<?php
session_start(); 

include 'db_connect.php';

$user_logged_in = isset($_SESSION['user_id']);

function addToBasket($serviceId, $name, $price) {
    global $conn ,$user_id;
    $user_id=$_SESSION['user_id'];
    if (isset($_SESSION['basket'])) {
        $found = false;
        foreach ($_SESSION['basket'] as &$item) {
            if ($item['id'] == $serviceId) {
                $item['quantity'] += 1;
                $found = true;
                $quantity=$item['quantity'];
                $sql_update = "UPDATE basket SET quantity = '$quantity' WHERE $user_id AND service_id = '$serviceId'";
         $conn->query($sql_update);
                break;
            }
        }
        if (!$found) {
            $_SESSION['basket'][] = [
                'id' => $serviceId,
                'name' => $name,
                'price' => $price,
                'quantity' => 1
            ];
            $sql_insert = "INSERT INTO basket (user_id, service_id, quantity) VALUES ('$user_id', '$serviceId', 1)";
            $conn->query($sql_insert);
        }
    } else {
        $_SESSION['basket'][] = [
            'id' => $serviceId,
            'name' => $name,
            'price' => $price,
            'quantity' => 1
        ];
        $sql_insert = "INSERT INTO basket (user_id, service_id, quantity) VALUES ('$user_id', '$serviceId', 1)";
            $conn->query($sql_insert);
    }
    
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'add_to_basket' && isset($_POST['service_id'])) {
        if ($user_logged_in) {
            $serviceId = $_POST['service_id'];
            $sql = "SELECT * FROM services WHERE id = $serviceId";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                addToBasket($row['id'], $row['name'], $row['price']);
            }
        } else {
            header("Location: login.php");
            exit();
        }
    }
}

$sql = "SELECT * FROM services";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="mystyle.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src=
"https://cdn.jsdelivr.net/npm/sweetalert2@11">

        </script>
</head>
<body>

<div class="sticky"> <i>WeGarden</i>  <br>
    <a class="contact" href="index.php#contact">Contact : +40 746 099 123   </a>
</div>

<ul class="sticky">
     <li class="sticky"><a href="index.php">Home</a></li>
     <li class="sticky"><a href="index.php#about">About us</a></li>
     <li class="sticky active"><a href="services.php">Services</a></li>
     <?php

if (isset($_SESSION['user_id'])) {
    $session_active = true;
} else {
    $session_active = false;
}
?>
<?php if ($session_active): ?>
         <li class="basket"><a href="logout.php"><i>Log Out</i></a></li>
     <?php endif; ?>

     <li class="basket"><a href="basket.php"><i  class="fa">&#xf291;</i></a></li>
     

</ul>


<div class="services">
<?php if (!$user_logged_in): ?>
    <p class="login-request">Please <a class="log "href="login.php">login</a> or <a class="log "href="register.php">register</a> to add to basket.</p>
<?php endif; ?>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="service" >
                <img class="services" src="images/<?php echo $row['picture']; ?>" alt="<?php echo $row['name']; ?>">
                <h2 class="newpage"  onclick="showPopup('<?php echo htmlspecialchars($row['name']) ; ?>','<?php echo htmlspecialchars($row['description']) ; ?>','images/<?php echo $row['picture']; ?>')" ><?php echo $row['name']; ?></h2>
                <p><?php echo $row['description']; ?></p>
                <p>$<?php echo $row['price']; ?></p>
                <?php if ($user_logged_in): ?>
                    <div style="text-align: right;">
                    <form method="post">
                        <input type="hidden" name="action" value="add_to_basket">
                        <input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">
                        <button type="submit">Add to Basket</button>
                    </form>
                </div>
                 <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No services available.</p>
    <?php endif; ?>
</div>
<script> 

function showPopup(name,description,picture) {
                Swal.fire({
                    title: name,
                    text:description,
                    imageUrl:picture,
                    imageAlt:name,
                    customClass:{
                        popup: 'popup-background',
                        confirmButton: 'ok-button'
                    }
                    
                    
                });
            }




</script>


</body>
</html>
