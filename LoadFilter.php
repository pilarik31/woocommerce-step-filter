<?php


require_once 'class/Filter.php';



class LoadFilter {



	public function __construct( $id = null ) {

		$f = new Filter();

		$filter = false;

		if ( $id ) {

			$filter = $f->getFilter( $id );

			include 'template/filter.phtml';

		}

	}



}
