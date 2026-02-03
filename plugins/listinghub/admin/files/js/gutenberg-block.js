"use strict";
var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType('listinghub/price-table', {
	title: 'Pricing Table',
	icon: 'dashicons dashicons-money-alt ',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_price_table]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_price_table]' );
    },
});


registerBlockType('listinghub/registration-form', {
	title: 'Registration Form',
	icon: 'dashicons dashicons-forms',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_form_wizard]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_form_wizard]' );
    },
});

registerBlockType('listinghub/my-account', {
	title: 'My Account',
	icon: 'dashicons dashicons-universal-access',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_profile_template]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_profile_template]' );
    },
});



registerBlockType('listinghub/author-profile-public', {
	title: 'Author profile',
	icon: 'dashicons dashicons-bank',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_profile_public]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_profile_public]' );
    },
});

registerBlockType('listinghub/login', {
	title: 'Login Form',
	icon: 'dashicons dashicons-unlock',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_login]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_login]' );
    },
});

registerBlockType('listinghub/author-directory', {
	title: 'Author Directory',
	icon: 'dashicons dashicons-admin-home',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_author_directory]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_author_directory]' );
    },
});


registerBlockType('listinghub/categories-image', {
	title: 'Categories Block',
	icon: 'dashicons dashicons-category',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_categories]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_categories]' );
    },
});

registerBlockType('listinghub/featured', {
	title: 'Featured Listing',
	icon: 'dashicons dashicons-sticky',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_featured]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_featured]' );
    },
});

registerBlockType('listinghub/map-full', {
	title: 'Map Full',
	icon: 'dashicons dashicons-location-alt',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_map]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_map]' );
    },
});
registerBlockType('listinghub/all-listing', {
	title: 'All Listing With map',
	icon: 'dashicons dashicons-grid-view',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_archive_grid]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_archive_grid]' );
    },
});
registerBlockType('listinghub/all-listing-without-map', {
	title: 'All Listing Without map',
	icon: 'dashicons dashicons-grid-view',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_archive_grid_no_map]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_archive_grid_no_map]' );
    },
});

registerBlockType('listinghub/search-form', {
	title: 'Search Form',
	icon: 'dashicons dashicons-search',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listinghub_search]' );
    },
    save: function() {
        return el( 'p', '', '[listinghub_search]' );
    },
});

registerBlockType('listinghub/filter', {
	title: 'Filter',
	icon: 'dashicons dashicons-admin-settings',
	category: 'listinghub-category',  		  
	edit: function() {
        return el( 'p', '', '[listing_filter]' );
    },
    save: function() {
        return el( 'p', '', '[listing_filter]' );
    },
});













