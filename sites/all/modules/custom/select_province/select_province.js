
/**
 * Request provinces for a given country and update province selector.
 */
function select_province_get(country_id, selector_id) {
  jQuery.get('/ajax/get-provinces/' + jQuery('#' + country_id).val(), function(response) {
    var provinces = JSON.parse(response);

    // Empty the province selector. Note, this seems to trigger the change()
    // event on the selector, which in turn copies the value (blank) to the
    // autocomplete. Which is fine.
    var selector = jQuery('#' + selector_id);
    selector.empty();

    // Populate the selector with provinces:
    var hasValues = false;
    for (code in provinces) {
      selector.append(jQuery('<option></option>').attr('value', code).text(provinces[code]));
      if (!hasValues && code) {
        hasValues = true;
      }
    }

    // Hide the province selector if there are no provinces to select:
    var formItem = selector.parents('.form-item-location-province');
    if (!hasValues) {
      formItem.hide();
    }
    else {
      formItem.show();
    }
  });
}

/**
 * Update the form, replacing all province autocomplete fields with selectors.
 */
function select_province_init() {
  jQuery('.location_auto_province').each(function() {

    // Hide the province autocomplete field:
    jQuery(this).hide();

    // Add the selector:
    var province_id = this.id;
    var selector_id = this.id + '-selector';
    var selector = jQuery('<select></select>').attr('id', selector_id);
    jQuery(this).after(selector);

    // Whenever the selector changes, we want to set the value of the autocomplete field:
    selector.change(function() {
      jQuery('#' + province_id).val(jQuery(this).val());
    });

    // Get the country selector id:
    var country = jQuery(this).parents('.location').find('.location_auto_country');
    var country_id = country.attr('id');

    // Populate the province selector:
    select_province_get(country_id, selector_id);

    // Whenever the country selector changes, update the province selector:
    country.change(function() {
      select_province_get(country_id, selector_id);
    })
  });
}

jQuery(select_province_init);
