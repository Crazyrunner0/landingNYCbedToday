const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'hero-offer': path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/blocks/hero-offer',
			'index.js'
		),
		'social-proof-strip': path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/blocks/social-proof-strip',
			'index.js'
		),
		'value-stack': path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/blocks/value-stack',
			'index.js'
		),
		'how-it-works': path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/blocks/how-it-works',
			'index.js'
		),
		'final-cta': path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/blocks/final-cta',
			'index.js'
		),
		'local-neighborhoods': path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/blocks/local-neighborhoods',
			'index.js'
		),
	},
	output: {
		path: path.resolve(
			process.cwd(),
			'web/app/themes/blocksy-child/build'
		),
	},
};
