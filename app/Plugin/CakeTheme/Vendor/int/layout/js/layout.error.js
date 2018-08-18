/**
 * @file Main file for layout Error
 * @version 0.1
 * @copyright 2017 Andrey Klimov.
 */

/**
 * Registration handler of event `MainAppScripts:update`.
 *  Used for styling UI and trigger this event.
 *
 * @function ready
 * @returns  {null}
 */
    $(
        function () {
            $(document).off('MainAppScripts:update.ErrorLayout').on(
                'MainAppScripts:update.ErrorLayout',
                function () {
                    MainAppScripts.setUIReadyCounter();
                    MainAppScripts.updateBodyClass();
                    MainAppScripts.updateFontAwesome();
                    MainAppScripts.updateTooltips();
                    MainAppScripts.processUIReadyCounter();
                }
            );

            MainAppScripts.update();
        }
    );
