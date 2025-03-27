<?php
/*
 * PHP File uploading Class
 *
 * @author Desmond Evans - evans.kwachie@ucc.edu.gh
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
    public $error;
    public $success;
    public $_move;

    function __construct($_fileName, $_fileSize, $_fileTmp, $_fileType, $_fileExt, $_destination)
    {
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
        if (!in_array($this->_fileExt, $this->_extensions)) {
            return $this->error = ['error', 'Please check file extension to be: jpeg, jpg, png, gif'];
        }

        if ($this->_fileSize > 2097152) { // 2MB limit
            return $this->error = ['error', 'Please check file size'];
        }

        if (in_array($this->_fileExt, ['jpeg', 'jpg', 'png', 'gif'])) {
            return $this->compressImage($this->_fileTmp, $this->_destination, 70);
        }

        return $this->move();
    }

    public function extensions()
    {
        return $this->_extensions = ['jpeg', 'jpg', 'png', 'gif'];
    }

    public function move()
    {
        $this->_move = move_uploaded_file($this->_fileTmp, $this->_destination . $this->_fileName);

        if ($this->_move) {
            return $this->success = true;
        }

        return $this->error = ['error', 'Failed to move the uploaded file'];
    }

    /* 
     * Custom method to compress image size and 
     * upload to the server
     */
    public function compressImage($source, $destination, $quality)
    {
        // Get image info 
        $imgInfo = getimagesize($source);
        $mime = $imgInfo['mime'];

        // Create a new image from file and set the appropriate save function
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                $saveFunction = 'imagejpeg';
                $extension = '.jpg';
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                $saveFunction = 'imagepng';
                $extension = '.png';
                $quality = (int)($quality / 10); // PNG quality is 0-9
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                $saveFunction = 'imagegif';
                $extension = '.gif';
                break;
            default:
                return $this->error = ['error', 'Unsupported image format'];
        }

        // Save the image in its original format
        $filePath = $destination . $this->_fileName;
        $saved = $saveFunction($image, $filePath, $quality);

        if ($saved) {
            return $this->success = true;
        }

        return $this->error = ['error', 'Failed to compress and upload the image'];
    }
}