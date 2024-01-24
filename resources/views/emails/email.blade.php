<!DOCTYPE html>
<html>
<head>
    <title>ItsolutionStuff.com</title>
</head>
<body>
    <h1>{{ $mailData['event_title'] }}</h1>
    
    <p> {{ $mailData['startdate'] }} To {{ $mailData['enddate'] }} </p>
    
    @if ($mailData['imagename'] != null)
    <p>Event Image</p>
    <img src="https://famebusinesssolutions.com/bendcc/public/events/{{ $mailData['imagename'] }}" alt="Event Image">
    @endif

  
    <p> Description </p>
    <p>{{ $mailData['event_description'] }}</p>

 
    <p>Thank you</p>
</body>
</html>