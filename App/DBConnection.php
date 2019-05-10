<?php
/**
 * Created by PhpStorm.
 * User: Rafal Feliga
 * Date: 09/05/2019
 * Time: 18:20
 */

    namespace App;

    use mysqli;
    use Exception;


    class DBConnection
    {
        public $servername = "localhost";
        public $database = "hoover_yoti";
        public $username = "root";
        public $password = "root";

        public $mysqli;
        public $attempt_id;

        // Create a new connection to the MySQL database using PDO
        public function __construct(){
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                $this->mysqli = new mysqli($this->servername, $this->username, $this->password, $this->database);
                $this->mysqli->set_charset("utf8mb4");
            } catch(Exception $e) {
                error_log($e->getMessage());
                exit('Error connecting to database (' . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error);
            }

            $this->tables_exist('attempts');
            $this->tables_exist('outputs');
            $this->tables_exist('patches');
        }

        private function tables_exist($table_name){

            if($this->mysqli->query("select 1 from $table_name LIMIT 1") == FALSE)
            {
                $this->create_table($table_name);
            }
        }

        public function create_table($table_name){
            switch ($table_name) {
                case "attempts":
                    $sql = "CREATE TABLE attempts (
                                            id int(13) NOT NULL,
                                            room_height int(13) NOT NULL,
                                            room_width int(13) NOT NULL,
                                            instructions varchar(9999) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                                            start_X int(13) NOT NULL,
                                            start_Y int(13) NOT NULL
                    )";
                    break;
                case "outputs":
                    $sql = "CREATE TABLE outputs (
                                            id int(13) NOT NULL,
                                            attempt_id int(13) NOT NULL,
                                            end_X int(13) NOT NULL,
                                            end_Y int(13) NOT NULL,
                                            cleaned_patches int(13) NOT NULL
                                            )";
                    break;
                case "patches":
                    $sql = "CREATE TABLE patches (
                                            id int(13) NOT NULL,
                                            attempt_id int(13) NOT NULL,
                                            patch_X int(13) NOT NULL,
                                            patch_Y int(13) NOT NULL
                                        )";
                    break;
            }

            $this->mysqli->prepare($sql)->execute();
        }

        public function save_attempts(int $room_height, int $room_width, string $instructions, int $start_x, int $start_y){
            $statement = $this->mysqli->prepare("INSERT INTO attempts (room_height, room_width, instructions, start_X, start_Y) VALUES (?,?,?,?,?)");

            $statement->bind_param("iisii", $room_height, $room_width, $instructions, $start_x, $start_y);

            if($statement->execute()) {
                $this->attempt_id = $this->mysqli->insert_id;
                return true;
            } else {
                return false;
            }

        }

        public function save_patches(array $patches){
            foreach ($patches as $key=>$patch) {
                $statement = $this->mysqli->prepare("INSERT INTO patches (attempt_id, patch_X, patch_Y) VALUES (?,?,?)");
                $statement->bind_param("iii", $this->attempt_id, $patch[0], $patch[1]);

                if ($statement->execute()) {

                } else {
                    return false;
                }
            }

            return true;

        }

        public function save_outputs(int $end_x, int $end_y, int $cleaned_patches){
            $statement = $this->mysqli->prepare("INSERT INTO outputs (attempt_id, end_X, end_Y, cleaned_patches) VALUES (?,?,?,?)");

            $statement->bind_param("iiii", $this->attempt_id, $end_x, $end_y, $cleaned_patches);

            if($statement->execute()) {
                return true;
            } else {
                return false;
            }

        }


    }