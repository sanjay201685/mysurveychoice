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
      var textFieldKey = 'field_survey_link[0][value]';
      var replaceFieldKey = 'field_identifier_of_survey_link[0][value]';
      var radioFieldContainer = 'field_survey_link_radio-field';
      var radioFieldKey = 'radio_' + textFieldKey;
      $('input[name="' + textFieldKey + '"]').once().on('paste, keydown', function () {
        $('.' + radioFieldContainer).html('');
        var element = this;
        setTimeout(function () {
          var text = $.trim($(element).val());
          $(element).val(text);
          getUrlVars(text, radioFieldKey, radioFieldContainer);

        }, 100);
      });
      $(document).once().on('click', 'input[name="' + radioFieldKey + '"]', function () {
        $('input[name="' + replaceFieldKey + '"]').val($(this).val());
      });
    }
  };
})(jQuery, Drupal, drupalSettings);

function getUrlVars(urlstring, radioFieldKey, radioFieldContainer)
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
  return vars;
}

(function($) {
  $.fn.ajaxAutoSelectedFieldMethod = function(data) {
    $('input[name="field_mobile[0][value]"]').val(data[0]);
    $('input[name="field_name[0][value]"]').val(data[1]);
  };
})(jQuery);
