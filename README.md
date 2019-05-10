# yoti_hoover

## Backend DB
   
   mySQL is used for a backend and data storage. 
   
   mysqli is used to prepare the statements for data input.
   
   All DB connection is managed by the DBConnection class.
   
   please set the parameters for you db system
           ```
           public $servername = "localhost";
           public $database = "hoover_yoti";
           public $username = "root";
           public $password = "root";
           ```
          

## Program

   JSON input is taken as local file in Input\input.json 
   I choosed this as there was no clear requirements for front end. 
    
   Hoover class constructur will test database connectivity.
   To get the values of input use the 
    ```$hoover->read_input_json('./Input/input.json');```
    
   To evaluate moves and get final output use:
    ```$hoover->read_moves();```
   
   If you require to send JSON output as payload use the folowing method and provide URL:
    ```hoover->json_payload_POST('localhost\yoti_hoove\index.php');```
    
   Class Hoover records all visited patches in ```$visited_patches```, all cleaned patches ```$cleaned_patches``` and the remainder or uncleaned patches is hold in the ```$dirt_locations```.
   
   If all patches have been cleaned the ```$dirt_locations``` array will be empty.
   
   Each move will change the ```$current_position``` and the history of all visited patches is kept in the ```$visited_patches``` this includes all duplicates of visited location.
    