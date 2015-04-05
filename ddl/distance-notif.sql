SELECT u.user_id, u.username, u.user_email, u.user_notify_type,	t.pnp_sendZip , t.pnp_recZip, 
			sendzip.lat as sendLat, sendzip.lon as sendLon, sendzip.city as sendcity, sendzip.state as sendstate, 
			reczip.lat, reczip.lon, reczip.city as reccity, reczip.state as recstate,
			p.pf_airport_id,p.pf_pilot_yn,p.pf_flying_radius,
			airports.lat as aptLat, airports.lon as aptLong,
60 * degrees(acos(sin(radians(airports.lat)) * sin(radians(sendzip.lat)) + cos(radians(airports.lat)) * cos(radians(sendzip.lat)) * cos(radians(airports.lon - sendzip.lon)))) + 
60 *  degrees(acos(sin(radians(airports.lat)) * sin(radians(reczip.lat)) + cos(radians(airports.lat)) * cos(radians(reczip.lat)) * cos(radians(airports.lon - reczip.lon)))) - 
60 *  degrees(acos(sin(radians(reczip.lat)) * sin(radians(sendzip.lat)) + cos(radians(reczip.lat)) * cos(radians(sendzip.lat)) * cos(radians(reczip.lon - sendzip.lon)))) as dist
,  ( 3959 * acos( cos( radians(reczip.lat) ) 
   * cos( radians( sendzip.lat ) ) 
   * cos( radians(sendzip.lon) - radians(reczip.lon)) + sin(radians(reczip.lat)) 
   * sin( radians(sendzip.lat)))) AS distance 

			FROM    phpbb_users u,
				phpbb_profile_fields_data p,
				phpbb_topics t,
				zipcodes sendzip,
				zipcodes reczip,
				airports
			WHERE 	t.topic_id = 28800 and
				p.pf_flying_radius > 0 and 
				p.pf_pilot_yn =1 and airports.apt_id = p.pf_airport_id and 
				sendzip.zip = t.pnp_sendZip and
				reczip.zip = t.pnp_recZip and
				u.user_id = p.user_id and
				60 * degrees(acos(sin(radians(airports.lat)) * sin(radians(sendzip.lat)) + cos(radians(airports.lat)) * cos(radians(sendzip.lat)) * cos(radians(airports.lon - sendzip.lon)))) + 
60 *  degrees(acos(sin(radians(airports.lat)) * sin(radians(reczip.lat)) + cos(radians(airports.lat)) * cos(radians(reczip.lat)) * cos(radians(airports.lon - reczip.lon)))) - 
60 *  degrees(acos(sin(radians(reczip.lat)) * sin(radians(sendzip.lat)) + cos(radians(reczip.lat)) * cos(radians(sendzip.lat)) * cos(radians(reczip.lon - sendzip.lon)))) < p.pf_flying_radius
and 
( 3959 * acos( cos( radians(reczip.lat) ) 
   * cos( radians( sendzip.lat ) ) 
   * cos( radians(sendzip.lon) - radians(reczip.lon)) + sin(radians(reczip.lat)) 
   * sin( radians(sendzip.lat)))) < 1000
