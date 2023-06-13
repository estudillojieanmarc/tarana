<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Attendance</title>
    <style>
          @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;900&display=swap');
        *{
            font-family: 'Roboto', sans-serif;
        }
        body {
            width: 100%;
       }
        header {
            width: 100%;
            margin-top: -2rem;
        }

        h1 {
            margin: 0;
            font-size: 20px;
        }

       .section {
            margin-bottom: 20px;
       }

       .body{
        margin-top: 1rem;
       }

       .section-title {
         font-size: 15px;
         font-weight: bold;
         margin-bottom: 10px;
         text-transform: uppercase;
       }

       .subsection-title {
         font-size: 14px;
         font-weight: bold;
         margin-bottom: 10px;
       }

       .item {
         margin-bottom: 5px;
       }

       .item-title {
         font-weight: bold;
         display: inline-block;
         width: 160px;
       }

       .item-title2{
        font-size: 14px;
        text-transform: uppercase;
         font-weight: bold;
         display: inline-block;
         width: 200px;
       }

       .item-title3{
        font-size: 14px;
        text-transform: uppercase;
         font-weight: bold;
         display: inline-block;
         width: 130px;
       }

       .item-content {
         display: inline-block;
       }

       .item-content2{
         display: inline-block;
       }

       .th1{
        width: 20%;
       }

       .scpiLogo{
        width: 40%;
       }
       .th2{
        font-weight: bold;
        line-height: 20px;
        margin-right: 2rem;
       }
       .th2 h5{
        font-weight: 500;
        letter-spacing: 1px;
        text-align: center;
        font-size:12px;
       }

       .th3{
        float: right;
        padding-left: 2rem;
        width: 20%;
       }
       .taranaLogo{
        margin-top: -10px;
        width: 100%;
       }

       .opeSummary{
        margin-top: 1rem;
       }
   </style>
</head>
<body>
    <header>
        <table>
            <tr>
                <th class="th1"><img class="scpiLogo" src="./assets/frontend/scpi.webp"></th>
                <th class="th2">
                    <h5>Subic Consolidated Projects, Inc.</h5>
                    <h5 style="margin: 6px 2rem">Bldg. 867 Remy Field Cmpd Canal Rd CBD Area, Olongapo, Philippines</h5>
                    <h5>scpi.ph@gmail.com | (047) 252 1877</h5>
                </th>
                <th class="th3"><img class="taranaLogo" src="./assets/frontend/logo.webp"></th>
            </tr>
        </table>
    </header>
    <div class="section body">
        <div class="section-title">Operation Summary:</div>
          <div class="item">
              <span class="item-title">Operation Id:</span>
              <span class="item-content">{{$operationId}}</span>
          </div>
          <div class="item">
              <span class="item-title">Ship Name:</span>
              <span class="item-content">{{$shipName}}</span>
          </div>
          <div class="item">
              <span class="item-title">Ship Carry:</span>
              <span class="item-content">{{$shipCarry}}</span>
          </div>
          <div class="item">
              <span class="item-title">Operation Start:</span>
              <span class="item-content">{{date('F j, Y | g:i a',strtotime($operationStart))}}</span>
            </div>
            <div class="item">
                <span class="item-title">Operation End:</span>
                <span class="item-content">{{date('F j, Y | g:i a',strtotime($operationEnd))}}</span>
          </div>
          <div class="item">
              <span class="item-title">Workers Needed:</span>
              <span class="item-content">{{$totalWorkers}} Total</span>
          </div>
          <div class="item">
              <span class="item-title">Workers Joined:</span>
              <span class="item-content">{{$totalWorkers - $slot}} Total</span>
          </div>
        </div>
    <div class="section">
        <div class="section-title">Project Workers Attendance</div>
    </div>
    <table class='table table-bordered text-center align-middle'>
        <thead>
            <tr>
                <th scope='col'>No.</th>
                <th scope='col'>Project Workers</th>
                <th scope='col'>Age</th>
                <th scope='col'>Signature</th>
            </tr>
        </thead>
        <tbody>
          @foreach($data as $count => $certainData)
          {{$count = $count + 1}}
            <tr>
                <td>{{$count}}.</td>
                <td>{{$certainData->firstname}} {{$certainData->lastname}} {{$certainData->extention}}</td>
                <td>{{$certainData->age}} years old </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
