<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head profile="http://www.w3.org/2005/10/profile">
  <!-- <title>Lazing out with YouTube Video APIs one fine Saturday afternoon...</title> -->
  <title>@open arms.. .. ..</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta content = "Aditya Sakhuja web site designing and data processing and web internet search enthusiast joining Yahoo! search. 
     He is a graduate from Georgia Institute of Technology"  name = "description">
  
  <meta content="Aditya Sakhuja" name="author">
  <meta content=" global" name="distribution">
  <meta content="1 days" name="revisit-after">  
  <meta content="Aditya Sakhuja, sakhuja, aditya, adita, sakuja, yahoo, Web Search,data processing, databases, 
  hiranandani gardens powai mumbai, sinhgad college of engineering, LTIT, SCOE, Mission College, CA" 
  name="keywords">
  <meta content = "INDEX, FOLLOW" name ="robots">

  <!-- **** layout stylesheet **** -->
  <link rel="stylesheet" type="text/css" href="style/style.css" />
  
  <link rel="icon"
      type="image/ico" 
      href="/images/favicon.ico"/>

  <link rel="stylesheet" type="text/css" href="style/orange.css" />

  <!-- Youtube video APIs -->
  <link href="YoutubeVideoBrowser/video_browser.css" type="text/css" rel="stylesheet"/>
  <script src="YoutubeVideoBrowser/video_browser.js" type="text/javascript"></script>
  <!-- end -->
  <script type="text/javascript" src="http://maps.yahooapis.com/v3.5/fl/javascript/apiloader.js?appid=YD-
		S1h1EBU_JXxsSBSJhQfTvQ--">
  </script>

  <script type="text/javascript">
	var addresses;
	var map;
	
   function init() {
		    addresses = new Array("655 South FairOaks Ave, Sunnyvale, CA 94086");
			 map = new Map("mapContainer", "YD-S1h1EBU_JXxsSBSJhQfTvQ--", "Sunnyvale CA", 8);
			map.addEventListener(Map.EVENT_INITIALIZE, onMapInit);
			map.addEventListener(Map.EVENT_MARKER_GEOCODE_SUCCESS, onMarkerGeocode);
   }
   
	function onMapInit(eventObj) {
   	 map.addTool( new PanTool(), true );
    		for(var i=0; i<addresses.length; i++) {
        		var marker = new CustomPOIMarker(addresses[i], '', 'Yahoo! Maps', '0xFF0000', '0xFFFFFF');
        		map.addMarkerByAddress( marker, addresses[i] );
    		}
    		map.addWidget( new ZoomBarWidget() );
	}

	function onMarkerGeocode(eventObj) {
   	 var geocodeResponse = eventObj.response;
	}
	
</script>

</head>

<body onload="init();">
  <div id="main">
    <div id="links">
      <!-- **** INSERT LINKS HERE **** -->
      | <a href="/main.php?pgid=11">feedback</a> | 
    </div>
    <div id="logo"><h1>@Open Arms.. .. ..</h1><h2>&quot;Everything done with arms wide open ~.. .. ..&quot;</h2></div>
    <div id="menu">
      <ul>
        <!-- **** INSERT NAVIGATION ITEMS HERE (use id="selected" to identify the page you're on **** 
        -->
      	  <li><a href="index.html">Home</a></li>
          <li><a href="main.php?pgid=1">Projects</a></li>
		  <li><a href="main.php?pgid=2">Interests</a></li>
		  <li><a href="tilter/">Memories</a></li>
		  <li><a href="main.php?pgid=8">Community</a></li>
	      <li><a href="main.php?pgid=10">Affiliations</a></li>
		  <li><a href="main.php?pgid=5">Favs lirxbase</a></li>
          <li><a href="main.php?pgid=6">Favs brainutilizers</a></li>
		  <li><a href="ytvbp_main.php?pgid=12">YouTube!</a></li>
        </ul>
    </div>
    <div id="content">

      <div id="column1">
              <div class="sidebaritem">
  			<h1>Search<input type="text" name="query" value="" size="15" class="txtbox">
        		<!-- <button  type="submit" name="btn_search" value="Site search" class="btn">Search</button> -->
    		</h1>
    		<h1></h1>
		</div>
        <div class="sidebaritem">
          <h1>whereAMi</h1>
          <div id="mapContainer"></div>
        </div>
        <div class="sidebaritem">
          <h1>placeholder</h1>
          <div class="sbilinks">
            <!-- **** INSERT ADDITIONAL LINKS HERE **** -->
            <ul>
            </ul>
          </div>
        </div>

        <div class="sidebaritem">
          <h1>Quote to ponder on</h1>
          <!-- **** INSERT OTHER INFORMATION HERE **** -->
         <p>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" type="application/x-shockwave-flash" width="170px" height="423px" id="InsertWidget_68f41530-7b20-41d7-9d8b-b0aba69f84c3" align="middle"><param name="movie" value="http://widgetserver.com/syndication/flash/wrapper/InsertWidget.swf"/><param name="quality" value="high" /><param name="wmode" value="transparent" /><param name="menu" value="false" /><param name="flashvars" value="r=2&appId=68f41530-7b20-41d7-9d8b-b0aba69f84c3" /> <embed src="http://widgetserver.com/syndication/flash/wrapper/InsertWidget.swf"  name="InsertWidget_68f41530-7b20-41d7-9d8b-b0aba69f84c3"  width="170px" height="423px" quality="high" menu="false" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent" align="middle" flashvars="r=2&appId=68f41530-7b20-41d7-9d8b-b0aba69f84c3" /></object>
         </p>
         </div>
		 <div class="sidebaritem" align="center">        
					<!-- START SITE EXPLORER BADGE CODE -->
						<script>yseBadgePref={s:1,t:1};</script>
						<script src="http://sec.yimg.com/us.js.yimg.com/lib/se4/ysch_se_badge_200808081045.js"></script>
					<!-- END SITE EXPLORER BADGE CODE -->
			</div>      	
      </div>
      <div id="column2">

  <?php

     	   if ($_GET["pgid"] == "0"){
     		   include("intro.html");
       	   }
     	   else if ($_GET["pgid"] == "1"){
				include("projects.php");     	 		
		    }
     	    else if ($_GET["pgid"] == "2"){
					include("interests.php");     	 		
     			}
     		  	else if ($_GET["pgid"] == "3"){
					include("resume.php");     	 		
     			  }
     		  		else if ($_GET["pgid"] == "4"){
						include("utilities.php"); 
					}
					else if ($_GET["pgid"] == "5"){
							include("lyrics.php");     	 		
 	    				}
     			  		 else if ($_GET["pgid"] == "6"){
							include("brainteasers.php"); 
						 }
	      			  	 else if ($_GET["pgid"] == "7"){
								include("about.php"); 
							 }
	 	      			  	 else if ($_GET["pgid"] == "9"){
								include("search.php"); 
							 }
							  else if ($_GET["pgid"] == "8"){
									include("ponder.php");
							       }
								   else if ($_GET["pgid"] == "10"){
									     include("affilations.php");
								       }
									   else if ($_GET["pgid"] == "11"){
										 include("feedback.php");
								       }
									   else if ($_GET["pgid"] == "12"){
									     // echo "Starting YouTube..";
										 include("./YouTubeVideoBrowser/index.php");
								       }
							           else{
									     include("projects.php");								 
							           }
   ?>
      </div>
    </div>
    <div id="footer">
      copyright &copy; 2010 Aditya Sakhuja | <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> | <a href="http://www.dcarter.co.uk">design by dcarter</a>
    </div>
  </div>
</body>
</html>
