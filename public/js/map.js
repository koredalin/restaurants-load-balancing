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
  let restaurantMarkers = [];
  let driversByRestaurantIdMarkersInit = {};
  let driversByRestaurantIdMarkersFinal = {};

  let getRestaurantsById = function () {
    let restaurantsMap = {};
    for (const [key, restaurant] of Object.entries(restaurants)) {
      restaurantsMap[restaurant[0]] = restaurant;
    }

    return restaurantsMap;
  };

  const driversByRestaurantId = foodDeliveryData['driversByRestaurantIdInit'];
  const driversByRestaurantIdFinal = foodDeliveryData['driversByRestaurantIdFinal'];
  const driverTransfers = foodDeliveryData['driverTransfers'];
  const restaurantsById = getRestaurantsById();
  const imagesDir = '/drivers/public/images';
  const driverMarkersDir = imagesDir + '/driverMarkers';
  const restaurantMarkersDir = imagesDir + '/restaurantMarkers';

  let RestaurantIcon = L.Icon.extend({
    options: {
      shadowUrl: restaurantMarkersDir + '/restaurant-shadow.png',

      iconSize:     [20, 25],
      shadowSize:   [17, 20],
      iconAnchor:   [10.773136, 23.348732],
      shadowAnchor: [0, 18],
      popupAnchor:  [-3, -36]
    }
  });

  let DriverIcon = L.Icon.extend({
    options: {
      shadowUrl: driverMarkersDir + '/driver-shadow.png',

      iconSize:     [15, 20],
      shadowSize:   [25, 25],
      iconAnchor:   [10.773136, 23.348732],
      shadowAnchor: [15, 25],
      popupAnchor:  [-3, -36]
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

  document.getElementById('drivers_locations').onclick = function () {
    printInitDriversLocations();
  };

  document.getElementById('transferred_drivers_locations').onclick = function () {
    printFinalDriversLocations();
  };
  
  // Drivers transfers text
  let explainDriverTransfers = function () {
    let transfersDiv = document.getElementById('drivers_transfers');
    let spanFrom = document.createElement("span");
    let spanTo = document.createElement("span");
    Object.values(driverTransfers).forEach ((transfer) => {
      transfer.forEach ((singleTransfer) => {
        let p = document.createElement("p");
        let restaurantFrom = restaurantsById[singleTransfer['transferFromRId'].toString()];
        spanFrom.class = restaurantFrom[4];
        spanFrom.textContent = restaurantFrom[1];
        let restaurantTo = restaurantsById[singleTransfer['transferToRId'].toString()];
        spanTo.class = restaurantTo[4];
        spanTo.textContent = restaurantTo[1];
        let html = 'Шофьор id: ' + singleTransfer['dId'] + ' да се прехвърли от "';
        html += '<span class="'+ restaurantFrom[4] + '">' + restaurantFrom[1] + '</span>';
        html += '" към "';
        html += '<span class="'+ restaurantTo[4] + '">' + restaurantTo[1] + '</span>';
        html += '".';
        p.innerHTML = html;
        transfersDiv.append(p);
      });
    });
  };
  explainDriverTransfers();
})();