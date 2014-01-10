create view vw_volunteers
as
select /* 12/26/2013 update MJG */
	date_add('1969-12-31 20:00:00', INTERVAL 
        greatest(u.user_regdate, u.user_lastvisit, u.user_lastmark, u.user_lastpost_time, u.user_last_search)
        SECOND ) as last_visit,
    DATE_FORMAT(date_add('1969-12-31 20:00:00', INTERVAL u.user_lastvisit SECOND )
        , '%a, %D %b %Y @ %h:%i%p') AS last_visit_human
        ,u.user_id, u.user_regdate, u.username, pf.pf_flying_radius, pf.pf_foster_yn, pf.pf_pilot_yn,
        pf.pf_airport_id as apt_id, a.apt_name,  pf.pf_zip_code as zip,
        coalesce(a.lat, z.lat) as lat, coalesce(a.lon, z.lon) as lon,  /* use airport location instead of zip code if available */
        coalesce(a.city, z.city) as city, coalesce(a.state,z.state) as state
from phpbb_users u
    join phpbb_profile_fields_data pf on u.user_id = pf.user_id
    left outer join airports a on a.apt_id = pf.pf_airport_id 
    /* airport IDs are mixed case when user enters them, need to upper case occasionally, joining on UPPER is SLLLOOOW */
    left outer join zipcodes z on z.zip = pf.pf_zip_code
where 1=1
    and not (pf.pf_airport_id = '' and pf.pf_zip_code = '') /* exclude users who didnt provide airport or zip code */ 
    and u.user_inactive_reason = 0 /* added 12/26/2013 to exclude inactive users */