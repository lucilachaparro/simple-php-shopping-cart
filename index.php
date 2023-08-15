<?php

session_start();

require_once("dbcontroller.php");
$db_handle = new DBController();

require_once("messageController.php"); 
$msg = new MSGController;


if(!empty($_GET["action"])) {
switch($_GET["action"]) {

	case "add":
		if(!empty($_POST["qty"])) {

			//fetch from db product info by code
			$productByCode = $db_handle->runQuery("SELECT * FROM product WHERE code='" . $_GET["code"] . "'");

			//check that qty is not greater than stock available
			if($_POST["qty"]<=$productByCode[0]["stock"]) {

			//array of items to add to cart, code as key
			$itemArray = array($productByCode[0]["code"]=>array('name'=>$productByCode[0]["name"], 'code'=>$productByCode[0]["code"], 'qty'=>$_POST["qty"], 'price'=>$productByCode[0]["price"], 'image'=>$productByCode[0]["image"], 'stock'=>$productByCode[0]["stock"]));
			
			if(!empty($_SESSION["cartItem"])) {

				//if product with that code is already in cart, update the qty
				if(in_array($productByCode[0]["code"],array_keys($_SESSION["cartItem"]))) {

					foreach($_SESSION["cartItem"] as $k => $v) {
							if($productByCode[0]["code"] == $k) {
								if(empty($_SESSION["cartItem"][$k]["qty"])) {
									$_SESSION["cartItem"][$k]["qty"] = 0;
								}
								$_SESSION["cartItem"][$k]["qty"] += $_POST["qty"];
								
							}
					}

				} else {
					
					//else, add the new product to the cart array
					$_SESSION["cartItem"] = array_merge($_SESSION["cartItem"],$itemArray);
					
				}

			} else {

				//else, add the first product to the cart
				$_SESSION["cartItem"] = $itemArray;
				
			}

			//update the product stock on the db (substract qty)
			$db_handle->substractStock($productByCode[0]["code"], $_POST["qty"]);


			}
		}
	$msg->sendMessage("addToCart", $itemArray, $_GET["code"]);
	break;

	case "remove":
		if(!empty($_SESSION["cartItem"])) {

			
			foreach($_SESSION["cartItem"] as $k => $v) {
					if($_GET["code"] == $k)

						$msg->sendMessage("removeFromCart", $_SESSION["cartItem"][$k], $_SESSION["cartItem"][$k]["code"]);

						//update the product stock on the db (add qty)
					 	$db_handle->addStock($_SESSION["cartItem"][$k]["code"], $_SESSION["cartItem"][$k]["qty"]);
						//unset the item
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
}
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

<?php
if(isset($_SESSION["cartItem"])){
    $totalQty = 0;
    $totalPrice = 0;
?>	
<a id="btnEmpty" href="index.php?action=empty">Vaciar</a>
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

				<!--input para cambiar la cantidad del producto-->

				<td style="text-align:right;">
					<form method="post" action="index.php?action=updateQty&code=<?php echo $item["code"]; ?>">
					<input type="number" class="product-quantity" name="qty" value="<?php echo $item["qty"]; ?>" size="1" max="<?php echo $item["stock"];?>"/>
					</form>
				</td>
				
				<td  style="text-align:right;"><?php echo CURRENCY_SYMBOL.$item["price"].' '.CURRENCY; ?></td>
				<td  style="text-align:right;"><?php echo CURRENCY_SYMBOL. number_format($itemPrice,2).' '.CURRENCY; ?></td>
				<td style="text-align:center;"><a href="index.php?action=remove&code=<?php echo $item["code"]; ?>" class="btnRemoveAction"><img src="icon-delete.png" alt="Quitar Item" /></a></td>
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
<form action="orderView.php"><button type="submit" class="btn btn-success">Comprar</button></form>
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
	<div class="txt-heading">Productos</div>
	<?php
	$productArray = $db_handle->runQuery("SELECT * FROM product ORDER BY id ASC");
	if (!empty($productArray)) { 
		foreach($productArray as $key=>$value){
	?>
		<div class="product-item">
			<form method="post" action="index.php?action=add&code=<?php echo $productArray[$key]["code"]; ?>">
			<div class="product-image">
				<img src="<?php echo "images/".$productArray[$key]["image"]; ?>" width="240" height="180" >
			</div>
			<div class="product-tile-footer">
				<div class="product-title"><?php echo $productArray[$key]["name"]; ?></div>

				<div class="product-price">

					<?php if($productArray[$key]["stock"] <= 0) {?>				
						<button type="button" class="btn btn-outline-secondary" disabled>Sin stock</button>
					<?php }else{
					echo CURRENCY_SYMBOL.$productArray[$key]["price"].' '.CURRENCY;}?>

				</div>

				<div class="cart-action">	
				<?php if($productArray[$key]["stock"] <= 0) {?>
					<input type="submit" value="Añadir al carro" class="btnAddAction" disabled/>
				<?php }else {?>
					<input type="number" class="product-quantity" name="qty" min="1" size="1" max="<?php echo $productArray[$key]["stock"];?>"/>
					<input type="submit" value="Añadir al carro" class="btnAddAction" />
				<?php }?>
				</div>
			</div>
			</form>
		</div>
	<?php
		}
	}
	?>
</div>
</BODY>
</HTML>