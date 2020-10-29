import jQuery from 'jquery';

import './vendor/foundation';

import activeItem from './components/activeItem';
import searchFilter from './components/searchFilter';
import { showJsOnlyContent } from './components/general';

jQuery(function ($) {

    activeItem('.sidebar__block');
    showJsOnlyContent();

    new searchFilter();

    Drupal.AjaxCommands.prototype.dismissBanner = function(ajax, response, status) {
        if(status == 'success') {
            const banner_id = response.banner_id;
            const banner_type = response.banner_type;

            $(`[data-banner="${banner_type}_${banner_id}"]`)
                .addClass('dismissing')
                .on('transitionend', function() {
                    $(this).remove();
                });
        }
    }
});
