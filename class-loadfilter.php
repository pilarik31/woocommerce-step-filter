<?php
require_once 'class/class-filter.php';
class LoadFilter {

	public function __construct( $id = null ) {

		$f      = new Filter();
		$filter = false;

		if ( $id ) {
			$filter = $f->get_filter( $id );
			include 'template/filter.phtml';
		}

	}



}
