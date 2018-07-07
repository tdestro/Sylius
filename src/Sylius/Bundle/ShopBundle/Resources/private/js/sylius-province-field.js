/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


function loadUploadField(select){

    var taxexemptionContainer = select.parent().parent().children('div.taxexemption-container');

    var taxExemptionSelectFieldName = select.attr('name').replace('provinceCode', 'taxExemption');

    if(!$(select).val()){
        taxexemptionContainer.html('');
        return;
    }

    taxexemptionContainer.attr('data-loading', true);
    $.get(taxexemptionContainer.attr('data-url'), {provinceCode: $(select).val()}, function (response) {
        if (!response.content) {
            taxexemptionContainer.html('');

            taxexemptionContainer.removeAttr('data-loading');
        } else {
            taxexemptionContainer.html(response.content.replace(
                'name="sylius_address_taxexemption"',
                'name="' + taxExemptionSelectFieldName.split('[')[0]  + '[taxExemption]"'
            ));
            taxexemptionContainer.removeAttr('data-loading');
        }

    });
}

import $ from 'jquery';

const getProvinceInputValue = function getProvinceInputValue(valueSelector) {
  return valueSelector == undefined ? '' : `value="${valueSelector}"`;
};

$.fn.extend({
  provinceField() {
    const countrySelect = $('select[name$="[countryCode]"]');

    countrySelect.on('change', (event) => {
      const select = $(event.currentTarget);
      const provinceContainer = select.parents('.field').next('div.province-container');

      const provinceSelectFieldName = select.attr('name').replace('country', 'province');
      const provinceInputFieldName = select.attr('name').replace('countryCode', 'provinceName');

      if (select.val() === '' || select.val() == undefined) {
        provinceContainer.fadeOut('slow', () => {
          provinceContainer.html('');
        });

        return;
      }

      provinceContainer.attr('data-loading', true);

      $.get(provinceContainer.attr('data-url'), { countryCode: select.val() }, (response) => {
        if (!response.content) {
          provinceContainer.fadeOut('slow', () => {
            provinceContainer.html('');

            provinceContainer.removeAttr('data-loading');
          });
        } else if (response.content.indexOf('select') !== -1) {
          provinceContainer.fadeOut('slow', () => {
            const provinceSelectValue = getProvinceInputValue((
              $(provinceContainer).find('select > option[selected$="selected"]').val()
            ));

            provinceContainer.html((
              response.content
                .replace('name="sylius_address_province"', `name="${provinceSelectFieldName}"${provinceSelectValue}`)
                .replace('option value="" selected="selected"', 'option value=""')
                .replace(`option ${provinceSelectValue}`, `option ${provinceSelectValue}" selected="selected"`)
            ));

            provinceContainer.removeAttr('data-loading');

            provinceContainer.fadeIn();
          });
        } else {
          provinceContainer.fadeOut('slow', () => {
            const provinceInputValue = getProvinceInputValue($(provinceContainer).find('input').val());

            provinceContainer.html((
              response.content
                .replace('name="sylius_address_province"', `name="${provinceInputFieldName}"${provinceInputValue}`)
            ));

            provinceContainer.removeAttr('data-loading');

            provinceContainer.fadeIn();
              if(provinceSelectFieldName == "sylius_checkout_address[shippingAddress][provinceCode]") {
                  var provinceSelect = $('select[name$="[shippingAddress][provinceCode]"]');
                  loadUploadField($(provinceSelect));
                  provinceSelect.on('change', function (event) {
                      loadUploadField($(event.currentTarget))
                  });
              }
          });
        }
      });
    });

    if (countrySelect.val() !== '') {
      countrySelect.trigger('change');
    }

    if ($.trim($('div.province-container').text()) === '') {
      $('select.country-select').trigger('change');
    }

    const billingAddressCheckbox = $('input[type="checkbox"][name$="[differentBillingAddress]"]');
    const billingAddressContainer = $('#sylius-billing-address-container');
    const toggleBillingAddress = function toggleBillingAddress() {
      billingAddressContainer.toggle(billingAddressCheckbox.prop('checked'));
    };
    toggleBillingAddress();
    billingAddressCheckbox.on('change', toggleBillingAddress);
  },
});
