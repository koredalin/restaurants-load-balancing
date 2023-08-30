const domain = 'https://score-predictor.com';
//const domain = 'http://localhost/drivers';

async function getConfig() {
  try {
    const response = await fetch(domain + "/api/config.php");
    if (!response.ok) {
      throw new Error("Network response was not OK");
    }

    return await response.json();
  } catch (error) {
    console.error("There has been a problem with your fetch operation:", error);
  }
};

function fetchApiConfig() {
  return getConfig();
}

(async() => {
  let config = await fetchApiConfig();
    let restaurantDriversRadiusInMeters = document.getElementById('restaurantDriversRadiusInMeters');
    restaurantDriversRadiusInMeters.placeholder = 'По подразбиране: ' + config['restaurantDriversRadiusInMeters'];
    let driverMaxTransferDistanceInMeters = document.getElementById('driverMaxTransferDistanceInMeters');
    driverMaxTransferDistanceInMeters.placeholder = 'По подразбиране: ' + config['driverMaxTransferDistanceInMeters'];
})();