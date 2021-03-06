<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../css/my.style.css">
    <link rel="stylesheet" href="../../alertifyjs/css/alertify.css">


    <title>Paiment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <!-- Styles -->
    <style>
        * {
            font-family: "Helvetica Neue", Helvetica;
            font-size: 15px;
            font-variant: normal;
            padding: 0;
            margin: 0;
        }

        html {
            height: 100%;
        }

        body {
            background: #E6EBF1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100%;
        }

        form {
            width: 480px;
            margin: 20px 0;
        }

        .group {
            background: white;
            box-shadow: 0 7px 14px 0 rgba(49, 49, 93, 0.10),
            0 3px 6px 0 rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            margin-bottom: 20px;
        }

        label {
            position: relative;
            color: #8898AA;
            font-weight: 300;
            height: 40px;
            line-height: 40px;
            margin-left: 20px;
            display: flex;
            flex-direction: row;
        }

        .group label:not(:last-child) {
            border-bottom: 1px solid #F0F5FA;
        }

        label > span {
            width: 80px;
            text-align: right;
            margin-right: 30px;
        }

        .field {
            background: transparent;
            font-weight: 300;
            border: 0;
            color: #31325F;
            outline: none;
            flex: 1;
            padding-right: 10px;
            padding-left: 10px;
            cursor: text;
        }

        .field::-webkit-input-placeholder {
            color: #CFD7E0;
        }

        .field::-moz-placeholder {
            color: #CFD7E0;
        }

        button {
            float: left;
            display: block;
            background: #666EE8;
            color: white;
            box-shadow: 0 7px 14px 0 rgba(49, 49, 93, 0.10),
            0 3px 6px 0 rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            border: 0;
            margin-top: 20px;
            font-size: 15px;
            font-weight: 400;
            width: 100%;
            height: 40px;
            line-height: 38px;
            outline: none;
        }

        button:focus {
            background: #555ABF;
        }

        button:active {
            background: #43458B;
        }

        .outcome {
            float: left;
            width: 100%;
            padding-top: 8px;
            min-height: 24px;
            text-align: center;
        }

        .success, .error {
            display: none;
            font-size: 13px;
        }

        .success.visible, .error.visible {
            display: inline;
        }

        .error {
            color: #E4584C;
        }

        .success {
            color: #666EE8;
        }

        .success .token {
            font-weight: 500;
            font-size: 13px;
        }

    </style>
</head>
<body>
<div id="details">
    <form id="payment-form">
        @csrf
        <div class="group">
            <label>
                <span>Nombre</span>
                <input id="cardholder-name" name="cardholder-name" class="field" placeholder="Jane Doe" required />
            </label>
            <label>
                <span>Teléfono</span>
                <input class="field" placeholder="(123) 456-7890" type="number" maxlength="10" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required />
            </label>
        </div>
        <div class="group">
            <label>
                <span>Tarjeta</span>
                <div id="card-element" placeholder="(123) 456-7890" class="field"></div>
            </label>
        </div>
        <button id="card-button">Siguiente</button>
    </form>
</div>

<div id="plans" hidden>
    <form id="installment-plan-form">
        @csrf
        <div class="container">
            <label class="checkbox-tools" id="cardlabel"><input id="immediate-plan" type="radio" name="installment_plan" value="-1" />Inmediato</label>
            <input id="payment-intent-id" type="hidden" />
        </div>
    </form>
    <button id="confirm-button">Confirmar Pago</button>
</div>
<div id="result" hidden>
    <strong><p id="status-message"></p></strong>
    <div class="alert alert-success" id="status-message"></div>
    <button id="backtostart" onclick="reload()">Regresar</button>
</div
</body>
<script>
    var stripe = Stripe('pk_test_rO2x0qqQwME9eqoqrKBQxdhG00vAtDboUa');
    var elements = stripe.elements();
    var cardElement = elements.create('card', {
        style: {
            base: {
                iconColor: '#666EE8',
                color: '#31325F',
                lineHeight: '40px',
                fontWeight: 300,
                fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
                fontSize: '15px',

                '::placeholder': {
                    color: '#CFD7E0',
                },
            },
        }
    });
    cardElement.mount('#card-element');
    var cardholderName = document.getElementById('cardholder-name');
    var form = document.getElementById('payment-form');
    var tokencfsr = document.getElementsByName('_token')[0];
    var align = tokencfsr.getAttribute('content');
    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
            billing_details: {name: cardholderName.value}
        }).then(function (result) {
            if (result.error) {
                alertify.set('notifier', 'position', 'botton-right');
                alertify.error('<strong style="color: white;">Ingrese los datos correctos de su tarjeta, por favor intente de nuevo.</strong>');
            } else {
                // Otherwise send paymentMethod.id to your server (see Step 2)
                fetch('http://127.0.0.1:8001/Plans', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-TOKEN": align
                    },
                    body: JSON.stringify({payment_method_id: result.paymentMethod.id, _token: tokencfsr.value})
                }).then(function (result) {
                    // Handle server response (see Step 3)
                    result.json().then(function (json) {
                        handleInstallmentPlans(json);
                    })
                });
            }
        });
    });
    const selectPlanForm = document.getElementById('installment-plan-form');
    let availablePlans = [];
    const handleInstallmentPlans = async (response) => {

        if (response.error) {
            alertify.set('notifier', 'position', 'botton-right');
            alertify.error('<strong style="color: white;">Error al comunicarse con el servidor, intente de nuevo.</strong>');
        } else {
            // Store the payment intent ID.
            document.getElementById('payment-intent-id').value = response.intent_id;
            availablePlans = response.available_plans;

            // Show available installment options
            availablePlans.forEach((plan, idx) => {
                const newlabel = document.getElementById('cardlabel').cloneNode();
                const newInput = document.getElementById('immediate-plan').cloneNode();
                
                newInput.setAttribute('value', idx);
                newInput.setAttribute('id', '');
                const label = document.createElement('radio');
                label.appendChild(newInput);
                label.appendChild(
                    document.createTextNode(`${plan.count} ${plan.interval}s`),
                );
                newlabel.appendChild(label);
                selectPlanForm.appendChild(newlabel);
                //selectPlanForm.appendChild(label);
            });

            document.getElementById('details').hidden = true;
            document.getElementById('plans').hidden = false;
        }
    };



    const confirmButton = document.getElementById('confirm-button');

    confirmButton.addEventListener('click', async (ev) => {
        const selectedPlanIdx = selectPlanForm.installment_plan.value;
        const selectedPlan = availablePlans[selectedPlanIdx];
        const intentId = document.getElementById('payment-intent-id').value;
        const response = await fetch('http://127.0.0.1:8001/Confirm/Plans', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', "X-CSRF-TOKEN": align},
            body: JSON.stringify({
                payment_intent_id: intentId,
                selected_plan: selectedPlan,
                _token: tokencfsr.value
            }),
        });

        const responseJson = await response.json();

        // Show success / error response.
        document.getElementById('plans').hidden = true;
        document.getElementById('result').hidden = false;

        var message;
        var mensaje;
        if (responseJson.status === "succeeded" && selectedPlan !== undefined) {
      
            mensaje=`Completo! Usted hizo un cargo con este plan:${
                selectedPlan.count
            } ${selectedPlan.interval}`;

            message = alertify.success(`<strong style="color:white;">¡Completo! Usted hizo un cargo con este plan:${
                selectedPlan.count
            } ${selectedPlan.interval}</strong>`);

        } else if (responseJson.status === "succeeded") {
            message = alertify.success('<strong style="color:white;">Completo! Pagaste inmediato!</strong>')
        } else {
            if(responseJson.status === "Your card has expired."){
                message =  alertify.error('<strong style="color: white;">Su tarjeta ha expirado.</strong>');

            }else if(responseJson.status === "Your card's security code is incorrect."){
                message =  alertify.error('<strong style="color: white;">El código de seguridad de su tarjeta es incorrecto.</strong>');

            }else if(responseJson.status === "An error occurred while processing your card. Try again in a little bit."){
                message =  alertify.error('<strong style="color: white;">Ha ocurrido un error al procesar su tarjeta. Intente de nuevo en unos momentos.</strong>');

            }else if(responseJson.status === "Your card has insufficient funds."){
                message =  alertify.error('<strong style="color: white;">Su tarjeta presenta fondos insuficientes.</strong>');

            }else if(responseJson.status === "Your card was declined."){
                message =  alertify.error('<strong style="color: white;">Su tarjeta fue rechazada.</strong>');

            }else{
               
                message =  alertify.error('<strong style="color: white;">Su tarjeta fue rechazada.</strong>');

            }
        }   

        document.getElementById("status-message").innerText = mensaje;

    });

    function reload(){
        location.reload();
    }
    

</script>
<script src="../../alertifyjs/alertify.js"></script>
</html>
