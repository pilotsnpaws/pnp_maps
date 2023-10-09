/* 
Created 2023-10-08 to help airports quantify their usefulness in PNP rescues

Update the airport code, KBJC, (and dates if desired) and run to see trips that
are either marked done or in the done forum

*/

SELECT
  trips.*,
  prod_forum.fn_distance (trips.sendLat, trips.sendLon, apts.lat, apts.lon) AS distSendingNearAirport,
  prod_forum.fn_distance (trips.recLat, trips.recLon, apts.lat, apts.lon) AS distReceivingNearAirport
FROM
  prod_forum.vw_lines_done trips,
  prod_forum.airports apts
WHERE
  1 = 1
  AND apts.apt_id = 'KBJC'
  AND last_post BETWEEN '2013-01-01' AND '2030-01-01'
  AND trip_status = 'Done'
  AND (
    prod_forum.fn_distance (trips.sendLat, trips.sendLon, apts.lat, apts.lon) < 50
    OR prod_forum.fn_distance (trips.recLat, trips.recLon, apts.lat, apts.lon) < 50
  ) LIMIT 5000;
