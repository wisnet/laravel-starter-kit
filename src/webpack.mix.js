const mix = require('laravel-mix');
require('laravel-mix-eslint-config');

const production = mix.inProduction();
const devtool = production ? false : 'source-map';

let webpackModuleConfig = {};

if (!production) {
	webpackModuleConfig = {
		rules: [
			{
				enforce: 'pre',
				test: /\.(js|vue)$/,
				loader: 'eslint-loader',
				exclude: /node_modules/
			}
		]
	}
}

mix.webpackConfig({
	devtool: devtool,
	output: {
		path: __dirname + '/public',
		publicPath: '',
		filename: '[name].js',
		chunkFilename: 'js/chunks/[name].js'
	},
	module: webpackModuleConfig
});

mix.alias({
	'@': '/resources/js',
	'~@': '/resources/sass'
});

mix.options({
	processCssUrls: false
})
mix.js('resources/js/app.js', 'public/js').vue();
mix.sass('resources/sass/app.scss', 'public/css');

if (production) {
	mix.version();
} else {
	mix.sourceMaps();
}
