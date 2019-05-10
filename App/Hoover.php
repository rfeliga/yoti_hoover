<?php
/**
 * User: Rafal Feliga
 * Date: 09/05/2019
 * Time: 06:05*/

    namespace App;

    include 'DBConnection.php';

    class Hoover
    {
        public $db_conn;
        public $json_input;
        public $json_output;
        public $input;

        /**
         * @var array
         */
        public $room_size;

        /**
         * @var array
         */
        public $dirt_locations=[];

        /**
         * @var array
        */
        public $current_position;

        /**
         * @var array
         */
        public $moves;

        /**
         * @var array
         */
        public $visited_patches = [];

        /**
         * @var array
         */
        public $cleaned_patches = [];

        public function __construct()
        {
            $this->db_conn = new DBConnection();
        }

        public function read_input_json(string $path){

            $this->json_input = file_get_contents($path);

            $this->set_input_json();
            $this->set_initial_values();
        }

        private function set_initial_values(){
            $this->room_size = $this->get_json_element('roomSize');
            $this->current_position = $this->get_json_element('coords');
            $this->dirt_locations = $this->get_json_element('patches');
            $this->moves = str_split($this->get_json_element('instructions'));

            if(!$this->db_conn->save_attempts(
                                        $this->room_size[0],
                                        $this->room_size[1],
                                        implode($this->moves),
                                        $this->current_position[0],
                                        $this->current_position[1])) {
                die("Cannot write to " . $this->db_conn->database . " table name attempts.");
            }

            if(!$this->db_conn->save_patches($this->dirt_locations)) {
                die("Cannot write to " . $this->db_conn->database . " table name patches.");
            }
        }

        private function isJson(){
            if (is_numeric($this->json_input)){
                return false;
            } else {
                json_decode($this->json_input);
                return (json_last_error() == JSON_ERROR_NONE);
            }
        }

        private function set_input_json(bool $get_assoc = true){
            if($this->isJson()) {
                $this->input = json_decode($this->json_input, $get_assoc);
            } else {
                die('There is an json syntax error please check');
            }
        }

        public function print_input_json(){
            print_r($this->input);
        }

        public function get_json_element($element_key){
            if(array_key_exists($element_key,$this->input)){
                if (is_array($this->input[$element_key])){
                    return ($this->input[$element_key]);
                } else {
                    return $this->input[$element_key];
                }
            } else {
                die($element_key.' missing from json input');
            }
        }

        public function go_north(){
            if($this->current_position[0] < $this->room_size[0]-1) {
                $this->current_position[0]++;
            }
        }

        public function go_south(){
            if($this->current_position[0] > 0) {
                $this->current_position[0]--;
            }
        }

        public function go_east(){
            if($this->current_position[1] < $this->room_size[1]-1) {
                $this->current_position[1]++;
            }
        }

        public function go_west(){
            if($this->current_position[1] > 0) {
                $this->current_position[1]--;
            }
        }

        private function is_dirt_patch(array $patch){

            foreach($this->dirt_locations as $key => $dirt) {
                if ($dirt == $patch) {
                    unset($this->dirt_locations[$key]);
                    return true;
                }
            }

            return false;
        }

        private function write_cleaned_patches(array $patch){
            $found = false;

            if ($this->is_dirt_patch($patch)) {
                if(count($this->cleaned_patches)>0){

                    foreach ($this->cleaned_patches as $cleaned) {
                        if ($cleaned == $patch) {
                            $found = true;
                            continue;
                        }
                    }

                    if (!$found) {
                        array_push($this->cleaned_patches, $patch);
                    }
                } else {
                    array_push($this->cleaned_patches, $patch);
                }

            }
        }

        private function set_final_values (){
            $output = array(
                'coords' => $this->current_position,
                'patches' => count($this->cleaned_patches)
            );

            $this->json_output = json_encode($output);
            $this->write_DB_output();
        }

        public function read_moves(){
            foreach($this->moves as $move){
                array_push($this->visited_patches, $this->current_position);
                $this->write_cleaned_patches($this->current_position);

                switch ($move){
                    case "N":
                        $this->go_north();
                        break;
                    case "S":
                        $this->go_south();
                        break;
                    case "E":
                        $this->go_east();
                        break;
                    case "W":
                        $this->go_west();
                        break;
                    default:

                }
            }
            array_push($this->visited_patches, $this->current_position);
            $this->write_cleaned_patches($this->current_position);

            $this->set_final_values();
        }

        private function write_DB_output(){
            if(!$this->db_conn->save_outputs(
                $this->current_position[0],
                $this->current_position[1],
                count($this->cleaned_patches))) {
                die("Cannot write to " . $this->db_conn->database . " table name outputs.");
            }
        }

        public function json_payload_POST($url){
            $curl = curl_init($url);

            $payload = $this->json_output;
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $payload );
            curl_setopt( $curl, CURLOPT_HTTPHEADER,  array('Content-Type:application/json', 'Accept:application/json'));
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec($curl);
            /*echo "<pre>$result</pre>";*/
            curl_close($curl);
        }

    }