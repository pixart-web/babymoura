/**
 * BabyMoura — detalhes de interface do tema.
 * - Entradas suaves apenas para conteúdo abaixo da dobra (nunca atrasa a leitura inicial).
 * - Respeita prefers-reduced-motion.
 * - Sombra discreta no cabeçalho ao fazer scroll.
 */
( function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var header = document.querySelector( '.bm-site-header' );
	if ( header ) {
		var onScroll = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	// Link "saltar para o conteúdo": garante que o foco entra no conteúdo principal.
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest && e.target.closest( '.skip-link' );
		var main = document.querySelector( 'main' );
		if ( link && main ) {
			main.setAttribute( 'tabindex', '-1' );
			main.focus();
		}
	} );

	if ( reduce || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var selectors = [
		'.bm-trust__item',
		'.bm-stage',
		'.bm-garden__card',
		'.bm-principle',
		'.bm-point',
		'.bm-steps > li',
		'.bm-split__image',
		'.bm-activity',
		'.bm-service',
		'.bm-gallery__item',
		'.bm-cta'
	];
	var fold = window.innerHeight;
	var io = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( entry.isIntersecting ) {
				entry.target.classList.add( 'is-visible' );
				io.unobserve( entry.target );
			}
		} );
	}, { rootMargin: '0px 0px -8% 0px' } );

	document.querySelectorAll( selectors.join( ',' ) ).forEach( function ( node ) {
		if ( node.getBoundingClientRect().top < fold ) {
			return;
		}
		var siblings = node.parentElement ? Array.prototype.indexOf.call( node.parentElement.children, node ) : 0;
		node.style.setProperty( '--bm-delay', Math.min( siblings, 4 ) * 70 + 'ms' );
		node.classList.add( 'bm-reveal' );
		io.observe( node );
	} );
}() );
