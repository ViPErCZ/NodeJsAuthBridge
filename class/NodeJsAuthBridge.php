<?php
/**
 * Created by PhpStorm.
 * User: viper
 * Date: 23.1.2015
 * Time: 9:33
 */

namespace Nodejs;

/**
 * Class NodeBridge
 * @package Nodejs
 */
class NodeJsAuthBridge {

	/** @var string */
	protected $url = "http://localhost:3000";

    /** @var string */
    protected $path;

	/** @var string */
	protected $fields_string;

	/** @var string */
	protected $domain;

	/** @var array */
	protected $cookies = array();

	/** @var string */
	protected $login;

	/**
	 *
	 */
	public function __construct() {
		if (isset($_SESSION['remote'])) {
			$this->cookies = $_SESSION['remote'];
		}
		$this->domain = filter_input(INPUT_SERVER, "HTTP_HOST");
	}

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path) {
        $this->path = $path;
    }


	/**
	 * @param $post
	 */
	public function login($post) {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->login = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 50);
		setcookie("login", $this->login, 0, "/");
		//setcookie("login", $this->login);
		$post['hash'] = $this->login;

		//open connection
		$ch = curl_init();

		if ($post['password']) {
			$post['password'] = md5($post['password']);
		}
		$this->fields_string = http_build_query($post);

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $this->url . $this->path . "/login");
		curl_setopt($ch,CURLOPT_POST, count($post));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $this->fields_string);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE['PHPSESSID'] . ".txt");
		curl_setopt($ch, CURLOPT_COOKIEFILE, $_COOKIE['PHPSESSID'] . ".txt");
		curl_setopt($ch, CURLOPT_HEADER, 1);

		//execute post
		$result = curl_exec($ch);
		//var_dump($result);

		// get cookie
		preg_match('/^Set-Cookie:\s*([^;]*)/mi', $result, $m);

		if (isset($m[1]) && $result != "error") {
			parse_str($m[1], $this->cookies);
			$_SESSION['remote'] = $this->cookies;
            setcookie("connect.sid", $this->cookies['connect_sid'], 0, "/");
			//setcookie("connect.sid", $this->cookies['connect_sid']);
			$_COOKIE['test'] = "pokus";
		} else {
			unset($_COOKIE['login']);
			setcookie('login', null, time() - 3600, "/");
			//setcookie('login', null, time() - 3600);
		}

		//close connection
		curl_close($ch);
	}

	/**
	 *
	 */
	public function logout() {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $this->url . $this->path . "/logout");
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		curl_setopt($ch,CURLOPT_TIMEOUT, 20);

		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE['PHPSESSID'] . ".txt");
		curl_setopt($ch, CURLOPT_COOKIEFILE, $_COOKIE['PHPSESSID'] . ".txt");
		curl_setopt($ch, CURLOPT_HEADER, 1);

		//execute post
		curl_exec($ch);

		//close connection
		curl_close($ch);

		unset($_COOKIE['login']);
		setcookie('login', '', time() - 3600, "/");
		//setcookie('login', '', time() - 3600);
		//session_destroy();
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn() {
		if (isset($_COOKIE['login']) || $this->login) {
			$userAgent = $_SERVER['HTTP_USER_AGENT'];

			//open connection
			$ch = curl_init();

			$login = $this->login ? $this->login : $_COOKIE['login'];
			$this->fields_string = http_build_query(array("login" => $login));

			session_write_close();

			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $this->url . $this->path . "/test");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->fields_string);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);

			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE['PHPSESSID'] . ".txt");
			curl_setopt($ch, CURLOPT_COOKIEFILE, $_COOKIE['PHPSESSID'] . ".txt");

			//execute post
			$result = curl_exec($ch);

			//close connection
			curl_close($ch);

			//var_dump($this->cookies);
			//var_dump($result);

			return $result === "not logged" ? false : true;
		} else {
			return false;
		}
	}
}