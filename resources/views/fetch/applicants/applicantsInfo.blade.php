@foreach ($data as $item)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SCPI Employees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">    <title>Operation {{$item->operationId}}</title>
    <style>
        body {
          font-family: Arial, sans-serif;
        }
    
        .header {
          text-align: center;
          margin-bottom: 20px;
        }
    
        .name {
          font-size: 24px;
          font-weight: bold;
        }
    
        .contact-info {
          font-size: 14px;
          margin-bottom: 10px;
        }
    
        .section {
          margin-bottom: 20px;
        }
    
        .section-title {
          font-size: 18px;
          font-weight: bold;
          margin-bottom: 10px;
        }
    
        .subsection-title {
          font-size: 16px;
          font-weight: bold;
          margin-bottom: 10px;
        }
    
        .item {
          margin-bottom: 5px;
        }
    
        .item-title {
          font-weight: bold;
          display: inline-block;
          width: 120px;
        }
    
        .item-content {
          display: inline-block;
        }
      </style>
</head>
<body>
    <div class="header">
      <div class="name">Project Workers Information</div>
        <div class="contact-info">Subic Consolidated Projects, Inc.</div>
      </div>
      </div>
      <div class="section">
        <div class="section-title">Profile Summary</div>
        <div class="item">
          <span class="item-title">Lastname:</span>
          <span class="item-content">{{$item->lastname}}</span>
        </div>
        <div class="item">
            <span class="item-title">Firstname:</span>
            <span class="item-content">{{$item->firstname}}</span>
          </div>
          <div class="item">
            <span class="item-title">Middlename:</span>
            <span class="item-content">{{$item->middlename}}</span>
          </div>
          <div class="item">
            <span class="item-title">Extention:</span>
            <span class="item-content">{{$item->extention}}</span>
          </div>
          <div class="item">
            <span class="item-title">Gender:</span>
            <span class="item-content">{{$item->Gender}}</span>
          </div>
        <div class="item">
          <span class="item-title">Status:</span>
          <span class="item-content">{{$item->status}}</span>
        </div>
        <div class="item">
          <span class="item-title">Birthday:</span>
          <span class="item-content">{{date('F j, Y',strtotime($item->birthday))}}</span>
        </div>
        <div class="item">
          <span class="item-title">Age:</span>
          <span class="item-content">{{$item->age}} years old</span>
        </div>
        <div class="item">
          <span class="item-title">Position:</span>
          <span class="item-content">{{$item->position}} </span>
        </div>
      </div>
      <div class="section">
        <div class="section-title">Contact Information</div>
        <div class="item">
          <span class="item-title">Phone:</span>
          <span class="item-content">{{$item->phoneNumber}}</span>
        </div>
        <div class="item">
          <span class="item-title">Email:</span>
          <span class="item-content">{{$item->emailAddress}}</span>
        </div>
        <div class="item">
          <span class="item-title">Address:</span>
          <span class="item-content">{{$item->address}}</span>
        </div>
      </div>
      <div class="section">
        <div class="section-title">Personal Id</div>
        <img src="{{ public_path($item->personal_id) }}" alt="Image" class="img-thumbnail" style="width:35%; margin-top:2rem;">
        <img src="{{ public_path($item->personal_id2) }}" alt="Image" class="img-thumbnail" style="width:35%; margin-top:2rem;">
      </div>
</body>
</html>
@endforeach