<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\User;
use App\Review;

class ReviewController extends Controller
{
    public function setReview(Request $request)
    {
        
        $user = User::find(Auth::user()->id);

        if($user == null){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'tidak ada user yang dimaksud',
                'error' => (Object)[],
            ],200);
        }

        
        $review = Review::where('id_pembelian', $request->id)->first();
        if($review == NULL){
            $review_kapal = new Review();
            $review_kapal->id_user = $user->id;
            $review_kapal->id_pembelian = $request->id;
            $review_kapal->review = $request->review;
            $review_kapal->score = $request->rating;
            $review_kapal->save();
        }else if($review != NULL){
            $review->id_user = $user->id;
            $review->id_pembelian = $request->id;
            $review->review = $request->review;
            $review->score = $request->rating;
            $review->update();
        }


        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[] 
        ],200);

    }

    
}
