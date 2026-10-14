/**
 * BabyMoura — formulários de visita e pré-inscrição.
 * Validação no cliente (espelha a do servidor), erros ligados aos campos, estados de envio,
 * prevenção de envios repetidos e recuperação de erros. Sem JavaScript, o formulário continua a funcionar.
 */
( function () {
	'use strict';

	var cfg = window.bmForms || {};
	var MSG = {
		name: 'Indique o seu nome.',
		nameLen: 'O nome deve ter entre 2 e 100 caracteres.',
		email: 'Indique um email para podermos responder.',
		emailBad: 'O email não parece válido. Exemplo: nome@exemplo.pt',
		phone: 'Indique um telefone com 9 a 20 dígitos (pode incluir +351).',
		stage: 'Escolha a resposta educativa.',
		age: 'Escolha a faixa etária aproximada.',
		start: 'Escolha o mês e o ano de entrada pretendidos.',
		startPast: 'A data de entrada não pode ser no passado.',
		message: 'A mensagem pode ter até 1000 caracteres.',
		network: 'Não foi possível contactar o servidor. Verifique a ligação e tente novamente — os seus dados continuam no formulário.'
	};

	function uuid() {
		if ( window.crypto && window.crypto.randomUUID ) {
			return window.crypto.randomUUID();
		}
		return 'r' + Date.now().toString( 36 ) + Math.random().toString( 36 ).slice( 2 );
	}

	function val( form, name ) {
		var el = form.elements[ name ];
		return el ? String( el.value || '' ).trim() : '';
	}

	function validate( form, type ) {
		var e = {};
		var name = val( form, 'name' );
		if ( ! name ) {
			e.name = MSG.name;
		} else if ( name.length < 2 || name.length > 100 ) {
			e.name = MSG.nameLen;
		}
		var email = val( form, 'email' );
		if ( ! email ) {
			e.email = MSG.email;
		} else if ( email.length > 150 || ! /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( email ) ) {
			e.email = MSG.emailBad;
		}
		var phone = val( form, 'phone' );
		if ( phone && ! /^\+?[0-9 ()\-]{9,20}$/.test( phone ) ) {
			e.phone = MSG.phone;
		}
		if ( ! val( form, 'stage' ) ) {
			e.stage = MSG.stage;
		}
		if ( type === 'pre-inscricao' ) {
			if ( ! val( form, 'age_band' ) ) {
				e.age_band = MSG.age;
			}
			var m = val( form, 'start_month' );
			var y = val( form, 'start_year' );
			if ( ! m || ! y ) {
				e.start = MSG.start;
			} else {
				var now = new Date();
				var current = now.getFullYear() * 100 + now.getMonth() + 1;
				if ( parseInt( y + m, 10 ) < current ) {
					e.start = MSG.startPast;
				}
			}
		}
		if ( val( form, 'message' ).length > 1000 ) {
			e.message = MSG.message;
		}
		return e;
	}

	function fieldBox( form, key ) {
		return form.querySelector( '[data-field="' + key + '"]' );
	}

	function clearErrors( form ) {
		form.querySelectorAll( '.bm-field__error' ).forEach( function ( p ) {
			p.hidden = true;
			p.textContent = '';
		} );
		form.querySelectorAll( '[aria-invalid]' ).forEach( function ( el ) {
			el.removeAttribute( 'aria-invalid' );
		} );
		form.querySelectorAll( '.bm-field.has-error' ).forEach( function ( f ) {
			f.classList.remove( 'has-error' );
		} );
		var summary = form.querySelector( '.bm-form__summary' );
		summary.hidden = true;
		summary.querySelector( 'ul' ).innerHTML = '';
	}

	function showErrors( form, errors ) {
		var summary = form.querySelector( '.bm-form__summary' );
		var list = summary.querySelector( 'ul' );
		var first = null;
		Object.keys( errors ).forEach( function ( key ) {
			var box = fieldBox( form, key );
			if ( ! box ) {
				return;
			}
			box.classList.add( 'has-error' );
			var err = box.querySelector( '.bm-field__error' );
			err.textContent = errors[ key ];
			err.hidden = false;
			var controls = box.querySelectorAll( 'input, select, textarea' );
			controls.forEach( function ( c ) {
				c.setAttribute( 'aria-invalid', 'true' );
			} );
			var target = controls[ 0 ];
			if ( target ) {
				first = first || target;
				var li = document.createElement( 'li' );
				var a = document.createElement( 'a' );
				a.href = '#' + target.id;
				a.textContent = errors[ key ];
				a.addEventListener( 'click', function ( ev ) {
					ev.preventDefault();
					target.focus();
				} );
				li.appendChild( a );
				list.appendChild( li );
			}
		} );
		if ( list.children.length ) {
			summary.hidden = false;
			summary.focus();
		}
		return first;
	}

	function setStatus( form, state, text ) {
		var s = form.querySelector( '.bm-form__status' );
		s.setAttribute( 'data-state', state );
		s.textContent = text;
		s.hidden = ! text;
		if ( text ) {
			s.focus();
		}
	}

	function setBusy( form, busy ) {
		var btn = form.querySelector( 'button[type="submit"]' );
		form.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
		btn.disabled = busy;
		btn.querySelector( '.bm-button__label' ).hidden = busy;
		btn.querySelector( '.bm-button__busy' ).hidden = ! busy;
	}

	function payload( form ) {
		var data = {};
		Array.prototype.forEach.call( form.elements, function ( el ) {
			if ( el.name && el.name !== 'action' ) {
				data[ el.name ] = el.value;
			}
		} );
		return data;
	}

	function send( form, type, retried ) {
		return fetch( cfg.endpoint + type, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
			credentials: 'same-origin',
			body: JSON.stringify( payload( form ) )
		} ).then( function ( res ) {
			return res.json().catch( function () {
				return {};
			} ).then( function ( body ) {
				if ( res.status === 400 && body.status === 'expired' && body.token && ! retried ) {
					form.elements.bm_token.value = body.token;
					return send( form, type, true );
				}
				return { code: res.status, body: body };
			} );
		} );
	}

	document.querySelectorAll( 'form[data-bm-form]' ).forEach( function ( form ) {
		var type = form.getAttribute( 'data-bm-form' );
		var busy = false;
		var started = false;
		form.elements.bm_request_id.value = uuid();

		form.addEventListener( 'input', function ( ev ) {
			if ( ! started ) {
				started = true;
				if ( window.bmTrack ) {
					window.bmTrack( 'form_start', { form: type } );
				}
			}
			// Remove o erro do campo assim que é corrigido.
			var box = ev.target.closest( '.bm-field' );
			if ( box && box.classList.contains( 'has-error' ) ) {
				var all = validate( form, type );
				var key = box.getAttribute( 'data-field' );
				if ( ! all[ key ] ) {
					box.classList.remove( 'has-error' );
					box.querySelector( '.bm-field__error' ).hidden = true;
					box.querySelectorAll( '[aria-invalid]' ).forEach( function ( el ) {
						el.removeAttribute( 'aria-invalid' );
					} );
				}
			}
		} );

		form.addEventListener( 'submit', function ( ev ) {
			ev.preventDefault();
			if ( busy ) {
				return; // Evita envios duplicados.
			}
			clearErrors( form );
			setStatus( form, '', '' );
			var errors = validate( form, type );
			if ( Object.keys( errors ).length ) {
				showErrors( form, errors );
				return;
			}
			busy = true;
			setBusy( form, true );
			send( form, type, false ).then( function ( r ) {
				var status = r.body.status || 'error';
				var message = r.body.message || MSG.network;
				if ( r.code === 200 && ( status === 'ok' || status === 'demo' || status === 'duplicate' ) ) {
					if ( window.bmTrack && status === 'ok' ) {
						window.bmTrack( 'form_submit_valid', { form: type } );
					}
					form.reset();
					form.elements.bm_request_id.value = uuid();
					setStatus( form, status === 'demo' ? 'demo' : 'success', message );
				} else if ( r.code === 422 && r.body.errors ) {
					showErrors( form, r.body.errors );
				} else {
					setStatus( form, 'error', message );
				}
			} ).catch( function () {
				setStatus( form, 'error', MSG.network );
			} ).then( function () {
				busy = false;
				setBusy( form, false );
			} );
		} );
	} );
}() );
