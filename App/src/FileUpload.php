<?php 
    namespace Jubilant;

    
    use Jubilant\RandomString;
    use Jubilant\Database;
    
    class FileUpload {
        private string $targetDirectory;
        private int $maxFileSize;
        private array $allowedFileTypes = [];
        
        public function __construct(string $targetDirectory, array $allowedFileTypes = ["jpg", "png", "gif"], $maxFileSize = 500000) {
            if($targetDirectory != null) {
                $this->targetDirectory = $targetDirectory;
                $this->allowedFileTypes = $allowedFileTypes;
                $this->maxFileSize = $maxFileSize;
            } else {
                echo "Nincs megadva a feltöltési hely!";
            }
        }
        
        private function createUploadedFilesTable() {
            require __DIR__.'/../settings.php';
            $Database = new Database($DatabaseConnection[0], $DatabaseConnection[1], $DatabaseConnection[2], $DatabaseConnection[3]);
            $Database->connect();

            $query = "CREATE TABLE IF NOT EXISTS uploadedFiles(
                ID INT AUTO_INCREMENT PRIMARY KEY,
                customID VARCHAR(255) NOT NULL,
                fileName VARCHAR(255) NOT NULL,
                status VARCHAR(255) NOT NULL,
                uploadTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

            if(!$Database->getConnection()->query($query)) {
                return $cantCreateFileUploadsTableError;
            }

            return true;
        }

        public function upload($file, ?bool $customFileName = false, ?bool $uploadToDatabase = false, ?string $fileCustomID = null) {
            require __DIR__.'/../settings.php';
            $Database = new Database($DatabaseConnection[0], $DatabaseConnection[1], $DatabaseConnection[2], $DatabaseConnection[3]);
            $Database->connect();
            $RandomString = new RandomString();
            $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

            if($file["size"] > $this->maxFileSize) {
                echo "A fájl mérete nagyobb a megengedettnél! Maxmimum ".$this->maxFileSize."!";
                return false;
            }
            if(!in_array($fileType, $this->allowedFileTypes)) {
                echo "A fájl formátuma nem megfelelő! Csak ".implode(", ", $this->allowedFileTypes)." megengedett!";
                return false;
            }

            if($customFileName === true) {
                $targetFile = $this->targetDirectory.$RandomString->generateCustomString('',1,5).'.'.$fileType;
            } else {
                $targetFile = $this->targetDirectory.basename($file["name"]);
            }

            if($uploadToDatabase === true) {
                $this->createUploadedFilesTable();

                $fileid = strval($fileCustomID);
                $filename = strval($targetFile);

                $Database->insert("uploadedfiles",array("$fileid","$filename","true"),array("customID","fileName","status"));
            }

            if(move_uploaded_file($file["tmp_name"], $targetFile)) {
                echo "A fájl sikeresen feltöltve! ".basename($targetFile);
            } else {
                echo "A fájl feltöltése sikertelen!";
            }

            return true;
        }

    }
?>