async function getConfig() {
  try {
    const response = await fetch("/drivers/api/config.php");
    if (!response.ok) {
      throw new Error("Network response was not OK");
    }

    return await response.json();
  } catch (error) {
    console.error("There has been a problem with your fetch operation:", error);
  }
};

function fetchApi() {
  return getConfig();
}

(async() => {
  let config = await fetchApi();
    let restaurantDriversRadiusInMeters = document.getElementById('restaurantDriversRadiusInMeters');
    restaurantDriversRadiusInMeters.placeholder = 'По подразбиране: ' + config['restaurantDriversRadiusInMeters'];
    let driverMaxTransferDistanceInMeters = document.getElementById('driverMaxTransferDistanceInMeters');
    driverMaxTransferDistanceInMeters.placeholder = 'По подразбиране: ' + config['driverMaxTransferDistanceInMeters'];
})();