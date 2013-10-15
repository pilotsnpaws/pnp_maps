create view vw_volunteers
as
select date_add('1969-12-31 20:00:00', INTERVAL 
        greatest(u.user_regdate, u.user_lastvisit, u.user_lastmark, u.user_lastpost_time, u.user_last_search)
        SECOND ) as last_visit,
    DATE_FORMAT(date_add('1969-12-31 20:00:00', INTERVAL u.user_lastvisit SECOND )
        , '%a, %D %b %Y @ %h:%i%p') AS last_visit_human
        ,u.user_id, u.user_regdate, u.username, pf.pf_flying_radius, pf.pf_foster_yn, pf.pf_pilot_yn,
        pf.pf_airport_id as apt_id, a.apt_name,  pf.pf_zip_code as zip,
        coalesce(z.lat, a.lat) as lat, coalesce(z.lon, a.lon) as lon, 
        coalesce(z.city, a.city) as city, coalesce(z.state,z.state) as state
from phpbb_users u
    join phpbb_profile_fields_data pf on u.user_id = pf.user_id
    left outer join airports a on a.apt_id = pf.pf_airport_id 
    /* airport IDs are mixed case when user enters them, need to upper case occasionally, joining on UPPER is SLLLOOOW */
    left outer join zipcodes z on z.zip = pf.pf_zip_code
where 1=1
    and not (pf.pf_airport_id = '' and pf.pf_zip_code = '') /* exclude users who didnt provide airport or zip code */ 
