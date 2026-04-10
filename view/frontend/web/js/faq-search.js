/**
 * Magendoo Faq Search JS
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $input = $(element).find('input[name="q"]');
        var $button = $(element).find('button[type="submit"]');
        var minLength = (config && config.minLength) ? config.minLength : 2;

        // Disable submit button if query is too short
        $button.prop('disabled', $input.val().length < minLength);

        $input.on('keyup', function () {
            var val = $(this).val();
            $button.prop('disabled', val.length < minLength);
        });
    };
});
