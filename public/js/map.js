var map = L.map('map').setView([42.67146376180638, 23.264471635109555], 12.4);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap'
}).addTo(map);

async function getFoodDeliveryData() {
  const response = await fetch("/drivers/api");
  return await response.json();
};

function fetchApi() {
  return getFoodDeliveryData();
}

(async() => {
  var foodDeliveryData = await fetchApi();

  console.log(foodDeliveryData);

  var restaurants = foodDeliveryData['restaurants'];
  var driversByRestaurantId = foodDeliveryData['driversByRestaurantIdInit'];
  var driversByRestaurantIdFinal = foodDeliveryData['driversByRestaurantIdFinal'];
  var restaurantMarkers = [];
  var driversByRestaurantIdMarkersInit = {};
  var driversByRestaurantIdMarkersFinal = {};

  const imagesDir = '/drivers/public/images';
  const driverMarkersDir = imagesDir + '/driverMarkers';
  const restaurantMarkersDir = imagesDir + '/restaurantMarkers';

  var RestaurantIcon = L.Icon.extend({
    options: {
      shadowUrl: restaurantMarkersDir + '/restaurant-shadow.png',

      iconSize:     [20, 25], // size of the icon
      shadowSize:   [17, 20], // size of the shadow
      iconAnchor:   [10.773136, 23.348732], // point of the icon which will correspond to marker's location
      shadowAnchor: [0, 18],  // the same for the shadow
      popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    }
  });

  var DriverIcon = L.Icon.extend({
    options: {
      shadowUrl: driverMarkersDir + '/driver-shadow.png',

      iconSize:     [15, 20], // size of the icon
      shadowSize:   [25, 25], // size of the shadow
      iconAnchor:   [10.773136, 23.348732], // point of the icon which will correspond to marker's location
      shadowAnchor: [15, 25],  // the same for the shadow
      popupAnchor:  [-3, -36] // point from which the popup should open relative to the iconAnchor
    }
  });

  var createRestaurantDriversInitList = function (restaurantId, restaurantColor, drivers) {
    let driverMarker = new DriverIcon({iconUrl: driverMarkersDir + '/' + restaurantColor + '-driver.png'});
    driversByRestaurantIdMarkersInit[restaurantId.toString()] = [];
    drivers.forEach (driver => {
      driversByRestaurantIdMarkersInit[restaurantId.toString()].push(new L.Marker([driver[1], driver[2]], {icon: driverMarker}).bindPopup('driverId: ' + driver[0]));
    });
  };

  var createRestaurantDriversFinalList = function (restaurantId, restaurantColor, drivers) {
    let driverMarker = new DriverIcon({iconUrl: driverMarkersDir + '/' + restaurantColor + '-driver.png'});
    driversByRestaurantIdMarkersFinal[restaurantId.toString()] = [];
    drivers.forEach (driver => {
      driversByRestaurantIdMarkersFinal[restaurantId.toString()].push(new L.Marker([driver[1], driver[2]], {icon: driverMarker}).bindPopup('driverId: ' + driver[0]));
    });
  };

  driversByRestaurantIdMarkersInit = {};
  driversByRestaurantIdMarkersFinal = {};
  restaurants.forEach ((restaurant) => {
    let restaurantId = restaurant[0];
    let restaurantColor = restaurant[4];
    let restaurantMarker = new RestaurantIcon({iconUrl: restaurantMarkersDir + '/' + restaurantColor + '-restaurant.png'});
    restaurantMarkers.push(new L.Marker([restaurant[2], restaurant[3]], {icon: restaurantMarker}).bindPopup(restaurant[1]).addTo(map));
    createRestaurantDriversInitList(restaurantId, restaurantColor, driversByRestaurantId[restaurantId.toString()]);
    createRestaurantDriversFinalList(restaurantId, restaurantColor, driversByRestaurantIdFinal[restaurantId.toString()]);
  });


  var clearDriversIcons = function () {
    Object.values(driversByRestaurantIdMarkersInit).forEach ((restaurantDriversMarkers) => {
      restaurantDriversMarkers.forEach((restaurantDriverMarker) => {
        map.removeLayer(restaurantDriverMarker);
      });
    });
    Object.values(driversByRestaurantIdMarkersFinal).forEach ((restaurantDriversMarkers) => {
      restaurantDriversMarkers.forEach((restaurantDriverMarker) => {
        map.removeLayer(restaurantDriverMarker);
      });
    });
  };

  var printInitDriversLocations = function () {
    clearDriversIcons();
    Object.values(driversByRestaurantIdMarkersInit).forEach ((restaurantDriversMarkers) => {
      restaurantDriversMarkers.forEach((restaurantDriverMarker) => {
        map.addLayer(restaurantDriverMarker);
      });
    });
  };

  var printFinalDriversLocations = function () {
    clearDriversIcons();
    Object.values(driversByRestaurantIdMarkersFinal).forEach ((restaurantDriversMarkers) => {
      restaurantDriversMarkers.forEach((restaurantDriverMarker) => {
        map.addLayer(restaurantDriverMarker);
      });
    });
  };

  document.getElementById('drivers').onclick = function () {
    printInitDriversLocations();
  };

  document.getElementById('transferred_drivers').onclick = function () {
    printFinalDriversLocations();
  };
})();