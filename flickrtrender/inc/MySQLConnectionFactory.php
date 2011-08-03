<?php
class MySQLConnectionFactory {
    static $SERVERS = array(
    array(
        'host' => 'localhost',
        'username' => 'nextgen',
        'password' => 'r1chn3ss',
        'database' => 'siefMetadata')
    );

    public static function create() {
    // Figure out which connections are open, automatically opening any connections
    // which are failed or not yet opened but can be (re)established.
    $cons = array();
    for ($i = 0, $n = count(MySQLConnectionFactory::$SERVERS); $i < $n; $i++) {
        $server = MySQLConnectionFactory::$SERVERS[$i];
        
        //Configure DATABASE hostname and 
        $server['host']=get_cfg_var("DBHOST");
        $server['database']=get_cfg_var("DATABASE");
        
        $con = mysql_connect($server['host'], $server['username'], $server['password']);
        if (!($con === false)) {
           if (true == array_key_exists('database', $_REQUEST)) {
              $db = mysql_real_escape_string($_REQUEST['database']);
              $server['database'] = $db;
           }
           if (mysql_select_db($server['database'], $con) === false) {
              echo('Could not select database: ' . mysql_error());
              continue;
           }
           $cons[] = $con;
        }
    }
    // If no servers are responding, throw an exception.
    if (count($cons) == 0) {
        throw new Exception
        ('Unable to connect to any database servers - last error: ' . mysql_error());
    }
    // Pick a random connection from the list of live connections.
    $serverIdx = rand(0, count($cons)-1);
    $con = $cons[$serverIdx];
    // Return the connection.
    return $con;
    }
}
?>
