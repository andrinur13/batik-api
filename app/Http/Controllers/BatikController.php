<?php

namespace App\Http\Controllers;

use App\Models\Batik;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;

use function PHPUnit\Framework\isEmpty;

class BatikController extends Controller
{

    public function ResponseUserFormatter($messages, $status, $code, $data)
    {
        $response = [
            'meta' => [
                'messages' => $messages,
                'status' => $status,
                'code' => $code,
            ],
            'data' => $data
        ];

        return $response;
    }


    public function index()
    {
        // show all batik
        $batik = Batik::get();

        $response = $this->ResponseUserFormatter('success fetch data batik', 'success', Response::HTTP_OK, $batik);
        return response()->json($response, Response::HTTP_OK);
    }



    public function store(Request $request)
    {
        //// create batik data
        $validator = Validator::make($request->all(), [
            // 'qr_code' => ['required'],
            'name' => ['required'],
            'description' => ['required'],
            'img' => 'required|mimes:jpeg,bmp,png|max:2000'
        ]);

        // jika validator gagal dijalankan
        if ($validator->fails()) {
            $response = $this->ResponseUserFormatter('failed validation data', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // tangkap form data
        $qr_code = "batiknitik" . time();
        $name = $request->name;
        $description = $request->description;

        if ($fileUpload = $request->file('img')) {
            try {
                $nameFile = time() . '-' . md5($fileUpload->getClientOriginalName()) . '.' . $fileUpload->getClientOriginalExtension();
                $path = $fileUpload->move('img/batik', $nameFile);
                $namePath = $fileUpload->getClientOriginalName();

                // store to directory dan db
                $saveUpload = new Batik();
                $saveUpload->qr_code = $qr_code;
                $saveUpload->name = $name;
                $saveUpload->description = $description;
                $saveUpload->path = $path;
                $nameQR = time() . '-'. $name . '.svg';
                $nameQR = trim($nameQR, ' ');
                $saveUpload->qr_path = 'img/qr/' . $nameQR;
                $saveUpload->save();
                // create QR Code and store to path
                QrCode::size(200)->format('svg')->generate($saveUpload->qr_code, public_path('img/qr/' . $nameQR));

                $saveUpload->path = 'img/batik/' . $nameFile;

                $response = $this->ResponseUserFormatter('success add data batik', 'success', Response::HTTP_OK, $saveUpload);
                return response()->json($response, Response::HTTP_OK);
            } catch (QueryExecuted $e) {
                $response = $this->ResponseUserFormatter('failed add data batik', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $e);
                return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // tangkap request
        // $userReq = [
        //     'qr_code' => $request->qr_code,
        //     'name' => $request->name,
        //     'description' => $request->description
        // ];

        // $batikAded = Batik::create($userReq);

        // $response = $this->ResponseUserFormatter('success add data batik', 'success', Response::HTTP_OK, $userReq);
        // return response()->json($response, Response::HTTP_OK);
    }


    public function show($qr)
    {
        // shot batik with id 
        $batik = Batik::where('qr_code', $qr)->first();

        if ($batik == null) {
            $response = $this->ResponseUserFormatter('batik not found!', 'failed', Response::HTTP_NOT_FOUND, $batik);
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $response = $this->ResponseUserFormatter('success fetch data batik', 'success', Response::HTTP_OK, $batik);
        return response()->json($response, Response::HTTP_OK);
    }


    public function edit(Request $request, $id)
    {
        //
        $batik = Batik::find($id);

        if ($batik == null) {
            $response = $this->ResponseUserFormatter('batik not found!', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $batik);
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $batikEdited = [
            'id' => $batik['id'],
            'qr_code' => $request->qr_code ? $request->qr_code : $batik->qr_code,
            'name' => $request->name ? $request->name : $batik->name,
            'description' => $request->description ? $request->description : $batik->description
        ];

        try {
            $batik->update($batikEdited);

            $response = $this->ResponseUserFormatter('bat`ik success updated!', 'success', Response::HTTP_OK, $batik);
            return response()->json($response, Response::HTTP_OK);
        } catch (QueryExecuted $q) {
            $response = $this->ResponseUserFormatter('batik failed updated!', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $q);
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function update(Request $request, Batik $batik)
    {
        //
    }


    public function destroy($qr)
    {

        // deleted data batik with id
        $batik = Batik::where('qr_code', $qr)->first();

        // pastikan $id
        if ($batik == null) {
            $response = $this->ResponseUserFormatter('failed deleted batik', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $batik);
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            // delete image and qr
            File::delete($batik->path);
            File::delete($batik->qr_path);

            $batik->delete();
            $response = $this->ResponseUserFormatter('success deleted batik', 'success', Response::HTTP_OK, $batik);
            return response()->json($response, Response::HTTP_OK);
        } catch (QueryExecuted $q) {
            $response = $this->ResponseUserFormatter('failed deleted batik', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $batik);
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
