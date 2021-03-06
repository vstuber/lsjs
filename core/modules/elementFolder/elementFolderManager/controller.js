/*
 * -- ACTIVATION: --
 *
 * To activate this module, the following code has to be put in the app.js:
 *
	 lsjs.__moduleHelpers.elementFolderManager.start({
		 el_domReference: el_domReference
	 });
 *
 * The el_domReference parameter is only required if this module initialization code is called in a cajax_domUpdate event.
 *
 *
 *
 * -- FUNCTIONALITY AND USAGE: --
 *
 * Add the following attribute to a DOM element to apply this module:
 *
 * data-lsjs-component="elementFolder"
 *
 *
 * Add the following attribute to an another element inside the above mentioned
 * outer DOM element in order to use it as the toggler:
 *
 * data-lsjs-element="elementFolderToggler"
 *
 *
 * Add the following attribute to yet another element inside the above mentioned
 * outer DOM element in order to use it as the toggler's content:
 *
 * data-lsjs-element="elementFolderContent"
 *
 *
 *
 * Using the attribute "data-lsjs-elementFolderOptions" the default options for an
 * elementFolder instance can be overridden. Since "elementFolder" leveragesf the "unfold" module
 * all options supported by the "unfold" module can be used here.
 *
 * The following example shows how to create an elementFolder instance that starts closed
 * although by default it would start open.
 *
 * <section
 * 		data-lsjs-component="elementFolder"
 * 		data-lsjs-elementFolderOptions="
 * 			{
 * 				str_initialCookieStatus: 'closed'
 * 			}
 * 		"
 * 		id="someId"
 * >
 * 		<div data-lsjs-element="elementFolderToggler">TOGGLE</div>
 * 		<div data-lsjs-element="elementFolderContent">CONTENT</div>
 * </section>
 *
 *
 * TODO: Write a detailed functionality and usage description. Until then, the options models of the manager and
 * the instance submodule can be consulted for further information.
 *
 */

(function() {
	
// ### ENTER MODULE NAME HERE ######
var str_moduleName = '__moduleName__';
// #################################

var obj_classdef = {
	start: function() {
		var els_toEnhance;
		/*
		 * Look for elements to enrich with the lsjs-module and then
		 * instantiate instances for each element found.
		 */
		if (this.__models.options.data.el_domReference !== undefined && typeOf(this.__models.options.data.el_domReference) === 'element') {
			els_toEnhance = this.__models.options.data.el_domReference.getElements(this.__models.options.data.str_selector);
		} else {
			els_toEnhance = $$(this.__models.options.data.str_selectors);
		}

		Array.each(els_toEnhance, function(el_container) {
			/* ->
			 * Make sure not to handle an element more than once
			 */
			if (!el_container.retrieve('alreadyHandledBy_' + str_moduleName)) {
				el_container.store('alreadyHandledBy_' + str_moduleName, true);
			} else {
				return;
			}
			/*
			 * <-
			 */

			el_container.addClass(this.__models.options.data.str_classToSetWhenModuleApplied);

			lsjs.createModule({
				__name: 'elementFolderInstance',
				__parentModule: this.__module,
				__useLoadingIndicator: false,
				__el_container: el_container
			});
		}.bind(this));
	}
};

lsjs.addControllerClass(str_moduleName, obj_classdef);

lsjs.__moduleHelpers[str_moduleName] = {
	self: null,
	
	start: function(obj_options) {
		this.self = lsjs.createModule({
			__name: str_moduleName
		});
		this.self.__models.options.set(obj_options);
	}
};

})();