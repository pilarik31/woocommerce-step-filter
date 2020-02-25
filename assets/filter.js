var $ = jQuery;





var isMoving = false;


var index = 0;


var last = $( '#navigation a:last' ).attr( 'step' );





$( document ).ready(
	function () {

				urlHash();

				//$('#navigation a').tooltip();

	}
);





//$(window).on('hashchange', function() { urlHash(); });





$( document ).on(
	'click',
	'.start',
	function () {

		goTo( 1 );

		// Dočasně skrýt navigaci

		//$('#navigation').fadeIn();

	}
);





$( document ).on(
	'click',
	'.vysledek',
	function (e) {

		e.preventDefault();

		getResult();

	}
);





$( document ).on(
	'click',
	'#navigation a',
	function (e) {

		e.preventDefault();

		var step = $( this ).attr( 'step' );

		var beforeStep = step - 1;

		if ($( this ).hasClass( 'select' ) || $( '#navigation a[step="' + beforeStep + '"]' ).hasClass( 'select' )) {

			goTo( step );

		}

	}
);





$( document ).on(
	'click',
	'#steps .val',
	function (e) {

		e.preventDefault();

		var $step = $( this ).parent().parent().parent();

		var multi = $step.attr( 'multi' );

		if (multi === '1') {

			if ($( this ).hasClass( 'selected' )) {

				$( this ).removeClass( 'selected' );

			} else {

				$( this ).addClass( 'selected' );

			}

			if ($step.find( '.val' ).hasClass( 'selected' )) {

				$step.find( '.next' ).fadeIn();

			} else {

				$step.find( '.next' ).fadeOut();

			}

			setFilter();

		} else {

			$( this ).parent().find( '.val' ).removeClass( 'selected' );

			$( this ).addClass( 'selected' );

			var stepNum = parseInt( $step.attr( 'step' ) ) + 1;

			goTo( stepNum );

		}

	}
);





//dále u multiselectu


$( document ).on(
	'click',
	'.next, .next2',
	function (e) {

		e.preventDefault();

		var step = parseInt( $( this ).parent().attr( 'step' ) ) + 1;

		goTo( step );

	}
);





//zpět


$( document ).on(
	'click',
	'.back',
	function (e) {

		e.preventDefault();

		var step = parseInt( $( this ).parent().attr( 'step' ) ) - 1;

		goTo( step );

	}
);








//přejít na krok


function goTo(step) {

	/*var beforeStep = step - 1;


	var $step = $('#steps .step[step="'+ beforeStep +'"]');*/

	if (step > last /*&& $step.attr('multi') != '1'*/) {

		setFilter();

		getResult();

		return;

	}

	isMoving = true;

	var leftTarget = step - index;

	leftTarget = "-=" + leftTarget + "00%";

	$( '.step' ).animate(
		{left: leftTarget},
		1000,
		function () {

			isMoving = false;

		}
	);

	index = step;

	$( '#navigation .actual' ).removeClass( 'actual' );

	$( '#navigation a[step="' + step + '"]' ).addClass( 'actual' );

	$( '#navigation a[step]' ).each(
		function () {

						var step = $( this ).attr( 'step' );

						var selected = $( '#steps .step[step="' + step + '"]' ).find( '.selected' );

			if (selected.length > 0) {

						  $( '#navigation a[step="' + step + '"]' ).addClass( 'select' );

			} else {

					   $( '#navigation a[step="' + step + '"]' ).removeClass( 'select' );

			}

		}
	);

	setFilter();

}





//načtení postupu z url


function urlHash() {

	var hash = parent.location.hash;

	hash = hash.replace( '#', '' );

	hash = hash.split( "%22" ).join( '"' );

	if (hash) {

		if (hash === 'start') {

			goTo( 1 );

			//$('#navigation').fadeIn();

		} else {

			var setSteps = JSON.parse( hash );

			var step = 0;

			$.each(
				setSteps,
				function (i, val) {

					step = val.step;

					if (val.multi === 1) {

						$.each(
							val.val,
							function (i, val) {

								$( '#steps .step[step="' + step + '"]' ).find( '.val[val="' + val + '"]' ).addClass( 'selected' );

								nextControl( $( '#steps .step[step="' + step + '"]' ) );

							}
						);

					} else {

						$( '#steps .step[step="' + step + '"]' ).find( '.val[val="' + val.val + '"]' ).addClass( 'selected' );

					}

					//$('#navigation').fadeIn();

				}
			);

			//var lastStep = parseInt( $('#navigation a.select:last').attr('step') ) + 1;

			goTo( 1 );

		}

	}

}





//kontrola zobrazení tlačítka dále


function nextControl($step) {

	var last = parseInt( $( '#navigation a:last' ).attr( 'step' ) );

	var actual = parseInt( $step.attr( 'step' ) );

	if ($step.find( '.val' ).hasClass( 'selected' ) && last !== actual) {

		$step.find( '.next' ).fadeIn();

	} else {

		$step.find( '.next' ).fadeOut();

	}

}





//nastavení uložených hodnot do pole


function setFilter() {

	var step = 0;

	var val = '';

	var obj = {};

	var valNum = 0;

	$( '#steps .step' ).each(
		function () {

						step = parseInt( $( this ).attr( 'step' ) );

			if (step !== 0) {

						  obj[step] = {};

						  obj[step].step = step;

						  obj[step].stepVal = $( this ).attr( 'param' );

						  obj[step].multi = 0;

				if ($( this ).attr( 'multi' ) === '1') {

					obj[step].multi = 1;

				}

				if (obj[step].multi === 1) {

					val = {};

					$( this ).find( '.selected' ).each(
						function () {

							val[valNum] = $( this ).attr( 'val' );

							valNum++;

						}
					);

				} else {

					val = $( this ).find( '.selected' ).attr( 'val' );

				}

				obj[step].val = val;

			}

		}
	);

	location.hash = '#' + JSON.stringify( obj );

}





//odeslání hodnot a zobrazení výsledku


function getResult1() {

	$( '.loading' ).fadeIn();

	var hash = parent.location.hash;

	hash = hash.replace( '#', '' );

	hash = hash.split( "%22" ).join( '"' );

	var setSteps = JSON.parse( hash );

	var curr_fields = {};

	$.each(
		setSteps,
		function (i, val) {

			//step = val.step;

			//console.log(val.multi);

			if (val.multi === 1) {

				$.each(
					val.val,
					function(i, val2) {

						curr_fields['pa_' + val.stepVal] = val2;

					}
				);

			} else {

				curr_fields['pa_' + val.stepVal] = val.val;

			}

		}
	);

	//console.log(curr_fields);

	var data = {

		action: 'prdctfltr_respond',

		pf_query: 'product_cat=matrace&error=&m=&p=0&post_parent=&subpost=&subpost_id=&attachment=&attachment_id=0&name=&static=&pagename=&page_id=0&second=&minute=&hour=&day=0&monthnum=0&year=0&w=0&category_name=&tag=&cat=&tag_id=&author=&author_name=&feed=&tb=&paged=0&meta_key=&meta_value=&preview=&s=&sentence=&title=&fields=&menu_order=&embed=&orderby=menu_order+title&order=ASC&meta_query%5B0%5D%5Bkey%5D=_visibility&meta_query%5B0%5D%5Bvalue%5D%5B0%5D=visible&meta_query%5B0%5D%5Bvalue%5D%5B1%5D=catalog&meta_query%5B0%5D%5Bcompare%5D=IN&tax_query%5B0%5D%5Btaxonomy%5D=product_cat&tax_query%5B0%5D%5Bfield%5D=slug&tax_query%5B0%5D%5Bterms%5D%5B0%5D=matrace&tax_query%5Brelation%5D=AND&posts_per_page=24&wc_query=product_query&ignore_sticky_posts=0&suppress_filters=0&cache_results=1&update_post_term_cache=1&lazy_load_term_meta=1&update_post_meta_cache=1&post_type=&nopaging=0&comments_per_page=50&no_found_rows=0&taxonomy=product_cat&term=matrace',

		pf_shortcode: 'false|4|5|yes|false|yes|yes|false|false|false|false|false|archive|false',

		pf_page: 1,

		//pf_action: curr_sc.attr('action'),

		//pf_paged: pf_paged,

		pf_filters: curr_fields,

		pf_widget: 'no',

		pf_mode: 'yes'

	};

	$.post(
		'/wp-admin/admin-ajax.php',
		data,
		function (response) {

			$( '.entry-content' ).fadeOut().html( response );

			$( '.entry-content' ).find( '#prdctfltr_woocommerce' ).remove();

			$( '.entry-content' ).fadeIn();

		}
	);

}





//získání výsledku podle filtru


function getResult() {

	$( '.loading' ).fadeIn();

	var hash = parent.location.hash;

	hash = hash.replace( '#', '' );

	hash = hash.split( "%22" ).join( '"' );

	var setSteps = JSON.parse( hash );

	var curr_fields = '';

	$.each(
		setSteps,
		function (i, val) {

			if (val.multi === 1) {

				$.each(
					val.val,
					function(i, val2) {

						if (val2) {

							curr_fields += 'pa_' + val.stepVal + '=' + val2 + '&';

						}

					}
				);

			} else {

				if (val.val) {

					curr_fields += 'pa_' + val.stepVal + '=' + val.val + '&';

				}

			}

		}
	);

	window.location.href = '/kategorie-produktu/matrace/?' + curr_fields;

}
