
$(document).ready(function(){
    $('input[name="decision_prise_en_charge"]').click(function(){
        var inputValue = $(this).attr("value");
        console.log(inputValue);

        if (inputValue === 'Oui')  $('.custom-file').show();
        if (inputValue === 'Non')  $('.custom-file').hide();

    });
});
