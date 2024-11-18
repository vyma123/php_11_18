<?php
require_once 'includes/db.inc.php';
require_once './includes/functions.php';

//index.php

$results = select_all_products($pdo);

$searchTerm = isset($_GET['search']) ? test_input($_GET['search']) : '';
$per_page_record = 5;
$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$page = filter_var($page, FILTER_VALIDATE_INT) !== false ? (int)$page : 1;

$start_from = ($page - 1) * $per_page_record;

$query = "SELECT * FROM products LIMIT :start_from, :per_page";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


$allowed_sort_columns = ['id', 'product_name', 'price'];
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort_columns) ? $_GET['sort_by'] : 'id';
$allowed_order_directions = ['ASC', 'DESC'];
$order = isset($_GET['order']) && in_array($_GET['order'], $allowed_order_directions) ? $_GET['order'] : 'ASC';

$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$price_from = $_GET['price_from'] ?? null;
$price_to = $_GET['price_to'] ?? null;

$category = isset($_GET['category']) ? $_GET['category'] : [];  
$tag = isset($_GET['tag']) ? $_GET['tag'] : [];  



$query = "
SELECT products.*, 
    GROUP_CONCAT(DISTINCT p_tags.name_ SEPARATOR ', ') AS tags, 
    GROUP_CONCAT(DISTINCT p_categories.name_ SEPARATOR ', ') AS categories,
    GROUP_CONCAT(DISTINCT g_images.name_ SEPARATOR ', ') AS gallery_images
FROM products
LEFT JOIN product_property pp_tags ON products.id = pp_tags.product_id
LEFT JOIN property p_tags ON pp_tags.property_id = p_tags.id AND p_tags.type_ = 'tag'
LEFT JOIN product_property pp_categories ON products.id = pp_categories.product_id
LEFT JOIN property p_categories ON pp_categories.property_id = p_categories.id AND p_categories.type_ = 'category'
LEFT JOIN product_property pp_gallery ON products.id = pp_gallery.product_id
LEFT JOIN property g_images ON pp_gallery.property_id = g_images.id AND g_images.type_ = 'gallery'
WHERE products.product_name LIKE :search_term
";


if (!empty($category) && $category[0] != 0) {
    if (is_string($category)) {
        $category = explode(',', $category);  // Convert string to array
    }
    $categoryPlaceholders = implode(',', array_map(function ($index) {
        return ':category' . $index;
    }, array_keys($category)));
    $query .= " AND pp_categories.property_id IN ($categoryPlaceholders)";
}

if (!empty($tag) && $tag[0] != 0) {
    if (is_string($tag)) {
        $tag = explode(',', $tag);  // Convert string to array
    }
    $tagPlaceholders = implode(',', array_map(function ($index) {
        return ':tag' . $index;
    }, array_keys($tag)));
    $query .= " AND pp_tags.property_id IN ($tagPlaceholders)";
}
if (!empty($gallery)) {
    $query .= " AND g_images.name_ LIKE :gallery"; 
}

if (!empty($date_from)) {
    $query .= " AND products.date >= :date_from"; 
}

if (!empty($date_to)) {
    $query .= " AND products.date <= :date_to"; 
}

if (!empty($price_from)) {
    $query .= " AND products.price >= :price_from"; 
}

if (!empty($price_to)) {
    $query .= " AND products.price <= :price_to";
}


$query .= " GROUP BY products.id 
            ORDER BY $sort_by $order 
            LIMIT :start_from, :per_page";


$stmt = $pdo->prepare($query);

$searchTermLike = "%$searchTerm%";
$stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);

if (!empty($category) && $category[0] != 0) {
    foreach ($category as $index => $category_id) {
        $stmt->bindValue(':category' . $index, $category_id, PDO::PARAM_INT);
    }
}

if (!empty($tag) && $tag[0] != 0) {
    foreach ($tag as $index => $tag_id) {
        $stmt->bindValue(':tag' . $index, $tag_id, PDO::PARAM_INT);
    }
}


if (!empty($date_from)) {
    $stmt->bindParam(':date_from', $date_from);
}

if (!empty($date_to)) {
    $stmt->bindParam(':date_to', $date_to);
}

if (!empty($price_from)) {
    $stmt->bindParam(':price_from', $price_from);
}

if (!empty($price_to)) {
    $stmt->bindParam(':price_to', $price_to);
}

$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);




if (!empty($category) || !empty($tag) || (!empty($date_from) && !empty($date_to)) || (!empty($price_from) && !empty($price_to))) {
    $total_records = getRecordCount($pdo, $searchTermLike, $category, $tag, $date_from, $date_to, $price_from, $price_to);
} else {
    $count_query = "SELECT COUNT(*) FROM products WHERE product_name LIKE :search_term";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
}


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

    </style>
</head>

<body>

<?php include('model_add_product.php');?>
<?php include('model_add_property.php');?>


    <section class="container">
        <h1>PHP1</h1>
        <div class="product_header">
            <div class="product_header_top">
                <div>
                    <button id="add_product" class="ui primary button" >Add product</button>
                    <button id="add_property" class="ui button">Add property</button>
                    <a href="#" class="ui button">Sync online</a>
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
                    $page  = $_GET["page"];    
                } else {    
                    $page=1;    
                }    
                
                
                if (count($results) > 0) {
                    foreach ($results as $row){
                    $product_id = $row['id']; ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date'])?></td>
                <td class="product_name"><?php echo htmlspecialchars($row['product_name'])?></td>
                <td class="sku"><?php echo htmlspecialchars($row['sku'])?></td>
                <td class="price"><?php echo htmlspecialchars($row['price'])?></td>
                <td class="featured_image">
                    <img height="30" src="./uploads/<?php echo $row['featured_image']; ?>">
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


<script>

$(document).on('click', '.edit_button', function(e) {
    e.preventDefault();

    var product_id = $(this).val();
	
	$('.ui.button[type="submit"]:contains("Add")').addClass('d-none'); 
    $('.ui.button[type="submit"]:contains("Update")').removeClass('d-none'); 

    $.ajax({
        type: "GET",
        url: "handler_product.php?product_id=" + product_id,
        dataType: "json", 
        success: function(res) {
			$('.ui.modal.product_box').modal('show');

            if(res.status == 422){
                alert(res.message);

				
            } else if(res.status == 200){				

                $('#product_id').val(res.data.id);
                $('#product_name').val(res.data.product_name);
                $('#sku').val(res.data.sku);
                $('#price').val(res.data.price);


				$('#uploadedImage').show();
				$('#okMessage_product').hide();
				$('#galleryPreviewContainer').show();
				
                $('#uploadedImage').attr('src', './uploads/' + res.data.featured_image); 
			
                $('#galleryPreviewContainer').empty();

				$.each(res.gallery, function(index, image) {
					var imagePath = './uploads/' + image.name_;  
				
				
					var imgElement = $('<img>')
						.attr('src', imagePath)
						.attr('alt', 'Gallery Image')
						.css('height', '80px');  
				
					$('#galleryPreviewContainer').append(imgElement);
				});
				
				$('#categories_select').empty();


				$.each(res.categories, function(index, category) {
					var option = $('<option></option>')
						.attr('value', category.id)  
						.text(category.name_);  
				 
						
					$('#categories_select').append(option);
					
						$.each(res.categoriesse, function(i, selectedCategory) {
							if (selectedCategory.name_ === category.name_) {
								$('#categories_select option[value="' + category.id + '"]').prop('selected', true);
							}
						});
				});
				
				$('#tags_select').empty();
				

				$.each(res.tags, function(index, tag) {
					var option = $('<option></option>')
						.attr('value', tag.id)  
						.text(tag.name_);  
				
					$('#tags_select').append(option);
				
					$.each(res.tagsse, function(i, selectedTag) {
						if (selectedTag.name_ === tag.name_) {
							$('#tags_select option[value="' + tag.id + '"]').prop('selected', true);
						}
					});
				});

            }
        }
    });
});

$(document).on('click', '.edit_button', function() {
    $('#addProductButton').css({
        'display': 'none'
    });

    $('#editProductButton').css({
        'display': 'block'
    });
});

$(document).on('click', '.edit_button', function() {
    var productId = $(this).val(); 
    $('#action_type').val('edit_product'); 
    $('#product_id').val(productId); 
    $('.ui.modal.product_box').modal('show'); 
});

function applyFilters(event) {
    
    event.preventDefault();

    const category = $('#category').val();  
    const tag = $('#tag').val();

    const search = document.getElementById("search").value;
    
    const sortBy = document.getElementById("sort_by").value;
    const order = document.getElementById("order").value;
    const dateFrom = document.getElementById("date_from").value;
    const dateTo = document.getElementById("date_to").value;
    const priceFrom = document.getElementById("price_from").value;
    const priceTo = document.getElementById("price_to").value;
    const gallery = document.getElementById("gallery").value;

    const data = {
        search: search || '', 
        sort_by: sortBy,
        order: order,
        date_from: dateFrom,
        date_to: dateTo,
        price_from: priceFrom,
        price_to: priceTo,
        gallery: gallery,
		category: category || [],  
        tag: tag || [] 
    };

    $.ajax({
        url: 'get_product.php',
        type: 'GET',
        data: data,  
        success: function(response) {
            var data = JSON.parse(response); 
            $('#tableID').html(data.content); 
            $('#paginationBox').html(data.pagination); 
            
        },
        error: function(error) {
            console.error("Error loading data:", error);
        }
    });
}



$(document).ready(function() {
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
               loadPage(page);
          });
            $('#applyFilters').click(function() {
                loadPage(1); 
            });

    function loadPage(page) {
        var search = $('#search').val();
        var sort_by = $('#sort_by').val();
        var order = $('#order').val();
        var category = $('#category').val();
        var tag = $('#tag').val();
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var price_from = $('#price_from').val();
        var price_to = $('#price_to').val();

            $.ajax({
                    url: 'get_product.php',
                    method: 'GET',
                     data: {
                        page: page,
                        search: search,
                        sort_by: sort_by,
                        order: order,
                        category: category,
                        tag: tag,
                        date_from: date_from,
                        date_to: date_to,
                        price_from: price_from,
                        price_to: price_to
            },
                    success: function(response) {
                        var data = JSON.parse(response); 
                        $('#tableID').html(data.content);
                        $('#paginationBox').html(data.pagination);
                        $('#inputpage').html(data.inputpage);

                        console.log(inputpage);
                        

                    },
                    error: function() {
                        alert("Có lỗi xảy ra trong quá trình tải dữ liệu.");
                    }
                });
            }
});


let debounceTimeout;
let currentRequest = null;

function loadApplyFilters(event) {
	clearTimeout(debounceTimeout);

	debounceTimeout = setTimeout(() => {
		applyFilters(event);
	}, 400);
}



//hover trash
function bindHoverEvents() {
    $('#tableID').on('mouseover', function() {
        $('#action_box .box_delete_buttons').css('display', 'block');
    });

    $('#tableID').on('mouseout', function() {
        $('#action_box .box_delete_buttons').css('display', 'none');
    });
}

bindHoverEvents();

$('#tableID').load(location.href + " #tableID", function() {
    
    $('#tableID').on('click', '.delete_buttons', function(event) {
        event.preventDefault();
        
        if (confirm('Xác nhận xóa tất cả!')) {
            $.ajax({
                url: 'delete.php', 
                type: 'POST',
                success: function(response) {
                    $('#tableID').load(location.href + " #tableID", function() {
                    });

                    $('#paginationBox').load(location.href + " #paginationBox", function() {
                    });
  
                },
                
                error: function(xhr, status, error) {
                    alert('Đã xảy ra lỗi: ' + error);
                }
            });
        }
    });
});



    
$(function(){
	$("#add_product").click(function(){
		$(".product_box").modal('show');
		
		$('#saveProduct')[0].reset(); 


		$('#errMessage_add').addClass('d-none');
		$('#err_valid_Message_product').addClass('d-none');
		$('#okMessage_product').addClass('d-none');
		$('#err_valid_Message_price').addClass('d-none');

		$('#uploadedImage').hide();
		$('#galleryPreviewContainer').hide();

		$('#featured_image').val('');
		$('#gallery').val('');
	});

	$(".product_box").modal({
		closable: true
	});
});


$(function(){
	$("#close_product").click(function(){
		$(".product_box").modal('hide');
	});
	$(".product_box").modal({
		closable: true
	});
});  

$('#featured_image').on('change', function() {
	if (this.files && this.files[0]) {
		var reader = new FileReader(); 
		reader.onload = function(e) {
			$('#uploadedImage').attr('src', e.target.result).show(); 
		};
		reader.readAsDataURL(this.files[0]); 
	}
});

$('#gallery').on('change', function() {
    $('#galleryPreviewContainer').empty();
    
    if (this.files) {
        for (let i = 0; i < this.files.length; i++) {
            let file = this.files[i];
            let reader = new FileReader();
            
            reader.onload = function(e) {
                const img = $('<img>', {
                    src: e.target.result,
                    alt: 'Gallery Image',
                    style: 'height: 80px;'
                });
                $('#galleryPreviewContainer').append(img);
                $('#galleryPreviewContainer').show();
            };
            reader.readAsDataURL(file); 
        }
    }
});

$(function(){
	$("#add_property").click(function(){
		$(".category_box").modal('show');
	});
	$(".category_box").modal({
		closable: true
	});
});   
$(function(){
	$("#close_property").click(function(){
		$(".category_box").modal('hide');
	});
	$(".category_box").modal({
		closable: true
	});
});  

$(document).on('submit', '#saveProperty', function(e){
	e.preventDefault();

	var formData = new FormData(this);
	formData.append("save_property", true);

	$.ajax({
		type:"POST",
		url: "handler_property.php",
		data: formData,
		processData:false,
		contentType:false,
		success: function(response) {
            var res = jQuery.parseJSON(response);
			$('#okMessage').addClass('d-none'); 
            $('#errMessage').addClass('d-none'); 
            $('#err_valid_Message').addClass('d-none'); 
            $('#input_cate').removeClass('err_border'); 
            $('#input_tag').removeClass('err_border'); 


            if (res.status == 422) {
				$('#errMessage').removeClass('d-none').fadeIn(400); 
                setTimeout(function() {
                    $('#errMessage').fadeOut(400, function() {
                        $(this).addClass('d-none');
                    });
                }, 3500);
            }else if (res.status == 400) {

				$('#err_valid_Message').removeClass('d-none').fadeIn(400);
				
				setTimeout(function() {
					$('#err_valid_Message').fadeOut(400, function() {
						$(this).addClass('d-none');
					});
				}, 3500);
               res.errors.forEach(function(error) {

				if (error.field === 'category') {
					$('#input_cate').addClass('err_border');
				} 
				if (error.field === 'tag') {
					$('#input_tag').addClass('err_border');
				}
			});
			}else if (res.status == 200) {
				$('#okMessage').removeClass('d-none').fadeIn(400); 
                $('#saveProperty')[0].reset();
				$('#load_property').load(location.href + " #load_property");

                setTimeout(function() {
                    $('#okMessage').fadeOut(400, function() {
                        $(this).addClass('d-none');
                    });
                }, 3500);
            }
		}
	})
})

$('#category').dropdown();
$('#tag').dropdown();


$('#add_product').click(function() {

$('#editProductButton').css({
    'display': 'none',  
  
});
$('#addProductButton').css({
    'display': 'block',  
  
});
});


$('.edit_button').click(function() {

$('#addProductButton').css({
    'display': 'none',  
  
});

$('#editProductButton').css({
    'display': 'block',  
  
});
});



document.getElementById('add_product').addEventListener('click', function() {
    $('.ui.modal.product_box').modal('show');

    fetch('handler_product.php')
        .then(response => response.json())
        .then(data => {
            const categoriesSelect = document.getElementById('categories_select');
            const tagsSelect = document.getElementById('tags_select');

            categoriesSelect.innerHTML = '';
            tagsSelect.innerHTML = '';

            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name_;
                categoriesSelect.appendChild(option);
            });

            data.tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.id;
                option.textContent = tag.name_;
                tagsSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching categories and tags:', error));
});


$(document).on('click', '#addProductButton', function() {
    $('#action_type').val('add_product');  
});

$(document).on('click', '#editProductButton', function() {
    $('#action_type').val('edit_product');  
});



$(document).on('submit', '#saveProduct', function(e){
	e.preventDefault();

	var formData = new FormData(this);

	if ($('#featured_image')[0].files.length > 0) {
        var file = $('#featured_image')[0].files[0]; 
        formData.append('featured_image', file); 
    }

    formData.append("action_type", $('#action_type').val());

	if ($('#action_type').val() === 'edit_product') {
		var productId = $(this).data('product-id'); 
    }

	var categories = [];
    var tags = [];

    $("select[name='categories[]']").each(function() {
      categories.push($(this).val());
    });

    $("select[name='tags[]']").each(function() {
      tags.push($(this).val());
    });


    formData.append('categories', JSON.stringify(categories));
    formData.append('tags', JSON.stringify(tags));

	$.ajax({
		type: "POST",
		url: "handler_product.php",
		data: formData,
		dataType: "",
		processData:false,
		contentType:false,
		success: function(response) {
            var res = jQuery.parseJSON(response);
			$('#okMessage').addClass('d-none'); 
			$('#okMessage_add').addClass('d-none'); 
            $('#errMessage').addClass('d-none'); 
            $('#err_valid_Message').addClass('d-none'); 
            $('#err_valid_Message_product').addClass('d-none'); 
			$('#product_name').removeClass('err_border'); 
            $('#sku').removeClass('err_border');
            $('#price').removeClass('err_border');

            if (res.status == 400) {
				
				setTimeout(function() {
					$('#err_valid_Message_product').fadeOut(400, function() {
						$(this).addClass('d-none');
					});
				}, 3500);
				setTimeout(function() {
					$('#err_valid_Message_price').fadeOut(400, function() {
						$(this).addClass('d-none');
					});
				}, 2500);
			
				
				
				res.errors.forEach(function(error) {

				if (error.field === 'empty') {
					$('#errMessage_add').removeClass('d-none').fadeIn(400); 
					setTimeout(function() {
						$('#errMessage_add').fadeOut(400, function() {
							$(this).addClass('d-none');
						});
					}, 2500);
				}
				if (error.field === 'exist') {
					$('#err_valid_Message_sku').removeClass('d-none').fadeIn(400); 
				setTimeout(function() {
					$('#err_valid_Message_sku').fadeOut(400, function() {
						$(this).removeClass('d-none');
					});
				}, 2500);
			}
	
				if (error.field === 'product_name') {
					$('#err_valid_Message_product').removeClass('d-none').fadeIn(400);
					$('#product_name').addClass('err_border');
				} 
				if (error.field === 'sku') {
					$('#sku').addClass('err_border');
				}
				if (error.field === 'price') {
					$('#err_valid_Message_price').removeClass('d-none').fadeIn(400);
					$('#price').addClass('err_border');
				}
				if (error.field === 'featured_image') {
					$('#err_valid_Message_product').removeClass('d-none').fadeIn(400);
					$('#featured_image').addClass('err_border');
				}
				if (error.field === 'exist') {
					$('#err_valid_Message_sku').removeClass('d-none').fadeIn(400);
					$('#sku').addClass('err_border');
				}

                if (error.field === 'gallery') {
				}
                
              
			});
			
			}
			else if (res.status == 200) {
				if(res.action == 'add'){

					$('#okMessage_product').removeClass('d-none').fadeIn(400); 
					$('#uploadedImage').attr('src', '').hide();
					$('#featured_image').val(''); 
					$('#galleryPreviewContainer').empty();
					$('#saveProduct')[0].reset();

					setTimeout(function() {
						$('#okMessage_product').fadeOut(400, function() {
							$(this).addClass('d-none');
						});
					}, 2000);
                    

                    $('#tableID').load(location.href + " #tableID"); 

                    $('#paginationBox').load(location.href + " #paginationBox"); 

				}else if(res.action == 'edit'){

					$('#featured_image').val(''); 
					$('#okMessage_product_update').removeClass('d-none').fadeIn(400); 
					$('#gallery').val('');

					setTimeout(function() {
						$('#okMessage_product_update').fadeOut(400, function() {
							$(this).addClass('d-none');
						});
					}, 2000);
                 
                    var productId = res.product_id; 

                var updatedProductName = res.product_name; 
                var updatedSku = res.sku; 
                var updatedPrice = res.price; 
                var featuredImage = res.featured_image.name;
                var category = res.category;
                var tag = res.tag;
                var gallery = res.gallery;

                console.log(gallery);
                
              


    $('#tableID').find('.edit_button').each(function() {
        var button = $(this);
        var productIDInRow = button.val(); 
        
        if (productIDInRow == productId) {

            button.closest('tr').find('.product_name').text(updatedProductName);
            button.closest('tr').find('.sku').text(updatedSku);
            button.closest('tr').find('.price').text(updatedPrice);


            if(featuredImage && featuredImage !== ''){
                button.closest('tr').find('.featured_image img').attr('src', './uploads/' + featuredImage);
            }


            
           
            if(gallery && Array.isArray(gallery.name) && gallery.name != ''){
                
                
                if (gallery && Array.isArray(gallery.name)) {

                    var galleryContainer = button.closest('tr').find('.gallery .gallery-container');
                    galleryContainer.empty();  
                    
                    gallery.name.forEach(function(image) {
                        
                        var img = galleryContainer.find('img[src="./uploads/' + image + '"]');
                        
                        if (img.length > 0) {
                            
                            img.attr('src', './uploads/' + image);
                            console.log('leng>0');
                            
                        } else {
                            
                        galleryContainer.append('<img height="30" src="./uploads/' + image + '" alt="Gallery Image">');  
                        console.log('leng=0');
                    }
                });
            } else {
               console.log('dd');
               
            }
            }

        if (category && category.length > 0) {
        var categoryNames = category.map(function(cat) {
            return cat.name_;  
        }).join(', ');  

        
        button.closest('tr').find('.category').text(categoryNames);
        } else {
            button.closest('tr').find('.category').text('');
        }

        if (tag && tag.length > 0) {
        var tagNames = tag.map(function(cat) {
            return cat.name_; 
        }).join(', ');  

        
        button.closest('tr').find('.tag').text(tagNames);
        } else {
            button.closest('tr').find('.tag').text('');
        }
        }
    });

    setTimeout(function() {
        $('#okMessage_product_update').fadeOut(400, function() {
            $(this).addClass('d-none');
        });
    }, 2000);
		}

            }
		}
	});
})


//delete one product

$(document).on('click', '.delete_button', function(e) {
    e.preventDefault(); 

    var productId = $(this).data('id'); 
    var productRow = $(this).closest('tr');
    var productIds = [];

    

    var currentPage = $('#currentPages').val();
    
    if(currentPage === undefined){

    var currentPage = $('#currentPage').val();
    
    }

    console.log('current page:'+currentPage);
    
$('#tableID tr').each(function() {
    var productId = $(this).find('.delete_button').data('id'); 
    if (productId) { 
        productIds.push(productId); 
    }
});
console.log(productIds);
console.log('PID'+productIds.length);

    if (confirm('Are you sure you want to delete this product?')) {
       
        $.ajax({
            url: 'delete_product.php', 
            method: 'POST',
            data: { id: productId },

           
            success: function(response) {
                if (response == 'success') {
                    productRow.fadeOut(function() {
                        $(this).remove(); 

                          if (productIds.length < 2) {
                        var previousPage = currentPage - 1;
                        if (previousPage < 1) {
                            previousPage = 1;
                        }

                        $('#tableID').load(`index.php?page=${previousPage} #tableID`);
                        $('#paginationBox').load(`index.php?page=${previousPage} #paginationBox`);

                    } else if (productIds.length >= 2 && productIds.length < 6) {

                        $('#tableID').load(`index.php?page=${currentPage}?category=1922 #tableID`);
                        
                        $('#paginationBox').load(`index.php?page=${currentPage} #paginationBox`);

                    }else if(productIds.length < 2){
                        currentPage = currentPage - 1 ;

                        $('#tableID').load(`index.php?page=${currentPage} #tableID`);
                        
                        $('#paginationBox').load(`index.php?page=${currentPage} #paginationBox`);                        
                    }

                    });                
                } else {
                    alert('Error deleting the product');
                }
            },
            error: function() {
                alert('An error occurred');
            }
        });
    }
});


</script>
</body>
</html>