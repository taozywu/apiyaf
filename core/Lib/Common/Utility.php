<?php
/**
 * 通用函数封装
 *
 */
namespace Common;

class Utility {

	public static function microtime() {
		return intval(microtime(true) * 1000);
	}

	public static function arrayOrderBy() {
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row) {
					$tmp[$key] = $row[$field];
				}

				$args[$n] = $tmp;
			}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}

	/**
	 * @desc:按照字符编码截取汉字，中文按两个宽度，英文一个宽度
	 */
	public static function utfSubstr($str, $position, $length, $type = 0) {
		$startPos = strlen($str);
		$startByte = 0;
		$endPos = strlen($str);
		$count = 0;
		for ($i = 0, $len = strlen($str); $i < $len; $i++) {
			if ($count >= $position && $startPos > $i) {
				$startPos = $i;
				$startByte = $count;
			}
			if (($count - $startByte) >= $length) {
				$endPos = $i;
				break;
			}
			$value = ord($str[$i]);
			if ($value > 127) {
				$count++;
				if ($value >= 192 && $value <= 223) {
					$i++;
				} elseif ($value >= 224 && $value <= 239) {
					$i = $i + 2;
				} elseif ($value >= 240 && $value <= 247) {
					$i = $i + 3;
				} else {
					//logger
				}
				//else return self::raiseError("\"$str\" Not a UTF-8 compatible string", 0, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			$count++;
		}
		if ($type == 1 && ($endPos - 6) > $length) {
			return substr($str, $startPos, $endPos - $startPos) . "...";
		} else {
			return substr($str, $startPos, $endPos - $startPos);
		}
	}

	public static function utf8_strlen($str) {
		$count = 0;
		for ($i = 0; $i < strlen($str); $i++) {
			$value = ord($str[$i]);
			if ($value > 127) {
				$count++;
				if ($value >= 192 && $value <= 223) {
					$i++;
				} elseif ($value >= 224 && $value <= 239) {
					$i = $i + 2;
				} elseif ($value >= 240 && $value <= 247) {
					$i = $i + 3;
				} else {
					die('Not a UTF-8 compatible string');
				}

			}
			$count++;
		}
		return $count;
	}

	public static function utf8Strlen($string) {
		// 将字符串分解为单元
		preg_match_all("/./us", $string, $match);
		// 返回单元个数
		return count($match[0]);
	}

	public static function utf8Substr($string, $length) {
		// 将字符串分解为单元
		preg_match_all("/./us", $string, $match);
		// 返回单元个数
		return join('', array_slice($match[0], 0, $length));
	}

	public static function debug($msg) {
		if ($_SERVER['env'] != 'product') {
			Logger::getInstance(Yaf_Registry::get('config')->application->logdir, Logger::DEBUG, 'debug')->logDEBUG($msg);
		}
	}

	public static function log($name, $msg) {
		Logger::getInstance(Yaf_Registry::get('config')->application->logdir, Logger::DEBUG, $name)->logInfo($msg);
	}

	public static function errorlog($msg) {
		Logger::getInstance(Yaf_Registry::get('config')->application->logdir, Logger::ERR, 'error')->logError($msg);
	}

	public static function logError($errno, $errstr, $errfile, $errline) {
		self::logException(new ErrorException($errstr, $errno, 1, $errfile, $errline));
	}

	public static function logException(Exception $e) {
		$log = sprintf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
		Utility::errorlog($log);
	}

	/**
	 * 这个内容可以不断增加
	 */
	public static function getRequestId() {
		$requestId = $_SERVER["SERVER_NAME"] . ':' . $_SERVER['uid'];
		return $requestId;
	}

	public static function setServerUid($uid, $m2) {
		if (!$uid) {
			$userInfo = UserStorageModel::getUserM2($m2);
			if ($userInfo) {
				$userInfo = json_decode($userInfo, true);
			}
			$params['uid'] = $userInfo['uid'];
		}

		$_SERVER['uid'] = $uid;
	}

	public static function hideMobile($mobile) {
		return preg_replace('/^(\d{3}).*(\d{2})$/', '\1******\2', $mobile);
	}

	public static function isMobile($mobile) {
		return preg_match('/^1(3|4|5|7|8)[0-9]{9}$/', $mobile) ? true : false;
	}

	public static function baseEncode($val, $base = 36, $chars = '0123456789abcdefghijklmnopqrstuvwxyz') {
		if (!isset($base)) {
			$base = strlen($chars);
		}

		$str = '';
		do {
			$m = bcmod($val, $base);
			$str = $chars[$m] . $str;
			$val = bcdiv(bcsub($val, $m), $base);
		} while (bccomp($val, 0) > 0);
		return $str;
	}

	public static function baseDecode($str, $base = 36, $chars = '0123456789abcdefghijklmnopqrstuvwxyz') {
		if (!isset($base)) {
			$base = strlen($chars);
		}

		$len = strlen($str);
		$val = 0;
		$arr = array_flip(str_split($chars));
		for ($i = 0; $i < $len; ++$i) {
			$val = bcadd($val, bcmul($arr[$str[$i]], bcpow($base, $len - $i - 1)));
		}
		return $val;
	}

	/**
	 * 替换说明文本中变量
	 *
	 * @param String $text 文本字符串
	 * @param Array $data map数组
	 *
	 * @return String 替换后字符串
	 */
	public static function replaceText($text, $data) {
		return str_replace(array_keys($data), array_values($data), $text);
	}

	public static function isUnit() {
		return $_REQUEST['unit'] || $_SERVER['phpunit'] ? true : false;
	}

	public static function encodeContent($content, $type) {
		if ($type == Constants::PUSH_TYPE_QUESTION || $content == Msg::TEXT_ADAPTER_QUESTION) {
			$content = '[真心话]';
		} elseif ($type == Constants::PUSH_TYPE_IMAGE) {
			$content = '[图片]';
		} elseif ($type == Constants::PUSH_TYPE_CIRCLE) {
			$content = base64_decode($content);
		} else {
			$content = $content;
		}

		return $content;
	}

	public static function filterSms($content) {
		preg_match_all('/(\s|\w|[\\p{P}+~$`^=|<>～｀＄＾＋＝｜＜＞￥×])+/u', $content, $matches);
		$content = join('', $matches[0]);
		return trim($content);
	}

	public static function checkKeyword($content) {
		$keyword_level_1 = file_get_contents(APPLICATION_PATH . '/conf/keyword_level_1.txt');
		$keyword_level_2 = file_get_contents(APPLICATION_PATH . '/conf/keyword_level_2.txt');
		$arr1 = explode("|", $keyword_level_1);
		$arr2 = explode("|", $keyword_level_2);

		for ($i = 0, $count = count($arr1); $i < $count; $i++) {
			if ($arr1[$i] == '') {
				continue;
			}

			if (strpos($content, trim($arr1[$i])) !== false) {
				return true;
			}
		}

		for ($i = 0, $count = count($arr2); $i < $count; $i++) {
			if ($arr2[$i] == '') {
				continue;
			}

			if (strpos($content, trim($arr2[$i])) !== false) {
				return $arr2[$i];
			}
		}

		return false;
	}

	public static function adapterQuestion($content, $v, $type) {
		//1030版本新增了真心话类型
		if ($v < 1030 && $type == 3) {
			$content = Msg::TEXT_ADAPTER_QUESTION;
			$type = Constants::PUSH_TYPE_TEXT;
		}

		return [$content, $type];
	}

	/**
	 * 为用户分配羞小白服务id
	 */
	public static function allocateXXB($uid) {
		return Constants::XIUXIAOBAI_MB;
	}

	public static function isXXB($uid) {
		return $uid == Constants::XIUXIAOBAI_ID;
	}

	public static function getRandNickAvatar() {
		$stime = microtime(true);
		$adjs = file(APPLICATION_PATH . '/conf/nickadj.txt');
		$rand = mt_rand(0, count($adjs) - 1);
		$adj = trim($adjs[$rand]);

		$xml = simplexml_load_string(file_get_contents(APPLICATION_PATH . '/conf/userhead.xml'));
		$rand = mt_rand(0, count($xml->item) - 1);

		foreach ($xml->item as $entry) {
			$i++;
			if ($i == $rand) {
				break;
			}
		}

		$ret = [
			'nick' => $adj . (string) $entry->attributes()->nick,
			'avatar' => $xml->attributes()->url . (string) $entry->attributes()->image,
		];
		unset($adjs, $xml);
		return $ret;
	}

	public static function getRandXXBName() {
		return '自己';
	}

	/**
	 * 默认短信文案
	 */
	public static function isDefaultSms($device) {
		return $device == Constants::DEVICE_IOS && Constants::SMS_FIRST_IOS_DEFAULT
		|| $device == Constants::DEVICE_ANDROID && Constants::SMS_FIRST_ANDROID_DEFAULT;
	}

	public static function xxbOpenids() {
		if ($_SERVER['env'] === 'test') {
			return [
				'op0gds74Cwg2dqGJVEtjciaBHGCc', //伟伟
				'op0gdswrrUROhe8Gddt1Bs0vHK5M', //笑笑
				'op0gds_LsDYcCrRCVNe4ELTWDJOA', //春
			];
		} else {
			return [
				'ogIHQtx0ZKEfDs34NSMiwbzrnSFI', //伟伟
				'ogIHQt34X_9VSWStFEEHPHkn-pA4', //刘帆
				'ogIHQt2RmCz9CYjFeXicIW377dYA', //迎春
				'ogIHQt75JThf0LbXZRRUK470BkbE', //笑笑
			];
		}
	}

	public static function getPostsTypeName($type) {
		if (!$type) {
			$type = 1;
		}

		$postTypes = [
			1 => '原创的帖子',
			2 => '赞的帖子',
			3 => '评论的帖子',
			4 => '推荐的帖子',
		];

		return $postTypes[$type];
	}

	public static function getTypeName($type) {
		if (!$type) {
			$type = 1;
		}

		$types = [
			1 => '话题',
			2 => '帖子',
			3 => '广告',
		];

		return $types[$type];
	}

	public static function sendWXContent($openid, $content, $accessToken) {
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=";

		$params = [
			'touser' => $openid,
			'msgtype' => 'text',
			'text' => [
				'content' => $content,
			],
		];

		$params = json_encode($params, JSON_UNESCAPED_UNICODE);
		$header = array(
			'Content-Type: application/json;charset=utf-8',
		);

		for ($i = 0; $i < 2; $i++) {
			$ret = Network::request($url . $accessToken, "POST", $params, false, 1, $header);
			if ($ret) {
				break;
			}
		}

		return $ret;
	}
}
