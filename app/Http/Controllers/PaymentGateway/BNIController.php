<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BNIController extends Controller
{
	public function test(Request $request)
	{
		$data = $request->all();

		return response()->json([
			'status' =>true,
			'data' => $data
		], 200);
	}

	public function token()
	{
		try {
			$client = new \GuzzleHttp\Client();
			$url = config('services.bni.endpoint_url') . '/api/oauth/token';
			$res = $client->request('POST', $url, [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded'
				],
				'auth' => [config('services.bni.client_id'), config('services.bni.client_secret')],
				'form_params' => [
					'grant_type' => 'client_credentials',
				],
			]);

			$response = json_decode($res->getBody()->getContents(), true);

			if (isset($response['access_token'])) {
				return [
					'status' => true,
					'access_token' => $response['access_token']
				];
			} else {
				return [
					'status' => false,
					'message' => 'Access token not found in the response'
				];
			}
		} catch (\Exception $e) {
			return [
				'status' => false,
				'message' => json_decode($e->getResponse()->getBody()->getContents()),
			];
		}
	}

	public function getbalance(Request $request)
	{
		$api_key = config('services.bni.api_key');
		$base64_client_name = config('services.bni.base64_client_name');

		$accountNo = $request->accountNo ?? '';

		$payload_signature = [
			"clientId" => "IDBNI" . $base64_client_name,
			"accountNo" => $accountNo,
		];
		$signature = self::generateSignature($payload_signature);
		$json = [
			...$payload_signature,
			"signature" => $signature,
		];
		$get_token = self::token();

		// if ($get_token['status']) {
		// 	$access_token = $get_token['access_token'];
		if (!empty($request->token)) {
			$access_token = $request->token;

			try {
				$client = new \GuzzleHttp\Client();
				$url = config('services.bni.endpoint_url') . '/H2H/v2/getbalance?access_token=' . $access_token;

				$res = $client->request('POST', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'X-API-Key' => $api_key,
					],
					'json' => $json,
				]);

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['getBalanceResponse'];

				return response()->json([
					'status' => true,
					'data' => $data,
				], 200);
			} catch (\Exception $e) {
				return response()->json([
					'status' => false,
					'message' => json_decode($e->getResponse()->getBody()->getContents()),
				], 500);
			}
		} else {
			return response()->json([
				'status' => false,
				'message' => $get_token['message'],
			], 500);
		}
	}

	public function getinhouseinquiry(Request $request)
	{
		$api_key = config('services.bni.api_key');
		$base64_client_name = config('services.bni.base64_client_name');

		$accountNo = $request->accountno ?? '';

		$payload_signature = [
			"clientId" => "IDBNI" . $base64_client_name,
			"accountNo" => $accountNo,
		];
		$signature = self::generateSignature($payload_signature);
		$json = [
			...$payload_signature,
			"signature" => $signature,
		];
		// $get_token = self::token();
		
		// if ($get_token['status']) {
		if (!empty($request->token)) {
			$access_token = $request->token;

			try {
				$client = new \GuzzleHttp\Client();
				$url = config('services.bni.endpoint_url') . '/H2H/v2/getinhouseinquiry?access_token=' . $access_token;

				$res = $client->request('POST', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'X-API-Key' => $api_key,
					],
					'json' => $json,
				]);

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['getInHouseInquiryResponse'];

				return response()->json([
					'status'  => true,
					'data' => $data,
				], 200);
			} catch (\Exception $e) {
				return response()->json([
					'status' => false,
					'message' => json_decode($e->getResponse()->getBody()->getContents()),
				], 500);
			}
		} else {
			return response()->json([
				'status' => false,
				'message' => $get_token['message'],
			], 500);
		}
	}

	public function dopayment(Request $request)
	{
		$api_key = config('services.bni.api_key');
		$base64_client_name = config('services.bni.base64_client_name');

		$customerReferenceNumber = $request->customerReferenceNumber ?? '';
		$paymentMethod = $request->paymentMethod ?? '';
		$debitAccountNo = $request->debitAccountNo ?? '';
		$creditAccountNo = $request->creditAccountNo ?? '';
		$valueDate = $request->valueDate ?? '';
		$valueCurrency = $request->valueCurrency ?? '';
		$valueAmount = $request->valueAmount ?? '';
		$remark = $request->remark ?? '';
		$beneficiaryEmailAddress = $request->beneficiaryEmailAddress ?? '';
		$beneficiaryName = $request->beneficiaryName ?? '';
		$beneficiaryAddress1 = $request->beneficiaryAddress1 ?? '';
		$beneficiaryAddress2 = $request->beneficiaryAddress2 ?? '';
		$destinationBankCode = $request->destinationBankCode ?? '';
		$chargingModelId = $request->chargingModelId ?? '';

		$payload_signature = [
			"clientId" => "IDBNI" . $base64_client_name,
			"customerReferenceNumber" => $customerReferenceNumber,
			"paymentMethod" => $paymentMethod,
			"debitAccountNo" => $debitAccountNo,
			"creditAccountNo" => $creditAccountNo,
			"valueDate" => $valueDate,
			"valueCurrency" => $valueCurrency,
			"valueAmount" => $valueAmount,
			"remark" => $remark,
			"beneficiaryEmailAddress" => $beneficiaryEmailAddress,
			"beneficiaryName" => $beneficiaryName,
			"beneficiaryAddress1" => $beneficiaryAddress1,
			"beneficiaryAddress2" => $beneficiaryAddress2,
			"destinationBankCode" => $destinationBankCode,
			"chargingModelId" => $chargingModelId,
		];
		$signature = self::generateSignature($payload_signature);
		$json = [
			...$payload_signature,
			"signature" => $signature,
		];
		$get_token = self::token();

		// if ($get_token['status']) {
		// 	$access_token = $get_token['access_token'];
		$result_payment = '';
		if (!empty($request->token)) {
			$access_token = $request->token;

			try {
				$client = new \GuzzleHttp\Client();
				$url = config('services.bni.endpoint_url') . '/H2H/v2/dopayment?access_token=' . $access_token;

				$res = $client->request('POST', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'X-API-Key' => $api_key,
					],
					'json' => $json,
				]);

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['doPaymentResponse'];
				$message = $data['parameters']['responseMessage'];
				$result_payment = $data;
				return response()->json([
					'status'  => true,
					'data' => $data,
					'message' => $message,
				], 200);
			} catch (\Exception $e) {
				$data_response = json_decode($e->getResponse()->getBody()->getContents(), true);

				if (!isset($data_response['doPaymentResponse'])) {
					return response()->json([
						'status' => false,
						'data' => $data_response['response'],
						'message' => $data_response['response']['parameters']['responseMessage'],
					], 500);
				}

				$data = $data_response['doPaymentResponse'];
				$message = $data['parameters']['responseMessage'];
				return response()->json([
					'status' => false,
					'data' => $data,
					'message' => $message,
				], 500);
			}
		} else {
			return response()->json([
				'status' => false,
				'message' => $get_token['message'],
			], 500);
		}
	}

	public function getpaymentstatus(Request $request)
	{
		$api_key = config('services.bni.api_key');
		$base64_client_name = config('services.bni.base64_client_name');

		$customerReferenceNumber = $request->customerReferenceNumber ?? '';

		$payload_signature = [
			"clientId" => "IDBNI" . $base64_client_name,
			"customerReferenceNumber" => $customerReferenceNumber,
		];
		$signature = self::generateSignature($payload_signature);
		$json = [
			...$payload_signature,
			"signature" => $signature,
		];
		$get_token = self::token();

		// if ($get_token['status']) {
		// 	$access_token = $get_token['access_token'];
		if (!empty($request->token)) {
			$access_token = $request->token;

			try {
				$client = new \GuzzleHttp\Client();
				$url = config('services.bni.endpoint_url') . '/H2H/v2/getpaymentstatus?access_token=' . $access_token;

				$res = $client->request('POST', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'X-API-Key' => $api_key,
					],
					'json' => $json,
				]);

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['getPaymentStatusResponse'];

				return response()->json([
					'status'  => true,
					'data' => $data,
				], 200);
			} catch (\Exception $e) {
				return response()->json([
					'status' => false,
					'message' => json_decode($e->getResponse()->getBody()->getContents()),
				], 500);
			}
		} else {
			return response()->json([
				'status' => false,
				'message' => $get_token['message'],
			], 500);
		}
	}

	public function getinterbankinquiry(Request $request)
	{
		$api_key = config('services.bni.api_key');
		$base64_client_name = config('services.bni.base64_client_name');

		$customerReferenceNumber = $request->customerReferenceNumber ?? '';
		$accountNum = $request->accountNum ?? '';
		$destinationBankCode = $request->destinationBankCode ?? '';
		$destinationAccountNum = $request->destinationAccountNum ?? '';

		$payload_signature = [
			"clientId" => "IDBNI" . $base64_client_name,
			"customerReferenceNumber" => $customerReferenceNumber,
			"accountNum" => $accountNum,
			"destinationBankCode" => $destinationBankCode,
			"destinationAccountNum" => $destinationAccountNum,
		];
		$signature = self::generateSignature($payload_signature);
		$json = [
			...$payload_signature,
			"signature" => $signature,
		];
		$get_token = self::token();

		// if ($get_token['status']) {
		// 	$access_token = $get_token['access_token'];
		if (!empty($request->token)) {
			$access_token = $request->token;

			try {
				$client = new \GuzzleHttp\Client();
				$url = config('services.bni.endpoint_url') . '/H2H/v2/getinterbankinquiry?access_token=' . $access_token;

				$res = $client->request('POST', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'X-API-Key' => $api_key,
					],
					'json' => $json,
				]);

			// 	$data_response = json_decode($res->getBody()->getContents(), true);
			// 	$data = $data_response['getInterbankInquiryResponse'];

			// 	return response()->json([
			// 		'status'  => true,
			// 		'data' => $data,
			// 	], 200);
			// } catch (\Exception $e) {
			// 	return response()->json([
			// 		'status' => false,
			// 		'message' => json_decode($e->getResponse()->getBody()->getContents()),
			// 	], 500);

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['getInterbankInquiryResponse'];
				$message = $data['parameters']['responseMessage'];

				return response()->json([
					'status'  => true,
					'data' => $data,
					'message' => $message,
				], 200);
			} catch (\Exception $e) {
				$data_response = json_decode($e->getResponse()->getBody()->getContents(), true);

				if (!isset($data_response['getInterbankInquiryResponse'])) {
					return response()->json([
						'status' => false,
						'data' => $data_response['response'],
						'message' => $data_response['response']['parameters']['responseMessage'],
					], 500);
				}

				$data = $data_response['getInterbankInquiryResponse'];
				$message = $data['parameters']['responseMessage'];
				return response()->json([
					'status' => false,
					'data' => $data,
					'message' => $message,
				], 500);
			}
		} else {
			return response()->json([
				'status' => false,
				'message' => $get_token['message'],
			], 500);
		}
	}

	public function getinterbankpayment(Request $request)
	{
		$api_key = config('services.bni.api_key');
		$base64_client_name = config('services.bni.base64_client_name');

		$customerReferenceNumber = $request->customerReferenceNumber ?? '';
		$amount = $request->amount ?? '';
		$destinationAccountNum = $request->destinationAccountNum ?? '';
		$destinationAccountName = $request->destinationAccountName ?? '';
		$destinationBankCode = $request->destinationBankCode ?? '';
		$destinationBankName = $request->destinationBankName ?? '';
		$accountNum = $request->accountNum ?? '';
		$retrievalReffNum = $request->retrievalReffNum ?? '';

		$payload_signature = [
			"clientId" => "IDBNI" . $base64_client_name,
			"customerReferenceNumber" => $customerReferenceNumber,
			"amount" => $amount,
			"destinationAccountNum" => $destinationAccountNum,
			"destinationAccountName" => $destinationAccountName,
			"destinationBankCode" => $destinationBankCode,
			"destinationBankName" => $destinationBankName,
			"accountNum" => $accountNum,
			"retrievalReffNum" => $retrievalReffNum
		];
		$signature = self::generateSignature($payload_signature);
		$json = [
			...$payload_signature,
			"signature" => $signature,
		];
		$get_token = self::token();

		if ($get_token['status']) {
			$access_token = $get_token['access_token'];
		// if (!empty($request->token)) {
		// 	$access_token = $request->token;

			try {
				$client = new \GuzzleHttp\Client();
				$url = config('services.bni.endpoint_url') . '/H2H/v2/getinterbankpayment?access_token=' . $access_token;

				$res = $client->request('POST', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Content-Type' => 'application/json',
						'X-API-Key' => $api_key,
					],
					'json' => $json,
				]);

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['getInterbankPaymentResponse'];
				$message = $data['parameters']['responseMessage'];

				return response()->json([
					'status'  => true,
					'data' => $data,
					'message' => $message,
				], 200);
			} catch (\Exception $e) {

				$data_response = json_decode($e->getResponse()->getBody()->getContents(), true);

				if (!isset($data_response['getInterbankPaymentResponse'])) {

					if (isset($data_response['getInterbankInquiryResponse'])) {
						return response()->json([
							'status' => false,
							'data' => $data_response['getInterbankInquiryResponse'],
							'message' => $data_response['getInterbankInquiryResponse']['parameters']['responseMessage'],
						], 500);
					}

					return response()->json([
						'status' => false,
						'data' => $data_response['response'],
						'message' => $data_response['response']['parameters']['responseMessage'],
					], 500);
				}

				$data = $data_response['getInterbankPaymentResponse'];
				$message = $data['parameters']['responseMessage'];
				return response()->json([
					'status' => false,
					'data' => $data,
					'message' => $message,
				], 500);
			}
		} else {
			return response()->json([
				'status' => false,
				'message' => $get_token['message'],
			], 500);
		}
	}

	public function generateSignature($payload)
	{
		$api_secret = config('services.bni.api_secret');

		// Create token header as a JSON string
		$header = JSON_encode([
			'alg' => 'HS256',
			'typ' => 'JWT'
		]);

		// Create token payload as a JSON string
		$payload = JSON_encode($payload);

		// Encode Header to Base64Url String
		$base64UrlHeader = str_replace(
			['+', '/', '='],
			['-', '_', ''],
			base64_encode($header)
		);

		//Encode Payload to Base64Url String
		$base64UrlPayload = str_replace(
			['+', '/', '='],
			['-', '_', ''],
			base64_encode($payload)
		);

		// Create Signature Hash
		$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $api_secret, true);

		// Encode Signature to Base64Url String
		$base64UrlSignature = str_replace(
			['+', '/', '='],
			['-', '_', ''],
			base64_encode($signature)
		);

		// Create JWT
		$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
		return $jwt;
	}
}
