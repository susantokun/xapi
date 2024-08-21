<?php

// Informasi Pengajuan pada proyek `0001119001` : ID Pengajuan : `2024/1119001-A/0000000015` Deskripsi : `test wa #1` Nilai Pengajuan: `Rp. 13.115.000` Tanggal Pengajuan: `11.07.2024` Status Terkini: `Workflow sedang berjalan`

// Informasi Pengajuan pada proyek 0001219006 :  ID Pengajuan : 11123829-920328493934SDA Deskripsi : tetst Nilai Pengajuan: 7000000.00 Tanggal Pengajuan: 06.02.2024 Status Terkini: Workflow sedang berjalan

// Atas perhatiannya, kami ucapkan terima kasih.


// {
// 	 "status": "success",
// 	 "data": "{\"status\":\"success\",\"data\":{\"id\":\"9903e5dd-1fcd-4cf4-b579-52f904d66ce8\",\"name\":\"Wisnu\",\"organization_id\":\"c926325b-e228-4cd7-a9b5-12693ebae543\",\"channel_integration_id\":\"4244afed-4bc9-4314-9136-3de7c60c6992\",\"contact_list_id\":null,\"contact_id\":\"e978681a-ac70-40fb-a88a-9caa92b345dc\",\"target_channel\":\"wa_cloud\",\"send_at\":\"2024-07-11T07:04:02.196Z\",\"execute_status\":\"todo\",\"execute_type\":\"immediately\",\"parameters\":{\"header\":{},\"body\":{\"1\":\"hello_world\",\"2\":\"hello_world2\"},\"buttons\":{}},\"created_at\":\"2024-07-11T07:04:02.202Z\",\"message_status_count\":{\"failed\":0,\"delivered\":0,\"read\":0,\"pending\":0,\"sent\":0},\"contact_extra\":{\"hello_world\":\"Wisnu \",\"hello_world2\":\"100.000\"},\"message_template\":{\"id\":\"3a928db1-5da3-4afc-bad7-84bf171cb8b4\",\"organization_id\":\"c926325b-e228-4cd7-a9b5-12693ebae543\",\"name\":\"template_2\",\"language\":\"id\",\"header\":null,\"body\":\"Your order {{1}} for a total of {{2}} is confirmed.\",\"footer\":null,\"buttons\":[],\"status\":\"APPROVED\",\"category\":\"UTILITY\",\"quality_rating\":null,\"quality_rating_text\":\"Neutral\"},\"division_id\":null,\"message_broadcast_error\":\"n/a\",\"sender_name\":\"Rangga Aulia Hakim\",\"sender_email\":\"rangga.hakim@waskita.co.id\",\"channel_account_name\":\"Waskita Karya\",\"channel_phone_number\":\"6285283778736\"}}"
// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MekariController extends Controller
{
	public function send_mekari(Request $request)
	{
		$parsed    = $request->getParsedBody();
		$username  = $parsed["username"];
		$useragent = $parsed["useragent"];
		$name      = $parsed["name"];
		$number    = $parsed["number"];
		$template  = $parsed["template"];
		$channel   = $parsed["channel"];
		$language  = $parsed["language"];
		$url_file  = "-";
		$file_name = "-";
		$val1      = $parsed["val1"];
		$val2      = $parsed["val2"];
		$token     = $parsed["token"];

		$result = self::sendMekari($name, $number, $template, $channel, $language, $url_file, $file_name, $val1, $val2, $token);
		return response()->json(["status" => "success", "data" => $result], 200);
	}

	function sendMekari($name, $number, $template, $channel, $language, $url_file, $file_name, $val1, $val2, $token)
	{
		$json = '{
			"to_name": "' . $name . '",
			"to_number": "' . $number . '",
			"message_template_id": "' . $template . '",
			"channel_integration_id": "' . $channel . '",
			"language": {
				"code": "' . $language . '"
			},
			"parameters": {
				
				"body": [
					{
						"key": "1",
						"value_text": "' . $val1 . '",
						"value": "hello_world"
					},
					{
						"key": "2",
						"value_text": "' . $val2 . '",
						"value": "hello_world2"
					}
				]
			}
		}';

		$headers = array(
			'Authorization: Bearer ' . $token,
			'Content-Type: application/json'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}
