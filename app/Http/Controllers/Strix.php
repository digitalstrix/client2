<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Categorie;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Dirape\Token\Token;
use Illuminate\Support\Facades\Hash;


class Strix extends Controller
{
    public function register(Request $request)
    {
        $rules =array(
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "",
            "user_type" => "required|in:user,admin,support",
            "employee_id" => "",
            "department_name" => "in:marketing,sales,technical"
        );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            $user = new User();
            $user->name=$request->name;
            $user->email=$request->email;
            $user->user_type=$request->user_type;
            $user->employee_id=$request->employee_id;
            $user->department_name=$request->department_name;
            $user->user_token = (new Token())->Unique('users', 'user_token', 60);
            $user->password=Hash::make($request->password);
            $result= $user->save();
            if ($result) {
                $token = $user->createToken('my-app-token')->plainTextToken;
                $response = [
                'user' => $user,
                'bearer-token' => $token,
                "message"=>"User created successfully"
            ];
                return response($response, 201);
            } else {
                return response(["status" =>"failed", "message"=>"User is not created"], 401);
            }
        }
    }

    public function login(Request $request)
    {
        $rules =array(
            "email" => "",
            "password" => "required|min:6",
            "user_type" => "required|in:user,support,admin",
            "employee_id" => "",
        );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {

            if($request->user_type=='user') {
                if (!User::where('email', $request->email)->first()) {
                    return response(["status" =>"failed", "message"=>"User is not Registered or Invaild User Type"], 401);
                }
                $user = User::where('email',$request->email)->first();
                if(!Hash::check($request->password, $user->password)){
                    return response(["status" =>"failed", "message"=>"Incorrect Password"], 401);
                }
                else{
                if ($user) {
                    $token = $user->createToken('my-app-token')->plainTextToken;
                    $response = [
                    'user' => $user,
                    'bearer-token' => $token,
                    "message"=>"User is Logged In"
                            ];
                    return response($response, 200);
                }
            }
            }
            elseif($request->user_type== 'support'){
                $user = User::where('employee_id',$request->employee_id)->first();
                if(!Hash::check($request->password, $user->password)){
                    return response(["status" =>"failed", "message"=>"Incorrect Password"], 401);
                }
                if ($user) {
                    $token = $user->createToken('my-app-token')->plainTextToken;
                    $response = [
                    'user' => $user,
                    'bearer-token' => $token,
                    "message"=>"User is Logged In"
                ];
                    return response($response, 200);
                }
            }   
        }
    }
    public function addCategory(Request $request)
    {
        $rules =array(
        
            "name" => "required|unique:categories",
            "token" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!User::where('user_token',$request->token)->where('user_type','admin')->first()){
                return response(["status" => "error", "message" =>"Categorie is not created | False Token"], 401);
            }
            $user = new Categorie();
            $user->name = $request->name;
            $result = $user->save();
            if ($result) {
                $response = [
                             'Status' => 'success',
                             'message' => 'Categorie is successfully created',
                             'data' => $user,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Categorie is not created"], 401);
            }
        }
    }
    public function addQuestion(Request $request)
    {
        $rules =array(
            "question" => "required",
            "categorie_id" => "required",
            "token" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!User::where('user_token',$request->token)->where('user_type','user')->first()){
                return response(["status" => "error", "message" =>"Question is not created | False Token"], 401);
            }
            if(!Categorie::where('id',$request->categorie_id)->first()){
                return response(["status" => "error", "message" =>"Categorie Does not existed"], 401);
            }
            $temp = User::where('user_token',$request->token)->where('user_type','user')->first();
            $user = new Question();
            $user->question = $request->question;
            $user->category_id = $request->categorie_id;
            $user->user_id = $temp->id;
            if ($request->hasFile('image_uri')) {
                $file = $request->file('image_uri')->store('public/question/image');
                $user->image_uri = $file;
            }
            if ($request->hasFile('audio_uri')) {
                $file = $request->file('audio_uri')->store('public/question/audio');
                $user->audio_uri = $file;
            }
            $result = $user->save();
            if ($result) {
                $response = [
                             'Status' => 'success',
                             'message' => 'Question is successfully created',
                             'data' => $user,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Categorie is not created"], 401);
            }
        }
    }
    public function addAnswer(Request $request)
    {
        $rules =array(
            "answer" => "required",
            "question_id" => "required",
            "token" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!User::where('user_token',$request->token)->orwhere('user_type','support')->orwhere('user_type','admin')->first()){
                return response(["status" => "error", "message" =>"Answer is not created | False Token"], 401);
            }
            if(!Question::where('id',$request->question_id)->first()){
                return response(["status" => "error", "message" =>"Question Does not existed"], 401);
            }
            $temp = User::where('user_token',$request->token)->first();
            $user = Question::where('id',$request->question_id)->first();
            $data = new Answer();
            $data->answered_by = $temp->id;
            $data->answer = $request->answer;
            $data->question_id = $request->question_id;
            $result = $data->save();
            if ($result) {
                $response = [
                             'Status' => 'success',
                             'message' => 'Answer is successfully created',
                             
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Answer is not created"], 401);
            }
        }
    }
    public function all(Request $request)
    {
        $rules =array(
            "token" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!User::where('user_token',$request->token)->orwhere('user_type','support')->orwhere('user_type','admin')->first()){
                return response(["status" => "error", "message" =>"Answer is not Fetched | False Token"], 401);
            }
            $user = Question::all();
            $main = array();
            foreach($user as $key){
                $temp = array();
                $answer = Answer::where('question_id',$key->id)->get();
                $temp[] = (['Question' => $key, 'answers' => $answer]);
                $main[] = $temp;
            }
            if ($user) {
                $response = [
                             'Status' => 'success',
                             'message' => 'All Question Answers',
                             'data' => $main,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Answer is not Fetched"], 401);
            }
        }
    }
    public function answeredBy(Request $request)
    {
        $rules =array(
            "token" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!User::where('user_token',$request->token)->orwhere('user_type','support')->orwhere('user_type','admin')->first()){
                return response(["status" => "error", "message" =>"Answer is not fetched | False Token"], 401);
            }
            $temp = User::where('user_token',$request->token)->first();
            $user = Question::where('answered_by',$temp->id)->get();

            if ($user) {
                $response = [
                             'Status' => 'success',
                             'message' => 'All Question Answers',
                             'data' => $user,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Answer is not fetched"], 401);
            }
        }
    }
    public function askedBy(Request $request)
    {
        $rules =array(
            "token" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!User::where('user_token',$request->token)->first()){
                return response(["status" => "error", "message" =>"Answer is not Fetched | False Token"], 401);
            }
            $temp = User::where('user_token',$request->token)->first();
            $user = Question::where('user_id',$temp->id)->get();
            $main = array();
            foreach($user as $key){
                $temp = array();
                $answer = Answer::where('question_id',$key->id)->get();
                $temp[] = (['Question' => $key, 'answers' => $answer]);
                $main[] = $temp;
            }
            if ($user) {
                $response = [
                             'Status' => 'success',
                             'message' => 'All Question Answers',
                             'data' => $main,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Answer is not Fetched"], 401);
            }
        }
    }
    public function byCategorie(Request $request)
    {
        $rules =array(
            "token" => "required",
            "categorie_id" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!Categorie::where('id',$request->categorie_id)->first()){
                return response(["status" => "error", "message" =>"Categorie Does not existed"], 401);
            }
            if(!User::where('user_token',$request->token)->first()){
                return response(["status" => "error", "message" =>"Answer is not Fetched | False Token"], 401);
            }
            $temp = User::where('user_token',$request->token)->first();
            $user = Question::where('category_id',$request->categorie_id)->get();
            $main = array();
            foreach($user as $key){
                $temp = array();
                $answer = Answer::where('question_id',$key->id)->get();
                $temp[] = (['Question' => $key, 'answers' => $answer]);
                $main[] = $temp;
            }
            if ($user) {
                $response = [
                             'Status' => 'success',
                             'message' => 'All Question Answers',
                             'data' => $main,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Answer is not Fetched"], 401);
            }
        }
    }
    public function QADetails(Request $request)
    {
        $rules =array(
            "token" => "required",
            "question_id" => "required",
         );
        $validator= Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        } else {
            if(!Question::where('id',$request->question_id)->first()){
                return response(["status" => "error", "message" =>"Question Does not existed"], 401);
            }
            if(!User::where('user_token',$request->token)->first()){
                return response(["status" => "error", "message" =>"Answer is not Fetched | False Token"], 401);
            }
            $user = Question::where('id',$request->question_id)->get();
            $main = array();
            foreach($user as $key){
                $temp = array();
                $answer = Answer::where('question_id',$key->id)->get();
                $temp[] = (['Question' => $key, 'answers' => $answer]);
                $main[] = $temp;
            }
            if ($user) {
                $response = [
                             'Status' => 'success',
                             'message' => 'Question Answers Details have been fetched successfully',
                             'data' => $main,
             ];
                return response($response, 201);
            } else {
                return response(["status" => "error", "message" =>"Answer is not Fetched"], 401);
            }
        }
    }


    
}