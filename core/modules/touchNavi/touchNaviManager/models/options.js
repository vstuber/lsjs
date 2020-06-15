var obj_classdef_model = {
	name: 'options',
	
	data: {},
	
	start: function() {
		/*
		 * Initializing the options in the data object with default values which
		 * can later be overwritten when the "set" method is called with other options
		 */
		this.data = {
			el_domReference: null,
			str_selector: '[data-lsjs-component~="touchNavi"]', // the selector for the dom element to enrich,
			str_classToSetWhenModuleApplied: 'useTouchNavi',
			obj_instanceOptions: null //used to pass options through to the instance
		};
	},
	
	set: function(obj_options) {
		Object.merge(this.data, obj_options);
		this.__module.onModelLoaded();
	}
};