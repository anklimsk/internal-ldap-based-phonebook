/**
 * File for action Index of controller Employees
 *
 * @file    File for action Index of controller Employees
 * @version 0.1
 */

/**
 * @version 0.1
 * @namespace AppActionScriptsEmployeesIndex
 */
var AppActionScriptsEmployeesIndex = AppActionScriptsEmployeesIndex || {};

(function ($) {
    'use strict';

    /**
     * This function used as callback for keyup event for
     *  add text to the search query input field if there
     *  is no focus.
     *
     * @param {object} e Event object
     *
     * @callback setFocusSearchInput
     *
     * @returns {null}
     */
    function _setFocusSearchInput(e)
    {
        var searchInput = $('#SearchQuery');
        if (searchInput.is(':focus') || ($('input:focus').length !== 0)) {
            return;
        }

        var keyCode = e.keyCode;
        if (e.ctrlKey || e.altKey
            || (keyCode < 0x20)
            || ((keyCode >= 0x21) && (keyCode <= 0x28))
            || ((keyCode >= 0x2C) && (keyCode <= 0x2E))
            || (keyCode === 0x5B) || (keyCode === 0x5d)
            || ((keyCode >= 0x70) && (keyCode <= 0x7B))
            || (keyCode === 0x90) || (keyCode === 0x91)
        ) {
            return;
        }

        searchInput.focus();
        var currText = searchInput.val();
        currText    += e.key;
        searchInput.val('');
        searchInput.val(currText);
    }

    /**
     * This function is used to bind keyup event for
     *  add text to the search query input field if there
     *  is no focus.
     *
     * @function updateSearchInput
     * @memberof AppActionScriptsEmployeesIndex
     *
     * @returns {null}
     */
    AppActionScriptsEmployeesIndex.updateSearchInput = function () {
        $(document).off('keyup.AppActionScriptsEmployeesIndex').on('keyup.AppActionScriptsEmployeesIndex', _setFocusSearchInput);
    };

    return AppActionScriptsEmployeesIndex;
})(jQuery);

/**
 * Registration handler of event `MainAppScripts:update`
 *
 * @function ready
 *
 * @returns {null}
 */
$(
    function () {
        $(document).off('MainAppScripts:update.AppActionScriptsEmployeesIndex').on(
            'MainAppScripts:update.AppActionScriptsEmployeesIndex',
            function () {
                AppActionScriptsEmployeesIndex.updateSearchInput();
            }
        );
    }
);
