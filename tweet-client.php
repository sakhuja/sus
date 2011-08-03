<?php 

include 'Twitter.php';

$twitter = new Twitter("sakhuja","20");
$tweets = $twitter->getTweets();

echo "<br><br><b>Latest Tweets! </b>";
// Looping over the 'tweets' object for user 'sakhuja'
echo "<br><br><br><ul>";
foreach ($tweets as $tweet) {
	echo "<li>";
	echo $tweet['status'] . ' - ' . $tweet['pDate'];

}

echo '</ul>';

?>