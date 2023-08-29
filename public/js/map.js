let map = L.map('map').setView([42.67146376180638, 23.264471635109555], 12.4);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap'
}).addTo(map);

async function getFoodDeliveryData(hasCustomSettings) {
  function getUrl(hasCustomSettings) {
    let defaultUrl = '/drivers/api';
    if (!hasCustomSettings) {
      return defaultUrl;
    }

    let restaurantDriversRadiusInMeters = document.getElementById('restaurantDriversRadiusInMeters').value;
    restaurantDriversRadiusInMeters = restaurantDriversRadiusInMeters === '' || isNaN(restaurantDriversRadiusInMeters) ? 0 : parseInt(restaurantDriversRadiusInMeters, 10);
    let driverMaxTransferDistanceInMeters = document.getElementById('driverMaxTransferDistanceInMeters').value;
    driverMaxTransferDistanceInMeters = driverMaxTransferDistanceInMeters === '' || isNaN(driverMaxTransferDistanceInMeters) ? 0 : parseInt(driverMaxTransferDistanceInMeters, 10);
    let isSerialization = document.getElementById('serialize').checked;
    if (restaurantDriversRadiusInMeters <= 0 && driverMaxTransferDistanceInMeters <= 0 && !isSerialization) {
      return defaultUrl;
    }

    let url = defaultUrl + '/index.php?';
    let getParamGlue = '';
    url += restaurantDriversRadiusInMeters > 0
      ? 'restaurantDriversRadiusInMeters=' + restaurantDriversRadiusInMeters : '';
    getParamGlue = url.slice(-1) !== '?' ? '&' : '';
    url += driverMaxTransferDistanceInMeters > 0
      ? getParamGlue + 'driverMaxTransferDistanceInMeters=' + driverMaxTransferDistanceInMeters : '';
    getParamGlue = url.slice(-1) !== '?' ? '&' : '';
    url += isSerialization ? getParamGlue + 'serialize=1' : '';

    return url;
  }

  try {
    const response = await fetch(getUrl(hasCustomSettings));
    if (!response.ok) {
      throw new Error("Network response was not OK");
    }

    return await response.json();
  } catch (error) {
    console.error("There has been a problem with your fetch operation:", error);
  }
};

function fetchApi(hasCustomSettings) {
  return getFoodDeliveryData(hasCustomSettings);
}

(async() => {
  let driversByRestaurantIdMarkersInit = {};
  let driversByRestaurantIdMarkersFinal = {};

  async function print(hasCustomSettings) {
    let foodDeliveryData = await fetchApi(hasCustomSettings);

    let restaurantMarkers = [];

    document.getElementById('container').style.display = 'block';
    const restaurantsInit = foodDeliveryData['restaurantsInit'];
    const restaurantsFinal = foodDeliveryData['restaurantsFinal'];
    let driversByRestaurantId;
    let driversByRestaurantIdFinal;
    let driverIdsByRestaurantId;
    let driverIdsByRestaurantIdFinal;
    let setRestaurants = function () {
      driversByRestaurantId = {};
      driversByRestaurantIdFinal = {};
      driverIdsByRestaurantId = {};
      driverIdsByRestaurantIdFinal = {};
      for (const [restaurantId, restaurant] of Object.entries(restaurantsInit)) {
        driversByRestaurantId[restaurantId] = restaurant.drivers;
        driverIdsByRestaurantId[restaurantId] = restaurant.drivers.map(driver => driver[0]);
        driversByRestaurantIdFinal[restaurantId] = restaurantsFinal[restaurantId]['drivers'];
        driverIdsByRestaurantIdFinal[restaurantId] = driversByRestaurantIdFinal[restaurantId].map(driver => driver[0]);
      }
    };
    setRestaurants();
    const driverTransfers = foodDeliveryData['driverTransfers'];
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
    for (const [restaurantId, restaurant] of Object.entries(restaurantsInit)) {
      let restaurantColor = restaurant['markerColor'];
      let restaurantMarker = new RestaurantIcon({iconUrl: restaurantMarkersDir + '/' + restaurantColor + '-restaurant.png'});
      restaurantMarkers.push(new L.Marker([restaurant['lat'], restaurant['lng']], {icon: restaurantMarker}).bindPopup(restaurant['name']).addTo(map));
      createRestaurantDriversInitList(restaurantId, restaurantColor, driversByRestaurantId[restaurantId.toString()]);
      createRestaurantDriversFinalList(restaurantId, restaurantColor, driversByRestaurantIdFinal[restaurantId.toString()]);
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

    function deleteChild(htmlElement) {
        //e.firstElementChild can be used.
        var child = htmlElement.lastElementChild; 
        while (child) {
            htmlElement.removeChild(child);
            child = htmlElement.lastElementChild;
        }
    }

    let drawReportTable = function () {
      let tBody = document.querySelector('#restaurants_table tbody');
      deleteChild(tBody);
      Object.values(restaurantsInit).forEach ((restaurantInit) => {
        let tr = document.createElement("tr");
        tBody.append(tr);
        let td = document.createElement("td");
        td.textContent = restaurantInit.id;
        tr.append(td);
        td = document.createElement("td");
        td.textContent = restaurantInit.name;
        tr.append(td);
        td = document.createElement("td");
        td.textContent = restaurantInit.orders;
        tr.append(td);
        td = document.createElement("td");
        td.textContent = restaurantInit.load;
        tr.append(td);
        td = document.createElement("td");
        td.textContent = driverIdsByRestaurantId[restaurantInit.id];
        tr.append(td);
        td = document.createElement("td");
        td.textContent = restaurantsFinal[restaurantInit.id]['load'];
        tr.append(td);
        td = document.createElement("td");
        td.textContent = driverIdsByRestaurantIdFinal[restaurantInit.id];
        tr.append(td);
      });
    };

    // Drivers transfers text
    let explainDriverTransfers = function () {
      let transfersDiv = document.getElementById('drivers_transfers');
      deleteChild(transfersDiv);
      let spanFrom = document.createElement("span");
      let spanTo = document.createElement("span");
      let cascadesCount = 0;
      Object.values(driverTransfers).forEach ((transfer) => {
        let pCascade = document.createElement("p");
        pCascade.innerHTML = '<strong>Каскада номер: ' + ++cascadesCount + '</strong>';
        transfersDiv.append(pCascade);
        transfer.forEach ((singleTransfer) => {
          let pSingleTransfer = document.createElement("p");
          let restaurantFrom = restaurantsInit[singleTransfer['transferFromRId'].toString()];
          spanFrom.class = restaurantFrom[4];
          spanFrom.textContent = restaurantFrom[1];
          let restaurantTo = restaurantsInit[singleTransfer['transferToRId'].toString()];
          spanTo.class = restaurantTo[4];
          spanTo.textContent = restaurantTo[1];
          let html = 'Шофьор id: ' + singleTransfer['dId'] + ' да се прехвърли от "';
          html += '<span class="'+ restaurantFrom['markerColor'] + '">' + restaurantFrom['name'] + '</span>';
          html += '" към "';
          html += '<span class="'+ restaurantTo['markerColor'] + '">' + restaurantTo['name'] + '</span>';
          html += '".';
          pSingleTransfer.innerHTML = html;
          transfersDiv.append(pSingleTransfer);
        });
      });
    };

    drawReportTable();
    explainDriverTransfers();
  }

  function clearDriversIcons() {
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
  }

  document.getElementById('submit_settings').onclick = function () {
    clearDriversIcons();
    print(true);
  };

  document.getElementById('default_settings').onclick = function () {
    clearDriversIcons();
    print(false);
  };
})();