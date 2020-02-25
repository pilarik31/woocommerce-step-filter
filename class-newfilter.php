<?php
require_once 'class/class-filter.php';

class NewFilter {

	public function __construct() {

		$f = new Filter();

		$filter = false;

		if ( $_GET['filterID'] ) {
			$id     = intval( $_GET['filterID'] );
			$filter = $f->get_filter( $id );
		}

		$data         = array();
		$data['name'] = $filter ? $filter['name'] : '';
		$data['desc'] = $filter ? $filter['desc'] : '';
		$data['step'] = $filter ? $filter['step'] : false;

		$attr_select = $f->attr_select();

		include 'template/new-filter.phtml';

	}



}




new NewFilter();
