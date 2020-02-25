<?php



class Filter {



	private $db;



	function __construct() {

		$this->db = $GLOBALS['wpdb'];

		$this->createDbTab();

	}



	//otestování existence tabulky mysql a případné vytvoření

	private function createDbTab() {

		$exist = $this->db->get_var( "SHOW TABLES LIKE 'wp_woocommerce_step_filter'" );

		if ( ! $exist ) {

			$this->db->query(
				'CREATE TABLE `wp_woocommerce_step_filter` (


                  `id` int(19) NOT NULL AUTO_INCREMENT PRIMARY KEY,


                  `name` varchar(255) NOT NULL,


                  `desc` text NOT NULL,


                  `step` longtext NOT NULL


                );'
			);

		}

	}



	//vrací uložené filtry

	public function getFilters() {

		$results = $this->db->get_results( 'SELECT * FROM wp_woocommerce_step_filter', ARRAY_A );

		$return = array();

		foreach ( $results as $f ) {

			$return[ $f['id'] ] = $f;

			$return[ $f['id'] ]['step'] = unserialize( $f['step'] );

			$return[ $f['id'] ]['desc'] = $this->decodeHtml( $f['desc'] );

			$steps = array();

			foreach ( $return[ $f['id'] ]['step'] as $s ) {

				$s['desc'] = $this->decodeHtml( $s['desc'] );

				$vals = array();

				foreach ( $s['vals'] as $v ) {

					$v['desc'] = $this->decodeHtml( $v['desc'] );

					$vals[] = $v;

				}

				$s['vals'] = $vals;

				$steps[] = $s;

			}

			$return[ $f['id'] ]['step'] = $steps;

		}

		return $return;

	}



	//získání dat filtru

	public function getFilter( $id ) {

		$results = $this->db->get_results( 'SELECT * FROM wp_woocommerce_step_filter WHERE id = ' . $id, ARRAY_A );

		$return = array();

		$return = $results[0];

		$return['desc'] = $this->decodeHtml( $return['desc'] );

		$return['step'] = unserialize( $return['step'] );

		$steps = array();

		if ( $return['step'] ) {

			foreach ( $return['step'] as $s ) {

				$s['desc'] = $this->decodeHtml( $s['desc'] );

				$vals = array();

				foreach ( $s['vals'] as $v ) {

					$v['desc'] = $this->decodeHtml( $v['desc'] );

					$vals[] = $v;

				}

				$s['vals'] = $vals;

				$steps[] = $s;

			}

			$return['step'] = $steps;

		}

		return $return;

	}



	//výběr atributů eshopu

	public function getAttributes() {

		return $this->db->get_results( 'SELECT * FROM wp_woocommerce_attribute_taxonomies', ARRAY_A );

	}



	//výběr hodnot k atributu

	public function getVals( $attrName ) {

		$attr = 'pa_' . $attrName;

		return $this->db->get_results( "SELECT * FROM wp_term_taxonomy LEFT JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id WHERE wp_term_taxonomy.taxonomy = '" . $attr . "'", ARRAY_A );

	}





	//uložení filtru

	public function saveFilter( $data ) {

		$filterID = $data['filterID'] != 'false' ? intval( $data['filterID'] ) : false;

		$filterName = $data['filterName'];

		$filterDesc = $this->encodeHtml( $data['filterDesc'] );

		//$steps = $data['steps'];

		//var_dump($filterDesc);exit;

		$steps = array();

		foreach ( $data['steps'] as $s ) {

			$s['desc'] = $this->encodeHtml( $s['desc'] );

			$vals = array();

			foreach ( $s['vals'] as $v ) {

				$v['desc'] = $this->encodeHtml( $v['desc'] );

				$vals[] = $v;

			}

			$s['vals'] = $vals;

			$steps[] = $s;

		}

		//var_dump($steps);exit;

		$save = array(

			'name' => $filterName,

			'desc' => $filterDesc,

			'step' => serialize( $steps ),

		);

		//var_dump($save);exit;

		if ( ! $filterID ) {

			$this->db->insert( 'wp_woocommerce_step_filter', $save );

			$id = $this->db->insert_id;

		} else {

			$this->db->update(
				'wp_woocommerce_step_filter',
				$save,
				array( 'id' => $filterID )
			);

			$id = $filterID;

		}

		return $id;

	}



	//smazání filtru

	public function deleteFilter( $id ) {

		$this->db->delete( 'wp_woocommerce_step_filter', array( 'id' => $id ) );

	}



	public function encodeHtml( $text ) {

		return htmlentities( htmlspecialchars( str_replace( '\\', '', $text ) ) );

	}



	public function decodeHtml( $text ) {

		return html_entity_decode( htmlspecialchars_decode( $text ) );

	}



	/**------ HTML -----------------------------------------------------------------------------------------------------------*/

	//vytvoření select listu

	public function attrSelect( $selected = false ) {

		$attr = $this->getAttributes();

		$select = '<select name="step_param">';

		foreach ( $attr as $a ) {

			$selectedOption = '';

			if ( $selected == $a['attribute_name'] ) {

				$selectedOption = 'selected="selected"';

			}

			$select .= '<option value="' . $a['attribute_name'] . '" ' . $selectedOption . '>' . $a['attribute_label'] . '</option>';

		}

		$select .= '</select>';

		return $select;

	}



	//select list s výběrem hodnoty

	public function valSelect( $attrName, $selected = false ) {

		$vals = $this->getVals( $attrName );

		$select = '<select name="val_param" attr="' . $attrName . '">';

		// var_dump($vals);

		foreach ( $vals as $v ) {

			if ( $selected && $selected == $v['slug'] ) {

				$select .= '<option value="' . $v['slug'] . '" selected="selected">' . $v['name'] . '</option>';

			} else {

				$select .= '<option value="' . $v['slug'] . '">' . $v['name'] . '</option>';

			}
		}

		$select .= '</select>';

		return $select;

	}



	//vytvoření select listů s výběrem hodnot

	public function allValSelect() {

		$attr = $this->getAttributes();

		$return = '<div id="vals_lists">';

		foreach ( $attr as $a ) {

			$return .= $this->valSelect( $a['attribute_name'] );

		}

		$return .= '</div>';

		return $return;

	}



}
