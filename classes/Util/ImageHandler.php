<?PHP
/* ImageHandler module.
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */
class ImageHandler {

    public static function UploadExists($fileKey){
        return $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK;
    }
    public static function GetUploadedFilePath($fileKey){
        return $_FILES[$fileKey]['tmp_name'];
    }
    public static function GetUploadedFileDimensions($fileKey){
        return getimagesize(self::GetUploadedFilePath($fileKey));
    }
    public static function GetUploadedFileExtension($fileKey){
        $fileParts = pathinfo($_FILES[$fileKey]['name']);
        return $fileParts['extension'];
    }

    /**
     * @param string $fileKey
     * @param string | array $extensions
     * @param string $targetPath
     * @param string $targetName
     * @param bool $softFail
     * @return array
     * @throws Exception
     */
    public static function HandleUpload($fileKey, $extensions, $targetPath, $targetName = null, $softFail = true){
        $result = array();
		$result['status'] = false;

        if (!empty($_FILES)) {
            $tempFile = $_FILES[$fileKey]['tmp_name'];
	        if (!$tempFile){
		        return $result;
	        }

            if (!is_dir($targetPath)){
                mkdir($targetPath, 0777, true);
            }

            $targetFile = rtrim($targetPath,'/') . '/' . ($targetName? $targetName : $_FILES[$fileKey]['name']);

            // Validate the file type
            if (is_string($extensions)){
                $fileTypes = explode(",", $extensions);
            }else{
                $fileTypes = $extensions; // File extensions
            }
            $fileParts = pathinfo($_FILES[$fileKey]['name']);

            if ($extensions === null || in_array(strtolower($fileParts['extension']),$fileTypes)) {
                move_uploaded_file($tempFile,$targetFile);
                $result['status'] = true;
                $result['name'] = $_FILES[$fileKey]['name'];
            } else {
                if (!$softFail) throw new Exception("Invalid filetype");
            }
        }else{
	        if (!$softFail) throw new Exception("No files received.");
        }
        return $result;
    }

    public static function GetFileName($fileKey){
        if (!empty($_FILES)) {
            return $_FILES[$fileKey]['name'];
        }else{
            throw new Exception("Can't get file name, no files received.");
        }
    }

    /**
     * @param string $path
     * @param  bool $getDimensions
     * @param  bool $filenameOnly
     * @return array
     */
    public static function GetImageList($path, $getDimensions = true, $filenameOnly = false){
        $result = array();
        $result["images"] = array();

        foreach (glob($path) as $filename) {
            if ($filenameOnly){
                array_push($result["images"], $filename);
            }else{
                $img["path"] = $filename;
                if ($getDimensions){
                    $imgData =  getimagesize($filename);
                    $img["width"] = $imgData[0];
                    $img["height"] = $imgData[1];
                }
                array_push($result["images"], $img);
            }
        }

        return $result;
    }

    public static function RemoveImage($path){
        $result = array();

        $result['file'] = $path;

        if (file_exists($path)){
            $result['status'] = unlink($path);
        }else{
            $result['status'] = false;
        }

        return $result;
    }

	public static function GetDimensions($file){
		$type = exif_imagetype($file);
		if ($type === IMAGETYPE_GIF){
			$img = imagecreatefromgif($file);
		}else if ($type === IMAGETYPE_PNG){
			$img = imagecreatefrompng($file);
		}else if ($type === IMAGETYPE_JPEG){
			$img = imagecreatefromjpeg($file);
		}else{
			LogMessage("Unrecognized extension. Failed to get image dimensions.");
			return false;
		}

		$width = imagesx( $img );
		$height = imagesy( $img );

		return [$width, $height];
	}


    public static function CreateThumbNail($file, $ext, $targetFile, $thumbWidth, $thumbHeight, $fixedDimensions = false, $crop = false, $quality=75, $png = false){
        /// Generate thumb
        if (!$ext){
            $ext = explode(".", $file);
            if (count($ext)>1){
                $ext = $ext[count($ext)-1];
            }else{
                $ext = '';
            }
        }

        $ext = strtolower($ext);

	    if(!$ext){
		    $type = exif_imagetype($file);
		    if ($type === IMAGETYPE_GIF){
			    $ext = "gif";
		    }else if ($type === IMAGETYPE_PNG){
			    $ext = "png";
		    }else if ($type === IMAGETYPE_JPEG){
			    $ext = "jpg";
		    }
	    }

	    if ($ext=="jpg"){
            $img = imagecreatefromjpeg($file);
        }else if ($ext=="gif"){
            $img = imagecreatefromgif($file);
        }else if ($ext=="png"){
            $img = imagecreatefrompng($file);
        }else{
            LogMessage("Unrecognized extension $ext. Failed to create thumbnail.");
            return false;
        }

        // Get size
        $width = imagesx( $img );
        $height = imagesy( $img );

//        LogMessage("Original file: ". $file .".". $ext);
//        LogMessage("Original dim.: ". $width ."x". $height);

        if (!$thumbWidth) $thumbWidth = 100;
        if (!$thumbHeight) $thumbHeight = 100;

        if (
            (($height/$thumbHeight) < ($width/$thumbWidth) && !$crop) || (($height/$thumbHeight) > ($width/$thumbWidth) && $crop)
        ){
            $new_width = $thumbWidth;
            $new_height = floor( $thumbWidth * ( $height / $width ) );
        }else{
            $new_width = floor( $thumbHeight * ( $width / $height ) );
            $new_height = $thumbHeight;
        }

        // Create the thumb
        if ($fixedDimensions){
            $tmp_img = imagecreatetruecolor( $thumbWidth, $thumbHeight );
            ImageCopyResampled( $tmp_img, $img, floor(($thumbWidth-$new_width)/2), floor(($thumbHeight-$new_height)/2), 0, 0, $new_width, $new_height, $width, $height );
        }else{
            $tmp_img = imagecreatetruecolor( $new_width, $new_height );
            ImageCopyResampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
        }

	    if (file_exists($targetFile)){
		    unlink($targetFile);
	    }

        if ($png){
            imagepng($tmp_img, $targetFile, 0);
        }else{
            imagejpeg($tmp_img, $targetFile, $quality);
        }

        return true;
    }
}
?>