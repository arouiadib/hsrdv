
import TranslatableInput from '@components/translatable-input';
import ChoiceTree from '@components/form/choice-tree';
import FormSubmitButton from '@components/form-submit-button';

const $ = window.$;

$(() => {
  let translatableInput = new TranslatableInput({localeInputSelector: '.js-locale-input'});

  let selectedLocale = translatableInput.getSelectedLocale();
  let titleInputSelector = '.js-locale-input.js-locale-' + selectedLocale;
  let $titleInput = $(`${titleInputSelector}`);

  $titleInput.each(function () {
    $(this).attr('onkeyup', 'modifyLinkRewrite();');
  });

  // TinyMCE
  window.prestashop.component.initComponents([
    'TranslatableField',
    'TinyMCEEditor',
    'TranslatableInput',
    'EventEmitter',
    'TextWithLengthCounter',
  ]);

  $('.datetimepicker').datetimepicker({
    locale: 'fr',
    useCurrent: false,
    sideBySide: true
  });

  // Choice tree for category form
  new ChoiceTree('#form_category_id_parent');
  new ChoiceTree('#form_post_id_category');
  new ChoiceTree('#form_category_shop_association').enableAutoCheckChildren();
  new FormSubmitButton();

  function modifyLinkRewrite() {

  }

});

