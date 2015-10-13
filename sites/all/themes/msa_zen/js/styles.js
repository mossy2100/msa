/**
 * This file contains any JavaScript related to general theme CSS manipulation.
 */

var navBottom;
var footerHeight;


function setMinHeight() {
  // Get the window height:
  var windowHeight = jQuery(window).height();

  // Set the min-height of #main:
  var minHeight = windowHeight - navBottom - footerHeight;
  jQuery('#main').css('min-height', minHeight + 'px');
}

function initStyles() {
  // Get the bottom of the nav bar:
  navBottom = jQuery('#navigation').offset().top + jQuery('#navigation').height();

  // Get the height of the footer:
  footerHeight = jQuery('#footer').height();

  // Set the min-height of #main:
  setMinHeight();

  // Also do this if the window is resized:
  jQuery(window).resize(setMinHeight);
  
  // Remove trailing colon from field labels:
  jQuery('.field-label, .views-label').each(function() {
    var label = jQuery(this).text();
    label.replace('&nbsp;', '');
    label = jQuery.trim(label);
    if (label.charAt(label.length - 1) == ':') {
      label = label.substr(0, label.length - 1);
      jQuery(this).text(label);
    }
  });

  // Add hint to username fields:
  var hintUserName = 'e.g. John Smith';
  jQuery('#edit-name').blur(
    function () {
      if (jQuery(this).val() == '' || jQuery(this).val() == hintUserName) {
        jQuery(this).val(hintUserName).addClass('textfield-hint');
      }
      else {
        jQuery(this).removeClass('textfield-hint');
      }
    }
  ).focus(
    function () {
      if (jQuery(this).hasClass('textfield-hint')) {
        jQuery(this).removeClass('textfield-hint');
        jQuery(this).val('');
      }
    }
  ).blur();

}

jQuery(initStyles);
