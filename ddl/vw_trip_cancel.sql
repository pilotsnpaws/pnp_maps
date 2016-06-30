CREATE VIEW vw_trip_cancel 
AS
select distinct topic_id, 'Cancelled' AS `trip_status` 
from phpbb_posts 
where (icon_id = 11);
