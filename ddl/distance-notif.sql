use pnp_forum_0420;

SELECT u.user_id, u.username, u.user_email, u.user_notify_type,	t.pnp_sendZip , t.pnp_recZip, 
			sendzip.lat as sendLat, sendzip.lon as sendLon, sendzip.city as sendcity, sendzip.state as sendstate, 
			reczip.lat as recLat, reczip.lon as recLon, reczip.city as reccity, reczip.state as recstate,
			p.pf_airport_id,p.pf_pilot_yn,p.pf_flying_radius,
			airports.lat as aptLat, airports.lon as aptLong,
60 * degrees(acos(sin(radians(airports.lat)) * sin(radians(sendzip.lat)) + cos(radians(airports.lat)) * cos(radians(sendzip.lat)) * cos(radians(airports.lon - sendzip.lon)))) + 
60 *  degrees(acos(sin(radians(airports.lat)) * sin(radians(reczip.lat)) + cos(radians(airports.lat)) * cos(radians(reczip.lat)) * cos(radians(airports.lon - reczip.lon)))) - 
60 *  degrees(acos(sin(radians(reczip.lat)) * sin(radians(sendzip.lat)) + cos(radians(reczip.lat)) * cos(radians(sendzip.lat)) * cos(radians(reczip.lon - sendzip.lon)))) as dist
,
fn_distance(sendzip.lat, sendzip.lon, airports.lat, airports.lon) as dist_pilot_from_send,
fn_distance(reczip.lat, reczip.lon, airports.lat, airports.lon) as dist_pilot_from_rec,
fn_distance(sendzip.lat, sendzip.lon, reczip.lat, reczip.lon) as dist_trip
			FROM    phpbb_users u,
				phpbb_profile_fields_data p,
				phpbb_topics t,
				zipcodes sendzip,
				zipcodes reczip,
				airports
			WHERE 1=1
				-- AND t.topic_id = $topic_id -- this line is for PHP prod use
				AND t.topic_id = 40269 -- this is for testing, comment out in PHP
				AND p.pf_flying_radius > 0 /* only send where flying radius, distance willing to fly, is set */
				AND p.pf_pilot_yn = 1 /* only send to pilots */
                AND airports.apt_id = p.pf_airport_id
				AND sendzip.zip = t.pnp_sendZip
				AND reczip.zip = t.pnp_recZip
				AND u.user_id = p.user_id 
                AND 60 * degrees(acos(sin(radians(airports.lat)) * sin(radians(sendzip.lat)) + cos(radians(airports.lat)) * cos(radians(sendzip.lat)) * cos(radians(airports.lon - sendzip.lon)))) + 
60 *  degrees(acos(sin(radians(airports.lat)) * sin(radians(reczip.lat)) + cos(radians(airports.lat)) * cos(radians(reczip.lat)) * cos(radians(airports.lon - reczip.lon)))) - 
60 *  degrees(acos(sin(radians(reczip.lat)) * sin(radians(sendzip.lat)) + cos(radians(reczip.lat)) * cos(radians(sendzip.lat)) * cos(radians(reczip.lon - sendzip.lon)))) < p.pf_flying_radius
				AND fn_distance(sendzip.lat, sendzip.lon, reczip.lat, reczip.lon) < 1000 /* Don't send notifs on posts more than 1000 */

