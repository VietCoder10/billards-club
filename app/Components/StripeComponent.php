<?php

namespace App\Components;

use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeComponent
{
    public static function createCustomer($email, $name)
    {
        Log::channel('log_stripe')->info([
            'api' => 'createCustomer',
            'data' => [
                'email' => $email,
                'name' => $name,
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->create([
                'email' => $email,
                'name' => $name,
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'createCustomer',
                'message' => $th->getMessage(),
            ]);
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function getCustomer($customerId)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->retrieve($customerId);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function getChargesRetrieve($chargeId)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->charges->retrieve(
                $chargeId,
                ['expand' => ['customer', 'invoice.subscription']]
            );
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function createCharge($invoice)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            $ret = $stripe->charges->create([
                'amount' => $invoice->total_price,
                'customer' => $invoice->user->customer_id,
                'currency' => 'jpy',
                'description' => 'Payment daily month ',
            ]);
            Log::channel('log_stripe')->info('Stripe return:'.$ret);
            if ($ret->status != 'succeeded') {
                return false;
            }

            return true;
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function getDefaultSourceCard($customerId)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));

        return $stripe->customers->retrieve(
            $customerId,
            []
        )->default_source;
    }

    public static function getCustomerCard($customerId, $cardId)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->retrieveSource($customerId, $cardId);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function addCustomerCard($customerId, $tokenCard)
    {
        Log::channel('log_stripe')->info([
            'api' => 'addCustomerCard',
            'data' => ['customerId' => $customerId, 'tokenCard' => $tokenCard],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->createSource(
                $customerId,
                ['source' => $tokenCard]
            );
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'addCustomerCard',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function deleteCustomer($customerId)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->delete(
                $customerId
            );
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function addCustomerCardDefault($customerId, $tokenCard)
    {
        Log::channel('log_stripe')->info([
            'api' => 'addCustomerCardDefault',
            'data' => ['customerId' => $customerId, 'tokenCard' => $tokenCard],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->update(
                $customerId,
                ['default_source' => $tokenCard]
            );
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function createTokenCard($request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->tokens->create([
                'card' => [
                    'number' => $request->credit_card,
                    'exp_month' => $request->expire_month,
                    'exp_year' => $request->expire_year,
                    'cvc' => $request->security_code,
                ],
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error($th->getMessage());

            return false;
        }
    }

    public static function createProduct($name)
    {
        Log::channel('log_stripe')->info([
            'api' => 'create product',
            'data' => ['name' => $name],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->products->create([
                'name' => $name,
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'create product',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function createPrice($productId, $amount)
    {
        Log::channel('log_stripe')->info([
            'api' => 'createPrice',
            'data' => ['productId' => $productId, 'amount' => $amount],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->prices->create([
                'unit_amount' => $amount,
                'currency' => 'jpy',
                'product' => $productId,
                'recurring' => ['interval' => 'day'],
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'createPrice',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function updatePrice($priceId, $amount)
    {
        Log::channel('log_stripe')->info([
            'api' => 'createPrice',
            'data' => ['priceId' => $priceId, 'amount' => $amount],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->prices->update($priceId, [
                'unit_amount' => $amount,
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'updatePrice',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function createSubscription($customerId, $priceId, $metaData, $trialEnd, $cancelAt = null)
    {
        Log::channel('log_stripe')->info([
            'api' => 'createSubscription',
            'data' => [
                'customerId' => $customerId,
                'priceId' => $priceId,
                'trialEnd' => $trialEnd,
                'cancelAt' => $cancelAt,
                // 'defaultPayment' => $defaultPayment,
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            $dataCreate = [
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'trial_end' => $trialEnd,
                'metadata' => $metaData,
                'collection_method' => 'charge_automatically',
            ];
            if ($cancelAt) {
                $dataCreate['cancel_at'] = $cancelAt;
            }

            return $stripe->subscriptions->create($dataCreate);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'createSubscription',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function updateSubscription($subcriptionId, $priceId, $trialEnd, $cancelAt, $defaultPayment)
    {
        Log::channel('log_stripe')->info([
            'api' => 'updateSubscription',
            'data' => [
                'subcriptionId' => $subcriptionId,
                'trialEnd' => $trialEnd,
                'cancelAt' => $cancelAt,
                'items' => [
                    ['price' => $priceId],
                ],
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->subscriptions->update($subcriptionId, [
                'trial_end' => $trialEnd,
                'cancel_at' => $cancelAt,
                'default_payment_method' => $defaultPayment,
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'updateSubscription',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function updateSubscriptionTrailEnd($subcriptionId, $trialEnd, $cancelAt)
    {
        Log::channel('log_stripe')->info([
            'api' => 'updateSubscriptionTrailEnd',
            'data' => [
                'subcriptionId' => $subcriptionId,
                'trialEnd' => $trialEnd,
                'cancelAt' => $cancelAt,
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->subscriptions->update($subcriptionId, [
                'trial_end' => $trialEnd,
                'cancel_at' => $cancelAt,
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'updateSubscriptionTrailEnd',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function cancelSubscription($subcriptionId)
    {
        Log::channel('log_stripe')->info([
            'api' => 'cancelSubscription',
            'data' => [
                'subcriptionId' => $subcriptionId,
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->subscriptions->cancel($subcriptionId, [
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'cancelSubscription',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function createSubscriptionSchedule($customerId, $priceId, $startDate)
    {
        Log::channel('log_stripe')->info([
            'api' => 'createSubscription',
            'data' => [
                'customerId' => $customerId,
                'priceId' => $priceId,
                'startDate' => $startDate,
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->subscriptionSchedules->create([
                'customer' => $customerId,
                'start_date' => $startDate,
                // 'end_date' => $endDate,
                'end_behavior' => 'release',
                'phases' => [
                    [
                        'items' => [
                            [
                                'price' => $priceId,
                                'quantity' => 1,
                            ],
                        ],
                        'iterations' => 1,
                    ],
                ],
            ]);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'createSubscription',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function createPaymentIntents($data)
    {
        Log::channel('log_stripe')->info([
            'api' => 'createpPaymentIntents',
            'data' => $data,
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->paymentIntents->create($data);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'createpPaymentIntents',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function capturePaymentIntents($id)
    {
        Log::channel('log_stripe')->info([
            'api' => 'capturePaymentIntents',
            'data' => $id,
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->paymentIntents->capture($id);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'capturePaymentIntents',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function cancelPaymentIntents($id)
    {
        Log::channel('log_stripe')->info([
            'api' => 'cancelPaymentIntents',
            'data' => $id,
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->paymentIntents->cancel($id);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'cancelPaymentIntents',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }

    public static function retrieveSource($customerId, $cardId)
    {
        Log::channel('log_stripe')->info([
            'api' => 'retrieveSource',
            'data' => [
                'customerId' => $customerId,
                'cardId' => $cardId,
            ],
        ]);
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        try {
            return $stripe->customers->retrieveSource($customerId, $cardId, []);
        } catch (\Throwable $th) {
            Log::channel('log_stripe')->error([
                'api' => 'retrieveSource',
                'message' => $th->getMessage(),
            ]);

            return false;
        }
    }
}
