<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $file_name = $request->file_name;
        $file_path = Storage::disk('public')->path($request->file_path);
        $file_mime = $request->file_mime;

        $curlFile = curl_file_create($file_path, $file_mime, $file_name);
        $postfields = array("uploaded_file[]" =>  $curlFile);
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

        $headers = array(
            'X-API-Key: ' . env('EOFFICE_API_KEY'),
            'X-API-Secret: ' . env('EOFFICE_API_SECRET'),
            'Content-Type: multipart/form-data'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('EOFFICE_ENDPOINT_URL') . '/apps/woorkspace/xapi/unigateway/upload_berkas_lampiran.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        // curl_setopt($ch, CURLOPT_INFILESIZE, $file_size);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result_eoffice = curl_exec($ch);
        curl_close($ch);
        $data = $this->get_response($result_eoffice);
        return response()->json($data);
    }

    function get_response($response)
    {
        $startPos = strpos($response, '{"result":"success","data":');
        $finalResponse = [];
        if ($startPos !== false) {
            $jsonPart = substr($response, $startPos);
            $decodedData = json_decode($jsonPart, true);
            if (isset($decodedData['result']) && isset($decodedData['data'])) {
                $status = $decodedData['result'];
                $data = $decodedData['data'];

                $finalResponse = [
                    'status' => $status,
                    'data' => $data
                ];
                // echo json_encode($finalResponse, JSON_PRETTY_PRINT);
            } else {
                $finalResponse = [
                    "status" => "failed",
                    "message" => "Data tidak ditemukan."
                ];
            }
        } else {
            $finalResponse = [
                "status" => "failed",
                "message" => "Bagian JSON tidak ditemukan."
            ];
        }

        return $finalResponse;
    }
}
