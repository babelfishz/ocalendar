<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Photo;
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
    public function index()
    {
        $photos = Photo::orderBy('dateTimeDigitized', 'desc')->paginate(30);
        $species = Photo::distinct('floraName')->count('floraName');
        return array('floras'=> $photos, 'species'=> $species);
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


        /*if ( ! in_array($extension, $this->allowed_ext)) {
            return response()->json(['success' => false]);
        }*/

     

        if ($file->move($destinationPath, $fileName)){
            
            $img = Image::make($destinationPath . $fileName);
            $dateTimeDigitized = $img->exif('DateTimeDigitized');

            $thumbnailFileName = basename($fileName,$extension) . 'thumbnail' . '.'. $extension;
            $thumbnail = $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbnail->save($destinationPath . $thumbnailFileName);

            
            $photo = new Photo;
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
    public function show($month)
    {
        return Photo::whereMonth('dateTimeDigitized', '=', $month)->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get();
        //return Photo::where('floraName', '=', $name)->orderBy('dateTimeDigitized', 'desc')->get();
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
    public function destroy($id)
    {
        $photo = Photo::find($id);
        $imageFile = $photo->filePath . $photo->fileName;
        log::info('photo to be deleted: ' . $imageFile);
        if(File::exists($imageFile)){
            File::delete($imageFile);
        }

        $thumbImageFile = $photo->filePath . $photo->thumbnailFileName;
        log::info('photo to be deleted: ' . $thumbImageFile);
        if(File::exists($thumbImageFile)){
            File::delete($thumbImageFile);
        }

        $destroy = Photo::destroy($id);
    }
}
