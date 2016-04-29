var vistas = {

  global : function()
  {
    //TYPE OF SEARCH
    $('.id-search').keyup(buscador("Simple"));
    $('.id-checkbox-search').click(buscador("Complex"));
    //ARROW FOR BUILDINGS LIST
    $('.left-btn-trigg').click(buildingsList(0));
    $('.right-btn-trigg').click(buildingsList(1));
    $('.inp-img-form').change(imgPreview());
    $('.btn-edit').click(editFormByType());
    $('.display-ticket').click(createFancyBox());
    $('#bg-black-window').click(bgWindowClick());
    $('#add-service-btn').click(addServiceBtn());
    $('.prod-id-select-option').click(displayServiceInfo());
    $('.modif-service-btn').click(modifServiceBtn());
    $('.action-confirm').click(confirmDialog());
    $('.disabled-input').click(refeshDisabledInput());
    $('.customer-seccion').click(changeSeccionView());
    //AjaxUpdates
    $('.save-btn').click(updateBtn());
    //Creat tickets.
    $('#create-customer-ticket').click(insertCustomerTicket());
    $.notify.defaults({ className: "success" });
//     $('.network-func-click').click(networkServices());


    if(!$('.validation-form'))
      return;

    validator.startValidations();

    $(".date" ).datepicker({ dateFormat: 'yy-mm-dd' });

  },
  bgWindowCheck : function()
  {
    if($('#bg-black-window').attr('typeoff') == 'close')
    {
      $('#bg-black-window').fadeIn('slow');
      $('#bg-black-window').attr('typeoff', 'open');
      return 'open';
    }
    else
    {
      $('#bg-black-window').fadeOut('slow');
      $('#bg-black-window').attr('typeoff', 'close');
      return 'close';
    }
  }

};