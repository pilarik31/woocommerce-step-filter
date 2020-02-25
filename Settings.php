<?php
require_once 'class/Filter.php';

class Settings {
	public function __construct() {

		$filter = new Filter();

		if ( isset( $_GET['deleteID'] ) ) {

			$filter->deleteFilter( intval( $_GET['deleteID'] ) );

			header( 'Location: /wp-admin/admin.php?page=woocommerce-step-filter' );

		}

		$filters = $filter->getFilters();
		include 'template/settings.phtml';
	}
}
new Settings();
