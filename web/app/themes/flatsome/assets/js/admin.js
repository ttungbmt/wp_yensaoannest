/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/scripts/theme/admin.js":
/*!************************************!*\
  !*** ./src/scripts/theme/admin.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/** global wp */
(function ($) {
  'use strict';

  var $options = $('.ux-menu-item-options');
  var blockIcon = '<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M7.73009 2.41274L8.95709 3.63719L3.40181 9.18095L2.17482 7.95652L7.73009 2.41274ZM7.73009 0.242432L0 7.95652L3.40181 11.3513L11.1319 3.63719L7.73009 0.242432Z" fill="#007CBA"/> <path d="M7.8196 11.3114L8.95987 12.4493L7.8196 13.5873L6.67928 12.4493L7.8196 11.3114ZM7.8196 9.14111L4.50439 12.4492L7.8196 15.7575L11.1348 12.4492L7.8196 9.14087V9.14111Z" fill="#007CBA"/> <path d="M12.2322 6.90786L13.3725 8.0458L12.2322 9.18369L11.0921 8.04584L12.2323 6.90795L12.2322 6.90786ZM12.2323 4.73763L8.91699 8.04584L12.2322 11.3542L15.5474 8.04584L12.2322 4.73755L12.2323 4.73763Z" fill="#007CBA" fill-opacity="0.6"/> </svg>';
  var insertId = null; // Expose or hide fields depending on other field values.

  $options.find('select').bind('change', function () {
    var id = $(this).attr('id');
    var value = $(this).val();
    var $menuItem = $(this).parents('li.menu-item');
    var $width = $menuItem.find('.ux-menu-item-options__width');
    var $height = $menuItem.find('.ux-menu-item-options__height');
    var $mediaPicker = $menuItem.find('.ux-menu-item-options__media');
    var $iconHtml = $menuItem.find('.ux-menu-item-options__icon-html'); // Menu design

    if (value === 'default') {
      hideFields([$width, $height]);
    }

    if (value === 'container-width') {
      hideFields([$width, $height]);
    }

    if (value === 'full-width') {
      hideFields([$width, $height]);
    }

    if (value === 'custom-size') {
      showFields([$width, $height]);
    } // Icon type


    if (value === 'media') {
      showFields([$mediaPicker]);
      hideFields([$iconHtml]);
    }

    if (value === 'html') {
      showFields([$iconHtml]);
      hideFields([$mediaPicker]);
    } // Indicate if menu has a block assigned


    if (id.startsWith('edit-menu-item-block-')) {
      if (value) {
        if (!$menuItem.find('.menu-item-ux-block-indicator').length) {
          $menuItem.find('.menu-item-title').before('<span class="menu-item-ux-block-indicator">' + blockIcon + '</span>');
        }
      } else {
        $('.menu-item-ux-block-indicator', $menuItem).remove();
      }
    }
  }).change(); // Media select/upload functionality

  $('.ux-menu-item-options__media-control', $options).on('click', '.upload-button', function (e) {
    e.preventDefault();
    var $button = $(this);
    var itemId = $button.data('item-id');
    var frame = wp.media({
      multiple: false
    }).on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $('.placeholder', '#menu-item-' + itemId).attr('src', attachment.url).show();
      $button.parent().find('input:hidden:first').val(attachment.id);
      $button.parent().find('.remove-button').show();
    }).open();
  }).on('click', '.remove-button', function (e) {
    e.preventDefault();
    var $button = $(this);
    var itemId = $button.data('item-id');
    $('.placeholder', '#menu-item-' + itemId).attr('src', '').hide();
    $button.parent().find('input:hidden:first').val('');
    $button.hide();
  });
  $options.on('click', '.thickbox', function (e) {
    insertId = jQuery(this).data('insert-id');
  });

  window.onmessage = function (e) {
    if (/^http(s)?:\/\/flatsome-icons.netlify.app/.test(e.origin)) {
      if (e.data.status === 'success') {
        var content = e.data.data.content;
        $('#' + insertId, $options).val(content);
      }
    }
  };
  /**
   * Expose fields
   *
   * @param fields Field elements
   */


  function showFields(fields) {
    fields.forEach(function (field) {
      field.slideDown();
    });
  }
  /**
   * Hide fields
   *
   * @param fields Field elements
   */


  function hideFields(fields) {
    fields.forEach(function (field) {
      field.slideUp();
    });
  }
})(jQuery);

/***/ }),

/***/ 0:
/*!******************************************!*\
  !*** multi ./src/scripts/theme/admin.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/jim/local/flatsome-d/app/public/wp-content/themes/flatsome-next/src/scripts/theme/admin.js */"./src/scripts/theme/admin.js");


/***/ })

/******/ });
//# sourceMappingURL=admin.js.map