<?php
class DBConnection {
	private $news_db = null;
	private $drupal_db = null;

	private function __construct(){
		try {
			//production
            		//$this->handle = new PDO('mysql:host=v3.onebrick.org;dbname=newsletter',"NewsletterTool","1bnews");
			//dev
            		$this->news_db = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=newsletter',"NewsletterTool","1bnews");
            		$this->drupal_db = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=drupal7',"drupal7","1_brick!");
		} catch (PDOException $e) {
			echo "<pre>" . $e . "</pre>";
			echo "Database error.  Error connecting with pdo"; 
		}
	}	

	public static function getInstance(){
		static $d = null;
		if($d == null){
			$d = new DBConnection();
		}
				
		return $d;
	}

  public function __clone() {}
  
	public function getNewsletterDb(){
		return $this->news_db;
	}

	public function getDrupalDb(){
		return $this->drupal_db;
	}
}
?>
