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

			str_sliderContainerSelector: '.lsSlider',

			/*
			 * If the last slide doesn't have enough content to fill the slider's visible area, only slide to the point where the whole visible area is still filled
			 */
			bln_lastSlideFilled: true
		};
	},
	
	set: function(obj_options) {
		Object.merge(this.data, obj_options);
		this.__module.onModelLoaded();
	}
};