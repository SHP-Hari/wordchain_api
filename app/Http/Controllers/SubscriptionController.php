<?php

namespace App\Http\Controllers;

use App\SubscriptionReference;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $hash = null;

        if ($request->query('hash')) {
            $hash = $request->query('hash');
        } else {
            return response()->json([
                "error" => true,
                "msg" => "Hash param not exist",
            ], 201);
        }

        $subscriptionReference = SubscriptionReference::where('subscriber_user_hash', $hash)->latest()->first();

        if ($subscriptionReference && ($subscriptionReference->status == 4)) {
            return response()->json([
                "error" => false,
                "msg" => "You have already subscribed",
            ], 201);
        }

        if (!$subscriptionReference) {
            return response()->json([
                "error" => false,
                "msg" => "Hash not available",
            ], 201);
        }

        $subscriptionReference->status = 4;
        $subscriptionReference->update();

        return response()->json([
            "error" => false,
            "msg" => "Subscribed successfully",
        ], 201);
    }

    public function unsubscribe(Request $request)
    {
        $hash = null;

        if ($request->query('hash')) {
            $hash = $request->query('hash');
        } else {
            return response()->json([
                "error" => true,
                "msg" => "Hash param not exist",
            ], 201);
        }

        $subscriptionReference = SubscriptionReference::where('subscriber_user_hash', $hash)->latest()->first();

        if (!$subscriptionReference) {
            return response()->json([
                "error" => false,
                "msg" => "Hash not available",
            ], 201);
        }

        /*       $subscriptionReference->status = 5;
        $subscriptionReference->update(); */

        SubscriptionReference::where('subscriber_user_hash', $hash)->update(['status' => 5]);

        return response()->json([
            "error" => false,
            "msg" => "Unsubscribed successfully",
        ], 201);
    }
}
