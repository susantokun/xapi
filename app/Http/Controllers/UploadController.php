<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    //
    public function store(Request $request)
    {
        $file_name = $request->file_name;
        $file_path = Storage::disk('public')->path($request->file_path);
        // $file_mime = "application/pdf";
         $file_mime = $request->file_mime;
       
        $curlFile = curl_file_create($file_path,$file_mime,$file_name);
        $postfields = array("uploaded_file[]" =>  $curlFile);
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

        $headers = array(
            'X-API-Key: SAP@WaskitaK4rya',
            'X-API-Secret: SAPGWAccess@WaskitaK4rya!',
            'Content-Type: multipart/form-data'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-eoffice.waskita.co.id/apps/woorkspace/xapi/gateway/sap_upload_berkas_lampiran.php');
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
        // var_dump( $ch);
        // sleep(3);
        // $eoffice = json_decode($result_eoffice,true);
        // if (curl_errno($ch)) { 
        //   print curl_error($ch); 
        // } 
        curl_close( $ch );
        $eoffice = json_decode($result_eoffice,true);
        //  var_dump( $eoffice);

        return response()->json($eoffice);
    }
}
