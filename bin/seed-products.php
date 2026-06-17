<?php
/**
 * WooCommerce product seeder.
 * Run via: npx wp-env run cli wp eval-file /var/www/html/wp-content/plugins/woo-plugin/bin/seed-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ----------------------------------------------------------------
   1. Categories
---------------------------------------------------------------- */
$categories = array(
	'T-Shirts'     => array( 'description' => 'Comfortable everyday tees', 'parent' => 0 ),
	'Hoodies'      => array( 'description' => 'Warm and cosy hoodies', 'parent' => 0 ),
	'Accessories'  => array( 'description' => 'Bags, belts, and more', 'parent' => 0 ),
	'Electronics'  => array( 'description' => 'Gadgets and devices', 'parent' => 0 ),
	'Books'        => array( 'description' => 'Print and digital books', 'parent' => 0 ),
	'Kitchen'      => array( 'description' => 'Cookware and appliances', 'parent' => 0 ),
	'Sports'       => array( 'description' => 'Gear for active lifestyles', 'parent' => 0 ),
	'Footwear'     => array( 'description' => 'Shoes and sneakers', 'parent' => 0 ),
);

$cat_ids = array();
foreach ( $categories as $name => $args ) {
	$existing = get_term_by( 'name', $name, 'product_cat' );
	if ( $existing ) {
		$cat_ids[ $name ] = $existing->term_id;
		echo "Category exists: {$name} (ID {$existing->term_id})\n";
	} else {
		$result = wp_insert_term( $name, 'product_cat', array(
			'description' => $args['description'],
		) );
		$cat_ids[ $name ] = $result['term_id'];
		echo "Created category: {$name} (ID {$result['term_id']})\n";
	}
}

/* ----------------------------------------------------------------
   2. Helper: download image and attach to post
---------------------------------------------------------------- */
function seed_attach_image( $url, $post_id, $alt ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$tmp = download_url( $url );
	if ( is_wp_error( $tmp ) ) {
		echo "  !! Image download failed: " . $tmp->get_error_message() . "\n";
		return false;
	}

	$ext      = 'jpg';
	$filename = sanitize_title( $alt ) . '.' . $ext;
	$file     = array(
		'name'     => $filename,
		'type'     => 'image/jpeg',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => filesize( $tmp ),
	);

	$attachment_id = media_handle_sideload( $file, $post_id );
	@unlink( $tmp );

	if ( is_wp_error( $attachment_id ) ) {
		echo "  !! Attachment failed: " . $attachment_id->get_error_message() . "\n";
		return false;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	return $attachment_id;
}

/* ----------------------------------------------------------------
   3. Products — [ name, price, sale_price, cats[], img_seed, desc ]
---------------------------------------------------------------- */
$products = array(
	// T-Shirts
	array( 'Classic White Tee',       '19.99', '',      array( 'T-Shirts' ),           10, 'A timeless white t-shirt in 100% organic cotton.' ),
	array( 'Graphic Print Tee',        '24.99', '19.99', array( 'T-Shirts' ),           11, 'Bold graphic print on soft cotton fabric.' ),
	array( 'Striped Polo Shirt',       '34.99', '',      array( 'T-Shirts' ),           12, 'Smart striped polo for casual Fridays.' ),
	array( 'V-Neck Basic Tee',         '17.99', '',      array( 'T-Shirts' ),           13, 'Slim-fit V-neck in a range of colours.' ),
	array( 'Longline Tee',             '22.99', '18.99', array( 'T-Shirts' ),           14, 'Dropped hem longline tee, perfect for layering.' ),

	// Hoodies
	array( 'Classic Pullover Hoodie',  '59.99', '',      array( 'Hoodies' ),            20, 'Heavyweight fleece pullover with kangaroo pocket.' ),
	array( 'Zip-Up Hoodie',            '69.99', '',      array( 'Hoodies' ),            21, 'Full-zip hoodie with ribbed cuffs and hem.' ),
	array( 'Oversized Hoodie',         '74.99', '59.99', array( 'Hoodies' ),            22, 'Relaxed oversized fit for ultimate comfort.' ),
	array( 'Tech Fleece Hoodie',       '89.99', '',      array( 'Hoodies' ),            23, 'Lightweight tech fleece with tapered fit.' ),
	array( 'Vintage Wash Hoodie',      '64.99', '49.99', array( 'Hoodies' ),            24, 'Garment-dyed vintage wash finish.' ),

	// Accessories
	array( 'Leather Belt',             '24.99', '',      array( 'Accessories' ),        30, 'Full-grain leather belt with brushed buckle.' ),
	array( 'Canvas Tote Bag',          '19.99', '14.99', array( 'Accessories' ),        31, 'Heavy-duty canvas tote, machine washable.' ),
	array( 'Wool Beanie',              '14.99', '',      array( 'Accessories' ),        32, 'Ribbed wool beanie, one size fits all.' ),
	array( 'Leather Wallet',           '39.99', '29.99', array( 'Accessories' ),        33, 'Slim bifold leather wallet, RFID-blocking.' ),
	array( 'Baseball Cap',             '29.99', '',      array( 'Accessories' ),        34, 'Adjustable cotton baseball cap with embroidered logo.' ),
	array( 'Sunglasses',               '49.99', '39.99', array( 'Accessories' ),        35, 'UV400 polarised lenses with acetate frame.' ),

	// Electronics
	array( 'Wireless Earbuds',         '79.99', '59.99', array( 'Electronics' ),        40, 'True wireless earbuds with 24h total battery life.' ),
	array( 'USB-C Hub',                '34.99', '',      array( 'Electronics' ),        41, '7-in-1 USB-C hub with 4K HDMI and 100W PD.' ),
	array( 'Portable Charger',         '29.99', '',      array( 'Electronics' ),        42, '10 000 mAh power bank, dual USB-A + USB-C.' ),
	array( 'Laptop Stand',             '44.99', '34.99', array( 'Electronics' ),        43, 'Adjustable aluminium laptop stand, 10–17 inch.' ),
	array( 'Bluetooth Speaker',        '59.99', '',      array( 'Electronics' ),        44, 'IPX7 waterproof speaker with 360° sound.' ),
	array( 'Mechanical Keyboard',     '119.99', '99.99', array( 'Electronics' ),        45, 'Compact TKL keyboard with Cherry MX switches.' ),

	// Books
	array( 'The Design of Everyday Things', '18.99', '', array( 'Books' ),             50, 'Don Norman\'s classic on user-centred design.' ),
	array( 'Clean Code',               '29.99', '24.99', array( 'Books' ),             51, 'Robert C. Martin on writing maintainable code.' ),
	array( 'Atomic Habits',            '16.99', '',      array( 'Books' ),             52, 'James Clear\'s guide to building good habits.' ),
	array( 'Deep Work',                '15.99', '12.99', array( 'Books' ),             53, 'Cal Newport on focused, distraction-free work.' ),

	// Kitchen
	array( 'Ceramic Pour-Over Set',    '39.99', '32.99', array( 'Kitchen' ),           60, 'Pour-over coffee dripper with matching carafe.' ),
	array( 'Chef\'s Knife',            '54.99', '',      array( 'Kitchen' ),           61, 'High-carbon stainless steel 8-inch chef\'s knife.' ),
	array( 'Cast Iron Skillet',        '49.99', '39.99', array( 'Kitchen' ),           62, 'Pre-seasoned 10-inch cast iron skillet.' ),
	array( 'Bamboo Cutting Board',     '22.99', '',      array( 'Kitchen' ),           63, 'Large end-grain bamboo cutting board.' ),

	// Sports
	array( 'Yoga Mat',                 '34.99', '27.99', array( 'Sports' ),            70, 'Non-slip 6mm thick TPE yoga mat.' ),
	array( 'Resistance Bands Set',     '19.99', '',      array( 'Sports' ),            71, 'Set of 5 resistance bands with carry bag.' ),
	array( 'Water Bottle',             '24.99', '',      array( 'Sports' ),            72, 'Insulated 750 ml stainless steel water bottle.' ),
	array( 'Running Belt',             '17.99', '13.99', array( 'Sports' ),            73, 'Lightweight running belt fits phone + keys.' ),

	// Footwear
	array( 'Canvas Sneakers',          '49.99', '39.99', array( 'Footwear' ),          80, 'Vulcanised canvas low-tops in classic white.' ),
	array( 'Leather Loafers',          '89.99', '',      array( 'Footwear' ),          81, 'Slip-on leather loafers with rubber sole.' ),
	array( 'Running Shoes',            '99.99', '79.99', array( 'Footwear' ),          82, 'Lightweight mesh running shoes with foam midsole.' ),
	array( 'Chelsea Boots',           '119.99', '',      array( 'Footwear' ),          83, 'Suede Chelsea boots with elastic side panels.' ),
);

/* ----------------------------------------------------------------
   4. Create / update products
---------------------------------------------------------------- */
$created = 0;
$skipped = 0;

foreach ( $products as $p ) {
	list( $name, $price, $sale_price, $cats, $img_seed, $desc ) = $p;

	// Check if already exists
	$existing_id = wc_get_product_id_by_sku( 'seed-' . $img_seed );
	if ( $existing_id ) {
		echo "Skip (exists): {$name}\n";
		$skipped++;
		continue;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( $desc );
	$product->set_short_description( $desc );
	$product->set_sku( 'seed-' . $img_seed );
	$product->set_regular_price( $price );
	if ( $sale_price ) {
		$product->set_sale_price( $sale_price );
	}
	$product->set_manage_stock( false );
	$product->set_stock_status( 'instock' );

	// Assign categories
	$term_ids = array();
	foreach ( $cats as $cat_name ) {
		if ( isset( $cat_ids[ $cat_name ] ) ) {
			$term_ids[] = $cat_ids[ $cat_name ];
		}
	}
	$product->set_category_ids( $term_ids );

	$product_id = $product->save();

	// Download and attach image
	$img_url = "https://picsum.photos/seed/{$img_seed}/600/600";
	echo "  Downloading image for: {$name} …\n";
	$attachment_id = seed_attach_image( $img_url, $product_id, $name );
	if ( $attachment_id ) {
		$product->set_image_id( $attachment_id );
		$product->save();
		echo "  ✓ Image attached (ID {$attachment_id})\n";
	}

	echo "Created: {$name} (ID {$product_id})\n";
	$created++;
}

echo "\n=== Done: {$created} created, {$skipped} skipped ===\n";
