<?php

namespace App\Http\Controllers;

use \Stripe\Stripe;

use Illuminate\Http\Request;


class PagoController extends Controller
{
    public function store(Request $request)
    {
        try {
            \Stripe\Stripe::setApiKey('sk_test_ZynBn0iZ7PVVihhQbKqnkdTo005DKYWfj8');
            $intent = \Stripe\PaymentIntent::create([
                'payment_method' => $request->payment_method_id,
                'amount' => 3099,
                'currency' => 'mxn',
                'payment_method_options' => [
                    'card' => [
                        'installments' => [
                            'enabled' => true
                        ]
                    ]
                ],
            ]);
            echo json_encode([
                'intent_id' => $intent->id,
                'available_plans' => $intent->payment_method_options->card->installments->available_plans
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            # "e" contains a message explaining why the request failed
            echo 'El mensaje de error de la tarjeta es:' . $e->getError()->message . '
';
            echo json_encode([
                'error_message' => $e->getError()->message
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            echo 'El mensaje de parámetros no válidos es:' . $e->getError()->message . '
';
            echo json_encode([
                'error_message' => $e->getError()->message
            ]);
        }
    }

    public function ConfirmPlan(Request $request)
    {
        \Stripe\Stripe::setApiKey('sk_test_ZynBn0iZ7PVVihhQbKqnkdTo005DKYWfj8');
        if (isset($request->selected_plan)) {
            $confirm_data = ['payment_method_options' =>
                [
                    'card' => [
                        'installments' => [
                            'plan' => $request->selected_plan
                        ]
                    ]
                ]
            ];
            $intent = \Stripe\PaymentIntent::retrieve(
                $request->payment_intent_id
            );
    
            $intent->confirm($params = $confirm_data);
    
            echo json_encode([
                'status' => $intent->status,
            ]);
        }else{
              if (isset($request->payment_intent_id)) {
                $intent = \Stripe\PaymentIntent::retrieve(
                  $request->payment_intent_id
                );
                
                try{
                    $intent->confirm();
                    if($intent->status != 'succeeded'){
                        echo json_encode([
                            'status' => $intent,
                        ]);
                    }
                }catch(\Stripe\Exception\CardException $e ){
                    return json_encode(['status' => $e->getError()->message]);
                }
                // generateResponse($intent);
              }
        } 
    }
}
