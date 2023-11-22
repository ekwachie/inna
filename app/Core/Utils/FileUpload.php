<?php
/*
 * PHP File uploading Class
 *
 * @author Desmond Evans - evans.kwachie@ucc.edu.gh http://www.iamdesmondjr.com
 * @version 1.0
 * @date July 26, 2019
 */

namespace app\Core\Utils;
class FileUpload
{

	private $_fileName;
	private $_fileSize;
	private $_fileTmp;
	private $_fileType;
	private $_fileExt;
	private $_extensions;
	private $_destination;
	public $error, $success;

	function __construct($_fileName, $_fileSize, $_fileTmp, $_fileType, $_fileExt, $_destination)
	{
		# code...
		// parent::__construct();
		$this->_fileName = $_fileName;
		$this->_fileSize = $_fileSize;
		$this->_fileTmp = $_fileTmp;
		$this->_fileType = $_fileType;
		$this->_fileExt = $_fileExt;
		$this->_destination = $_destination;
	}

	public function uploader()
	{
		$this->extensions();
		if (in_array($this->_fileExt, $this->_extensions) == false) {
			return $this->error = ['danger', 'please check file extention to be: jpeg, jpg, png, gif'];
		}
		else {
			if (empty($this->error) == true) {
				# code...
				if ($this->_fileSize <= 2097152) {
					# code...
					// $this->move();
					$this->compressImage($this->_fileTmp, $this->_destination, 70);
				//return $this->alert = true;
				}
				else {
					return $this->error = ['danger', 'please check file size'];
				}

			}
			else {
				return $this->error = ['danger', 'please check file extention to be: jpeg, jpg, png, gif'];
			}
		}
	}

	public function extensions()
	{
		return $this->_extensions = (['jpeg', 'jpg', 'png', 'gif']);
	}

	// public function move()
	// {
	// 	$this->_move = move_uploaded_file($this->_fileTmp, $this->_destination . $this->_fileName);

	// 	if ($this->_move) {
	// 		return $this->alert = true;
	// 	}
	// 	else {
	// 		return $this->alert = false;
	// 	}
	// }

	/* 
	 * Custom method to compress image size and 
	 * upload to the server
	 */
	public function compressImage($source, $destination, $quality)
	{
		// Get image info 
		$imgInfo = getimagesize($source);
		$mime = $imgInfo['mime'];

		// Create a new image from file 
		switch ($mime) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg($source);
				break;
			case 'image/png':
				$image = imagecreatefrompng($source);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($source);
				break;
			default:
				$image = imagecreatefromjpeg($source);
		}

		// Save image 
		$compressed = imagejpeg($image, $destination . $this->_fileName, $quality);

		if ($compressed) {
			return $this->success = true;
		}
		else {
			return $this->error = ['danger', 'Something went wrong uploading image'];
		}

	// Return compressed image 
	// return $destination; 
	}
}

?>