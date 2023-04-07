<?php 
/** 
* Plugin Name: Color Dropdown 
* Description: Finds same product in different colours based on SKU and displays them as color swatches. 
* Author: Lawrence Kwok 
* Version: 2.0 */

add_action( 'woocommerce_before_variations_form', 'color_changer');

// This array was orignally part of another file. It has been truncated and added to this file for clarity
$GLOBALS['colors'] = array( 
	'Brown' => array('brown','umber','khaki','nugget','mud','torf','bourbon'),
	'Red' => array('red','carmine','carmin','melon','bordo','sangria'),
	'Blue' => array('blue','lake','shark','denim','river','topaz','cobalt')
);

// This function takes secondary colors and returns the main color CSS class. For example, "aqua" and "marine" would return "blue".
function get_main_color( $color ) {
	$product_color = strtolower( $color );
	$product_color = strtok( $product_color, ' ' );
	$main_colors = $GLOBALS['colors'];

	foreach ( $main_colors as $main_color => $sec_color ) {
		if( 0 < count( array_intersect( array_map( 'strtolower', explode( ' ', $product_color) ), $sec_color ) ) ) {
			$main_color_class = strtolower( str_replace( $sec_color, $main_color, $product_color ) );
		}
	}
	return $main_color_class;
}

function color_changer() {
	global $post;
	$product = wc_get_product( $post->ID );
	$sku = $product->get_sku();
	$brandname = output_brand();
	
	// Different brands have different SKU prefix lengths
	switch( $brandname ) {
		case 'Brand1':
			$prefix_length = 5;
			break;
		case 'Brand2':
			$prefix_length = 5;
			break;
		case 'Brand3':
			$prefix_length = 9;
			break;
		case 'Brand4':
			$prefix_length = 4;
			break;
		case 'Brand5':
			$prefix_length = 5;
			break;
		case 'Brand6':
			$prefix_length = 6;
			break;
	}

	//Check for brands which have no similar SKUs
	if( $prefix_length > 0 ) {
		
		$sku_prefix = substr( $sku, 0, $prefix_length );

		global $wpdb;
		$products = $wpdb->get_results(
			"SELECT post_id, post_title FROM $wpdb->postmeta 
			LEFT JOIN wp_posts ON $wpdb->postmeta.post_id = wp_posts.ID
			WHERE meta_key='_sku' AND meta_value LIKE '$sku_prefix%' AND post_status = 'publish' AND post_type = 'product'
			LIMIT 15"
		);

		$output_HTML = '<table id="color_changer" class="color-changer variations" cellspacing="0">
            <tbody>
                <tr>
                    <td class="label">
                        <label>Colour</label>
                    </td>
                    <td>';
		
		foreach( $products as $product ) {	
			$jumpurl = get_the_permalink( $product->post_id );
			$currenturl = curPageURL();
			$currenturlhttps = str_replace( 'http', 'https', currenturl );
            $selected = ( $jumpurl === $currenturl || $jumpurl === $currenturlhttps ) ? ' selected' : '';
			
			$product_name = $product->post_title;

            // Format some product titles. Eg. "(Patent) (Black)"
			$replace_chars = array( ') (', '/', '-' );
			$product_name = str_replace( $replace_chars, ' ', $product_name );
			
            // Regex gets color name between brackets from product title
			preg_match( '#\((.*?)\)#', $product_name, $match );
			$color = esc_attr( $match[1] );
			$color_class = esc_attr( strtolower( $color ) ); // Keep secondary colour in case there is CSS for it
			$main_color_class = esc_attr( get_main_color( $color ) );

			$stock = get_post_meta( $product->post_id, '_stock', true );

			if ( $stock === 0 || $stock === 999 ) {
				$instock_color = 'outofstock-color';
				$color = $color.' (Out of Stock)';
			} else {
				$instock_color = 'instock-color';
			}

			$output_HTML .= "<a data-toggle='tooltip' data-placement='top' title='$color' class='$color_class $main_color_class $instock_color$selected' href='$jumpurl'>$color</a>";

		}
		$output_HTML .= '</td></tr></tbody></table>';
		echo $output_HTML;
	}
}

?>