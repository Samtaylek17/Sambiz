<?php
/**
 * Created by PhpStorm.
 * User: O-Temitayo
 * Date: 24/03/2018
 * Time: 06:04
 */
require_once  $_SERVER['DOCUMENT_ROOT'].'/SamBiz/core/init.php';
if(!is_signed_in()){
    sellers_login_error_redirect();
}
include 'includes/head.php';
include 'includes/navigation2.php';
echo '<br><br><br>';

//Delete Product
if(isset($_GET['delete'])){
    $id = sanitize($_GET['delete']);
    $restored = ("UPDATE custom_goods SET deleted = 1 WHERE id = '$id'");
    $db->query($restored);
//    $destroy = ("UPDATE products SET featured = 0 WHERE deleted = 1");
//    $db->query($destroy);
    header('Location: products.php');
//    $db->query("SELECT * FROM products WHERE deleted = 1");
}
$dbPath = '';
if(isset($_GET['add']) || isset($_GET['edit'])){
//    $brandQuery = $db->query("SELECT * FROM brand ORDER BY brand");
    $parentQuery = $db->query("SELECT * FROM categories WHERE parent = 0 ORDER BY category");
    $title = ((isset($_POST['title']) && $_POST['title'] != '')?sanitize($_POST['title']):'');
//    $brand = ((isset($_POST['brand']) && !empty($_POST['brand']))?sanitize($_POST['brand']):'');
//    $parent = ((isset($_POST['parent']) && !empty($_POST['parent']))?sanitize($_POST['parent']):'');
//    $category = ((isset($_POST['child'])) && !empty($_POST['child'])?sanitize($_POST['child']):'');
    $price = ((isset($_POST['price']) && $_POST['price'] != '')?sanitize($_POST['price']):'');
//    $list_price = ((isset($_POST['list_price']) && $_POST['list_price'] != '')?sanitize($_POST['list_price']):'');
    $description = ((isset($_POST['description']) && $_POST['description'] != '')?sanitize($_POST['description']):'');
    $email = ((isset($_POST['email']) && $_POST['email'] != '')?sanitize($_POST['email']):'');
    $phone_number = ((isset($_POST['phone_number']) && $_POST['phone_number'] != '')?sanitize($_POST['phone_number']):'');
    $address = ((isset($_POST['address']) && $_POST['address'] != '')?sanitize($_POST['address']):'');
    $saved_image = '';

    if(isset($_GET['edit'])){
        $edit_id = (int)$_GET['edit'];
        $productResults = $db->query("SELECT * FROM custom_goods WHERE id = '$edit_id'");
        $product = mysqli_fetch_assoc($productResults);
        if(isset($_GET['delete_image'])){
            $image_url = $_SERVER['DOCUMENT_ROOT'].$product['image']; echo'<br><br><br>'; echo$image_url;
            unlink($image_url);
            $db->query("UPDATE custom_goods SET image = '' WHERE id = '$edit_id'");
            header('Location: products.php?edit='.$edit_id);
        }
//        $category = ((isset($_POST['child']) && $_POST['child'] != '')?sanitize($_POST['child']):$product['categories']);
        $title = ((isset($_POST['title']) && $_POST['title'] != '')?sanitize($_POST['title']):$product['title']);
//        $brand = ((isset($_POST['brand']) && $_POST['brand'] != '')?sanitize($_POST['brand']):$product['brand']);
//        $parentQ = $db->query("SELECT * FROM categories WHERE id = '$category'");
//        $parentResult = mysqli_fetch_assoc($parentQ);
//        $parent = ((isset($_POST['parent']) && $_POST['parent'] != '')?sanitize($_POST['parent']):$parentResult['parent']);
        $price = ((isset($_POST['price']) && $_POST['price'] != '')?sanitize($_POST['price']):$product['price']);
//        $list_price = ((isset($_POST['list_price']))?sanitize($_POST['list_price']):$product['list_price']);
        $description = ((isset($_POST['description']))?sanitize($_POST['description']):$product['description']);
        $email = ((isset($_POST['email']))?sanitize($_POST['email']):$product['email']);
        $phone_number = ((isset($_POST['phone_number']))?sanitize($_POST['phone_number']):$product['phone_number']);
        $address = ((isset($_POST['address']))?sanitize($_POST['address']):$product['address']);
        $saved_image = (($product['image'] != '')?$product['image']:'');
        $dbPath = $saved_image;
    }
    if (!empty($sizes)) {
        $sizeString = sanitize($sizes);
        $sizeString = rtrim($sizeString, ',');
        $sizesArray = explode(',', $sizeString);
        $sArray = array();
        $qArray = array();
        $tArray = array();
        foreach ($sizesArray as $ss) {
            $s = explode(':', $ss);
            $sArray[] = $s[0];
            $qArray[] = $s[1];
            $tArray[] = $s[2];
        }
    } else {
        $sizesArray = array();
    }
    if($_POST) {
        $errors = array();
        $required = array('title','price','phone_number','address');
        foreach ($required as $field) {
            if ($_POST[$field] == '') {
                $errors[] = 'All Fields With an Asterisk are required.';
                break;
            }
        }

        if ($_FILES['photo']['name'] != '') {
            $photo = $_FILES['photo'];
            $name = $photo['name'];
            $nameArray = explode('.', $name);
            $fileName = $nameArray[0];
            $fileExt = $nameArray[1];
            $mime = explode('/', $photo['type']);
            $mimeType = $mime[0];
            $mimeExt = $mime[1];
            $tmpLoc = $photo['tmp_name'];
            $fileSize = $photo['size'];
            $allowed = array('png', 'jpg', 'jpeg', 'gif');
            $uploadName = md5(microtime()) . '.' . $fileExt;
            $uploadPath = BASEURL.'assets/img/'.$uploadName;
            $dbPath = '/SamBiz/assets/img/' . $uploadName;

            if ($mimeType != 'image') {
                $errors[] = 'The file must be an Image.';
            }
            if (!in_array($fileExt, $allowed)) {
                $errors[] = 'The file extension must be a png, jpg, or gif.';
            }
            if ($fileSize > 10000000) {
                $errors[] = 'The files size must be under 15MB.';
            }
            if ($fileExt != $mimeExt && ($mime == 'jpeg' && $fileExt != 'jpg')) {
                $errors[] = 'File extension does not match the file.';
            }

        }


        if (!empty($errors)) {
            echo display_errors($errors);
        } else {
            //upload file and insert into database
            if(!empty($_FILES)) {
                move_uploaded_file($tmpLoc, $uploadPath);
            }
            $insertSql = "INSERT INTO custom_goods (`title`,`price`,`image`,`description`,`email`,`phone_number`,`address`)
 VALUES ('$title','$price','$dbPath','$description','$email','$phone_number','$address') ";
            if(isset($_GET['edit'])){
                $insertSql = "UPDATE custom_goods SET title = '$title', price = '$price', image = '$dbPath', description = '$description',email = '$email',phone_number = '$phone_number', address = '$address'
  WHERE id = '$edit_id'";
            }
            $db->query($insertSql);
            if (mysqli_error($db) != ''){
                die(mysqli_error($db));
            }
            header('Location: products.php');
        }
    }
    ?>

    <h2 class="text-center"><?= ((isset($_GET['edit']))?'Edit':'Add A New');?> Product</h2><hr>
    <form action="products.php?<?= ((isset($_GET['edit']))?'edit='.$edit_id:'add=1');?>" method="POST" enctype="multipart/form-data">
        <div class="form-group col-md-3">
            <label for="title">Title*:</label>
            <input type="text" name="title" class="form-control" id="title" value="<?= $title; ?>">
        </div>
        <div class="form-group col-md-3">
            <label for="price">Price*:</label>
            <input type="text" id="price" name="price" class="form-control" value="<?= $price; ?>">
        </div>
        <div class="form-group col-md-6">
            <?php if($saved_image != ''): ?>
                <div class="saved-image"><img src="<?= $saved_image; ?>" alt="saved_image"/><br>
                    <a href="products.php?delete_image=1&edit=<?= $edit_id; ?>" class="text-danger">Delete Image</a>
                </div>
            <?php else: ?>
                <label for="photo">Product Photo:</label>
                <input type="file" name="photo" id="photo" class="form-control">
            <?php endif; ?>
        </div>
        <div class="form-group col-md-3">
            <label for="title">Email*:</label>
            <input type="text" name="email" class="form-control" id="email" value="<?= $email; ?>">
        </div>
        <div class="form-group col-md-3">
            <label for="title">Phone Number*:</label>
            <input type="text" name="phone_number" class="form-control" id="phone_number" value="<?= $phone_number; ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" rows="6"><?= $description; ?></textarea>
        </div>
        <div class="form-group col-md-6">
            <label for="description">Address*:</label>
            <textarea id="address" name="address" class="form-control" rows="6"><?= $address; ?></textarea>
        </div>
        <div class="form-group pull-right">
            <a href="products.php" class="btn btn-default">Cancel</a>
            <input type="submit" value="<?= ((isset($_GET['edit']))?'Edit':'Add');?> Product" class="btn btn-success">
        </div><div class="clearfix"></div>
    </form>
    <!-- Modal -->
    <div class="modal fade" id="sizesModal" tabindex="-1" role="dialog" aria-labelledby="sizesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal" aria-label="close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title text-center" id="sizesModalLabel">Size & Quantity</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <?php for($i=1;$i <= 12; $i++): ?>
                            <div class="form-group col-md-2">
                                <label for="size<?=$i;?>">Size:</label>
                                <input type="text" name="size<?=$i;?>" id="size<?=$i; ?>" value="<?=((!empty($sArray[$i-1]))?$sArray[$i-1]:''); ?>" class="form-control">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="qty<?=$i;?>">Quantity:</label>
                                <input type="number" name="qty<?=$i;?>" id="qty<?=$i; ?>" value="<?=((!empty($qArray[$i-1]))?$qArray[$i-1]:''); ?>" min="0" class="form-control">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="threshold<?=$i; ?>">Threshold:</label>
                                <input type="number" name="threshold<?=$i;?>" id="threshold<?=$i; ?>" value="<?=((!empty($tArray[$i-1]))?$tArray[$i-1]:''); ?>" min="0" class="form-control">
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" onclick="updateSizes(); $('#sizesModal').modal('toggle'); return false;">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }else{
    $sql = "SELECT * FROM custom_goods WHERE deleted = 0";
    $pResults = $db->query($sql);
    if(isset($_GET['featured'])){
        $id = (int)$_GET['id'];
        $featured = (int)$_GET['featured'];
        $featuredSql = "UPDATE custom_goods SET featured = '$featured' WHERE id = '$id'";
        $db->query($featuredSql);
        header('Location: products.php');
    }
    ?>
    <h2 class="text-center">Products</h2>
    <a href="products.php?add=1" class="btn btn-success pull-right" style="margin-top: -35px;" id="add-product-btn">Add Product</a>
    <div class="clearfix"></div>
    <hr>
    <table class="table table-bordered table-condensed table-striped">
        <thead><th></th><th>Products</th><th>Price</th><th>Featured</th><th>Sold</th></thead>
        <tbody>
        <?php while($product = mysqli_fetch_assoc($pResults)):
//            $childID = $product['categories'];
//            $catSql = "SELECT * FROM categories WHERE id = '$childID'";
//            $result = $db->query($catSql);
//            $child = mysqli_fetch_assoc($result);
//            $parentID = $child['parent'];
//            $pSql = "SELECT * FROM categories WHERE id = '$parentID'";
//            $pResult = $db->query($pSql);
//            $parent = mysqli_fetch_assoc($pResult);
//            $category = $parent['category'].'-'.$child['category'];
            ?>
            <tr>
                <td>
                    <a href="products.php?edit=<?= $product['id']; ?>" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil"></span></a>
                    <a href="products.php?delete=<?= $product['id']; ?>" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-remove"></span></a>
                </td>
                <td><?= $product['title']; ?></td>
                <td><?= money($product['price']); ?></td>

                <td><a href="products.php?featured=<?= (($product['featured'] == 0)?'1':'0'); ?>&id=<?= $product['id']; ?>" class=" btn btn-xs btn-default">
                        <span class="glyphicon glyphicon-<?= (($product['featured'] == 1)?'minus':'plus '); ?>"></span>
                    </a> &nbsp; <?=(($product['featured'] == 1)?'Featured Product':''); ?></td>
                <td>0</td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

<?php } include 'admin/includes/footer.php'; ?>

