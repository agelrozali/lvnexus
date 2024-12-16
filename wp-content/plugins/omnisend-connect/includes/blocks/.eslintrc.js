module.exports = {
	env: {
		node: true,
		browser: true,
		es2021: true,
	},
	extends: [
		'eslint:recommended',
		'plugin:react/recommended',
	],
	parserOptions: {
		ecmaVersion: 12,
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true,
		},
	},
	plugins: [
		'react',
		'react-hooks',
	],
	rules: {
		'react/prop-types': 'off',
		'no-unused-vars': 'warn',
		'no-console': 'off',
		'eqeqeq': 'error',
		'indent': ['error', 'tab', { SwitchCase: 1 }], // Ensure tabs are used for indentation
		'quotes': ['error', 'single'],
		'semi': ['error', 'always'],
		'react/react-in-jsx-scope': 'off',
	},
	settings: {
		react: {
			version: 'detect',
		},
	},
};
