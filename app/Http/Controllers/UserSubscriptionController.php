<?php

namespace App\Http\Controllers;

use App\SubscriptionReference;

class UserSubscriptionController extends Controller
{
    public function show($contact)
    {
        $subscriptionReference = SubscriptionReference::where('subscriber_phone_no', $contact)->latest()->first();
        return response()->json([
            "error" => false,
            "data" => (object) [
                "subscription_status" => $this->getSubscriptionParam($subscriptionReference->status),
                "subscription_time" => $subscriptionReference->created_at,
            ],
        ], 200);
    }

    private function getSubscriptionParam($status = null)
    {
        switch ($status) {
            case '3':
                return 'SUBSCRIBE_PENDING';
            case '4':
                return 'SUBSCRIBED';
            case '5':
                return 'UNSUBSCRIBED';
            default:
                return 'NOT_AVAILABLE';
        }
    }
}
