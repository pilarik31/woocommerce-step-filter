<?php
require_once 'class/class-filter.php';

class Settings {
	public function __construct() {

		$filter = new Filter();

		if ( isset( $_GET['deleteID'] ) ) {

			$filter->delete_filter( intval( $_GET['deleteID'] ) );

			header( 'Location: /wp-admin/admin.php?page=woocommerce-step-filter' );

		}

		$filters = $filter->get_filters();
		include 'template/settings.phtml';
	}
}
new Settings();
