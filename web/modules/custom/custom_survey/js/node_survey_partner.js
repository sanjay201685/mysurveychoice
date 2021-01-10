/**
 * @file
 * Contains the definition of the behaviour jsTestBlackWeight.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches the JS test behavior to to weight div.
   */
  Drupal.behaviors.jsSurveyPartnerFieldType = {
    attach: function (context, settings) {
      var field_list = {
        'field_survey_link':'field_identifier_of_survey_link',
        'field_completes_redirect':'field_identifier_of_completes',
        'field_quota_full_redirect':'field_identifier_of_quota_full',
        'field_terminates_redirect':'field_identifier_of_terminates'
      };
      $.each(field_list, function( key_name, identifier ) {
        var textFieldKey = key_name+'[0][value]';
        var replaceFieldKey = identifier+'[0][value]';
        var radioFieldContainer = key_name+'_radio-field';
        var radioFieldKey = 'radio_' + key_name;
        $('input[name="' + textFieldKey + '"]').once().on('paste, keydown', function (e) {
          switch(e.which) {
            case 37: // left
            case 38: // up
            case 39: // right
            case 40: // down
            break;
            default:
            $('.' + radioFieldContainer).html('');
            var element = this;
            setTimeout(function () {
              var text = $.trim($(element).val());
              $(element).val(text);
              getUrlVars(text, radioFieldKey, radioFieldContainer, replaceFieldKey);
            }, 100);
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);

function getUrlVars(urlstring, radioFieldKey, radioFieldContainer, replaceFieldKey)
{

  var vars = [], hash;
  var hashes = urlstring.slice(urlstring.indexOf('?') + 1).split('&');
  for (var i = 0; i < hashes.length; i++)
  {

    hash = hashes[i].split('=');
    if (typeof hash[1] !== 'undefined') {
      var radioBoxKey = radioFieldKey + '_' + i;
      jQuery('<div><input id="' + radioBoxKey + '" type="radio" name="' + radioFieldKey + '" value="' + hash[0] + '" /><label class="option" for="' + radioBoxKey + '">' + hash[0] + '</label></div>').appendTo('.' + radioFieldContainer);
    }
  }
  jQuery('input[name="'+radioFieldKey+'"]').once().on('click', function () {
    jQuery('input[name="'+replaceFieldKey+'"]').val(jQuery(this).val());
  });
  return vars;
}

(function($) {
  $.fn.ajaxAutoSelectedFieldMethod = function(data) {
    $.each(data, function( index, value ) {
      $('input[name="'+index+'[0][value]"]').val(value);
    });
  };
})(jQuery);
