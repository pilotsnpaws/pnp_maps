SELECT t.forum_id, f.forum_name,  t.topic_id,
		FROM_UNIXTIME(t.topic_time) as topic_time, 
			round(( 3959 * acos( cos( radians(reczip.lat) ) 
			   * cos( radians( sendzip.lat ) ) 
			   * cos( radians(sendzip.lon) - radians(reczip.lon)) + sin(radians(reczip.lat)) 
			   * sin( radians(sendzip.lat)))) 
			,0) as trip_distance,
		t.topic_title,
		u.user_id, u.username, u.user_email, u.user_notify_type,	t.pnp_sendZip , t.pnp_recZip, 
			sendzip.lat as sendLat, sendzip.lon as sendLon, sendzip.city as sendcity, sendzip.state as sendstate, 
			reczip.lat, reczip.lon, reczip.city as reccity, reczip.state as recstate
			FROM phpbb_topics t 
				join phpbb_users u on t.topic_poster = u.user_id
				join zipcodes sendzip on sendzip.zip = t.pnp_sendZip
				join zipcodes reczip on reczip.zip = t.pnp_recZip 
				join phpbb_forums f on t.forum_id = f.forum_id
			WHERE 	1=1
				and from_unixtime(t.topic_time) > '2014-05-31'
				and ( 3959 * acos( cos( radians(reczip.lat) ) 
   * cos( radians( sendzip.lat ) ) 
   * cos( radians(sendzip.lon) - radians(reczip.lon)) + sin(radians(reczip.lat)) 
   * sin( radians(sendzip.lat)))) > 1000
order by 4 desc
-- select * from phpbb_topics

select * from phpbb_forums