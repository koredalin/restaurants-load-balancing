let map = L.map('map').setView([42.67146376180638, 23.264471635109555], 12.4);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap'
}).addTo(map);

async function getFoodDeliveryData() {
  try {
    const response = await fetch("/drivers/api");
    if (!response.ok) {
      throw new Error("Network response was not OK");
    }

    return await response.json();
  } catch (error) {
    console.error("There has been a problem with your fetch operation:", error);
  }
};

function fetchApi() {
  return getFoodDeliveryData();
}

(async() => {
  let foodDeliveryData = await fetchApi();

  let restaurants = foodDeliveryData['restaurants'];
  let driversByRestaurantId = foodDeliveryData['driversByRestaurantIdInit'];
  let driversByRestaurantIdFinal = foodDeliveryData['driversByRestaurantIdFinal'];
  let restaurantMarkers = [];
  let driversByRestaurantIdMarkersInit = {};
  let driversByRestaurantIdMarkersFinal = {};

  const imagesDir = '/drivers/public/images';
  const driverMarkersDir = imagesDir + '/driverMarkers';
  const restaurantMarkersDir = imagesDir + '/restaurantMarkers';

  let RestaurantIcon = L.Icon.extend({
    options: {
      shadowUrl: restaurantMarkersDir + '/restaurant-shadow.png',

      iconSize:     [20, 25], // size of the icon
      shadowSize:   [17, 20], // size of the shadow
      iconAnchor:   [10.773136, 23.348732], // point of the icon which will correspond to marker's location
      shadowAnchor: [0, 18],  // the same for the shadow
      popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    }
  });

  let DriverIcon = L.Icon.extend({
    options: {
      shadowUrl: driverMarkersDir + '/driver-shadow.png',

      iconSize:     [15, 20], // size of the icon
      shadowSize:   [25, 25], // size of the shadow
      iconAnchor:   [10.773136, 23.348732], // point of the icon which will correspond to marker's location
      shadowAnchor: [15, 25],  // the same for the shadow
      popupAnchor:  [-3, -36] // point from which the popup should open relative to the iconAnchor
    }
  });

  let createRestaurantDriversInitList = function (restaurantId, restaurantColor, drivers) {
    let driverMarker = new DriverIcon({iconUrl: driverMarkersDir + '/' + restaurantColor + '-driver.png'});
    driversByRestaurantIdMarkersInit[restaurantId.toString()] = [];
    drivers.forEach (driver => {
      driversByRestaurantIdMarkersInit[restaurantId.toString()].push(new L.Marker([driver[1], driver[2]], {icon: driverMarker}).bindPopup('driverId: ' + driver[0]));
    });
  };

  let createRestaurantDriversFinalList = function (restaurantId, restaurantColor, drivers) {
    let driverMarker = new DriverIcon({iconUrl: driverMarkersDir + '/' + restaurantColor + '-driver.png'});
    driversByRestaurantIdMarkersFinal[restaurantId.toString()] = [];
    let transferDriversCount = 0;
    const transferredDriverLatDecrement = 0.0002;
    const transferredDriverLngIncrement = 0.00002;
    drivers.forEach (driver => {
      let driverLat = driver[3] ? driver[1] - (++transferDriversCount * transferredDriverLatDecrement) : driver[1];
      driversByRestaurantIdMarkersFinal[restaurantId.toString()].push(new L.Marker([driverLat, driver[2] + transferredDriverLngIncrement], {icon: driverMarker}).bindPopup('driverId: ' + driver[0]));
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


  let clearDriversIcons = function () {
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

  let printInitDriversLocations = function () {
    clearDriversIcons();
    Object.values(driversByRestaurantIdMarkersInit).forEach ((restaurantDriversMarkers) => {
      restaurantDriversMarkers.forEach((restaurantDriverMarker) => {
        map.addLayer(restaurantDriverMarker);
      });
    });
  };

  let printFinalDriversLocations = function () {
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