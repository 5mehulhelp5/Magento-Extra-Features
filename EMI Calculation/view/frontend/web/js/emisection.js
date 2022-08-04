define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function ($,modal,Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.emisection = customerData.get('emisection');
            var data = this.data;
            var price= this.price;
            var gender= this.emisection().gender;
            var currency=this.currency;
            // console.log(data,'abcd');
            // console.log(price,'abc');
            // console.log(gender,'abc');
            var plan=[];
            for (var i=0; i<data.length; i++) {
                if (data[i].gender===gender) {
                    var Si = price * data[i].tenure * data[i].interest_rate;
                    var intr = Si/1200;
                    var totalprice = Number(price)+Number(intr);
                    var monthprice = totalprice/ data[i].tenure;
                    plan.push({emiplan:currency + monthprice.toFixed(2)+'x'+data[i].tenure+'m',interest: currency + intr+'('+ data[i].interest_rate+'%)', totalprice: currency +totalprice});
                }
            }
            console.log(plan,'xyz');

            /** Preparing Table */
            plan.forEach(res => {
                var row = document.createElement("tr");
                row.className = "row-value";
                Object.values(res).map(value => {
                    var col = document.createElement("td");
                    col.innerText = value;
                    row.appendChild(col)
                })
                document.getElementById("emi-table").appendChild(row)
            })

            /** POP-UP */
            $(document).ready(function () {
                $('.emi-table').hide(0);
                $('.emi-plan').on('click', function () {
                    var modaloption = {
                        type: 'popup',
                        modalClass: 'modal-popup',
                        responsive: true,
                        innerScroll: true,
                        clickableOverlay: true,
                        title: 'EMI Plans'
                    };
                    var callforoption = modal(modaloption, $('.emi-table'));
                    $('.emi-table').modal('openModal');
                })
            });
        }
    });
});

