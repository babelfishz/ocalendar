<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Photo;
use App\Orchid;
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
        $year = $request->Input('year');
        $photos = Photo::where('userId', '=', $userId)->whereYear('dateTimeDigitized', '=', $year)->orderBy('dateTimeDigitized', 'desc')->paginate(30);
        //$photos = Photo::where('userId', '=', $userId)->orderBy('dateTimeDigitized', 'desc')->paginate(30);
        $species = Photo::where('userId', '=', $userId)->distinct('floraName')->count('floraName');
        $speciesOfYear = Photo::where('userId', '=', $userId)->whereYear('dateTimeDigitized', '=', $year)->distinct('floraName')->count('floraName');
        $count = Photo::where('userId', '=', $userId)->count();
        return array('floras'=> $photos, 'species'=> $species, 'count'=>$count, 'userId' => $userId, 'speciesOfYear' => $speciesOfYear);
    }

    public function queryByName($name, Request $request)
    {
        $userId = Crypt::decrypt($request->Input('userId'));
        $photos = Photo::where('floraName', 'like', '%'.$name.'%')->where('userId', '=', $userId)->orderBy('dateTimeDigitized', 'desc')->paginate(300);
        $species = Photo::where('floraName', 'like', '%'.$name.'%')->where('userId', '=', $userId)->distinct('floraName')->count('floraName');
        $count = count($photos);
        return array('floras'=> $photos, 'species'=> $species, 'count'=>$count, 'userId' => $userId);
    }

    public function queryAllPhotoByName($name, Request $request)
    {
        $userId = Crypt::decrypt($request->Input('userId'));
        $photos = Photo::where('floraName', 'like', '%'.$name.'%')->orderBy('dateTimeDigitized', 'desc')->paginate(300);
        $species = Photo::where('floraName', 'like', '%'.$name.'%')->distinct('floraName')->count('floraName');
        $count = count($photos);
        //return array('floras'=> $photos, 'species'=> $species, 'count'=>$count, 'userId' => $userId);
        $morePhotos = Orchid::where('orchids.genus', 'like', '%'.$name.'%')->join('photos', 'floraName', '=', 'orchids.species')->where('floraName','not like', '%'.$name.'%')->orderBy('dateTimeDigitized', 'desc')->get();
        return array('floras'=> $photos, 'species'=> $species, 'count'=>$count, 'userId' => $userId, 'more' => $morePhotos);
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

        $userId = Crypt::decrypt($request->Input('userId'));
        $myFloras = Photo::whereMonth('dateTimeDigitized', '=', $month)->where('userId', '=', $userId)->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get(['floraName','dateTimeDigitized','filePath','fileName', 'thumbnailFileName']);
        $othersFloras = Photo::whereMonth('dateTimeDigitized', '=', $month)->where('userId', '!=', $userId)->leftjoin('user_infos', 'user_infos.openid', '=','photos.userId')->orderBy('dateTimeDigitized', 'desc')->groupBy('floraName')->get(['photos.floraName','photos.dateTimeDigitized','photos.filePath','photos.fileName', 'photos.thumbnailFileName', 'user_infos.nickName']);

        //$test = Photo::whereMonth('dateTimeDigitized', '=', $month)->where('userId', '!=', $userId)->leftjoin('user_infos', 'user_infos.openid', '=','photos.userId')->orderBy('dateTimeDigitized', 'desc')->get(['photos.floraName','photos.dateTimeDigitized','photos.filePath','photos.fileName', 'photos.thumbnailFileName', 'user_infos.nickName']);

        $floraNames = array();
        foreach($myFloras as $myflora) {
            $floraNames[] = $myflora->floraName;
        }

        $extraFloras = [];
        foreach ($othersFloras as $flora) {
            $key = array_search($flora->floraName, $floraNames);

            if ($key === false)
            {
                array_push($extraFloras, $flora);
            }
        };
        
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
