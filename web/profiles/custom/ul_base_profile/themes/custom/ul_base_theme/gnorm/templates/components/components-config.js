// var dest = './build';
var src = './app';

module.exports = {
	title: 'Components Library',
	src: src,
	dest: './templates/1_core',
	includesDataPath: './templates',
	includesRenderPath: '@ul_base_theme/',
	sass: {
		partialSrc: './gnorm/templates/components/styles/_docs.components.scss',
		partialDest: src + '/styles/0_utility/_docs.components.scss'
	},
	filter: {
		indexPath: './app/scripts/modules/index.js',
		componentsLoadPath: './gnorm/templates/components/filter/load.js',
		componentsModulePath: './gnorm/templates/components/filter/main.js',
		componentsFilter: 'componentsFilter',
		modulesPath: './app/scripts/modules/',
		filterBy: {
			includeFilter: true,
			filterParameters: {
				abcOrder: true,
				type: true,
				clear: true
			}
		}
	},
	add:[
		// {
		// 	title: 'Card Default',
		// 	folder: '2_pieces',
		// 	base: 'card/card--default.twig',
		// 	withAttribute: 'card: cardComponent'
		// }
	],
	excludes: [
		'.gitkeep',
		// 'aside.twig',
	]
};
