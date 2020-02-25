<?php
class Filter {
	private $db;

	public function __construct() {
		$this->db = $GLOBALS['wpdb'];
		$this->create_db_tab();
	}

	/**
	 * Otestování existence tabulky mysql a případné vytvoření.
	 *
	 * @return void
	 */
	private function create_db_tab() {

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

	/**
	 * Vrací uložené filtry.
	 *
	 * @return void
	 */
	public function get_filters() {

		$results = $this->db->get_results( 'SELECT * FROM wp_woocommerce_step_filter', ARRAY_A );

		$return = array();

		foreach ( $results as $f ) {

			$return[ $f['id'] ]         = $f;
			$return[ $f['id'] ]['step'] = unserialize( $f['step'] );
			$return[ $f['id'] ]['desc'] = $this->decode_html( $f['desc'] );

			$steps = array();

			foreach ( $return[ $f['id'] ]['step'] as $s ) {

				$s['desc'] = $this->decode_html( $s['desc'] );

				$vals = array();

				foreach ( $s['vals'] as $v ) {

					$v['desc'] = $this->decode_html( $v['desc'] );

					$vals[] = $v;
				}

				$s['vals'] = $vals;

				$steps[] = $s;
			}
			$return[ $f['id'] ]['step'] = $steps;
		}
		return $return;
	}

	/**
	 * Získání dat filtru.
	 *
	 * @param [type] $id
	 * @return void
	 */
	public function get_filter( $id ) {

		$results = $this->db->get_results( 'SELECT * FROM wp_woocommerce_step_filter WHERE id = ' . $id, ARRAY_A );

		$return         = array();
		$return         = $results[0];
		$return['desc'] = $this->decode_html( $return['desc'] );
		$return['step'] = unserialize( $return['step'] );

		$steps = array();

		if ( $return['step'] ) {

			foreach ( $return['step'] as $s ) {

				$s['desc'] = $this->decode_html( $s['desc'] );

				$vals = array();

				foreach ( $s['vals'] as $v ) {

					$v['desc'] = $this->decode_html( $v['desc'] );

					$vals[] = $v;
				}

				$s['vals'] = $vals;

				$steps[] = $s;

			}
			$return['step'] = $steps;
		}

		return $return;

	}

	/**
	 * Výběr atributů eshopu.
	 *
	 * @return array Výsledky z DB query.
	 */
	public function get_attributes() {

		return $this->db->get_results( 'SELECT * FROM wp_woocommerce_attribute_taxonomies', ARRAY_A );

	}

	/**
	 * Výběr hodnot k atributu.
	 *
	 * @param string $attr_name Název atributu.
	 * @return array Výsledky z DB query.
	 */
	public function get_vals( $attr_name ) {

		$attr = 'pa_' . $attr_name;

		return $this->db->get_results( "SELECT * FROM wp_term_taxonomy LEFT JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id WHERE wp_term_taxonomy.taxonomy = '" . $attr . "'", ARRAY_A );

	}

	/**
	 * Uložení filtru.
	 *
	 * @param [type] $data
	 * @return void
	 */
	public function save_filter( $data ) {

		$filter_id   = $data['filterID'] != 'false' ? intval( $data['filterID'] ) : false;
		$filter_name = $data['filterName'];
		$filter_desc = $this->encode_html( $data['filterDesc'] );

		//$steps = $data['steps'];

		//var_dump($filter_desc);exit;

		$steps = array();

		foreach ( $data['steps'] as $s ) {

			$s['desc'] = $this->encode_html( $s['desc'] );

			$vals = array();

			foreach ( $s['vals'] as $v ) {

				$v['desc'] = $this->encode_html( $v['desc'] );

				$vals[] = $v;
			}

			$s['vals'] = $vals;

			$steps[] = $s;
		}

		//var_dump($steps);exit;

		$save = array(
			'name' => $filter_name,
			'desc' => $filter_desc,
			'step' => serialize( $steps ),
		);

		//var_dump($save);exit;

		if ( ! $filter_id ) {

			$this->db->insert( 'wp_woocommerce_step_filter', $save );

			$id = $this->db->insert_id;

		} else {

			$this->db->update(
				'wp_woocommerce_step_filter',
				$save,
				array( 'id' => $filter_id )
			);

			$id = $filter_id;
		}
		return $id;
	}

	/**
	 * Smazání filtru.
	 *
	 * @param int $id ID filtru.
	 * @return void
	 */
	public function delete_filter( $id ) {
		$this->db->delete( 'wp_woocommerce_step_filter', array( 'id' => $id ) );
	}



	public function encode_html( $text ) {
		return htmlentities( htmlspecialchars( str_replace( '\\', '', $text ) ) );
	}



	public function decode_html( $text ) {
		return html_entity_decode( htmlspecialchars_decode( $text ) );
	}



	/* ------ HTML ------ */
	/* ------ ---- ------ */

	/**
	 * Vytvoření select listu.
	 *
	 * @param boolean $selected
	 * @return string HTML
	 */
	public function attr_select( $selected = false ) {

		$attr = $this->get_attributes();

		$select = '<select name="step_param">';

		foreach ( $attr as $a ) {
			$selected_option = '';
			if ( $selected == $a['attribute_name'] ) {
				$selected_option = 'selected="selected"';
			}
			$select .= '<option value="' . $a['attribute_name'] . '" ' . $selected_option . '>' . $a['attribute_label'] . '</option>';
		}
		$select .= '</select>';
		return $select;
	}

	/**
	 * Select list s výběrem hodnoty.
	 *
	 * @param [type] $attr_name
	 * @param boolean $selected
	 * @return string HTML
	 */
	public function val_select( $attr_name, $selected = false ) {

		$vals = $this->get_vals( $attr_name );

		$select = '<select name="val_param" attr="' . $attr_name . '">';

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

	/**
	 * Vytvoření select listů s výběrem hodnot.
	 *
	 * @return string HTML
	 */
	public function allval_select() {

		$attr = $this->get_attributes();

		$return = '<div id="vals_lists">';

		foreach ( $attr as $a ) {
			$return .= $this->val_select( $a['attribute_name'] );
		}
		$return .= '</div>';

		return $return;
	}
}
