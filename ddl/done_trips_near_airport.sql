/* 
Created 2023-10-08 to help airports quantify their usefulness in PNP rescues

Update the airport code, KBJC, (and dates if desired) and run to see trips that
are either marked done or in the done forum

*/

select trips.*, 
	prod_forum.fn_distance(trips.sendLat, trips.sendLon, apts.lat, apts.lon) as distSendingNearAirport,
    prod_forum.fn_distance(trips.recLat, trips.recLon, apts.lat, apts.lon) as distReceivingNearAirport
from prod_forum.vw_lines trips
	, prod_forum.airports apts
where 1=1
	and apts.apt_id = 'KBJC'
	and last_post BETWEEN '2020-10-01' and '2030-01-01'
	and (trip_status = 'Done' or forum_id = 8) # done trip forum is forum_id = 8
	and ( prod_forum.fn_distance(trips.sendLat, trips.sendLon, apts.lat, apts.lon) < 50
			or prod_forum.fn_distance(trips.recLat, trips.recLon, apts.lat, apts.lon) < 50)
    ;
