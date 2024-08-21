<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Http\Controllers\Controller;
use BniApi\BniPhp\api\OneGatePayment;
use BniApi\BniPhp\Bni;
use Illuminate\Http\Request;

class BNIController extends Controller
{
	protected $bni;
	protected $ogp;

	function __construct()
	{
		$this->bni = new Bni(
			$env = config('services.bni.env'),
			$clientId = config('services.bni.client_id'),
			$clientSecret = config('services.bni.client_secret'),
			$apiKey = config('services.bni.api_key'),
			$apiSecret = config('services.bni.api_secret'),
			$appName = config('services.bni.base64_client_name')
		);

		$this->ogp = new OneGatePayment($this->bni);
	}

	public function send(Request $request)
	{
		try {
			$client = new \GuzzleHttp\Client();
			$url = 'https://wa.susantokun.com/api/send-message';
			$res = $client->request('POST', $url, [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
					'api_key' => '07b6d24fb82e75c937763fa25795b263bd8651f8',
					'receiver' => '6281906515912',
					'data' => [
						'message' => "Hello World"
					],
				],
			]);

			$response = json_decode($res->getBody()->getContents(), true);

			return [
				'status' => true,
				'message' => $response
			];
		} catch (\Exception $e) {
			return [
				'status' => false,
				'message' => $e->getMessage(),
			];
		}
	}

	public function test(Request $request)
	{
		// $data = $request->all();

		// return response()->json([
		// 	'status' => true,
		// 	'data' => $data
		// ], 200);

		// $bni = new Bni(
		// 	$env = 'sandbox',
		// 	$clientId = config('services.bni.client_id'),
		// 	$clientSecret = config('services.bni.client_secret'),
		// 	$apiKey = config('services.bni.api_key'),
		// 	$apiSecret = config('services.bni.api_secret'),
		// 	$appName = config('services.bni.base64_client_name')
		// );

		// $ogp = new OneGatePayment($bni);

		$getbalance = $this->ogp->getBalance(
			$accountNo = '115471119'
		);

		return response()->json($getbalance);
	}

	public function token22()
	{
		$json = '{
				"grant_type":"client_credentials",
		}';
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => "https://sandbox.bni.co.id/api/oauth/token",
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => array(
				"Authorization: Basic ZjYwZTNjNjMtNjkwNi00OTk2LTgxNmUtM2UzMjU1Y2I1NDI5OmY0OWY5ZjU3LTQ2NzktNDYxMS05ZTljLWU4NjFkNWE4NmMyYw==",
				"Content-Type: application/x-www-form-urlencoded",
			),
		));
		$output = curl_exec($ch);
		curl_close($ch);
		
		return response()->json($output);
	}

	public function token2()
	{
		$client = new \GuzzleHttp\Client();
		$url = 'https://sandbox.bni.co.id/api/oauth/token';
		$res = $client->request('POST', $url, [
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => 'Basic ZjYwZTNjNjMtNjkwNi00OTk2LTgxNmUtM2UzMjU1Y2I1NDI5OmY0OWY5ZjU3LTQ2NzktNDYxMS05ZTljLWU4NjFkNWE4NmMyYw==',
			],
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
	}

	public function token()
	{
		try {
			$client = new \GuzzleHttp\Client();
			$url = config('services.bni.endpoint_url') . '/api/oauth/token';
			$res = $client->request('POST', $url, [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
					'Authorization' => 'Basic ' . config('services.bni.base64_auth'),
					// YjMyNzk5MWUtOTA5Ny00MDI4LTlmOGUtODFiN2MwOWE2MGFhOmU4YmQ2OTFjYzAwOTBhMmMyODRkZDQ5YzZkZmU4ZWEzNzFiY2VmNWExYjczNzJlZjY0MjlkZmRlYjU3ZjkzMzI=
				],
				// 'auth' => [config('services.bni.client_id'), config('services.bni.client_secret')],
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
				$result_payment = $data;
				return response()->json([
					'status'  => true,
					'data' => $data,
				], 200);
			} catch (\Exception $e) {
				return response()->json([
					'status' => false,
					// 'message' => json_decode($e->getResponse()->getBody()->getContents()),
					'message' => $e->getResponse()->getBody()->getContents(),
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

				$data_response = json_decode($res->getBody()->getContents(), true);
				$data = $data_response['getInterbankInquiryResponse'];

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

				return response()->json([
					'status'  => true,
					'data' => $data,
				], 200);
			} catch (\Exception $e) {
				return response()->json([
					'status' => false,
					// 'message' => json_decode($e->getResponse()->getBody()->getContents()),
					'message' => json_encode($e->getResponse()->getBody()->getContents()),
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
