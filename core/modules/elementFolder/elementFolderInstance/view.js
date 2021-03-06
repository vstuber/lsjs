(function() {

// ### ENTER MODULE NAME HERE ######
var str_moduleName = '__moduleName__';
// #################################

var obj_classdef = 	{
	str_containerID: null,

	start: function() {
	    var str_options,
            obj_options,
            obj_unfoldOptions,
            el_elementFolderToggler,
            el_elementFolderContent;

		this.registerElements(this.__el_container, 'main', true);

        str_options = this.__el_container.getProperty('data-lsjs-elementFolderOptions');

        if (str_options) {
            obj_options = JSON.decode(str_options);
        }

        this.__models.options.set(obj_options);

        this.str_containerID = this.__el_container.getProperty('id');

        /*
         * In order to support nested element folders we use only the first toggler and content element that has been found
         */
        el_elementFolderToggler = this.__autoElements.main.elementFolderToggler[0];
        el_elementFolderContent = this.__autoElements.main.elementFolderContent[0];

        obj_unfoldOptions = Object.merge(
            {
                str_initialToggleStatus: 'open',
                bln_toggleOnInitialization: false,
                bln_skipAnimationWhenTogglingOnInitialization: false,
                var_togglerSelector: el_elementFolderToggler,
                var_contentBoxSelector: el_elementFolderContent,
                var_wrapperSelector: this.__el_container,
                str_cookieIdentifierName: this.str_containerID ? this.str_containerID : '',
                str_initialCookieStatus: 'open',
                obj_morphOptions: {
                    'duration': 600
                }
            },
            this.__models.options.data
        );

		lsjs.__moduleHelpers.unfold.start(obj_unfoldOptions);

	}
};

lsjs.addViewClass(str_moduleName, obj_classdef);

})();
