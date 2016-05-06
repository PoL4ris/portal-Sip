var vistas = {

  global : function()
  {
    //TYPE OF SEARCH
    warpol('.id-search').keyup(buscador("Simple"));
    warpol('.id-checkbox-search').click(buscador("Complex"));
    //ARROW FOR BUILDINGS LIST
    warpol('.left-btn-trigg').click(buildingsList(0));
    warpol('.right-btn-trigg').click(buildingsList(1));
    warpol('.baba').click(function(){alert('s');});
    warpol('.inp-img-form').change(imgPreview());
    warpol('.btn-edit').click(editFormByType());
    warpol('.display-ticket').click(createFancyBox());
    warpol('#bg-black-window').click(bgWindowClick());
    warpol('#add-service-btn').click(addServiceBtn());
    warpol('.prod-id-select-option').click(displayServiceInfo());
    warpol('.modif-service-btn').click(modifServiceBtn());
    warpol('.action-confirm').click(confirmDialog());
    warpol('.disabled-input').click(refeshDisabledInput());
    warpol('.customer-seccion').click(changeSeccionView());
    //AjaxUpdates
    warpol('.save-btn').click(updateBtn());
    //Creat tickets.
    warpol('#create-customer-ticket').click(insertCustomerTicket());
    warpol.notify.defaults({ className: "success" });
//     warpol('.network-func-click').click(networkServices());


    if(!warpol('.validation-form'))
      return;

    validator.startValidations();

    warpol(".date" ).datepicker({ dateFormat: 'yy-mm-dd' });

  },
  bgWindowCheck : function()
  {
    if(warpol('#bg-black-window').attr('typeoff') == 'close')
    {
      warpol('#bg-black-window').fadeIn('slow');
      warpol('#bg-black-window').attr('typeoff', 'open');
      return 'open';
    }
    else
    {
      warpol('#bg-black-window').fadeOut('slow');
      warpol('#bg-black-window').attr('typeoff', 'close');
      return 'close';
    }
  }

};