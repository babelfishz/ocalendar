<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Photo;
use Crypt;
use Image;
use File;
use Log;

class PhotoController extends Controller
{
    protected $allowed_ext = ["png", "jpg", "gif", 'jpeg'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $userId = Crypt::decrypt($request->Input('userId'));
        //Log::info('------userid:------' . $userId);
        $photos = Photo::where('userId', '=', $userId)->orderBy('dateTimeDigitized', 'desc')->paginate(30);
        $species = Photo::where('userId', '=', $userId)->distinct('floraName')->count('floraName');
        return array('floras'=> $photos, 'species'=> $species, 'userId' => $userId);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';
        $destinationPath = 'storage/uploads/images/';
        
        $fileName = time() . '_' . str_random(10) . '.' . $extension;
        $tmpFileName = $fileName . '.tmp';

        /*if ( ! in_array($extension, $this->allowed_ext)) {
            return response()->json(['success' => false]);
        }*/


        //if ($file->move($destinationPath, $fileName)){
        if ($file->move($destinationPath, $tmpFileName)){
            
            //$img = Image::make($destinationPath . $fileName);
            $img = Image::make($destinationPath . $tmpFileName);
            $dateTimeDigitized = $img->exif('DateTimeDigitized');

            //$data = $img->exif();
            //Log::info($data);

            if($dateTimeDigitized == null) {
                $dateTimeDigitized = $img->exif('DateTime');
            };

            $targetWidth = 1200;
            if($img->width() > $img->height()){
                $targetWidth = 1600;
            }

            $resizedFile = $img->resize($targetWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $resizedFile->save($destinationPath . $fileName);

            $thumbnailFileName = basename($fileName,$extension) . 'thumbnail' . '.'. $extension;
            $thumbnail = $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbnail->save($destinationPath . $thumbnailFileName);
            
            File::delete($destinationPath . $tmpFileName);

            $photo = new Photo;
            $photo->userId = Crypt::decrypt($request->Input('userId'));
            $photo->floraName = $request->input('name');
            $photo->filePath = $destinationPath;
            $photo->fileName = $fileName;
            $photo->thumbnailFileName = $thumbnailFileName;
            $photo->dateTimeDigitized = $dateTimeDigitized;
            $photo->save();

            return response()->json(['success' => true]);  
        }else{
            return response()->json(['success' => false]);    
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($month, Request $request)
    {
        /*if ($request->Input('userId') != null) {
            $userId = Crypt::decrypt($request->Input('userId'));
            return Photo::whereMonth('dateTimeDigitized', '=', $month)->where('userId', '=', $userId)->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get();
        }
        else{
            return Photo::whereMonth('dateTimeDigitized', '=', $month)->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get();
        }*/

        $userId = Crypt::decrypt($request->Input('userId'));
        $myFloras = Photo::whereMonth('dateTimeDigitized', '=', $month)->where('userId', '=', $userId)->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get();
        $othersFloras = Photo::whereMonth('dateTimeDigitized', '=', $month)->where('userId', '!=', $userId)->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get();

        $floraNames = array();
        foreach($myFloras as $myflora) {
            $floraNames[] = $myflora->floraName;
        }

        //log::info($floraNames);

        $extraFloras = [];
        foreach ($othersFloras as $flora) {
            $key = array_search($flora->floraName, $floraNames);
            //log::info($flora->floraName);
            //log::info($key);

            if ($key === false)
            {
                array_push($extraFloras, $flora);
            }
        };
        
        //log::info($extraFloras);
        return array('myFloras'=> $myFloras, 'extraFloras'=> $extraFloras);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $photo = Photo::find($id);
        $photo->floraName = $request->input('name');
        $photo->save();
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, request $request)
    {
        $photo = Photo::find($id);

        $userId = Crypt::decrypt($request->Input('userId'));
        //if($photo->userId != $userId){};

        $imageFile = $photo->filePath . $photo->fileName;
        log::info('photo to be deleted: ' . $imageFile);
        if(File::exists($imageFile)){
            File::delete($imageFile);
        }

        $thumbImageFile = $photo->filePath . $photo->thumbnailFileName;
        //log::info('photo to be deleted: ' . $thumbImageFile);
        if(File::exists($thumbImageFile)){
            File::delete($thumbImageFile);
        }

        $destroy = Photo::destroy($id);

        $species = Photo::where('userId', '=', $userId)->distinct('floraName')->count('floraName');
        return response()->json(['species' => $species]);
    }
}
