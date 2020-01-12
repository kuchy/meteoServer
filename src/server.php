<?php
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/../vendor/autoload.php';


class Chat implements MessageComponentInterface
{
  private $connect;
  protected $clients;
  private $fullMessage;
  private $wundergroundConfig;
	private $mysqlConfig;

	public function __construct()
  {
    $this->fullMessage = '';
    $this->clients = new \SplObjectStorage;

    // WU config
	  $wConfig = file_get_contents(__DIR__ . "/../etc/wunderground.json");
	  if (!empty($wConfig)){
	  	$this->wundergroundConfig = json_decode($wConfig, true);
	  }

	  // Mysql config
	  $mysqlConfig = file_get_contents(__DIR__ . "/../etc/app.json");
	  if (!empty($mysqlConfig)){
		  $this->mysqlConfig = json_decode($mysqlConfig, true);
		  $dsn = $this->mysqlConfig['dsn'];
		  $username = $this->mysqlConfig['username'];
		  $passwd = $this->mysqlConfig['passwd'];
	  }

    try {
      $this->connect = new PDO($dsn, $username, $passwd);
    } catch (PDOException $pe) {
      die($pe->getMessage() . "\n");
    }

  }

  public function onOpen(ConnectionInterface $conn)
  {
    $this->clients->attach($conn);
    echo (new DateTime())->format('Y-m-d H:i:s')." New connection! ({$conn->resourceId})\n";
  }

  public function onMessage(ConnectionInterface $from, $msg)
  {
    $this->fullMessage .= $msg;
    $decoded = json_decode($this->fullMessage,true);
    if ($decoded){
      $qry = 'INSERT INTO `rtl_433` (`id`,`device_id`, `model`, `time`, `channel`, `temperature`, `humidity`, `original_data`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?);';
      $stmt = $this->connect->prepare($qry);
      $stmt->execute([
        $decoded['id'] ?? null,
        $decoded['model'] ?? '',
        $decoded['time'] ?? null,
        $decoded['channel'] ?? null,
        $decoded['temperature_C'] ?? null,
        $decoded['humidity'] ?? null,
        $this->fullMessage
      ]);
	    echo (new DateTime())->format('Y-m-d H:i:s')."value uploaded \n";

      if (isset($decoded['humidity']) && isset($decoded['temperature_C'])){
      	echo (new DateTime())->format('Y-m-d H:i:s')." sending to wunderground...";
      	// convert c to f
	      $f = ($decoded['temperature_C'] * 9/5) + 32;
      	$url = 'https://weatherstation.wunderground.com/weatherstation/updateweatherstation.php?ID='.$this->wundergroundConfig['id'].'&PASSWORD='.$this->wundergroundConfig['password'].'&dateutc=now&humidity='.$decoded['humidity'].'&action=updateraw&tempf='.$f;

	      $ch = curl_init($url);
	      curl_setopt($ch, CURLOPT_HEADER, true);
	      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	      curl_setopt($ch, CURLOPT_TIMEOUT,10);
	      curl_exec($ch);
	      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	      curl_close($ch);

	      if ($httpcode === 200){
	      	echo "uload successful \n";
	      } else{
		      echo "uload failed \n";
	      }
      }

      $this->fullMessage = '';
    }
  }

  public function onClose(ConnectionInterface $conn)
  {
    $this->clients->detach($conn);

    echo (new DateTime())->format('Y-m-d H:i:s')." Connection {$conn->resourceId} has disconnected\n";
  }

  public function onError(ConnectionInterface $conn, \Exception $e)
  {
    echo (new DateTime())->format('Y-m-d H:i:s')." An error has occurred: {$e->getMessage()}\n";

    $conn->close();
  }
}


$server = IoServer::factory(
  new Chat(),
  8080
);

$server->run();
