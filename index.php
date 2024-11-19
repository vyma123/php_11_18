<?php
require_once 'includes/db.inc.php';
require_once './includes/functions.php';

include 'select_products.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css" type="text/css">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css"  />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>

    <title>PHP1</title>

    <style>
        .field .required{
    color: #52515196 ;
    
}

.form_add_products{
    font-size: 10px;
}

#categories_select{
    height: 47px;
}

#tags_select{
    height: 55px;
}
.delete_buttons{
    color: #000;
}

.delete_button{
    cursor: pointer;
    color: #000;
}

.edit_button i{
    color: #000;
}
.tag{
    white-space: nowrap;
    overflow-x: auto;
    max-width: 200px;
}
    </style>
</head>

<body>


<?php include('model_add_product.php');?>
<?php include('model_add_property.php');?>


    <section class="container">
<div class="ui centered inline loader"></div>

        <h1>PHP1</h1>
        <div class="product_header">
            <div class="product_header_top">
                <div>
                    <button id="add_product" class="ui primary button" >Add product</button>
                    <button id="add_property" class="ui button">Add property</button>
                    <a href="#" class="ui button" id="syncButton">Sync online</a>
                </div>
                <div class="ui icon input">
                    <input id="search" type="text"  oninput="loadApplyFilters(event)" placeholder="Search product..." value="">
                </div>
            </div>
            <div class="product_header_bottom">
                <select class="ui dropdown" id="sort_by">
                    <option value="date">Date</option>
                    <option value="product_name">Product name</option>
                    <option value="price">Price</option>
                </select>
                <select class="ui dropdown" id="order">
                    <option value="ASC">ASC</option>
                    <option value="DESC">DESC</option>
                </select>

                <div class="category_boxx">

                <select name="category[]" id="category" class="ui fluid search dropdown select_category" multiple="">
                <option value="">Category</option>
                <?php
                $query = "SELECT p.id, p.name_ FROM property p WHERE p.type_ = 'category'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $selectedCategory = $_GET['category'] ?? [];
                foreach ($categories as $category) {
                    $selected = in_array($category['id'], $selectedCategory) ? 'selected' : '';
                    echo "<option $selected value=\"{$category['id']}\">" . htmlspecialchars($category['name_']) . "</option>";
                }
                ?>
        </select>
        </div>
        <div class="category_boxx">
        <select name="category[]" id="tag" class="ui fluid search dropdown select_tag" name="tag[]" multiple="">
                <option value="">Select Tag</option>
                <?php
                $query = "SELECT p.id, p.name_ FROM property p WHERE p.type_ = 'tag'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $selectedTag = $_GET['tag'] ?? [];
                foreach ($tags as $tag) {
                    $selected = in_array($tag['id'], $selectedTag) ? 'selected' : '';
                    echo "<option $selected value=\"{$tag['id']}\">" . htmlspecialchars($tag['name_']) . "</option>";
                }
                ?>
            </select>
            </div>
                <div class="ui input"><input type="date" id="date_from"></div>
                <div class="ui input"><input type="date" id="date_to"></div>
                <div class="ui input"><input  onkeypress="return isNumber(event)" type="number" id="price_from" placeholder="price from"></div>
                <div class="ui input"><input  onkeypress="return isNumber(event)" type="number" id="price_to" placeholder="price to"></div>
                <button id="filter" onclick="applyFilters(event)" class="ui button">Filter</button>
            </div>
        </div>
     
        
            <!-- table -->
         <div id="inputpage"></div>
         <div id="mytable" class="mytable">
         <div id="box_table" class="box_table table_index">
            <table id="tableID" class="ui compact celled table ">
            <thead>
            <tr>
            <th class="date">Date</th>
            <th class="prd_name">Product name</th>
            <th>SKU</th>
            <th>Price</th>
            <th>Feature Image</th>
            <th class="gallery_name">Gallery</th>
            <th >Categories</th>
            <th class="tag_name">Tags</th>
            <th id="action_box" class="action_box">
                <span>Action</span>
                <div class="box_delete_buttons">
                    <a  class="delete_buttons" >
                        <i class="trash icon"></i>
                    </a>
                </div>
            </th>
            </tr>
            </thead>
            <tbody id="productTableBody">
            <?php 
            if (isset($_GET["page"])) {    
                $page = $_GET["page"];    
                if (!is_numeric($page) || $page <= 0) {
                    $page = 1;
                }
            } else {    
                $page = 1;
            }
                
                if (count($results) > 0) {
                    foreach ($results as $row){
                    $product_id = $row['id']; 
                    $imageSrc = $row['featured_image'];

                    ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date'])?></td>
                <td class="product_name"><?php echo htmlspecialchars($row['product_name'])?></td>
                <td class="sku"><?php echo htmlspecialchars($row['sku'])?></td>
                <td class="price">$<?php echo htmlspecialchars($row['price'])?></td>

                
                <td class="featured_image">
                    <?php
                    if (filter_var($imageSrc, FILTER_VALIDATE_URL)) {
                        // Display as an external URL
                        echo '<img height="30" src="' . $imageSrc . '">';
                    } else {
                        // Display as a local file in the uploads folder
                        echo '<img height="30" src="./uploads/' . $imageSrc . '">';
                    }
                    ?>
                </td>
                <?php
            $galleryImages = $row['gallery_images'];
           if (!empty($galleryImages)) {
           $galleryImagesArray = explode(', ', $galleryImages);
           echo "<td class='gallery'>
                <div class='gallery-container'>";
           foreach ($galleryImagesArray as $image) {
            echo "<img  height='30' src='./uploads/" . htmlspecialchars($image) . "'>";
           }
           echo "
           </div>
           </td>";
           } else {
            echo "<td>
            no gallery image
        </td>";
           } 
             echo "<td class='category'>" . htmlspecialchars($row['categories']) . "</td>";
             echo "<td class='tag'>" . htmlspecialchars($row['tags']) . "</td>";
             ?>
            <td>
            <input  type="hidden" name="id" id="id">
                <button type="submit"   value="<?= $row['id']?>" class="edit_button" >
                <i class="edit icon"></i>
                </button>
            
                <a class="delete_button" data-id="<?= $row['id'] ?>">
                <i class="trash icon"></i>
                </a>
            </td>
            </tr>
            <?php }}else {?>
                <tr>
                    <td colspan="9" style="text-align: center;">Product not found</td>
                </tr>
                <?php }?>
            </tbody>
            </table>
        </div>
    </div>
         

    <input type="hidden" id="currentPage" value='<?php echo $page ?>'> 


<!-- pagination -->
<div id="paginationBox" class="pagination_box">
    <div class="ui pagination menu">
        <?php
        $total_pages = ceil($total_records / $per_page_record);
        if ($page > 1) {
            echo '<a class="item pagination-link active" data-page="' . ($page - 1) . '">
            <i class="arrow left icon"></i>
            </a>';
        } else {
            echo '<a class="item disabled">
            <i class="arrow left icon"></i>
            </a>
            ';
        }

     
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $page) ? 'active' : '';
            echo '<a class="item pagination-link ' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
        }

      
        if ($page < $total_pages) {
            echo '<a class="item pagination-link" data-page="' . ($page + 1) . '">
        <i class="arrow right icon"></i>
            </a>';
        } else {
            echo '<a class="item disabled">
        <i class="arrow right icon"></i>
            </a>';
        }
        ?>
    </div>
</div>
</section>
<script src="script.js">
</script>
</body>
</html>