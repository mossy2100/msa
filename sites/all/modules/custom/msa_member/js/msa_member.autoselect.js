var $ = jQuery;

$(function() {

  // Select the correct new/renewing radio button based on whether the user is or was an MSA member:
  if (Drupal.settings.msa.is_or_was_member) {
    $('.form-item-attributes-field-membership-type input:radio[value=renewing]').attr('checked', 'checked');
  }
  else {
    $('.form-item-attributes-field-membership-type input:radio[value=new]').attr('checked', 'checked');
  }
  
  // Change it to regular:
  $('.form-item-attributes-field-membership-level select').val('regular').change();

});
