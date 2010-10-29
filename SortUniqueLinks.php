<?php
error_reporting(E_ALL | E_STRICT);

/**
 * Sorts unique and duplicate links and
 * write the results to files
 *
 */
class SortUniqueLinks {

	private static $argv = array();
	private static $argc = 0;
	private static $printUsage = "\nArguments: \n========== \n -f {link filename - required} \n";

	public function __construct($argv = array(), $argc = 0) {
		self::$argv = $argv;
		self::$argc = $argc;

		$this->validatePassedParameters();
		$this->setPassedParameters();
	}

	public function __set($name, $value) {
		$this->$name = $value;
	}

	public function __get($name) {
		if(isset($this->$name))
			return $this->$name;

		return null;
	}

	/**
	 * Sorts unique and duplicate links and write them to files
	 *
	 * @return String
	 */
	public function execute() {
		$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->file;
		$links = file($file);

		$linkDomains = array();
		foreach($links as $link) {
			if (strpos($link, 'http') === 0) {
				$url = parse_url($link);
				$domain = $url['scheme'] . '://' . $url['host'];

				if(!in_array($domain, $linkDomains))
					$linkDomains[] = $domain;
			}
		}

		$findDuplicates = array();
		$findUniques = array();

		foreach($linkDomains as $url) {
			$duplicates = 0;

			foreach($links as $link) {
				if(strpos($link, $url) === 0) {
					$duplicates++;

					if($duplicates == 1) {
						$findUniques[] = $link;
					}

					if ($duplicates > 1) {
						$findDuplicates[] = $link;
					}
				}
			}
		}

		array_unique($findDuplicates);
		array_unique($findUniques);

		$h = fopen(dirname(__FILE__) . '/output/duplicate-links.txt', 'w');
		fwrite($h, implode("", $findDuplicates));
		fclose($h);

		$h = fopen(dirname(__FILE__) . '/output/unique-links.txt', 'w');
		fwrite($h, implode("", $findUniques));
		fclose($h);

		echo "done\n";
		exit(0);
	}

	/**
	 * Checks for parameters
	 *
	 * @return String
	 */
	private function validatePassedParameters() {
		if(self::$argc != 3) {
			echo self::$printUsage;
			exit(1);
		}
	}

	/**
	 * Set parameter values
	 *
	 * @return String
	 */
	private function setPassedParameters() {
		for ($i = 1; $i < self::$argc; $i++) {
			if(self::$argv[$i] == '-f') {
				$this->file = self::$argv[$i+1];
				echo "";
			}
		}

		if(is_null($this->file)) {
			echo self::$printUsage;
			exit(1);
		}
	}
}

$sortUniqueLinks = new SortUniqueLinks($argv, $argc);
$sortUniqueLinks->execute();

