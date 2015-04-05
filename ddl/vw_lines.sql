/* this view supports create_xml.php 
it provides the topic and sending/receiving lat/lon for forum 5, the request forum
*/
alter view vw_lines
AS
select date_add('1969-12-31 20:00:00', INTERVAL t.topic_last_post_time SECOND ) as last_post,
    DATE_FORMAT(date_add('1969-12-31 20:00:00', INTERVAL t.topic_last_post_time SECOND )
        , '%a, %D %b %Y @ %h:%i%p') AS last_post_human,
    t.topic_id, t.topic_title,
    t.pnp_sendZip, z_send.lat as sendLat, z_send.lon as sendLon, t.pnp_recZip, z_rec.lat as recLat, z_rec.lon as recLon,
    concat(z_send.city,', ',z_send.state) as sendCity,
    concat(z_rec.city,', ',z_rec.state) as recCity,
    (z_send.lat - z_rec.lat) AS diffLat,
    (z_send.lon - z_rec.lon) AS diffLon
from phpbb_topics t 
    LEFT OUTER JOIN zipcodes z_send on t.pnp_sendZip = z_send.zip 
    LEFT OUTER JOIN zipcodes z_rec on t.pnp_recZip = z_rec.zip 
where forum_id = 5 
    and t.pnp_sendZip is not null
    and t.pnp_recZip is not null