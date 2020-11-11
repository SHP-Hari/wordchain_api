<?php

namespace App\Http\Controllers;

use App\SubscriptionReference;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionReferenceController extends Controller
{
    public function otpRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscriber_phone_no' => 'required|regex:/(94)\d{9}$/',
            'application_hash' => 'required',
            'provider' => 'required',
            'device' => 'required',
            'os' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1001",
                "errors" => $validator->errors(),
            ], 201);
        }

        $subscriber_id = "tel:{$request->input('subscriber_phone_no')}";

        $apiCallParams = $this->getApiCallParams($request->input('provider'));

        if (!$apiCallParams) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1003",
                "errors" => (object) [
                    "available_providers" => [
                        "Dialog", "Hutch", "Airtel", "Mobitel",
                    ],
                ],
            ], 201);
        }

        $data = [
            "applicationId" => $apiCallParams['application_id'],
            "password" => $apiCallParams['password'],
            "subscriberId" => $subscriber_id,
            "applicationHash" => $request->input('application_hash'),
            "applicationMetaData" => [
                "client" => "MOBILEAPP",
                "device" => $request->input('device'),
                "os" => $request->input('os'),
                "appCode" => 'url',
            ],
        ];

        $url = $apiCallParams["url"] . "/request";

        if (config('app.env') == "production") {
            $apiResponse = $this->callExternalApi($data, $url);
        } else {
            $apiResponse = [
                "error" => false,
                "payload" => (object) [
                    "referenceNo" => uniqid(mt_rand(), true),
                    "statusDetail" => "Request was successfully processed.",
                    "version" => "1.0",
                    "statusCode" => "S1000",
                ],
            ];
        }

        if ($apiResponse["error"]) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1002",
                "errors" => null,
            ], 201);
        }

        if ($apiResponse["payload"]->statusCode != "S1000") {
            return response()->json([
                "error" => true,
                "err_code" => $apiResponse["payload"]->statusCode,
                "errors" => null,
            ], 201);
        }

        $subscriptionReference = new SubscriptionReference();
        $subscriptionReference->subscriber_phone_no = $request->input('subscriber_phone_no');
        $subscriptionReference->device = $request->input('device');
        $subscriptionReference->os = $request->input('os');
        $subscriptionReference->provider = $request->input('provider');
        $subscriptionReference->status = 2;
        $subscriptionReference->reference_no = $apiResponse["payload"]->referenceNo;
        $subscriptionReference->save();

        return response()->json([
            "error" => false,
            "data" => (object) [
                "reference_no" => $apiResponse["payload"]->referenceNo,
            ],
        ], 201);
    }

    public function otpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required',
            'reference_no' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1001",
                'errors' => $validator->errors(),
            ], 201);
        }

        $apiCallParams = $this->getApiCallParams($request->input('provider'));

        if (!$apiCallParams) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1003",
                "errors" => (object) [
                    "available_providers" => [
                        "Dialog", "Hutch", "Airtel", "Mobitel",
                    ],
                ],
            ], 201);
        }

        $data = [
            "applicationId" => $apiCallParams['application_id'],
            "password" => $apiCallParams['password'],
            "referenceNo" => $request->input('reference_no'),
            "otp" => $request->input('otp'),
        ];

        $url = $apiCallParams["url"] . "/verify";

        if (config('app.env') == "production") {
            $apiResponse = $this->callExternalApi($data, $url);
        } else {
            if ($request->input('otp') == "123123") {
                $apiResponse = [
                    "error" => false,
                    "payload" => (object) [
                        "subscriptionStatus" => "INITIAL CHARGING PENDING",
                        "subscriberId" => "tel:A#3B42Z0NgTPy8wqSz39RKu0uU+XWnV4U2tU7SLnHHoZ59npVMOITR70XNxbPsd\/J1g3U",
                        "statusDetail" => "Success",
                        "version" => "1.0",
                        "statusCode" => "S1000",
                    ],
                ];
            } else {
                $apiResponse = [
                    "error" => false,
                    "payload" => (object) [
                        "subscriptionStatus" => "INITIAL CHARGING PENDING",
                        "subscriberId" => "tel:A#3B42Z0NgTPy8wqSz39RKu0uU+XWnV4U2tU7SLnHHoZ59npVMOITR70XNxbPsd\/J1g3U",
                        "statusDetail" => "Success",
                        "version" => "1.0",
                        "statusCode" => "E1850",
                    ],
                ];
            }
        }

        if ($apiResponse["error"]) {
            return response()->json([
                "error" => true,
                "err_code" => "EC1002",
                "errors" => null,
            ], 201);
        }

        if ($apiResponse["payload"]->statusCode != "S1000") {
            return response()->json([
                "error" => true,
                "err_code" => $apiResponse["payload"]->statusCode,
                "errors" => null,
            ], 201);
        }

        $subscriptionReference = SubscriptionReference::where('reference_no', $request->input('reference_no'))->first();
        $subscriptionReference->status = 3;
        $subscriptionReference->otp = $request->input('otp');
        $subscriptionReference->subscriber_user_hash = $apiResponse["payload"]->subscriberId;
        $subscriptionReference->update();

        return response()->json([
            "error" => false,
            "data" => (object) [
                "subscriber_user_hash" => $apiResponse["payload"]->subscriberId,
            ],
        ], 201);
    }

    private function getApiCallParams($provider)
    {
        switch (strtoupper($provider)) {
            case 'DIALOG':
            case 'ETISALAT':
            case 'HUTCH':
            case 'AIRTEL':
                return [
                    "url" => config('provider.IDEAMART_OTP_REQUEST_URL'),
                    "application_id" => config('provider.IDEAMART_APP_ID'),
                    "password" => config('provider.IDEAMART_APP_PASSWORD'),
                ];
            case 'MOBITEL':
                return [
                    "url" => config('provider.MOBITEL_OTP_REQUEST_URL'),
                    "application_id" => config('provider.MOBITEL_APP_ID'),
                    "password" => config('provider.MOBITEL_APP_PASSWORD'),
                ];
            default:
                return [];
        }
    }

    private function callExternalApi($data, $url)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $client = new GuzzleClient([
            'headers' => $headers,
        ]);

        $response = $client->request('POST', $url, [
            'json' => $data,
        ]);

        $resultBody = $response->getBody();

        return [
            "error" => false, "payload" => json_decode($resultBody),
        ];
    }
}
