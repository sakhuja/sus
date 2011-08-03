<h2>Using YQL to Access the Upcoming API</h2>
<!--
<form name='upcoming_form'>
Location: <input name='location' id='location' type='text' size='20'/><br/>
Event: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name='event' id='event' type='text' size='20'/><br/>
<p><button id='find_event'>Find Event</button></p>
</form>

<script>
  // Attach event handler to button
  document.getElementById("find_event").addEventListener("click",find_event,false);
  // Get user input and submit form
  function find_event(){
    document.upcoming_form.event.value = document.getElementById('event').value || "music";
    document.upcoming_form.location.value = document.getElementById('location').value || "San Francisco";
    document.upcoming_form.submit();
} 
</script>
-->
<?php
  $BASE_URL = "https://query.yahooapis.com/v1/public/yql";

  // if(isset($_GET['event']) && isset($_GET['location'])){
    $location = $_GET['location'];
    $query = $_GET['event'];
    $events="";
     
    // Form YQL query and build URI to YQL Web service
    // $yql_query = "select * from upcoming.events where location='$location' and search_text='$query'";
    // $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json";
    $yql_query_url = $BASE_URL . "?q=select * from music.artist.search where keyword='Madonna'" ."&format=json";
    $yql_query_url = "https://query.yahooapis.com/v1/public/yql?q=select * from music.track.search where keyword='Madonna'&format=json";
    echo $yql_query_url;

    // Make call with cURL
    $session = curl_init($yql_query_url);
    echo $session;
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
    $json = curl_exec($session);
    echo "    JSON : ".$json;
    // Convert JSON to PHP object 
    $phpObj =  json_decode($json);

    // Confirm that results were returned before parsing
    if(!is_null($phpObj->query->results)){
      // Parse results and extract data to display
      foreach($phpObj->query->results->event as $event){
        $events .= "<div><h2>" . $event->name . "</h2><p>";
        $events .= html_entity_decode(wordwrap($event->description, 80, "<br/>"));
        $events .="</p><br/>$event->venue_name<br/>$event->venue_address<br/>";
        $events .="$event->venue_city, $event->venue_state_name";
        $events .="<p><a href=$event->ticket_url>Buy Tickets</a></p></div>";
      }
    }
    // No results were returned
    if(empty($events)){
      $events = "Sorry, no events matching $query in $location";
    }
    // Display results and unset the global array $_GET
    echo $events;
    unset($_GET);
  // }
?>

