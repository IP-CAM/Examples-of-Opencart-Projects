<?php

    //include '/home/smart/web/test.smart.md/public_html/visely/init_visely.php';
    include '/home/smart/web/test.smart.md/public_html/admin/config.php';
	$mysqli = mysqli_connect("db.smart.md", DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$mysqli -> set_charset("utf8");

	$lang_id_ru = '2';
	$lang_id_ro = '3';

	//currency
	$query_currency = "SELECT * FROM currency ";
	$result_currency = mysqli_query($mysqli, $query_currency);
	$products_currency = array();
	while ($row_currency = mysqli_fetch_assoc($result_currency)) {
		$products_currency[] = $row_currency;
	}
    //print_r($products_currency);
	//brands 
	$query_brands = "SELECT * FROM manufacturer ";
	$result_brands = mysqli_query($mysqli, $query_brands);
	$products_brands = array();
	while ($row_brands = mysqli_fetch_assoc($result_brands)) {
		$products_brands[$row_brands['manufacturer_id']] = $row_brands;
	}

	//products
	$today = date("Y-m-d");  
	//$query = "SELECT * FROM product WHERE date_update2 != '".$today."' OR date_update2 IS NULL order by sort_order limit 10";
	$query = "SELECT * FROM product LEFT JOIN product_to_category ON product.product_id = product_to_category.product_id WHERE product_to_category.category_id = 4494 AND date_update2 != '".$today."' OR date_update2 IS NULL order by sort_order limit 400";
	//$query = "SELECT * FROM product LEFT JOIN product_to_category ON product.product_id = product_to_category.product_id WHERE product_to_category.category_id = 4494 order by sort_order limit 500";

	$result = mysqli_query($mysqli, $query);
	
	while ($row = mysqli_fetch_assoc($result)) {
		$product = array();
		$product['id'] = $row['product_id'];
		$query_description = "SELECT * FROM product_description WHERE product_id = '".$row['product_id']."' ";

		$result_description = mysqli_query($mysqli, $query_description);

		while ($row_description = mysqli_fetch_assoc($result_description)) {

			if($row_description['language_id'] == '2') {
				if(!empty($ru)) {
				$product['title']['ru'] = $row_description['name'];
				}
				$product['description']['ru'] = $row_description['description'];
				//$product['tags']['ru'][] = $row_description['tag'];
				
			}
			if($row_description['language_id'] == '3') {
				if(!empty($ro)) {
				$product['title']['ro'] = $row_description['name'];
				}
			   	$product['description']['ro'] = $row_description['description'];
			   // $product['tags']['ro'][] = $row_description['tag'];
				
			}
		}

		$product['sku'] = $row['sku'];
		$product['brand'] = $products_brands[$row['manufacturer_id']]['name'];
        // regularPrice
		$product['regularPrice']['amount'] = $row['price'];
		$product['regularPrice']['currency'] = 'MDL';
		// special_price
	    $query_price_special = "SELECT * FROM product_special WHERE product_id = '".$row['product_id']."' ";

	    $result_price_special = mysqli_query($mysqli, $query_price_special);

	    while ($row_price_special = mysqli_fetch_assoc($result_price_special)) {
	    	if($row_price_special['price']>0) {
	    		$product['salePrice']['amount'] = $row_price_special['price'];
	    		$product['salePrice']['currency'] = 'MDL';
		}
	}

     // special_labels
	 $query_labels = "SELECT * FROM label_description LEFT JOIN labels ON label_description.label_id = labels.label_id WHERE in_category=1";
	 $result_labels = mysqli_query($mysqli, $query_labels);

	 $products_labels = array();

	 while ($row_labels = mysqli_fetch_assoc($result_labels)) {
		 $products_labels[] = $row_labels;
	 }

	 $rutags = array();
	 $rotags = array();
	 
	 foreach(json_decode($row['labels']) AS $key => $label) {
				foreach($products_labels AS $l) {
					if($l['label_id'] == $label) {
						$concat_ru = $l['name'].'|style|background:'.$l['color'].';'.'color:'.$l['text_color'].';';
	                    $concat_ro = $l['name'].'|style|background:'.$l['color'].';'.'color:'.$l['text_color'].';';
							if($l['language_id'] == '2') {
								$rutags[] = $l['name'];
								$rutags[] = $concat_ru;
							}

						    if($l['language_id'] == '3') {
								$rotags[] = $l['name'];
							    $rotags[] = $concat_ro;
						    }				
					}
				}
		}
		if(!empty($rutags)) {
			$product['tags']['ru'] = $rutags;
			$product['tags']['ro'] = $rotags;
		}
		$product['createdAt'] =date("c",  strtotime($row['date_added']));

		$product['productUrl'] = 'https://'.$_SERVER['SERVER_NAME'].'/index.php?route=product/product&path='.implode('_', $urls).'&product_id='.$row['product_id'];
		$product['media'][] = 'https://'.'smart.md'.'/image/'.$row['image'];
		$product['published'] = (bool)$row['status'];
		$product['inventoryLevel'] = $row['quantity'];

        //product_options
		$query_options = "SELECT * FROM product_variation_attributes_description";
		$result_options = mysqli_query($mysqli, $query_options);
		$products_options = array();
		while ($row_options = mysqli_fetch_assoc($result_options)) {
			$products_options[] = $row_options;
		}

		//product_to_category
		$query_ptc = "SELECT * FROM product_to_category WHERE product_id = '".$row['product_id']."' ";

		$urls = array();
		$result_ptc = mysqli_query($mysqli, $query_ptc);
		while ($row_ptc = mysqli_fetch_assoc($result_ptc)) {
			$urls[] = $row_ptc['category_id'];

			$cat = array();
			$query_category = "SELECT *, category.image AS img_cat FROM category 
					LEFT JOIN category_description ON category.category_id = category_description.category_id
						WHERE category.category_id = '".$row_ptc['category_id']."' ";

			$result_category = mysqli_query($mysqli, $query_category);

			while ($row_category = mysqli_fetch_assoc($result_category)) {

				if($row_category['language_id'] == '2') {

					$cat['title']['ru'] = $row_category['name'];
					$cat['id'] = $row_category['category_id'];
					if($row_category['parent_id'] != '0') {
					$cat['parentId'] = $row_category['parent_id'];
				    }
					//$cat['is_lading'] = (bool)$row_category['is_lading'];
					//$cat['show_in_lading'] = (bool)$row_category['show_in_lading'];
					$cat['position'] = $row_category['sort_order'];
					if(!empty($row_category['img_cat'])) {
					$cat['media'] = ['https://'.'smart.md'.'/image/'.$row_category['img_cat']];
					}
				}
				if($row_category['language_id'] == '3') {

					$cat['title']['ro'] = $row_category['name'];
					$cat['id'] = $row_category['category_id'];
					if($row_category['parent_id'] != '0') {
					$cat['parentId'] = $row_category['parent_id'];
					}
					//$cat['is_lading'] = (bool)$row_category['is_lading'];
					//$cat['show_in_lading'] = (bool)$row_category['show_in_lading'];
					$cat['position'] = $row_category['sort_order'];
					if(!empty($row_category['img_cat'])) {
					$cat['media'] = ['https://'.'smart.md'.'/image/'.$row_category['img_cat']];
					}
				}
			}

			
			if(!empty($ru)) {
				$product['categories'][] = $cat;
			}
			
			
		}

		//filter
		$query_filter = "SELECT * FROM product_to_value WHERE product_id = '".$row['product_id']."' AND value_id>0";

		$result_filter = mysqli_query($mysqli, $query_filter);

					$f = array();
					$product['filters']['ru'] = array();
					$product['filters']['ro'] = array();

		while ($row_filter = mysqli_fetch_assoc($result_filter)) {

			$ru = array();
			$ro = array();

			$query_optionn_filter = "SELECT * FROM category_option_description WHERE option_id = '".$row_filter['option_id']."' ";
			$result_optionn_filter = mysqli_query($mysqli, $query_optionn_filter);

			$option_name = '';
			while ($row_optionn_filter = mysqli_fetch_assoc($result_optionn_filter)) {
				
				if($row_optionn_filter['language_id'] == '2') {
					$option_name_ru = $row_optionn_filter['name'];
				}
				if($row_optionn_filter['language_id'] == '3') {
					$option_name_ro = $row_optionn_filter['name'];
				}
			}

       
            if($option_name_ru != '') {
			$query_option_filter = "SELECT * FROM category_option_value_description WHERE value_id = '".$row_filter['value_id']."' ";
			$result_option_filter = mysqli_query($mysqli, $query_option_filter);

			while ($row_option_filter = mysqli_fetch_assoc($result_option_filter)) {

				if(!empty($row_option_filter['name'])) {

					$ru['id'] = $row_filter['option_id'];
					$ro['id'] = $row_filter['option_id'];
				}

				if($row_option_filter['language_id'] == '2') {
					$ru['name'] = $option_name_ru;
					$ru['value'] = $row_option_filter['name'];
				}
				if($row_option_filter['language_id'] == '3') {

					$ro['name'] = $option_name_ro;
					$ro['value'] = $row_option_filter['name'];
				}
			}
			   if(!empty($ru)) {
			   $product['filters']['ru'][] = $ru;
			   $product['filters']['ro'][] = $ro;
			   }
            }
		}

      


         //v
		$vars = array();
		$query_options_values = "SELECT * FROM product_variations_links LEFT JOIN product_variations ON product_variations_links.variation=product_variations.variation WHERE product_variations_links.sku = '".$row['sku']."' ";
		$result_options_values = mysqli_query($mysqli, $query_options_values);
		while ($row_options_values = mysqli_fetch_assoc($result_options_values)) {
			$var_id = $row_options_values['variation'];
			$vars[$var_id][] = $row_options_values;
		}

		$newvirtual_products = array();
		foreach($vars as $key => $var) {
			$nvproduct = array();



			foreach($var as $vkey =>$vvalue) {
				$ru = array();
				$ro = array();

		
			//v_product_description
			$vv_query_description = "SELECT * FROM product_description JOIN product ON product_description.product_id=product.product_id WHERE product.sku = '".$key."' ";
	     	//print_r($vv_arr_description);

			$vv_result_description = mysqli_query($mysqli, $vv_query_description);
			$vv_arr_description = array();
			while ($vv_arr = mysqli_fetch_assoc($vv_result_description)) {
	            $vv_arr_description[] = $vv_arr;

				foreach($vv_arr_description AS $vv) {
	
					  if($vv['language_id'] == '2') {
						$v_products_title_ru = $vv['name'];
				        $v_products_description_ru = $vv['description'];
					  }
					  if($vv['language_id'] == '3') {
						$v_products_title_ro = $vv['name'];
				        $v_products_description_ro = $vv['description'];
					  }
	
				}
	
			}
			// v_special_price
            $vv_query_price_special = "SELECT * FROM product_special WHERE product_id = '".$vv['product_id']."' ";

            $vv_result_price_special = mysqli_query($mysqli, $vv_query_price_special);

            while ($vv_row_price_special = mysqli_fetch_assoc($vv_result_price_special)) {
                if($vv_row_price_special['price']>0) {
                    $vv_products_salePrice_amount = $vv_row_price_special['price'];
                    $vv_products_salePrice_currency = $products_currency[0]['code'];
                }
            }

				foreach($products_options AS $po) {
					if($po['option_id'] == $vvalue['option_id']) {
						if($po['language_id'] == '2') {
							$nvproduct['id'] = $vv['product_id'].'-'.$row['product_id'];

							$nvproduct['baseProductId'] = $row['product_id'];

							$nvproduct['title']['ru'] = $v_products_title_ru;
				            $nvproduct['description']['ru'] = $v_products_description_ru;

							$nvproduct['sku'] = $vv['sku'];

							$nvproduct['brand'] = $products_brands[$vv['manufacturer_id']]['name'];

							$nvproduct['regularPrice']['amount'] = $vv['price'];
                            $nvproduct['regularPrice']['currency'] = 'MDL';

							$nvproduct['salePrice']['amount'] = $vv_products_salePrice_amount;
                            $nvproduct['salePrice']['currency'] = 'MDL';

							$nvproduct['createdAt'] =date("c",  strtotime($vv['date_added']));

							$nvproduct['productUrl'] = 'https://'.$_SERVER['SERVER_NAME'].'/index.php?route=product/product&path='.implode('_', $urls).'&product_id='.$vv['product_id'];

							$nvproduct['published'] = (bool)$vv['status'];
                            $nvproduct['inventoryLevel'] = $vv['quantity'];


							$ru['name'] = $po['name'];
							$ru['value'] = $vvalue['value'];
						}

						if($po['language_id'] == '3') {
							$nvproduct['id'] = $vv['product_id'].'-'.$row['product_id'];

							$nvproduct['baseProductId'] = $row['product_id'];

							$nvproduct['title']['ro'] = $v_products_title_ro;
				            $nvproduct['description']['ro'] = $v_products_description_ro;

							$nvproduct['sku'] = $vv['sku'];

						    $nvproduct['brand'] = $products_brands[$vv['manufacturer_id']]['name'];

							$nvproduct['regularPrice']['amount'] = $vv['price'];
                            $nvproduct['regularPrice']['currency'] = 'MDL';

							$nvproduct['salePrice']['amount'] = $vv_products_salePrice_amount;
                            $nvproduct['salePrice']['currency'] = 'MDL';

							$nvproduct['createdAt'] =date("c",  strtotime($vv['date_added']));

							$nvproduct['productUrl'] = 'https://'.$_SERVER['SERVER_NAME'].'/index.php?route=product/product&path='.implode('_', $urls).'&product_id='.$vv['product_id'];

							$nvproduct['published'] = (bool)$vv['status'];
                            $nvproduct['inventoryLevel'] = $vv['quantity'];


							$ro['name'] = $po['name'];
							$ro['value'] = $vvalue['value'];
						}
					}
				}

				if(!empty($ru)) {
					$nvproduct['options']['ru'][] = $ru;
					$nvproduct['options']['ro'][] = $ro;
				}
			}

			$newvirtual_products[] = $nvproduct;
		}

		if(!empty($f)) {
			$product['filters'][] = $f;
		}
		if(!empty($product['categories'])) {
		    $products['products'][] = $product;
            if(!empty($newvirtual_products)) {
	        foreach($newvirtual_products AS $nvp) {
		    $products['products'][] = $nvp;
	        }
            }
		}
		$query_date = "UPDATE product SET date_update=NOW(), date_update2=NOW() WHERE product_id = '".$row['product_id']."' ";
		mysqli_query($mysqli, $query_date);

	}

	print_r(json_encode($products, JSON_UNESCAPED_UNICODE));

	$curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => "https://smartmd.visely.io/prometheus/api/v2/products/batch",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($products),
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "authorization: Bearer prv_a27a74a6-8f59-4fa2-8f97-25bf79da26c6",
    "content-type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
	echo 'ok';
  //print_r(json_encode($products));
}










