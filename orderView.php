<?php
session_start();
require_once("config.php");

require_once("dbcontroller.php");
$db_handle = new DBController();

require_once("messageController.php"); 
$msg = new MSGController;


if(!empty($_GET["action"])) {
    switch($_GET["action"]) {
        case "remove":
            if(!empty($_SESSION["cartItem"])) {
                foreach($_SESSION["cartItem"] as $k => $v) {
                        if($_GET["code"] == $k)

                        $msg->sendMessage("removeFromCart", $_SESSION["cartItem"][$k], $_SESSION["cartItem"][$k]["code"]);

						//update the product stock on the db (add qty)
                        $db_handle->addStock($_SESSION["cartItem"][$k]["code"], $_SESSION["cartItem"][$k]["qty"]);

                            unset($_SESSION["cartItem"][$k]);				
                        if(empty($_SESSION["cartItem"]))
                            unset($_SESSION["cartItem"]);
                }
            }
        break;
        case "empty":

            foreach($_SESSION["cartItem"] as $k => $v) {
    
            //update the product stock on the db (add qty)
            $db_handle->addStock($_SESSION["cartItem"][$k]["code"], $_SESSION["cartItem"][$k]["qty"]);
            }
    
            //unset the whole cart array
            unset($_SESSION["cartItem"]);
        $msg->sendOtherMessage("empty");
        break;	

    }
};
?>

<HTML>
<HEAD>
<TITLE>Carrito de compras PHP</TITLE>
<!-- Bootstrap core CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" 
integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<!--  Custom CSS -->
<link href="style.css" type="text/css" rel="stylesheet" />

</HEAD>
<BODY>


<div id="shopping-cart">
<div class="txt-heading">Carrito de compra</div>

<a id="btnEmpty"  href="index.php" style="border: #919191 1px solid; color: #000000;">Seguir comprando</a>

<?php

if(isset($_SESSION["cartItem"])){
    $totalQty = 0;
    $totalPrice = 0;
?>	
<table class="tbl-cart" cellpadding="10" cellspacing="1">
<tbody>
<tr>
<th style="text-align:left;">Nombre</th>
<th style="text-align:left;">Codigo</th>
<th style="text-align:right;" width="5%">Cantidad</th>
<th style="text-align:right;" width="10%">Precio unitario</th>
<th style="text-align:right;" width="10%">Precio</th>
<th style="text-align:center;" width="5%">Quitar</th>
</tr>	

<?php		
    foreach ($_SESSION["cartItem"] as $item){
        $itemPrice = $item["qty"]*$item["price"];
		?>
				<tr>
				<td><img src="<?php echo "images/".$item["image"]; ?>" class="cart-item-image" /><?php echo $item["name"]; ?></td>
				<td><?php echo $item["code"]; ?></td>
				<td style="text-align:right;"><?php echo $item["qty"]; ?></td>
				<td  style="text-align:right;"><?php echo CURRENCY_SYMBOL.$item["price"].' '.CURRENCY; ?></td>
				<td  style="text-align:right;"><?php echo CURRENCY_SYMBOL. number_format($itemPrice,2).' '.CURRENCY; ?></td>
				<td style="text-align:center;"><a href="orderView.php?action=remove&code=<?php echo $item["code"]; ?>" class="btnRemoveAction"><img src="icon-delete.png" alt="Quitar Item" /></a></td>
				</tr>
				<?php
				$totalQty += $item["qty"];
				$totalPrice += ($item["price"]*$item["qty"]);
		}
		?>

<tr>
<td colspan="2" align="right">Total:</td>
<td align="right"><?php echo $totalQty; ?></td>
<td align="right" colspan="2"><strong><?php echo CURRENCY_SYMBOL.number_format($totalPrice, 2)." ".CURRENCY; ?></strong></td>
<td></td>
</tr>
</tbody>
</table>	
<div class="text-right">
<a id="btnEmpty" href="orderView.php?action=empty">Vaciar</a>
</div>
  <?php
} else {
?>
<div class="no-records">El carro está vacío</div>
<?php 
}
?>

</div>

<div id="product-grid">
	<div class="txt-heading">Detalles de contacto</div>
<div class="row">
    <div class="col"></div>
<div class="col-sm-4">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name">Nombre</label>
                                <input type="text" class="form-control" name="first_name" value="" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name">Apellido</label>
                                <input type="text" class="form-control" name="last_name" value="" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone">Teléfono</label>
                            <input type="text" class="form-control" name="phone" value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name">Dirección</label>
                            <input type="text" class="form-control" name="address" value="" required>
                        </div>
                        <input type="hidden" name="action" value="placeOrder"/>
                        <input class="btn btn-success btn-block" type="submit" name="checkoutSubmit" value="Pagar">
                    </form>
</div>
<div class="col"></div>
</div>
</div>