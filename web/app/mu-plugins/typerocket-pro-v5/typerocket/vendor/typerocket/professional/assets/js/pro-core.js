import {TypeRocketConditions} from "./advanced/conditions";
import {editor} from "./advanced/editor";

;jQuery(function($) {

    [editor].forEach(function(caller) {
        caller($(document));
        TypeRocket.repeaterCallbacks.push(caller);
    });

    new TypeRocketConditions('name', $);

    $(document).on('input blur change', '.tr-component-nameable-input', function() {
        let $that = $(this);
        let $parent = $that.closest('[data-tr-component]').first();

        if( $parent ) {
            let hash = $parent.attr('data-tr-component');
            let value = $that.val() ? $that.val() : $that.attr('placeholder');
            $('[data-tr-component-tile='+hash+'] .tr-builder-component-title').first().text(value);
        }
    });

    $(document).on('blur keyup change', '.tr-has-conditionals', function(e) {
        let $that = $(this);

        let fn = () => {
            let bound = $that.data('tr-conditions-bond') ? $that.data('tr-conditions-bond') : [];
            for(let b in bound) {
                bound[b].trigger('condition');
            }
        };

        if(e.type === 'keyup') {
            window.trUtil.delay(fn, 250);
        } else {
            fn()
        }
    });

    $(document).on('input', '.tr-range-input', function(e) {
        e.preventDefault();
        $(this).prev().find('span').html($(this).val());
    });

    $(document).on('blur keyup change input', '.tr-input-textexpand', function() {
        let $that = $(this);

        $that.next().val($that.html()).trigger('change');
    });

    $(document).on('click', '.typerocket-elements-fields-textexpand .tr-label', function() {
        let hidden_input = $(this).attr('for');

        $('#' + hidden_input).prev().focus();
    });

});