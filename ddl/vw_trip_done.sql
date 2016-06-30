CREATE VIEW vw_trip_done
AS 
select distinct topic_id, 'Done' AS `trip_status` 
from phpbb_posts
where icon_id = 12;
