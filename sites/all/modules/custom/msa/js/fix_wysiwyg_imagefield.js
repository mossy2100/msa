/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This is a dirty hack to fix a current bug with the wysiwyg_imagefield module.
 * If the insert image dialog doesn't get opened, then all the images are deleted.
 * This function opens and closes the dialog to prevent this.
 * The user doesn't see the dialog get rendered (at least on this computer/browser).
 */
function fix_wysiwyg_imagefield() {
  jQuery('#wysiwyg_imagefield-wrapper').dialog('open').dialog('close');
}

jQuery(function() {
  // Wait one second to give the editor time to initialise:
  window.setTimeout(fix_wysiwyg_imagefield, 1000);
});
