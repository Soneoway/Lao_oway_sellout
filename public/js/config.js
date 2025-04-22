/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.toolbarGroups = [
    { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
    { name: 'links' },{ name: 'paragraph',   groups: [ 'list', 'indent' ] },
    '/',
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    
    { name: 'styles' },
    { name: 'colors' }
];
};
