/**
 * BabyMoura — integração no editor de blocos (sem passo de compilação).
 * Os blocos são dinâmicos: o editor mostra a pré-visualização do servidor e controlos simples.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var ServerSideRender = wp.serverSideRender;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var data = window.bmEditor || { icons: [], spaces: [] };

	var STAGES = [
		{ value: 'all', label: 'Todas as etapas' },
		{ value: 'creche', label: 'Creche' },
		{ value: 'jardim', label: 'Jardim de Infância' }
	];

	/** Controlos por bloco: [tipo, atributo, rótulo, opções] */
	var CONTROLS = {
		'babymoura/contact-details': [
			[ 'select', 'variant', 'Variante', [ { value: 'full', label: 'Completa' }, { value: 'compact', label: 'Compacta' }, { value: 'footer', label: 'Rodapé' } ] ],
			[ 'toggle', 'showHours', 'Mostrar horário' ],
			[ 'toggle', 'showMap', 'Mostrar ligação para o mapa' ]
		],
		'babymoura/hours': [],
		'babymoura/activities': [
			[ 'select', 'stage', 'Etapa', STAGES ],
			[ 'select', 'kind', 'Tipo', [ { value: 'all', label: 'Todos' }, { value: 'curricular', label: 'Curricular' }, { value: 'extracurricular', label: 'Extracurricular' } ] ],
			[ 'select', 'layout', 'Apresentação', [ { value: 'grid', label: 'Grelha com descrição' }, { value: 'compact', label: 'Lista compacta' }, { value: 'matrix', label: 'Tabela por etapa' } ] ],
			[ 'range', 'limit', 'Máximo de itens (0 = todos)', [ 0, 12 ] ]
		],
		'babymoura/services': [ [ 'select', 'stage', 'Etapa', STAGES ] ],
		'babymoura/faq': [
			[ 'select', 'group', 'Grupo', [ { value: '', label: 'Todos' }, { value: 'geral', label: 'Geral' }, { value: 'visitas', label: 'Visitas' }, { value: 'inscricao', label: 'Pré-inscrição' }, { value: 'dia-a-dia', label: 'Dia a dia' } ] ],
			[ 'toggle', 'homeOnly', 'Só perguntas marcadas para a página inicial' ],
			[ 'range', 'limit', 'Máximo de perguntas (0 = todas)', [ 0, 12 ] ],
			[ 'select', 'headingLevel', 'Nível do título das perguntas', [ { value: 2, label: 'H2' }, { value: 3, label: 'H3' }, { value: 4, label: 'H4' } ] ]
		],
		'babymoura/gallery': [
			[ 'select', 'space', 'Espaço', [ { value: '', label: 'Todos os espaços' } ].concat( data.spaces ) ],
			[ 'toggle', 'filters', 'Mostrar filtros' ],
			[ 'range', 'limit', 'Máximo de imagens (0 = todas)', [ 0, 60 ] ]
		],
		'babymoura/form': [
			[ 'select', 'formType', 'Formulário', [ { value: 'visita', label: 'Pedido de visita' }, { value: 'pre-inscricao', label: 'Pré-inscrição' } ] ],
			[ 'select', 'stage', 'Etapa pré-selecionada', [ { value: '', label: 'Nenhuma' }, { value: 'creche', label: 'Creche' }, { value: 'jardim', label: 'Jardim de Infância' } ] ]
		],
		'babymoura/icon': [
			[ 'select', 'name', 'Ícone', data.icons.map( function ( n ) {
				return { value: n, label: n };
			} ) ],
			[ 'select', 'tone', 'Cor', [ { value: 'green', label: 'Verde' }, { value: 'yellow', label: 'Amarelo' }, { value: 'peach', label: 'Pêssego' }, { value: 'lavender', label: 'Lavanda' }, { value: 'sage', label: 'Sálvia' } ] ],
			[ 'select', 'size', 'Tamanho', [ { value: 'sm', label: 'Pequeno' }, { value: 'md', label: 'Médio' }, { value: 'lg', label: 'Grande' } ] ]
		]
	};

	function control( def, props ) {
		var type = def[ 0 ];
		var key = def[ 1 ];
		var common = {
			key: key,
			label: def[ 2 ],
			__nextHasNoMarginBottom: true,
			__next40pxDefaultSize: true
		};
		var set = function ( v ) {
			var o = {};
			o[ key ] = v;
			props.setAttributes( o );
		};
		if ( type === 'select' ) {
			return el( SelectControl, Object.assign( common, {
				value: props.attributes[ key ],
				options: def[ 3 ],
				onChange: function ( v ) {
					set( key === 'headingLevel' ? parseInt( v, 10 ) : v );
				}
			} ) );
		}
		if ( type === 'toggle' ) {
			return el( ToggleControl, Object.assign( common, { checked: !! props.attributes[ key ], onChange: set } ) );
		}
		return el( RangeControl, Object.assign( common, { value: props.attributes[ key ], min: def[ 3 ][ 0 ], max: def[ 3 ][ 1 ], onChange: set } ) );
	}

	Object.keys( CONTROLS ).forEach( function ( name ) {
		// As definições (título, atributos, suportes) vêm do block.json, registado no servidor.
		wp.blocks.registerBlockType( name, {
			edit: function ( props ) {
				var blockProps = useBlockProps();
				var controls = CONTROLS[ name ];
				return el( 'div', blockProps,
					controls.length ? el( InspectorControls, {},
						el( PanelBody, { title: __( 'Definições', 'babymoura-core' ), initialOpen: true },
							controls.map( function ( def ) {
								return control( def, props );
							} ),
							name === 'babymoura/form' ? el( 'p', { className: 'components-base-control__help' }, 'Em modo demonstração o formulário valida os dados mas não os envia nem guarda.' ) : null,
							name === 'babymoura/contact-details' || name === 'babymoura/hours' ? el( 'p', { className: 'components-base-control__help' }, 'Os dados vêm de BabyMoura → Dados da instituição.' ) : null
						)
					) : null,
					el( ServerSideRender, { block: name, attributes: props.attributes, skipBlockSupportAttributes: true } )
				);
			},
			save: function () {
				return null;
			}
		} );
	} );

	// Painel de SEO na barra lateral do editor de páginas.
	if ( wp.plugins && ( wp.editor.PluginDocumentSettingPanel || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel ) ) ) {
		var Panel = wp.editor.PluginDocumentSettingPanel || wp.editPost.PluginDocumentSettingPanel;
		var useSelect = wp.data.useSelect;
		var useDispatch = wp.data.useDispatch;
		wp.plugins.registerPlugin( 'babymoura-seo', {
			render: function () {
				var postType = useSelect( function ( s ) {
					return s( 'core/editor' ).getCurrentPostType();
				}, [] );
				var meta = useSelect( function ( s ) {
					return s( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
				}, [] );
				var edit = useDispatch( 'core/editor' ).editPost;
				if ( postType !== 'page' ) {
					return null;
				}
				var desc = meta.bm_seo_description || '';
				return el( Panel, { name: 'babymoura-seo', title: 'Pesquisa (SEO)' },
					el( TextControl, {
						label: 'Título para motores de pesquisa',
						help: 'Deixe vazio para usar o título da página.',
						value: meta.bm_seo_title || '',
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						onChange: function ( v ) {
							edit( { meta: { bm_seo_title: v } } );
						}
					} ),
					el( TextareaControl, {
						label: 'Descrição',
						help: desc.length + ' caracteres (recomendado: 120–160).',
						value: desc,
						__nextHasNoMarginBottom: true,
						onChange: function ( v ) {
							edit( { meta: { bm_seo_description: v } } );
						}
					} )
				);
			}
		} );
	}
}( window.wp ) );
