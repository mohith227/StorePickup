require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function(
        $,
        modal
    ) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: $.mage.__('Continue'),
                class: 'store-pickup',
                click: function () {
                    this.closeModal();
                }
            }]
        };
        var popup = modal(options, $('#storePickup'));
        $("#popupButton").on('click',function(){
            $("#storePickup").modal("openModal");
        });
    });
