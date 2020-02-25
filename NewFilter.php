<?php


require_once 'class/Filter.php';



class NewFilter {



	public function __construct() {

		$f = new Filter();

		$filter = false;

		if ( $_GET['filterID'] ) {

			$id = intval( $_GET['filterID'] );

			$filter = $f->getFilter( $id );

			//var_dump($filter);

		}

		$data = array();

		$data['name'] = $filter ? $filter['name'] : '';

		$data['desc'] = $filter ? $filter['desc'] : '';

		$data['step'] = $filter ? $filter['step'] : false;

		//var_dump($filter['step']);

		$attrSelect = $f->attrSelect();

		include 'template/new-filter.phtml';

	}



}




new NewFilter();
