@extends('emails.layout')
@section('content')
<div  style="display: table-cell; vertical-align: middle; ">
   <div class="main-wrapper" style="border:1px solid #eee; border-top: 3px solid #009de4 !important; min-height: 400px;  margin: 0 auto; background-color: #fff; ">
      <div>
         <div  style="padding: 28px 0px; padding-bottom:0 ">
            <div  style="padding: 0px 60px;">
               <h4 style="color: #009de4 !important; font-size: 18px">
                 Hello  {{$name}}
               </h4>
                <p  style="font-size: 13px; letter-spacing: 0px; line-height: 1.3; font-family: sans-serif;">Your new login password is: {{$password}}<span></span>
                </p>
            </div>
         </div>
      </div>
   <div>
</div>
@endsection