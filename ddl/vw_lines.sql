/* this view supports create_xml.php 
it provides the topic and sending/receiving lat/lon for forum 5, the request forum
*/
create view vw_lines
AS
select DISTINCT date_add('1969-12-31 20:00:00', INTERVAL t.topic_last_post_time SECOND ) as last_post,
    DATE_FORMAT(date_add('1969-12-31 20:00:00', INTERVAL t.topic_last_post_time SECOND )
        , '%a, %D %b %Y @ %h:%i%p') AS last_post_human,
    t.topic_id, t.topic_title,
    t.pnp_sendZip, z_send.lat as sendLat, z_send.lon as sendLon, t.pnp_recZip, z_rec.lat as recLat, z_rec.lon as recLon,
    concat(z_send.city,', ',z_send.state) as sendCity,
    concat(z_rec.city,', ',z_rec.state) as recCity,
    (z_send.lat - z_rec.lat) AS diffLat,
    (z_send.lon - z_rec.lon) AS diffLon,
    t.icon_id, forum_id,
	COALESCE(CASE t.icon_id
				WHEN 11 THEN 'Cancelled'
				WHEN 12 THEN 'Done'
				WHEN 13 THEN 'Filled'
				END,
		vw_trip_cancel.trip_status, vw_trip_done.trip_status,
		CASE t.forum_id
			WHEN 16 THEN 'Outdated'
			WHEN 28 THEN 'Cancelled'
			WHEN  8 THEN 'Done'
			WHEN  5 THEN 'Open'
		END
		) as trip_status
from phpbb_topics t 
    LEFT OUTER JOIN zipcodes z_send on t.pnp_sendZip = z_send.zip 
    LEFT OUTER JOIN zipcodes z_rec 	on t.pnp_recZip = z_rec.zip 
    LEFT OUTER JOIN vw_trip_cancel 	on vw_trip_cancel.topic_id = t.topic_id
    LEFT OUTER JOIN vw_trip_done 	on vw_trip_done.topic_id = t.topic_id
where 1=1 
	AND t.topic_last_post_time > UNIX_TIMESTAMP(date_add(CURRENT_TIMESTAMP, INTERVAL -1 YEAR)) /* limit to last year for performance reasons */
	-- and t.forum_id = 5 
    and t.pnp_sendZip is not null
    and t.pnp_recZip is not null;

/* prereqs for vw_lines to work */

CREATE VIEW vw_trip_cancel
AS 
SELECT DISTINCT topic_id, 'Cancelled' as trip_status
						FROM phpbb_posts
						WHERE icon_id
						IN ( 11 );
                        
CREATE VIEW vw_trip_done
AS 
SELECT DISTINCT topic_id, 'Done' as trip_status
						FROM phpbb_posts
						WHERE icon_id
						IN ( 12);