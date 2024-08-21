<?php

namespace App\Http\Controllers;

use App\Http\Requests\DraftRequest;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DraftController extends Controller
{
    public function store(DraftRequest $request)
    {
        $document_title = $request->document_title;
        $role_wxc_id = $request->role_wxc_id;
        $berkas_utama = $request->berkas_utama;
        $list_lampiran = $request->list_lampiran;
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        $json_berkas_utama = json_decode($berkas_utama,true);
        $json_list_lampiran = json_decode($list_lampiran,true);

        $berkas_utama = json_encode($json_berkas_utama);
        $list_lampiran = json_encode($json_list_lampiran);
        $postfields = array(
        'document_title' => $document_title,
        'role_wxc_id' => $role_wxc_id,
        'berkas_utama' => $berkas_utama,
        'list_lampiran' => $list_lampiran    
        );

        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;
        $post_data = $this->build_data_files($boundary, $postfields);
        $ch = curl_init();
        curl_setopt_array($ch, array(
          CURLOPT_URL => "https://dev-eoffice.waskita.co.id/apps/woorkspace/xapi/gateway/sap_create_document_draft.php",
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POST => 1,
          CURLOPT_POSTFIELDS => $post_data,
          CURLOPT_HTTPHEADER => array(
            //"Authorization: Bearer $TOKEN",
            'X-API-Key: SAP@WaskitaK4rya',
            'X-API-Secret: SAPGWAccess@WaskitaK4rya!',
            "Content-Type: multipart/form-data; boundary=" . $delimiter,
            "Content-Length: " . strlen($post_data)

          ),

          
        ));

        $result_eoffice = curl_exec($ch);
        // var_dump($result_eoffice);
        curl_close( $ch );
        $berkas_eoffice = json_decode($result_eoffice,true);
        

        return response()->json($berkas_eoffice);
    }

    function build_data_files($boundary, $fields){
        $data = '';
        $eol = "\r\n";

        $delimiter = '-------------' . $boundary;

        foreach ($fields as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
                . $content . $eol;
        }


        // foreach ($files as $name => $content) {
        //     $data .= "--" . $delimiter . $eol
        //         . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
        //         //. 'Content-Type: image/png'.$eol
        //         . 'Content-Transfer-Encoding: binary'.$eol
        //         ;

        //     $data .= $eol;
        //     $data .= $content . $eol;
        // }
        $data .= "--" . $delimiter . "--".$eol;


        return $data;
    }
}
