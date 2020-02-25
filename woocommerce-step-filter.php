<?php
/*
Plugin Name: WooCommerce step filter
Description: WooCommerce products step filter
Version: 1.0
Author: IT Lab czech s.r.o.
License: Commercial
*/

//Přídání souborů css a js

function css_js() {

	//wp_register_script( 'js', plugins_url('vendor/ckeditor/ckeditor.js',__FILE__ ));

	//wp_enqueue_script('js');

}


add_action( 'admin_init', 'css_js' );




//načtení filtru pomocí shortcode


add_shortcode( 'STEP_FILTER', 'show_filter' );

function show_filter( $atts = array() ) {

	shortcode_atts(
		array(

			'id' => false,

		),
		$atts
	);

	$return = '';

	if ( $atts['id'] ) {

		include 'class-loadfilter.php';

		new LoadFilter( $atts['id'] );

	}

}




/**------------- Položka menu --------------------------------------------------------------------------------------------------------------------*/


//přidá funkci do menu


add_action( 'admin_menu', 'woocommerce_step_filter' );




//určení názvů, levelu oprávněného uživatele a stránky v administračním rozhraní

function woocommerce_step_filter() {

	add_menu_page( 'WooCommerce step filter', 'WooCommerce step filter', administrator, 'woocommerce-step-filter', 'menu_options' );

	add_submenu_page( 'woocommerce-step-filter', 'Vytvořit filtr', 'Vytvořit filtr', administrator, 'new-woocommerce-step-filter', 'new_filter' );

}




//Obsah položky menu v administraci

function menu_options() {

	include 'Settings.php';

}



function new_filter() {

	include 'class-newfilter.php';

}




/**------------- Položka menu - END --------------------------------------------------------------------------------------------------------------*/




/**------------- AJAX ----------------------------------------------------------------------------------------------------------------------------*/




//uložení filtru


add_action( 'wp_ajax_save_filter', 'save_filter' );

function save_filter() {

	require_once 'class/class-filter.php';

	$f = new Filter();

	$id = $f->save_filter( $_POST );

	//$steps = $_POST['steps'];

	//var_dump($steps);exit;

	wp_send_json(
		array(
			'success' => true,
			'id'      => $id,
		)
	);

}




//editor


add_action( 'wp_ajax_get_editor', 'get_editor' );

function get_editor() {

	$name = $_POST['name'];

	$settings = array(

		'teeny'         => true,

		'textarea_rows' => 10,

		'tabindex'      => 1,

	);

	ob_start();

	wp_editor( '', $name, $settings );

	$output = ob_get_contents();

	ob_end_clean();

	wp_send_json(
		array(
			'success' => true,
			'editor'  => $output,
		)
	);

}




add_action( 'wp_ajax_step_filter_respond', 'step_filter_respond' );

function step_filter_respond() {

	global $prdctfltr_global;

	$shortcode_params = explode( '|', $_POST['pf_shortcode'] );

	$preset = ( $shortcode_params[0] !== 'false' ? $shortcode_params[0] : '' );

	$columns = ( $shortcode_params[1] !== 'false' ? $shortcode_params[1] : 4 );

	$rows = ( $shortcode_params[2] !== 'false' ? $shortcode_params[2] : 4 );

	$pagination = ( $shortcode_params[3] !== 'false' ? $shortcode_params[3] : '' );

	$no_products = ( $shortcode_params[4] !== 'false' ? $shortcode_params[4] : '' );

	$show_products = ( $shortcode_params[5] !== 'false' ? $shortcode_params[5] : '' );

	$use_filter = ( $shortcode_params[6] !== 'false' ? $shortcode_params[6] : '' );

	$action = ( $shortcode_params[7] !== 'false' ? $shortcode_params[7] : '' );

	$bot_margin = ( $shortcode_params[8] !== 'false' ? $shortcode_params[8] : '' );

	$class = ( $shortcode_params[9] !== 'false' ? $shortcode_params[9] : '' );

	$shortcode_id = ( $shortcode_params[10] !== 'false' ? $shortcode_params[10] : '' );

	$disable_overrides = ( $shortcode_params[11] !== 'false' ? $shortcode_params[11] : '' );

	$show_categories = ( $shortcode_params[12] !== 'false' ? $shortcode_params[12] : '' );

	$cat_columns = ( $shortcode_params[13] !== 'false' ? $shortcode_params[13] : '' );

	$res_paged = ( isset( $_POST['pf_paged'] ) ? $_POST['pf_paged'] : $_POST['pf_page'] );

	parse_str( $_POST['pf_query'], $qargs );

	$qargs = array_merge(
		$qargs,
		array(

			'post_type'   => 'product',

			'post_status' => 'publish',

		)
	);

	$ajax_query = http_build_query( $qargs );

	$current_page = WC_Prdctfltr::prdctfltr_get_between( $ajax_query, 'paged=', '&' );

	$page = $res_paged;

	$args = str_replace( 'paged=' . $current_page . '&', 'paged=' . $page . '&', $ajax_query );

	if ( $no_products == 'yes' ) {

		$use_filter = 'no';

		$pagination = 'no';

		$orderby = 'rand';

	}

	$add_ajax = ' data-query="' . $args . '" data-page="' . $res_paged . '" data-shortcode="' . $_POST['pf_shortcode'] . '"';

	$bot_margin = (int) $bot_margin;

	$margin = " style='margin-bottom:" . $bot_margin . "px'";

	if ( isset( $_POST['pf_filters'] ) ) {

		$curr_filters = $_POST['pf_filters'];

	} else {

		$curr_filters = array();

	}

	$filter_args = '';

	foreach ( $curr_filters as $k => $v ) {

		if ( strpos( $v, ',' ) ) {

			$new_v = str_replace( ',', '%2C', $v );

		} elseif ( strpos( $v, '+' ) ) {

			$new_v = str_replace( '+', '%2B', $v );

		} else {

			$new_v = $v;

		}

		$filter_args .= '&' . $k . '=' . $new_v;

	}

	$prdctfltr_global['ajax_query'] = $args;

	$args = $args . $filter_args . '&prdctfltr=active';

	$prdctfltr_global['ajax_paged'] = $res_paged;

	$prdctfltr_global['active_filters'] = $curr_filters;

	if ( $action !== '' ) {

		$prdctfltr_global['action'] = $action;

	}

	if ( $preset !== '' ) {

		$prdctfltr_global['preset'] = $preset;

	}

	if ( $disable_overrides !== '' ) {

		$prdctfltr_global['disable_overrides'] = $disable_overrides;

	}

	$out = '';

	global $woocommerce, $woocommerce_loop, $wp_the_query, $wp_query;

	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', $columns );

	$prdctfltr_global['ajax'] = true;

	$prdctfltr_global['sc_ajax'] = $_POST['pf_mode'] == 'no' ? 'no' : null;

	$products = new WP_Query( $args );

	$products->is_search = false;

	$wp_query = $products;

	$wp_the_query = $products;

	ob_start();

	if ( $use_filter == 'yes' ) {

		include WC_Prdctfltr::$dir . 'woocommerce/loop/product-filter.php';

	}

	if ( $products->have_posts() ) :

		if ( $show_products == 'yes' ) {

			woocommerce_product_loop_start();

			if ( isset( $prdctfltr_global['categories_active'] ) && $prdctfltr_global['categories_active'] === true ) {

				if ( $show_categories == 'archive' ) {

					if ( isset( $cat_columns ) ) {

						$woocommerce_loop['columns'] = $cat_columns;

					}

					woocommerce_product_subcategories();

				} elseif ( $show_categories == 'yes' ) {

					if ( isset( $cat_columns ) ) {

						$woocommerce_loop['columns'] = $cat_columns;

					}

					get_step_filter_categories();

				}
			}

			while ( $products->have_posts() ) :
				$products->the_post();

				if ( isset( $columns ) ) {

					$woocommerce_loop['columns'] = $columns;

				}

				wc_get_template_part( 'content', 'product' );

			 endwhile;

			woocommerce_product_loop_end();

		} else {

			$pagination = 'no';

		}

	 else :

		 include WC_Prdctfltr::$dir . 'woocommerce/loop/product-filter-no-products-found.php';

	 endif;

	 $prdctfltr_global['ajax'] = null;

	 $shortcode = str_replace( 'type-product', 'product type-product', ob_get_clean() );

	 $out .= '<div' . ( $shortcode_id != '' ? ' id="' . $shortcode_id . '"' : '' ) . ' class="prdctfltr_sc_products woocommerce prdctfltr_ajax' . ( $class != '' ? ' ' . $class . '' : '' ) . '"' . $margin . $add_ajax . '>';

	 $out .= do_shortcode( $shortcode );

	 if ( $pagination == 'yes' ) {

		 $wp_query = $products;

		 ob_start();

		 add_filter( 'woocommerce_pagination_args', 'WC_Prdctfltr::prdctfltr_pagination_filter', 999, 1 );

		 wc_get_template( 'loop/pagination.php' );

		 remove_filter( 'woocommerce_pagination_args', 'WC_Prdctfltr::prdctfltr_pagination_filter' );

		 $pagination = ob_get_clean();

		 $out .= $pagination;

	 }

	 if ( $_POST['pf_widget'] == 'yes' ) {

		 if ( isset( $_POST['pf_widget_title'] ) ) {

			 $curr_title = explode( '%%%', $_POST['pf_widget_title'] );

		 }

		 ob_start();

        the_widget(
			'prdctfltr',
			'preset=' . $_POST['pf_preset'] . '&template=' . $_POST['pf_template'],
			array(
				'before_title' => stripslashes( $curr_title[0] ),
				'after_title'  => stripslashes( $curr_title[1] ),
			)
        );

		 $out .= ob_get_clean();

	 }

	 $out .= '</div>';

	 die( $out );

	 exit;

}



function get_step_filter_categories() {

	global $wp_query;

	$defaults = array(

		'before'        => '',

		'after'         => '',

		'force_display' => false,

	);

	$args = array();

	$args = wp_parse_args( $args, $defaults );

	extract( $args );

	$term = get_queried_object();

	$parent_id = empty( $term->term_id ) ? 0 : $term->term_id;

	if ( $parent_id == 0 && isset( $_GET['product_cat'] ) ) {

		$term = get_term_by( 'slug', $_GET['product_cat'], 'product_cat' );

		$parent_id = $term->term_id;

	}

	/*			if ( is_product_category() ) {

					$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );


					switch ( $display_type ) {
						case 'products' :
							return;
						break;
						case '' :
							if ( get_option( 'woocommerce_category_archive_display' ) == '' ) {
								return;
							}
						break;
					}
				}*/

	$product_categories = get_categories(
		apply_filters(
			'woocommerce_product_subcategories_args',
			array(

				'parent'       => $parent_id,

				'menu_order'   => 'ASC',

				'hide_empty'   => 0,

				'hierarchical' => 1,

				'taxonomy'     => 'product_cat',

				'pad_counts'   => 1,

			)
		)
	);

	/*			if ( ! apply_filters( 'woocommerce_product_subcategories_hide_empty', false ) ) {

					$product_categories = wp_list_filter( $product_categories, array( 'count' => 0 ), 'NOT' );
				}*/

	if ( $product_categories ) {

		echo $before;

		foreach ( $product_categories as $category ) {

			wc_get_template(
				'content-product_cat.php',
				array(

					'category' => $category,

				)
			);

		}

		/*				if ( is_product_category() ) {

							$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );


							switch ( $display_type ) {
								case 'subcategories' :
									$wp_query->post_count    = 0;
									$wp_query->max_num_pages = 0;
								break;
								case '' :
									if ( get_option( 'woocommerce_category_archive_display' ) == 'subcategories' ) {
										$wp_query->post_count    = 0;
										$wp_query->max_num_pages = 0;
									}
								break;
							}
						}


						if ( is_shop() && get_option( 'woocommerce_shop_page_display' ) == 'subcategories' ) {
							$wp_query->post_count    = 0;
							$wp_query->max_num_pages = 0;
						}*/

		echo $after;

		return true;

	}

}




/**------------- AJAX - END ----------------------------------------------------------------------------------------------------------------------*/




