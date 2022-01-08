@extends('frontend.master')

@section('content')

{{-- store below file in public/firebase-messaging-sw.js  --}}
{{-- <script>
    // Add Firebase products that you want to use
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

// Firebase SDK
firebase.initializeApp({
    apiKey: "AIzaSyBVT5vW4zm4CgMBjGSMWwgjpJ3AlzNrmwc",
    authDomain: "baraotdeal.firebaseapp.com",
    projectId: "baraotdeal",
    storageBucket: "baraotdeal.appspot.com",
    messagingSenderId: "724946596498",
    appId: "1:724946596498:web:539c5596b6b24add12ec4b",
    measurementId: "G-DP13F0P3F2"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    console.log("Message has received : ", payload);
    const title = "First, solve the problem.";
    const options = {
        body: "Push notificaiton!",
        icon: "/icon.png",
    };
    return self.registration.showNotification(
        title,
        options,
    );
});
</script>

upto here  --}}









<!-- Firebase App (the core Firebase SDK) is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase.js"></script>

<script>
    var firebaseConfig = {
        apiKey: "AIzaSyBVT5vW4zm4CgMBjGSMWwgjpJ3AlzNrmwc",
    authDomain: "baraotdeal.firebaseapp.com",
    projectId: "baraotdeal",
    storageBucket: "baraotdeal.appspot.com",
    messagingSenderId: "724946596498",
    appId: "1:724946596498:web:539c5596b6b24add12ec4b",
    measurementId: "G-DP13F0P3F2"
    };

    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    function initNotification() {
        messaging
            .requestPermission().then(function () {
                return messaging.getToken()
            }).then(function (response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: '{{ route("save-device.token") }}',
                    type: 'POST',
                    data: {
                        token: response
                    },
                    dataType: 'JSON',
                    success: function (response) {
                        console.log('Device token saved.');
                    },
                    error: function (error) {
                        console.log(error);
                    },
                });
            }).catch(function (error) {
                console.log(error);
            });
    }

    messaging.onMessage(function (payload) {
        const title = payload.notification.title;
        const options = {
            body: payload.notification.body,
            icon: payload.notification.icon,
        };
        new Notification(title, options);
    });


</script>


{{-- ask for permission after login  --}}
@auth
<script>
initNotification()
</script>
    
@endauth











{{-- push controller  --}}

<?php
  
  namespace App\Http\Controllers\backend;
  use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class PushnotifyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
  
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
    }
  
    /** 
     * Write code on Method
     *
     * @return response()
     */
    public function saveToken(Request $request)
    {
        User::find(1)->update(['device_token'=>$request->token]);
        return response()->json(['token saved successfully.']);
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        $SERVER_API_KEY = 'AAAAqMovMpI:APA91bH_cfQpg0cO99oyTh8HkMG6UBQ3QeUxBo0p_JufBjwDl36Wjrcpa6A48X6-zJicrtUG_bi7A8hpST9S2tgjrCP1K-WrryTwxx-ya86maYkgExHaeUM9_MDKo3v6eWbv1ARw6HZ2';
  
        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => 'Hello world',
                "body" => "djfjg ",  
            ]
        ];
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
      
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
               
        $response = curl_exec($ch);
  
        dd($response);
    }
}


// route file 

Route::post('/save-device.token','backend\PushnotifyController@saveToken')->name('save-device.token');
Route::get('/sendnotification','backend\PushnotifyController@sendNotification')->name('send.notification');
Route::get('/abcd',function(){
    return view('frontend.push');
});
@endsection