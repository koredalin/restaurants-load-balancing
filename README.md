# Food Delivery Drivers Tranfers Between Restaurants

The program estimates and visualize which free restaurant drivers should be transferred to neighbors restaurants.

The aim is minimum number of restaurants with higher load.

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

- Just add a "serialize" GET parameter with value "1" to the standard API endpoint.

- http://localhost{:port}/drivers/api/index.php?serialize=1

- Please, keep in mind the folder "/serialization" should be created in advance with suitable rwx rights.

### Errors logging

The errors logging is enabled about the API requests.

- Please, keep in mind the folder "/error_logs" should be created in advance with suitable rwx rights.

### /service_mode.php file

- The file should be deleted or forbidden for production usage. It could be allowed for custom ips only.

### Repeat Balance Calculations

- DriverBalancingSimulation::repeatBalanceCalculations()

If we found that there are useless transfers (A transfer from restaurant in need.) on the first estimations block.. These restaurants are skipped from the estimations. But the estimations and transfers continue. So, at later moment there could have better possible driver transfers.

I think that this method could be helpful in higher number of restaurants. It is not helpful for our case after my tests. So, _it is switched off_ from the config file.

## Implemented functionalities

### Backend

- FoodDelivery PHP API.

- Errors logging.

- Serialization.

- PHPUnit tests.

### Front end visualization

- A block for custom settings. Default config settings are shown for drivers calculation distances.

- A map with all the restaurants and drivers (initial and moved positions).

- A report table for the restaurants needs and their appended drivers.

- Text explanation of all the drivers cascades between the restaurants.

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