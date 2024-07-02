<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = File::where('category', 'waskita')->get([
            'id',
            'files',
            'details'
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil!',
            "data" => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileRequest $request)
    {
        $data_files = [];
        $result_eoffice = "";
        $data_details = [];
        $result_eoffice = [];
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        // var_dump($request->hasFile('files'));

        if ($request->hasFile('files')) {
            $files = $request->file('files');
            
            foreach ($files as $key => $file) {
                $file_name = $file->getClientOriginalName();


                //Store in Difo
                $file_path = 'documents/' . str()->random(40) . "." . $file->extension();
                Storage::disk('public')->putFileAs('', $file, $file_path);

                // $file_url = Storage::url($file_path);
                
                // var_dump(Storage::disk('public')->path($file_path));
                //UPLOAD KE EOFFICE
                // $result_eoffice = $this->uploadEoffice($file,$file_path);

 //=========================CURL HERE 
                $file_size = $file->getSize();
                $filepath = $file_path;//Storage::disk('public')->path($file_path);
                // var_dump($filepath);
                $file_mime =$file->getClientMimeType();
                // $json = 
                // '{
                //    "path":"/home/beljar43/api.difolestari.com/api/src/../uploads/074148675120-waskita-test.pdf",
                //    "mime":"application/pdf",
                //    "name":"TestPDFfile.pdf" 
                // }';
                // '{
                //    "path":"D:\xampp\htdocs\xapi\storage\app/public\documents/5XeRDGXsDRzA7HuelsUgfWjk3kH6mXp76V1kE7ip.pdf",
                //    "mime":"'.$file_mime.'",
                //    "name":"'.$file_name.'" 
                // }';
                
                // $headers = array(
                // 'Content-Type: application/json'
                // );
// ======================MENGIRIM JSON UNTUK UPLOAD DARI DIRI SENDIRI
                $json = '{
                        "file_name":"'.$file_name.'",
                        "file_path":"'.$file_path.'"
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
                curl_close( $ch );
                $eoffice = json_decode($result_eoffice,true);


//==============================================================================


// ======================MENGIRIM FILE KE API.DIFOLESTARI
               //  $curlFile = new \CURLFile($filepath, $file_mime, $file_name);
               //  $postfields = array("file" => $curlFile);
               //  $headers = array(
               //      'X-API-Key: SAP@WaskitaK4rya',
               //      'X-API-Secret: SAPGWAccess@WaskitaK4rya!',
               //      'Content-Type: multipart/form-data'
               //  );
               //  $ch = curl_init();
               //  curl_setopt($ch, CURLOPT_URL, 'http://api.difolestari.com/api/api/eoffice/');
               // curl_setopt($ch, CURLOPT_POST, true);
               //  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
               //  // curl_setopt($ch, CURLOPT_HEADER, true);
               //  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
               //  // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
               //  // curl_setopt($ch, CURLOPT_POSTREDIR, 3);
               //  // curl_setopt($ch, CURLOPT_INFILESIZE, $file_size);
               //  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
               //  curl_setopt($ch, CURLOPT_AUTOREFERER, true);
               //  // curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
               //  curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
               //  $result_eoffice = curl_exec($ch);
               //  // var_dump($result_eoffice);
               //  $eoffice = json_decode($result_eoffice,true);
               //  curl_close( $ch );
//====================================================================================
                


                $data_files[] = [
                    'name' => $file_name,
                    'key' => $key,
                    'path' => $file_path,
                    'eoffice' => $eoffice,
                    
                ];
               

            }
        }

        if(is_array($request->input('details'))) {
            foreach ($request->input('details') as $key => $value) {
                $data_details["detail_$key"] = $value;
            }
        }
        

        $data = File::create([
            'user_id' => auth()->check() ? auth()->id() : NULL,
            'category' => 'waskita',
            'files' => $data_files,
            'details' => $data_details,

        ]);

        if (!empty($eoffice['data'])) {
            $slot_wxc_id = $eoffice['data'][0]['slot_wxc_id'];
            $file_url = $eoffice['data'][0]['files']['0']['file_url'];
            $creation_date = $eoffice['data'][0]['files']['0']['creation_date'];
            $last_action_date = $eoffice['data'][0]['files']['0']['last_action_date'];
            $pic = $eoffice['data'][0]['files']['0']['pic'];
        }else{
            $slot_wxc_id = 'empty';
            $file_url = 'empty';
            $creation_date = 'empty';            
            $last_action_date = 'empty';
            $pic = 'empty';
        }

        if (!empty($data['files'])) {
            $file_name = $data['files'][0]['name'];
            $file_path = $data['files'][0]['path'];
        }else{
            $file_name = 'empty';
            $file_path = 'empty';
            }
        if (!empty($data['id'])) {
            $file_id = $data['id'];
        }else{
            $file_id = 'empty';
        }
        if (!empty($data['details'])) {
            $file_detail = $data['details']['detail_1'];
        }else{
            $file_detail = 'empty';
        }

   

    // $eoff = $eoffice['data'];

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan!',
            // "data" => $data,
            'slot_wxc_id' => $slot_wxc_id,
            'file_url' => $file_url,
            'creation_date' => $creation_date,
            'last_action_date' => $last_action_date,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'file_id' => $file_id,
            'file_detail' => $file_detail,
            'pic' => $pic,

        ]);
    }
   
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = File::where('id', $id)->firstOrFail();

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function uploadEoffice($file,$filepath)
    {
        $file_name = $file->getClientOriginalName();
        $file_size = $file->getSize();
        $file_path = $path = Storage::disk('public')->path($filepath);;
        $file_mime =$file->getClientMimeType();
        $curlFile = new \CURLFile($file_path, $file_mime, $file_name);
         $postfields = array("uploaded_file" => $curlFile);

        $headers = array(
                    'X-API-Key: SAP@WaskitaK4rya',
                    'X-API-Secret: SAPGWAccess@WaskitaK4rya!',
                    'Content-Type: multipart/form-data'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-eoffice.waskita.co.id/apps/woorkspace/xapi/gateway/sap_upload_berkas_lampiran.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36");
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
           curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        // curl_setopt($ch, CURLOPT_INFILESIZE, $file_size);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($ch);
        // var_dump($result);
        curl_close( $ch );

        
       
        return $result; 
    }
}
