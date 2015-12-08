<?php
namespace Gilbitron\Util;

/*
 * SimpleCache v1.4.1
 *
 * By Gilbert Pellegrom
 * http://dev7studios.com
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
class SimpleCache {

	public $s3;
	public $s3_bucket_id;

	// Path to cache folder (with trailing /)
	public $cache_path = 'cache/';
	// Length of time to cache a file (in seconds)
	public $cache_time = 3600;
	// Cache file extension
	public $cache_extension = '.cache';

	function __construct($s3, $s3_bucket_id) {
		$this->s3 = $s3;
		$this->s3_bucket_id = $s3_bucket_id;
	}

	// This is just a functionality wrapper function
	public function get_data($label, $url)
	{
		if($data = $this->get_cache($label)){
			return $data;
		} else {
			$data = $this->do_curl($url);
			$this->set_cache($label, $data);
			return $data;
		}
	}

	public function set_cache($label, $data)
	{
		$filename = $this->cache_path . $this->safe_filename($label) . $this->cache_extension;

		try {
			// Upload a file.
			$result = $this->s3->putObject(array(
			    'Bucket'       => $this->s3_bucket_id,
			    'Key' 				=> $filename,
			    'Body'   				=> $data,
			    'ContentType'  => 'text/plain',
			    'ACL'          => 'public-read',
			    'StorageClass' => 'REDUCED_REDUNDANCY',
			));
			echo $result['ObjectURL'];

		} catch (Exception $e) {
		    echo $e->getMessage() . "\n";
		}
	}

	public function get_cache($label)
	{
		if($this->is_cached($label)){

			$filename = $this->cache_path . $this->safe_filename($label) . $this->cache_extension;

			$result = $this->s3->getObject(array(
			    'Bucket' => $this->s3_bucket_id,
			    'Key'    => $filename
			));

			$last_modified_timestamp = strtotime($result['LastModified']);

			if($last_modified_timestamp + $this->cache_time >= time()):
 				return $result['Body'];
			endif;
		}

		return false;
	}

	public function is_cached($label)
	{
		$filename = $this->cache_path . $this->safe_filename($label) . $this->cache_extension;

		$file_exists = $this->s3->doesObjectExist($this->s3_bucket_id, $filename);

		if($file_exists):
			return true;
		endif;

		return false;
	}

	//Helper function for retrieving data from url
	public function do_curl($url)
	{
		if(function_exists("curl_init")){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$content = curl_exec($ch);
			curl_close($ch);
			return $content;
		} else {
			return file_get_contents($url);
		}
	}

	//Helper function to validate filenames
	private function safe_filename($filename)
	{
		return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
	}
}
