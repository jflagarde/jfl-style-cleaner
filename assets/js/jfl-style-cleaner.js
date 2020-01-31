// When the Elementor editor is initialized
jQuery(window).on('elementor:init', function () {

    // Add actions to the contextual menus
    elementor.hooks.addFilter('elements/widget/contextMenuGroups', function (groups, widget) {
        return jflAddCleanerActions(groups, widget);
    });
    elementor.hooks.addFilter('elements/column/contextMenuGroups', function (groups, column) {
        return jflAddCleanerActions(groups, column);
    });
    elementor.hooks.addFilter('elements/section/contextMenuGroups', function (groups, section) {
        return jflAddCleanerActions(groups, section);
    });

});

/*
 * This function adds different actions to the contextual menu
 */
function jflAddCleanerActions(groups, element) {

    // Add actions to each contextual group
    jQuery.each(groups, function (index, value) {

        // Add actions to the general group
        if (value.name == 'general') {
            groups[index].actions.push(
                {
                    name: 'jfl-inspect-selected-element',
                    title: 'Inspect Selected Element',
                    callback: function () {
                        jflInspectSelectedElement(element);
                        return;
                    }
                }
            );
        }

        // Add actions to the clipboard group
        if (value.name == 'clipboard') {
            groups[index].actions.push(
                {
                    name: 'jfl-clear-typo-colours',
                    title: 'Clear Typo and Colours',
                    callback: function () {
                        jflClearTypoColours();
                        return;
                    }
                },
                {
                    name: 'jfl-reset-all-styles',
                    title: 'Reset All Styles',
                    callback: function () {
                        jflResetAllStyles();
                        return;
                    }
                }
            );
        }

        // Add actions to the save group
        if (value.name == 'save') {
            groups[index].actions.push(
                {
                    name: 'jfl-set-navigator-title',
                    title: 'Set Navigator Title',
                    callback: function () {
                        jflSetNavigatorTitle(element);
                        return;
                    }
                }
            );
        }

    });

    // Return the groups for the filter
    return groups;
}

/*
 * This function will show the element in the console to inspect it
 */
function jflInspectSelectedElement(element) {

    // Log the element in the console
    console.log('element', element);

    // return nothing for the callback
    return;
}

/*
 * This function will return all the containers on a page
 */
function jflGetAllContainers() {

    // Get all the containers of the document
    var allContainers = Object.values(elementor.getPreviewView()._getNestedViews()).map((view) => {

        // Remove the empty views
        if (view.el.className != 'elementor-empty-view' && !view.isDestroyed) {

            var container = view.getContainer();
            container.view.allowRender = false;
            return container;

        } else {

            // The view contained nothing
            return;

        }

    });

    // Clean-up the array of containers
    var filteredContainers = allContainers.filter((container) => {
        return container;
    });
    filteredContainers.reverse();

    // return the containners
    return filteredContainers;
}

/*
 * This function will remove all the hard coded typography and colours of all the elements on a page
 */
function jflClearTypoColours() {

    /*
    // List all the controls that we want to be resetted
    const controlsToReset = [
        'background_color',
        'border_color',
        'heading_color',
        'color_text',
        'typography_typography',
        'title_color',
        'title_typography_typography',
        'description_color',
        'description_typography_typography',
        'primary_color',
        'secondary_color',
        'content_content_color',
        'content_typography_typography',
        'name_text_color',
        'name_typography_typography'
    ];
    */

    // Get all the containers of the document
    var filteredContainers = jflGetAllContainers();

    // For each container
    filteredContainers.forEach((container) => {

        // Get the controls of the container
        const controls = container.settings.controls;
        const defaultValues = {};

        // Stop the rendering to avoid container issues
        container.view.allowRender = false;

        // For each control of the container
        Object.entries(controls).forEach(([controlName, control]) => {

            // From the Elementor code, not too sure what it does. Probably check to be sure the element has controls.
            if (!container.view.isStyleTransferControl(control)) {
                return;
            }

            /*
            // Check if the control is in our list we have defined manually above
            if ( controlsToReset.includes(controlName) ) {
                defaultValues[ controlName ] = control.default;
            }
            */

            // Reset all the settings ending with either color or typography, the method could be combined with the manual list if needed
            if (controlName.endsWith("color") || controlName.endsWith("typography")) {
                defaultValues[controlName] = control.default;
            }

        });

        // Reset the selected settings to their default values
        $e.run('document/elements/settings', {
            container,
            settings: defaultValues,
            options: {
                external: true,
            },
        });

        // Allow the container to be rendered
        container.view.allowRender = true;

    });

    // Render all the page
    elementor.getPreviewView().render();

    // return nothing for the callback
    return;
}

/*
 * This function will remove all the hard coded styling of all the elements on a page
 */
function jflResetAllStyles() {

    // Get all the containers of the document
    var filteredContainers = jflGetAllContainers();

    // Remove the styling of all containers
    $e.run('document/elements/reset-style', { containers: filteredContainers });

    // Render the document
    filteredContainers.forEach((container) => {
        container.view.allowRender = true;
    });
    elementor.getPreviewView().render();

    // return nothing for the callback
    return;
}

/*
 * This function will set the Navigator element title from the widget content
 */
function jflSetNavigatorTitle(element) {

    // Get the settings of the element
    const settingsModel = element.model.get('settings')

    // Find the best title from the attributes
    if (settingsModel.attributes.title) {
        newTitle = settingsModel.attributes.title;
    } else if (settingsModel.attributes.title_text) {
        newTitle = settingsModel.attributes.title_text;
    } else {
        newTitle = settingsModel.get('_title');
    }

    // Set the navigator title
    settingsModel.set('_title', newTitle);

    // return nothing for the callback
    return;
}