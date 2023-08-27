# Food Delivery Drivers Tranfers Between Restaurants

## Installation

1. Unpack the zip file.

2. Set a PHP v8.1 server.

_Possible options:_

- Use XAMPP, LAMP, or a similar application.

- Just establish a PHP v8.1 instance on your PC, docker container, MultiPass virtual machine.

_Example code:_

$ **cd ~/public_html**

$ **php -S localhost:8000**

3. Go to "http://localhost{:port}/drivers/public/".

- On the back end the program downloads all the needed data for printing the restaurants and the drivers location icons.

- It should loads a map with all the restaurant in Sofia.

- There are 2 buttons on the web page: "Drivers Locations" and "Transferred Drivers Locations".

- The one shows the drivers initial locations with the color of their parent restaurant.

- The one shows the drivers after their transfers between the restaurants. They have new locations and colors if they are transferred.

## Documentation

### Serialization

- Just add a "serialization" GET parameter with value "1" to the standard API endpoint.

- http://localhost{:port}/drivers/api/index.php?serialization=1

- Please, keep in mind the folder "/serialization" should be created in advance with rwx rights.

### Errors logging

The errors logging is enabled about the API requests.

- Please, keep in mind the folder "/error_logs" should be created in advance with rwx rights.

### /service_mode.php file

- The file should be deleted or forbidden for production usage. It could be allowed for custom ips only.

## Implemented functionalities

- FoodDelivery PHP API.

- Errors logging.

- Serialization.

- PHPUnit tests.

- Front end visualization.

## Used Design Patterns

- MVC

_Note:_ The view is totally separated. So, we have Model and Controller only.

- PHP API

- SOLID

## Used Technologies

- PHP v8.1

- PHPUnit v10

- JS

## Used Applications

- XAMPP v3.3.0

- paint.net

## Used Document sources

- https://www.php.net/

- https://stackoverflow.com/

- https://leafletjs.com/

## Author

- Hristo Hristov

- https://github.com/koredalin?tab=repositories