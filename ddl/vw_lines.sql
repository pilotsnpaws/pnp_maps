/* this view supports create_xml.php 
it provides the topic and sending/receiving lat/lon for forum 5, the request forum
*/
create view vw_lines
AS
select date_add('1970-01-01', INTERVAL t.topic_last_post_time SECOND ) as last_post,
    t.topic_id, t.topic_title,
    t.pnp_sendZip, z_send.lat as sendLat, z_send.lon as sendLon, t.pnp_recZip, z_rec.lat as recLat, z_rec.lon as recLon
from phpbb_topics t 
    LEFT OUTER JOIN zipcodes z_send on t.pnp_sendZip = z_send.zip 
    LEFT OUTER JOIN zipcodes z_rec on t.pnp_recZip = z_rec.zip 
where forum_id = 5 
    and t.pnp_sendZip is not null
    and t.pnp_recZip is not null