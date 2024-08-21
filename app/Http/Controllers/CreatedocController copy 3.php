<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use Illuminate\Support\Facades\Storage;

class CreatedocController extends Controller
{
    public function store(FileRequest $request)
    {

        $document_title = $request->document_title;

        $data_files = [];
        $result_eoffice = "";
        $data_details = [];
        $result_eoffice = [];
        $list_lampiran = [];

        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

        if (is_array($request->input('details'))) {
            foreach ($request->input('details') as $key => $value) {
                $data_details["detail_$key"] = $value;
            }
        }

        if ($request->hasFile('files')) {
            $files = $request->file('files');

            foreach ($files as $key => $file) {
                $file_name = $file->getClientOriginalName();

                //Store in Difo
                $file_path = 'documents/' . str()->random(40) . "." . $file->extension();
                Storage::disk('public')->putFileAs('', $file, $file_path);

                //=========================CURL HERE 
                $file_size = $file->getSize();
                $filepath = $file_path; //Storage::disk('public')->path($file_path);
                $file_mime = $file->getClientMimeType();

                // ======================MENGIRIM JSON UNTUK UPLOAD DARI DIRI SENDIRI
                $json = '{
                        "file_name":"' . $file_name . '",
                        "file_path":"' . $file_path . '",
                        "file_mime":"' . $file_mime . '"
                    }';

                $headers = array(
                    // 'Authorization: Bearer '.$token,
                    'Content-Type: application/json'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://localhost/xapi/api/upload');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                $result_eoffice = curl_exec($ch);
                curl_close($ch);
                // $result_eoffice = '{
                //                        "data" : [
                //                           {
                //                              "file_count" : 1,
                //                              "files" : [
                //                                 {
                //                                    "creation_date" : "Feb 28, 2024 - 06:02",
                //                                    "file_name" : "d",
                //                                    "file_url" : "/data/sap/berkas_lampiran/240228/00_240228_063930_00_53_d",
                //                                    "last_action_date" : "Feb 28, 2024 - 06:02",
                //                                    "pic" : "xxxx"
                //                                 }
                //                              ],
                //                              "slot_wxc_id" : "xx1001001017-65de72b21cc1d0038181931-540"
                //                           }
                //                        ],
                //                        "result" : "success"
                //                     }';
                $eoffice = json_decode($result_eoffice, true);

                // SELEKSI BERKAS UTAMA ATAU LAMPIRAN
                $eoffice['data'][0]['files'][0]['file_description'] = $data_details["detail_$key"];
                if ($key == 1) {
                    $berkas_utama = $eoffice['data'][0];
                } else {
                    array_push($list_lampiran, $eoffice['data'][0]);
                }

                $data_files[] = [
                    'name' => $file_name,
                    'key' => $key,
                    'path' => $file_path,
                    'eoffice' => $eoffice,

                ];
            }
        }

        // =============CURL untuk membuat draft dokumen di eoffice
        $json_document_title = $document_title;
        // $json_role_wxc_id = '17201000100031-61ada70cd84e38058158412-31';
        $json_berkas_utama = json_encode($berkas_utama);
        $json_list_lampiran = json_encode($list_lampiran);
        $json_document_originator = $request->document_originator; // 'SAP';
        $json_pic_email_address = $request->pic_email_address; // 'andre@gmail.com';
        $berkas_array = array(
            'document_title' => $json_document_title,
            // 'role_wxc_id' => $json_role_wxc_id,
            'berkas_utama' => $json_berkas_utama,
            'list_lampiran' => $json_list_lampiran,
            'document_originator' => $json_document_originator,
            'pic_email_address' => $json_pic_email_address,
        );

        $fields = http_build_query($berkas_array);
        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;
        $post_data = $this->build_data_files($boundary, $berkas_array);

        dd([
            '$post_data' => $post_data,
            '$delimiter' => $delimiter,
        ]);

        // $ch = curl_init();
        // curl_setopt_array($ch, array(
        //     CURLOPT_URL => "http://localhost/xapi/api/create_draft",
        //     CURLOPT_RETURNTRANSFER => 1,
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30,
        //     //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POST => 1,
        //     CURLOPT_POSTFIELDS => $post_data,
        //     CURLOPT_HTTPHEADER => array(
        //         //"Authorization: Bearer $TOKEN",
        //         "Content-Type: multipart/form-data; boundary=" . $delimiter,
        //         "Content-Length: " . strlen($post_data)
        //     ),
        // ));

        // $result_eoffice = curl_exec($ch);
        // curl_close($ch);
        // $berkas_eoffice = json_decode($result_eoffice, true);

        // if (!empty($data_files)) {
        //     $utama_name = $data_files[0]['name'];
        //     $utama_path = $data_files[0]['path'];
        //     $utama_slotwxcid = $data_files[0]['eoffice']['data'][0]['slot_wxc_id'];
        //     $utama_url = $data_files[0]['eoffice']['data'][0]['files'][0]['file_url'];
        //     $utama_creation_date = $data_files[0]['eoffice']['data'][0]['files'][0]['creation_date'];
        //     $lampiran_string = '';
        //     foreach ($data_files as $key_final => $file_final) {

        //         if ($key_final !== 0) {
        //             $lampiran_string = $lampiran_string . $file_final['eoffice']['data'][0]['files'][0]['file_url'] . "^" .
        //                 $file_final['eoffice']['data'][0]['files'][0]['file_name'] . "^" .
        //                 $file_final['eoffice']['data'][0]['slot_wxc_id'] . "^" .
        //                 $file_final['path'] . "^" .
        //                 "|";
        //         }
        //     }
        // } else {
        //     $utama_name = "";
        //     $utama_path = "";
        //     $utama_slotwxcid = "";
        //     $utama_url = "";
        //     $lampiran_string = "";
        // }
        // if (!empty($berkas_eoffice['data'])) {
        //     $berkas_wxc = $berkas_eoffice['data'][0]['wxc_id'];
        // } else {
        //     $berkas_wxc = 'empty';
        // }

        // if (!empty($data['files'])) {
        //     $file_name = $data['files'][0]['name'];
        //     $file_path = $data['files'][0]['path'];
        // } else {
        //     $file_name = 'empty';
        //     $file_path = 'empty';
        // }
        // if (!empty($data['id'])) {
        //     $file_id = $data['id'];
        // } else {
        //     $file_id = 'empty';
        // }
        // if (!empty($data['details'])) {
        //     $file_detail = $data['details']['detail_1'];
        // } else {
        //     $file_detail = 'empty';
        // }

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Data berhasil ditambahkan!',
        //     'utama_slotwxcid' => $utama_slotwxcid,
        //     'utama_url' => $utama_url,
        //     'utama_creation_date' => $utama_creation_date,
        //     'utama_name' => $utama_name,
        //     'utama_path' => $utama_path,
        //     'file_detail' => $file_detail,
        //     'berkas_wxc' => $berkas_wxc,
        //     'lampiran_string' => $lampiran_string,
        // ]);
    }

    function build_data_files($boundary, $fields)
    {
        $data = '';
        $eol = "\r\n";

        $delimiter = '-------------' . $boundary;

        foreach ($fields as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol
                . $content . $eol;
        }

        $data .= "--" . $delimiter . "--" . $eol;
        return $data;
    }
}
