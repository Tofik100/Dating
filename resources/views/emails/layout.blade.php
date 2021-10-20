<!doctype html>
<html lang="en" class="fullscreen-bg">
   <head>
      <title>Datting App</title>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
      <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
      <!-- ICONS -->
      <style>
         .main-wrapper{width: 50%}
         @media (min-width: 768px) and (max-width: 1024px) {
         .main-wrapper{width: 70%}
         }
         @media (min-width: 481px) and (max-width: 767px) {
         .main-wrapper{width: 90%}
         }
         @media (min-width: 320px) and (max-width: 480px) { 
         .main-wrapper{width: 100%}
         }
      </style>
   </head>
   <body style="height: 100%; background: #f1f6f7; font-family: sans-serif; font-size: 15px; color: #676a6d; margin: 0;">
      <div>
         <div  style="position: absolute; width: 100%; height: 100%; display: table;">
             <!-- main content  -->
              @yield('content')   
              <!-- main content end -->
            <div style="text-align: left; border-top: 1px solid #eee; margin-top: 40px; padding: 10px 60px">
               <table style="width: 100%">
                  <tr>
                     <td colspan="3">
                        <p style="margin-top: 12px; font-size: 12px; text-align: center"><a href="mailto:info@datting.com" style="color:#888">info@datting.com</a> <br/> </p>
                     </td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
      <!-- END WRAPPER -->
</body>
</html>