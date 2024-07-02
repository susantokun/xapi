<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocRequest;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EofficeNumController extends Controller
{
    public function store(DocRequest $request)
    {
        $document_wxc_id = $request->document_wxc_id;
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        $json_document_wxc_id = json_decode($document_wxc_id,true);
        $postfields = array(
        'document_wxc_id' => $document_wxc_id
        );
        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;
        $post_data = $this->build_data_files($boundary, $postfields);
        $ch = curl_init();
        curl_setopt_array($ch, array(
          CURLOPT_URL => "https://dev-eoffice.waskita.co.id/apps/woorkspace/xapi/gateway/sap_check_doc_number.php",
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
