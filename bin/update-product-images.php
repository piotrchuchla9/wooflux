<?php
/**
 * Updates product featured images with keyword-relevant photos.
 * Run via: npx wp-env run cli wp eval-file /var/www/html/wp-content/plugins/woo-plugin/bin/update-product-images.php
 */

$keyword_map = array(
	// Footwear
	'Chelsea Boots'                    => 'chelsea boots',
	'Running Shoes'                    => 'running shoes',
	'Leather Loafers'                  => 'leather loafers',
	'Canvas Sneakers'                  => 'canvas sneakers',

	// Sports & Fitness
	'Running Belt'                     => 'running belt sport',
	'Resistance Bands Set'             => 'resistance bands fitness',
	'Water Bottle'                     => 'water bottle',
	'Yoga Mat'                         => 'yoga mat',

	// Kitchen
	'Bamboo Cutting Board'             => 'cutting board kitchen',
	'Cast Iron Skillet'                => 'cast iron skillet',
	"Chef's Knife"                     => 'chef knife kitchen',
	'Ceramic Pour-Over Set'            => 'coffee pour over',

	// Books
	'Deep Work'                        => 'book reading desk',
	'Atomic Habits'                    => 'book habit',
	'Clean Code'                       => 'programming code book',
	'The Design of Everyday Things'    => 'design book',

	// Electronics
	'Mechanical Keyboard'              => 'mechanical keyboard',
	'Bluetooth Speaker'                => 'bluetooth speaker',
	'Laptop Stand'                     => 'laptop stand',
	'Portable Charger'                 => 'portable charger',
	'USB-C Hub'                        => 'usb hub tech',
	'Wireless Earbuds'                 => 'wireless earbuds',

	// Accessories
	'Sunglasses'                       => 'sunglasses fashion',
	'Baseball Cap'                     => 'baseball cap',
	'Leather Wallet'                   => 'leather wallet',
	'Wool Beanie'                      => 'wool beanie hat',
	'Canvas Tote Bag'                  => 'canvas tote bag',
	'Leather Belt'                     => 'leather belt',

	// Hoodies
	'Vintage Wash Hoodie'              => 'hoodie fashion',
	'Tech Fleece Hoodie'               => 'fleece hoodie',
	'Zip-Up Hoodie'                    => 'zip hoodie',
	'Oversized Hoodie'                 => 'oversized hoodie',
	'Classic Pullover Hoodie'          => 'pullover hoodie',
	'Pullover Hoodie'                  => 'pullover hoodie',

	// T-shirts
	'Longline Tee'                     => 'longline tshirt',
	'Striped Polo Shirt'               => 'polo shirt stripe',
	'V-Neck Basic Tee'                 => 'vneck tshirt',
	'Graphic Print Tee'                => 'graphic tshirt',
	'Classic White Tee'                => 'white tshirt',
	'Vintage Wash Tee'                 => 'vintage tshirt',
	'Classic Logo Tee'                 => 'tshirt fashion',
	'Nike T-Shirt'                     => 'sport tshirt',
);

$products = wc_get_products( array(
	'limit'  => -1,
	'status' => 'publish',
) );

$updated = 0;
$failed  = 0;

foreach ( $products as $product ) {
	$title   = $product->get_name();
	$keyword = $keyword_map[ $title ] ?? null;

	if ( ! $keyword ) {
		echo "SKIP: {$title} (no keyword mapping)\n";
		continue;
	}

	$slug    = urlencode( $keyword );
	$sig     = $product->get_id();
	$img_url = "https://loremflickr.com/600/600/{$slug}?lock={$sig}";

	$tmp = download_url( $img_url );

	if ( is_wp_error( $tmp ) ) {
		echo "FAIL: {$title} — " . $tmp->get_error_message() . "\n";
		$failed++;
		continue;
	}

	$ext  = 'jpg';
	$file = array(
		'name'     => sanitize_title( $title ) . '.' . $ext,
		'type'     => 'image/jpeg',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => filesize( $tmp ),
	);

	$attachment_id = media_handle_sideload( $file, $product->get_id() );

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
		echo "FAIL: {$title} — " . $attachment_id->get_error_message() . "\n";
		$failed++;
		continue;
	}

	// Delete old featured image attachment to keep media library clean.
	$old_id = $product->get_image_id();
	if ( $old_id ) {
		wp_delete_attachment( $old_id, true );
	}

	$product->set_image_id( $attachment_id );
	$product->save();

	echo "OK: {$title}\n";
	$updated++;
}

echo "\nDone — updated: {$updated}, failed: {$failed}\n";
