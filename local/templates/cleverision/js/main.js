window.$ && $(function() {
    const main = window.main = {};
    main.serviceList = [];

    const servicesPath = '/local/services';
    const addServisePath = `${servicesPath}/add_service.php`;
    const removeServisePath = `${servicesPath}/remove_service.php`;

    const counter = $('.counterArea .counter');
    const cartCounter = $('.cmpSrvSelected .selectedCounter');
    const modalCounter = $('#modalService .serviceCounter .counter');

    $('#mobileMenuBtn').click(function(){
        const menu = $('#siteMenu');
        if($(this).hasClass('opened')){
            $(this).removeClass('opened');
            $(menu).hide();
        }
        else{
            $(this).addClass('opened');
            $(menu).show();
        }
    });

    $('button[data-target="#modalService"]').click(function(){
        $(modalCounter).text(main.getServiceNumber());
    });

    main.setServiceCounter = function(value) {
        value = parseInt(value) || 0;
        $(counter).text(value);
        $(cartCounter).text(value);
        $(modalCounter).text(value);
    };

    main.getServiceNumber = function(){
        return parseInt($(counter).text()) || 0;
    };

    main.addToCart = function(serviceId, callback) {
        callback = callback || function(){};

        $.post(
            addServisePath,
            {
                sessid: BX && BX.bitrix_sessid(),
                serviceId: serviceId
            },
            function (data) {
                if (!data || (typeof data !== 'object')) {
                    data = {};
                }

                if (data.success) {
                    callback({success: true});
                    main.setServiceCounter(data.servicesCount);
                    main.serviceList = data.services;
                } else {
                    callback({error: true})
                }
            },
            'json'
        );
    };

    main.removeFromCart = function(serviceId, callback) {
        callback = callback || function(){};

        $.post(
            removeServisePath,
            {
                sessid: BX && BX.bitrix_sessid(),
                serviceId: serviceId
            },
            function (data) {
                if (!data || (typeof data !== 'object')) {
                    data = {};
                }

                if (data.success) {
                    callback({success: true});
                    main.setServiceCounter(data.servicesCount);
                    main.serviceList = main.serviceList.filter(function(v){return v.ID != serviceId})
                } else {
                    callback({error: true})
                }
            },
            'json'
        );
    };

    main.notificationSuccess = function(title, message, delay) {
        title = title || '';
        message = message || '';

        if ($.notify) {
            $.notify({
                title: title,
                message: message
            }, {
                type: 'customSuccess',
                delay: delay || 7000,
                placement: {
                    align: 'center'
                },
                template:
                    '<div data-notify="container" class="col-xs-11 col-sm-10 alert alert-{0}" role="alert">' +
                        '<div class="row flex-nowrap">' +
                            '<div class="col">' +
                                '<div class="row contentWrapper align-items-center">' +
                                    '<div class="col-12 col-sm-auto">' +
                                        '<div data-notify="icon" class="icon"></div>' +
                                    '</div>' +
                                    '<div class="col">' +
                                        '<div data-notify="title" class="title">{1}</div>' +
                                        '<div data-notify="message" class="message">{2}</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-auto">' +
                                '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                            '</div>' +
                        '</div>'+
                    '</div>'
            });
        }
    };

    main.notificationWarning = function(title, message, delay) {
        title = title || '';
        message = message || '';

        if ($.notify) {
            $.notify({
                title: title,
                message: message
            }, {
                type: 'customVarning',
                delay: delay || 7000,
                placement: {
                    align: 'center'
                },
                template:
                    '<div data-notify="container" class="col-xs-11 col-sm-10 alert alert-{0}" role="alert">' +
                        '<div class="row flex-nowrap">' +
                            '<div class="col">' +
                                '<div class="row contentWrapper align-items-center">' +
                                    '<div class="col-12 col-sm-auto">' +
                                        '<div data-notify="icon" class="icon"></div>' +
                                    '</div>' +
                                    '<div class="col">' +
                                        '<div data-notify="title" class="title">{1}</div>' +
                                        '<div data-notify="message" class="message">{2}</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-auto">' +
                                '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                            '</div>' +
                        '</div>'+
                    '</div>'
            });
        }
    };
});
