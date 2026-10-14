/**
 * BabyMoura — comportamentos públicos: acordeão de perguntas, galeria com visualização ampliada
 * e eventos de medição opcionais (desativados por omissão; nunca incluem dados pessoais).
 */
( function () {
	'use strict';

	var cfg = window.bmSite || { analytics: false, i18n: {} };
	var t = cfg.i18n || {};

	/* ------------------------------------------------------------------
	 * Medição opcional: só eventos genéricos, sem nomes, emails ou mensagens.
	 * ---------------------------------------------------------------- */
	var ALLOWED = { visit_cta: 1, form_start: 1, form_submit_valid: 1 };
	function track( name, props ) {
		if ( ! cfg.analytics || ! ALLOWED[ name ] ) {
			return;
		}
		var safe = {};
		[ 'location', 'form' ].forEach( function ( k ) {
			if ( props && typeof props[ k ] === 'string' ) {
				safe[ k ] = props[ k ].slice( 0, 40 );
			}
		} );
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( Object.assign( { event: 'bm_' + name }, safe ) );
	}
	window.bmTrack = track;

	document.addEventListener( 'click', function ( e ) {
		var el = e.target.closest && e.target.closest( 'a[href*="#pedido-de-visita"]' );
		if ( el ) {
			var area = el.closest( 'header, footer, section' );
			track( 'visit_cta', { location: area ? ( area.className.match( /bm-[a-z-]+/ ) || [ area.tagName.toLowerCase() ] )[ 0 ] : '' } );
		}
	} );

	/* ------------------------------------------------------------------
	 * Acordeão: conteúdo visível sem JavaScript; com JavaScript fecha e liga os botões.
	 * ---------------------------------------------------------------- */
	document.querySelectorAll( '[data-bm-accordion]' ).forEach( function ( acc ) {
		acc.querySelectorAll( '.bm-faq__toggle' ).forEach( function ( btn, i ) {
			var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
			if ( ! panel ) {
				return;
			}
			var open = i === 0;
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			panel.hidden = ! open;
			btn.addEventListener( 'click', function () {
				var expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
				btn.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
				panel.hidden = expanded;
			} );
		} );
	} );

	/* ------------------------------------------------------------------
	 * Galeria: filtros com aria-pressed e <dialog> com foco gerido.
	 * ---------------------------------------------------------------- */
	var dialog = null;
	var state = { items: [], index: 0, opener: null };

	function buildDialog() {
		if ( dialog ) {
			return dialog;
		}
		dialog = document.createElement( 'dialog' );
		dialog.className = 'bm-lightbox';
		dialog.setAttribute( 'aria-label', t.position ? '' : 'Imagem' );
		dialog.innerHTML =
			'<div class="bm-lightbox__inner">' +
			'<button type="button" class="bm-lightbox__close" data-act="close"><span class="screen-reader-text"></span><svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>' +
			'<figure class="bm-lightbox__figure"><img alt=""><figcaption><span class="bm-lightbox__count"></span><span class="bm-lightbox__caption"></span></figcaption></figure>' +
			'<div class="bm-lightbox__nav">' +
			'<button type="button" class="bm-lightbox__btn" data-act="prev"><svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg><span class="screen-reader-text"></span></button>' +
			'<button type="button" class="bm-lightbox__btn" data-act="next"><svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg><span class="screen-reader-text"></span></button>' +
			'</div></div>';
		dialog.querySelector( '[data-act="close"] .screen-reader-text' ).textContent = t.close || 'Fechar';
		dialog.querySelector( '[data-act="prev"] .screen-reader-text' ).textContent = t.previous || 'Imagem anterior';
		dialog.querySelector( '[data-act="next"] .screen-reader-text' ).textContent = t.next || 'Imagem seguinte';
		document.body.appendChild( dialog );

		dialog.addEventListener( 'click', function ( e ) {
			var act = e.target.closest( '[data-act]' );
			if ( act ) {
				var a = act.getAttribute( 'data-act' );
				if ( a === 'close' ) {
					dialog.close();
				} else {
					show( state.index + ( a === 'next' ? 1 : -1 ) );
				}
			} else if ( e.target === dialog ) {
				dialog.close(); // Clique no fundo.
			}
		} );
		dialog.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowRight' ) {
				e.preventDefault();
				show( state.index + 1 );
			} else if ( e.key === 'ArrowLeft' ) {
				e.preventDefault();
				show( state.index - 1 );
			}
		} );
		dialog.addEventListener( 'close', function () {
			document.documentElement.classList.remove( 'bm-no-scroll' );
			if ( state.opener ) {
				state.opener.focus();
			}
		} );
		return dialog;
	}

	function show( i ) {
		var n = state.items.length;
		state.index = ( i + n ) % n;
		var b = state.items[ state.index ];
		var img = dialog.querySelector( 'img' );
		img.src = b.getAttribute( 'data-full' );
		img.alt = b.getAttribute( 'data-alt' ) || '';
		img.width = parseInt( b.getAttribute( 'data-width' ), 10 ) || 1200;
		img.height = parseInt( b.getAttribute( 'data-height' ), 10 ) || 900;
		var pos = ( t.position || 'Imagem %1$s de %2$s' ).replace( '%1$s', state.index + 1 ).replace( '%2$s', n );
		dialog.querySelector( '.bm-lightbox__count' ).textContent = pos;
		dialog.querySelector( '.bm-lightbox__caption' ).textContent = b.getAttribute( 'data-caption' ) || '';
		dialog.setAttribute( 'aria-label', pos );
		dialog.classList.toggle( 'is-provisional', b.getAttribute( 'data-provisional' ) === '1' );
		var single = n < 2;
		dialog.querySelectorAll( '.bm-lightbox__btn' ).forEach( function ( btn ) {
			btn.hidden = single;
		} );
	}

	document.querySelectorAll( '[data-bm-gallery]' ).forEach( function ( gallery ) {
		var status = gallery.querySelector( '[data-bm-gallery-status]' );
		var visible = function () {
			return Array.prototype.filter.call( gallery.querySelectorAll( '.bm-gallery__item' ), function ( li ) {
				return ! li.hidden;
			} ).map( function ( li ) {
				return li.querySelector( '.bm-gallery__open' );
			} );
		};

		gallery.addEventListener( 'click', function ( e ) {
			var chip = e.target.closest( '[data-filter]' );
			if ( chip ) {
				var f = chip.getAttribute( 'data-filter' );
				gallery.querySelectorAll( '[data-filter]' ).forEach( function ( c ) {
					c.setAttribute( 'aria-pressed', c === chip ? 'true' : 'false' );
				} );
				var count = 0;
				gallery.querySelectorAll( '.bm-gallery__item' ).forEach( function ( li ) {
					var match = ! f || ( ' ' + li.getAttribute( 'data-spaces' ) + ' ' ).indexOf( ' ' + f + ' ' ) !== -1;
					li.hidden = ! match;
					count += match ? 1 : 0;
				} );
				if ( status ) {
					status.textContent = count + ( count === 1 ? ' imagem' : ' imagens' );
				}
				return;
			}
			var open = e.target.closest( '.bm-gallery__open' );
			if ( open && typeof HTMLDialogElement === 'function' ) {
				e.preventDefault();
				buildDialog();
				state.items = visible();
				state.opener = open;
				show( state.items.indexOf( open ) );
				document.documentElement.classList.add( 'bm-no-scroll' );
				dialog.showModal();
				dialog.querySelector( '[data-act="close"]' ).focus();
			}
		} );
	} );
}() );
